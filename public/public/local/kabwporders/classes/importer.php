<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_kabwporders;

/**
 * Импорт выгрузки WordPress (orders.tsv, items.tsv, products.tsv) в реестр.
 *
 * Выгрузку делает tools/payment-archive/export.sh в приватном репозитории kabacademy.com.
 * Импорт заменяет реестр целиком в одной транзакции: повторный запуск не даёт дублей,
 * а при ошибке прежние данные остаются. Пользователей Moodle не создаёт и не правит.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class importer {

    /** Колонки orders.tsv — в том порядке, в каком их отдаёт выгрузка. */
    const ORDER_COLS = ['wp_order_id', 'wp_user_id', 'moodle_user_id', 'payer_email', 'payer_first_name',
        'payer_last_name', 'created_utc', 'paid_ts', 'status', 'total', 'currency', 'refunded', 'gateway',
        'gateway_title', 'transaction_id'];

    /** Колонки items.tsv. */
    const ITEM_COLS = ['wp_order_id', 'wp_item_id', 'wp_product_id', 'wp_variation_id', 'product_name', 'qty',
        'line_total'];

    /** Колонки products.tsv. */
    const PRODUCT_COLS = ['wp_product_id', 'kind', 'wp_parent_id', 'status', 'title', 'moodle_course_ids'];

    /** Имена файлов выгрузки (с .gz или без). */
    const FILES = ['orders', 'items', 'products'];

    /**
     * Какой из трёх файлов выгрузки это по имени: orders.tsv, items.tsv.gz…
     *
     * @param string $filename
     * @return string|null orders|items|products
     */
    public static function kind_of(string $filename): ?string {
        if (preg_match('/^(orders|items|products)\.tsv(\.gz)?$/i', trim($filename), $m)) {
            return \core_text::strtolower($m[1]);
        }
        return null;
    }

    /**
     * Файлы выгрузки в каталоге на сервере.
     *
     * @param string $dir
     * @return array [orders|items|products => путь]; неполный набор — пустой массив
     */
    public static function files_in(string $dir): array {
        $paths = [];
        $dir = rtrim($dir, '/');
        if ($dir === '' || !is_dir($dir)) {
            return [];
        }
        foreach (self::FILES as $kind) {
            foreach (['.tsv', '.tsv.gz'] as $ext) {
                if (is_readable($dir . '/' . $kind . $ext)) {
                    $paths[$kind] = $dir . '/' . $kind . $ext;
                    break;
                }
            }
        }
        return count($paths) === count(self::FILES) ? $paths : [];
    }

    /**
     * Импорт: читает три файла, сопоставляет людей, заменяет реестр.
     *
     * @param array $paths [orders => путь, items => путь, products => путь]
     * @return array итоги для сверки
     */
    public static function run(array $paths): array {
        global $DB;

        foreach (self::FILES as $kind) {
            if (empty($paths[$kind]) || !is_readable($paths[$kind])) {
                throw new \moodle_exception('err_nofile', 'local_kabwporders', '', $kind . '.tsv');
            }
        }
        \core_php_time_limit::raise(600);
        raise_memory_limit(MEMORY_EXTRA);

        // Товар → курсы Moodle.
        $courses = [];
        foreach (self::read($paths['products'], self::PRODUCT_COLS) as $r) {
            $ids = array_filter(array_map('intval', explode(',', $r['moodle_course_ids'])));
            if ($ids) {
                $courses[(int) $r['wp_product_id']] = implode(',', $ids);
            }
        }

        $now = time();
        $orders = [];
        foreach (self::read($paths['orders'], self::ORDER_COLS) as $r) {
            $id = (int) $r['wp_order_id'];
            if ($id <= 0 || isset($orders[$id])) {
                continue;
            }
            $created = strtotime($r['created_utc'] . ' UTC');
            $orders[$id] = (object) [
                'wporderid'    => $id,
                'userid'       => 0,
                'wpuserid'     => (int) $r['wp_user_id'],
                'payeremail'   => self::cut(\core_text::strtolower($r['payer_email']), 100),
                'payername'    => self::cut(trim($r['payer_first_name'] . ' ' . $r['payer_last_name']), 255),
                'timecreated'  => $created ?: 0,
                'timepaid'     => (int) $r['paid_ts'],
                'status'       => self::cut($r['status'], 20),
                'amount'       => round((float) $r['total'], 2),
                'currency'     => self::cut(\core_text::strtoupper($r['currency']), 3),
                'refunded'     => round((float) $r['refunded'], 2),
                'gateway'      => self::cut($r['gateway'], 50),
                'gatewaytitle' => self::cut($r['gateway_title'], 255),
                'txnid'        => self::cut($r['transaction_id'], 100),
                'timeimported' => $now,
                'linkid'       => (int) $r['moodle_user_id'],
            ];
        }
        if (!$orders) {
            throw new \moodle_exception('err_empty', 'local_kabwporders');
        }

        $match = self::match_users($orders);

        $items = [];
        $orphans = 0;
        $withitems = [];
        foreach (self::read($paths['items'], self::ITEM_COLS) as $r) {
            $orderid = (int) $r['wp_order_id'];
            if (!isset($orders[$orderid])) {
                $orphans++;
                continue;
            }
            $withitems[$orderid] = true;
            $productid = (int) $r['wp_product_id'];
            $variationid = (int) $r['wp_variation_id'];
            $items[] = (object) [
                'wporderid'     => $orderid,
                'wpitemid'      => (int) $r['wp_item_id'],
                'wpproductid'   => $productid,
                'wpvariationid' => $variationid,
                'productname'   => self::cut($r['product_name'], 255),
                'qty'           => max(1, (int) $r['qty']),
                'linetotal'     => round((float) $r['line_total'], 2),
                'courseids'     => $courses[$variationid] ?? ($courses[$productid] ?? ''),
            ];
        }

        foreach ($orders as $o) {
            unset($o->linkid);
        }

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records('local_kabwporders_item');
        $DB->delete_records('local_kabwporders_order');
        $DB->insert_records('local_kabwporders_order', $orders);
        $DB->insert_records('local_kabwporders_item', $items);
        $transaction->allow_commit();

        set_config('lastimport', $now, 'local_kabwporders');

        $withcourse = 0;
        foreach ($items as $i) {
            if ($i->courseids !== '') {
                $withcourse++;
            }
        }
        return [
            'orders'           => count($orders),
            'items'            => count($items),
            'itemswithcourse'  => $withcourse,
            'itemorphans'      => $orphans,
            'orderswithoutitems' => count($orders) - count($withitems),
            'products'         => count($courses),
        ] + $match;
    }

    /**
     * Проставляет userid: сначала по связке из WordPress, потом по почте.
     *
     * Связке верим, только если такой пользователь в Moodle есть и не удалён. Людей не создаём.
     *
     * @param \stdClass[] $orders заказы по номеру; у каждого linkid и payeremail
     * @return array счётчики для сверки
     */
    protected static function match_users(array $orders): array {
        global $DB, $CFG;

        $linkids = [];
        foreach ($orders as $o) {
            if ($o->linkid > 0) {
                $linkids[$o->linkid] = true;
            }
        }
        $known = [];
        foreach (array_chunk(array_keys($linkids), 1000) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED);
            $known += $DB->get_records_select_menu('user', "id $insql AND deleted = 0", $params, '', 'id, email');
        }

        $emails = [];
        foreach ($orders as $o) {
            if (($o->linkid <= 0 || !isset($known[$o->linkid])) && $o->payeremail !== '') {
                $emails[$o->payeremail] = true;
            }
        }
        $byemail = [];
        foreach (array_chunk(array_keys($emails), 500) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED);
            $params['mnet'] = $CFG->mnet_localhost_id;
            $recs = $DB->get_records_select('user', "email $insql AND deleted = 0 AND mnethostid = :mnet", $params,
                'id', 'id, email');
            foreach ($recs as $u) {
                $key = \core_text::strtolower(trim($u->email));
                $byemail[$key] = $byemail[$key] ?? (int) $u->id; // При дублях почты — самый старый аккаунт.
            }
        }

        $stats = ['bylink' => 0, 'byemail' => 0, 'unmatched' => 0, 'unmatchedemails' => 0, 'linkemaildiffers' => 0];
        $lost = [];
        foreach ($orders as $o) {
            if ($o->linkid > 0 && isset($known[$o->linkid])) {
                $o->userid = $o->linkid;
                $stats['bylink']++;
                if ($o->payeremail !== '' && \core_text::strtolower(trim($known[$o->linkid])) !== $o->payeremail) {
                    $stats['linkemaildiffers']++;
                }
            } else if ($o->payeremail !== '' && isset($byemail[$o->payeremail])) {
                $o->userid = $byemail[$o->payeremail];
                $stats['byemail']++;
            } else {
                $stats['unmatched']++;
                $lost[$o->payeremail] = true;
            }
        }
        $stats['unmatchedemails'] = count($lost);
        return $stats;
    }

    /**
     * Читает TSV из `mysql --batch` (обычный или .gz) строками-массивами по именам колонок.
     *
     * @param string $path
     * @param string[] $columns ожидаемый заголовок
     * @return \Generator
     */
    protected static function read(string $path, array $columns): \Generator {
        $h = gzopen($path, 'rb'); // Обычный файл gzopen читает как есть.
        if (!$h) {
            throw new \moodle_exception('err_nofile', 'local_kabwporders', '', basename($path));
        }
        try {
            $head = gzgets($h);
            $head = $head === false ? [] : explode("\t", rtrim($head, "\r\n"));
            if ($head !== $columns) {
                throw new \moodle_exception('err_header', 'local_kabwporders', '', basename($path));
            }
            $n = count($columns);
            while (($line = gzgets($h)) !== false) {
                $line = rtrim($line, "\r\n");
                if ($line === '') {
                    continue;
                }
                $cells = explode("\t", $line);
                if (count($cells) !== $n) {
                    throw new \moodle_exception('err_columns', 'local_kabwporders', '', basename($path));
                }
                yield array_combine($columns, array_map([self::class, 'unescape'], $cells));
            }
        } finally {
            gzclose($h);
        }
    }

    /**
     * Снимает экранирование `mysql --batch`: \t, \n, \\, \0; NULL — пустая строка.
     *
     * @param string $value
     * @return string
     */
    protected static function unescape(string $value): string {
        if ($value === 'NULL') {
            return '';
        }
        if (strpos($value, '\\') !== false) {
            $value = preg_replace_callback('/\\\\(.)/su', function ($m) {
                switch ($m[1]) {
                    case 'n':
                        return "\n";
                    case 't':
                        return "\t";
                    case 'r':
                        return "\r";
                    case '0':
                        return '';
                    default:
                        return $m[1];
                }
            }, $value) ?? $value;
        }
        return trim(fix_utf8($value));
    }

    /**
     * Обрезает строку под длину поля.
     *
     * @param string $value
     * @param int $length
     * @return string
     */
    protected static function cut(string $value, int $length): string {
        return \core_text::substr($value, 0, $length);
    }
}
