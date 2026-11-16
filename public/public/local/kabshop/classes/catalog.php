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

namespace local_kabshop;

use context_course;
use context_system;
use moodle_url;

/**
 * Данные витрины: разделы, товары, доступ, выдача покупки.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catalog {

    /** Виды товара. */
    const KINDS = ['course', 'donation', 'service', 'external'];

    /** Валюта российской витрины. */
    const RU_CURRENCY = 'RUB';

    /**
     * Рынок товара: 'ru' — цена в рублях (российская витрина, продавец в РФ), иначе 'main'.
     *
     * @param \stdClass $product
     * @return string
     */
    public static function market(\stdClass $product): string {
        return strtoupper((string) $product->currency) === self::RU_CURRENCY ? 'ru' : 'main';
    }

    /**
     * Адрес каталога рынка.
     *
     * @param string $market
     * @return moodle_url
     */
    public static function catalog_url(string $market): moodle_url {
        return new moodle_url($market === 'ru' ? '/local/kabshop/ru.php' : '/local/kabshop/index.php');
    }

    /**
     * Парный товар на другом рынке: видимый платный товар того же курса. Из нескольких —
     * сначала из того же раздела, затем по порядку. У товаров без курса пары нет.
     *
     * @param \stdClass $product
     * @return \stdClass|null
     */
    public static function twin(\stdClass $product): ?\stdClass {
        global $DB;
        if (empty($product->courseid) || $product->kind === 'external') {
            return null;
        }
        $other = self::market($product) === 'ru' ? 'currency <> :cur' : 'currency = :cur';
        $sql = "SELECT *
                  FROM {local_kabshop_product}
                 WHERE courseid = :courseid AND id <> :id AND visible = 1 AND kind <> 'external'
                       AND (price > 0 OR customamount = 1) AND $other
              ORDER BY CASE WHEN sectionid = :sectionid THEN 0 ELSE 1 END, sortorder, id";
        $recs = $DB->get_records_sql($sql, [
            'courseid' => $product->courseid,
            'id' => $product->id,
            'cur' => self::RU_CURRENCY,
            'sectionid' => $product->sectionid,
        ], 0, 1);
        return $recs ? reset($recs) : null;
    }

    /**
     * Есть ли на рынке видимые товары.
     *
     * @param string $market
     * @return bool
     */
    public static function has_products(string $market): bool {
        foreach (self::get_sections(false, $market) as $s) {
            if ($s->products) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ссылка на витрину другого рынка. На основной витрине её нет, пока нет рублёвых товаров.
     *
     * @param string $market текущий рынок
     * @return array|null
     */
    public static function switch_link(string $market): ?array {
        $other = $market === 'ru' ? 'main' : 'ru';
        if ($other === 'ru' && !self::has_products('ru')) {
            return null;
        }
        return [
            'url' => self::catalog_url($other)->out(false),
            'label' => get_string($other === 'ru' ? 'switchtoru' : 'switchtomain', 'local_kabshop'),
        ];
    }

    /**
     * Разделы с товарами для каталога.
     *
     * @param bool $all true — включая скрытые (для управления)
     * @param string|null $market только товары этого рынка; null — все
     * @return array
     */
    public static function get_sections(bool $all = false, ?string $market = null): array {
        global $DB, $USER;
        $where = $all ? [] : ['visible' => 1];
        $sections = $DB->get_records('local_kabshop_section', $where, 'sortorder, id');
        $products = $DB->get_records('local_kabshop_product', $where, 'sortorder, id');
        // Товар с условием записи видят только прошедшие его; управляющий витриной — все.
        $viewer = ($all || has_capability('local/kabshop:manage', context_system::instance()))
            ? null : (int) ($USER->id ?? 0);
        foreach ($sections as $s) {
            $s->products = [];
        }
        foreach ($products as $p) {
            if ($market !== null && self::market($p) !== $market) {
                continue;
            }
            if ($viewer !== null && !self::prerequisite_ok($p, $viewer)) {
                continue;
            }
            if (isset($sections[$p->sectionid])) {
                $sections[$p->sectionid]->products[] = $p;
            }
        }
        return $sections;
    }

    /**
     * Товар по id.
     *
     * @param int $id
     * @param int $strictness
     * @return \stdClass|false
     */
    public static function get_product(int $id, int $strictness = MUST_EXIST) {
        global $DB;
        return $DB->get_record('local_kabshop_product', ['id' => $id], '*', $strictness);
    }

    /**
     * Бесплатный ли товар.
     *
     * @param \stdClass $product
     * @return bool
     */
    public static function is_free(\stdClass $product): bool {
        return empty($product->customamount) && (float)$product->price < 0.01;
    }

    /**
     * Цена строкой для карточки.
     *
     * @param \stdClass $product
     * @return string
     */
    public static function price_string(\stdClass $product): string {
        if ($product->kind === 'external') {
            return '';
        }
        if (self::is_free($product)) {
            return get_string('free', 'local_kabshop');
        }
        $s = \core_payment\helper::get_cost_as_string((float)$product->price, $product->currency);
        if (!empty($product->customamount)) {
            $s = get_string('pricefrom', 'local_kabshop', $s);
        }
        if (!empty($product->pricelabel)) {
            $s .= ' ' . format_string($product->pricelabel);
        } else if (!empty($product->recurmonths)) {
            $s .= ' ' . subscriptions::period_label((int) $product->recurmonths);
        }
        return $s;
    }

    /** Наибольшая сумма на выбор — защита от опечаток. */
    const MAX_AMOUNT = 100000;

    /**
     * Готовые суммы для товара с суммой на выбор: из настройки товара, не меньше минимума.
     *
     * @param \stdClass $product
     * @return float[]
     */
    public static function presets(\stdClass $product): array {
        $min = (float)$product->price;
        $out = [];
        foreach (preg_split('/[\s,;]+/', (string)($product->presets ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $v) {
            $v = round((float)str_replace(',', '.', $v), 2);
            if ($v >= $min && $v > 0 && $v <= self::MAX_AMOUNT) {
                $out[] = $v;
            }
        }
        if (!$out && $min > 0) {
            $out = [$min];
        }
        return array_values(array_unique($out));
    }

    /**
     * Запомнить выбранную покупателем сумму. Минимум — цена товара.
     *
     * @param \stdClass $product
     * @param int $userid
     * @param float $amount
     * @return int id записи
     */
    public static function create_intent(\stdClass $product, int $userid, float $amount): int {
        global $DB;
        $amount = round($amount, 2);
        if (empty($product->customamount) || $amount < (float)$product->price || $amount <= 0
                || $amount > self::MAX_AMOUNT) {
            throw new \moodle_exception('invalidamount', 'local_kabshop');
        }
        return $DB->insert_record('local_kabshop_intent', (object)[
            'productid' => $product->id,
            'userid' => $userid,
            'amount' => $amount,
            'currency' => $product->currency,
            'timecreated' => time(),
        ]);
    }

    /**
     * Выбранная сумма по id.
     *
     * @param int $id
     * @param int $strictness
     * @return \stdClass|false
     */
    public static function get_intent(int $id, int $strictness = MUST_EXIST) {
        global $DB;
        return $DB->get_record('local_kabshop_intent', ['id' => $id], '*', $strictness);
    }

    /**
     * Картинка товара: своя, иначе обложка курса.
     *
     * @param \stdClass $product
     * @return string|null
     */
    public static function image_url(\stdClass $product): ?string {
        $fs = get_file_storage();
        $files = $fs->get_area_files(context_system::instance()->id, 'local_kabshop', 'image',
            $product->id, 'filename', false);
        if ($files) {
            $f = reset($files);
            return moodle_url::make_pluginfile_url($f->get_contextid(), 'local_kabshop', 'image',
                $f->get_itemid(), $f->get_filepath(), $f->get_filename())->out(false);
        }
        global $DB;
        if ($product->courseid && ($course = $DB->get_record('course', ['id' => $product->courseid]))) {
            $url = \core_course\external\course_summary_exporter::get_course_image($course);
            if ($url) {
                return $url;
            }
        }
        return null;
    }

    /**
     * Есть ли товар у пользователя.
     *
     * @param \stdClass $product
     * @param int $userid
     * @return bool
     */
    public static function is_owned(\stdClass $product, int $userid): bool {
        global $DB;
        if (!$userid || isguestuser($userid)) {
            return false;
        }
        if ($product->courseid && $DB->record_exists('course', ['id' => $product->courseid])) {
            return is_enrolled(context_course::instance($product->courseid), $userid, '', true);
        }
        // Пожертвования и сервисы можно оплачивать повторно.
        return false;
    }

    /** @var array courseid => int[] — курсы-условия всех товаров курса, на время запроса */
    protected static $courserequirements = [];

    /**
     * ID курсов из строки вида «236, 231».
     *
     * @param string $raw
     * @return int[]
     */
    public static function parse_courseids(string $raw): array {
        $ids = array_map('intval', preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY));
        return array_values(array_unique(array_filter($ids, function(int $id): bool {
            return $id > 0;
        })));
    }

    /**
     * Курсы-условия товара: купить или записаться может только записанный на один из них.
     *
     * Условие относится к курсу товара: списки всех товаров курса объединяются, иначе часть 2
     * купили бы через парный товар на другом рынке, у которого условие забыли задать.
     *
     * @param \stdClass $product
     * @return int[]
     */
    public static function required_courses(\stdClass $product): array {
        global $DB;
        $ids = self::parse_courseids((string) ($product->requirecourses ?? ''));
        $courseid = (int) $product->courseid;
        if ($courseid) {
            if (!array_key_exists($courseid, self::$courserequirements)) {
                $all = [];
                foreach ($DB->get_fieldset_select('local_kabshop_product', 'requirecourses', 'courseid = ?',
                        [$courseid]) as $raw) {
                    $all = array_merge($all, self::parse_courseids((string) $raw));
                }
                self::$courserequirements[$courseid] = $all;
            }
            $ids = array_merge($ids, self::$courserequirements[$courseid]);
        }
        return array_values(array_diff(array_unique($ids), [$courseid]));
    }

    /**
     * Проходит ли человек условие записи товара: есть запись на любой из курсов-условий или на
     * сам курс товара — любым способом, активная или приостановленная (так же считает «Условия
     * записи» local_kabsignup). Отчисленный право теряет: Moodle удаляет запись целиком.
     *
     * @param \stdClass $product
     * @param int $userid 0 — гость
     * @return bool
     */
    public static function prerequisite_ok(\stdClass $product, int $userid): bool {
        global $DB;
        $required = self::required_courses($product);
        if (!$required) {
            return true;
        }
        if ($userid <= 0 || isguestuser($userid)) {
            return false;
        }
        if ($product->courseid) {
            $required[] = (int) $product->courseid;
        }
        [$insql, $params] = $DB->get_in_or_equal($required, SQL_PARAMS_NAMED);
        return $DB->record_exists_sql(
            "SELECT 1
               FROM {user_enrolments} ue
               JOIN {enrol} e ON e.id = ue.enrolid
              WHERE ue.userid = :userid AND e.courseid $insql",
            ['userid' => $userid] + $params);
    }

    /**
     * Названия курсов-условий для сообщений: «Часть первая», «Часть 1 (весна)».
     *
     * @param \stdClass $product
     * @return string HTML
     */
    public static function prerequisite_names(\stdClass $product): string {
        global $DB;
        $required = self::required_courses($product);
        if (!$required) {
            return '';
        }
        $names = [];
        foreach ($DB->get_records_list('course', 'id', $required, 'id', 'id, fullname') as $c) {
            $names[] = '«' . format_string($c->fullname, true, ['context' => context_course::instance($c->id)]) . '»';
        }
        return implode(', ', array_unique($names));
    }

    /**
     * Выдать товар: журнал покупки + запись в курс.
     *
     * @param \stdClass $product
     * @param int $userid
     * @param int $paymentid 0 — без оплаты (бесплатно, 100% купон, ручная выдача)
     * @param \stdClass|null $sub подписка, если это автосписание
     * @param array $opts couponid, amountoff — купон; grantedby, note, timeend, mail — ручная выдача
     * @return int номер покупки (он же номер заказа в письме)
     */
    public static function grant(\stdClass $product, int $userid, int $paymentid = 0, ?\stdClass $sub = null,
            array $opts = []): int {
        global $DB;
        $amount = 0;
        $currency = $product->currency;
        $payment = $paymentid ? $DB->get_record('payments', ['id' => $paymentid]) : null;
        if ($payment) {
            $amount = $payment->amount;
            $currency = $payment->currency;
        }
        $couponid = (int) ($opts['couponid'] ?? 0);
        $amountoff = (float) ($opts['amountoff'] ?? 0);
        $purchaseid = $DB->insert_record('local_kabshop_purchase', (object)[
            'productid' => $product->id,
            'userid' => $userid,
            'paymentid' => $paymentid,
            'amount' => $amount,
            'currency' => $currency,
            'timecreated' => time(),
            'status' => 'ok',
            'grantedby' => (int) ($opts['grantedby'] ?? 0),
            'note' => isset($opts['note']) ? \core_text::substr(trim((string) $opts['note']), 0, 250) : null,
            'couponid' => $couponid,
            'amountoff' => $amountoff,
        ]);
        if ($couponid) {
            coupons::redeem($couponid, $userid, $purchaseid, $amountoff, $currency);
        }

        // Регулярный товар: первая оплата заводит подписку; оплата, пришедшая от шлюза при уже
        // активной подписке (своё автосписание шлюза, напр. Robokassa), продлевает её.
        // Скидка по купону — только на первый платёж: подписка списывает полную цену.
        if (!$sub && $payment && !empty($product->recurmonths)) {
            $sub = subscriptions::get_active($product->id, $userid);
            $sub = $sub ? subscriptions::extend($sub, $paymentid)
                : subscriptions::create($product, $userid, $payment, $couponid ? (float) $product->price : null);
        }

        if ($product->courseid) {
            // По подписке доступ — до следующего списания плюс запас на повторы.
            $timeend = $sub ? $sub->nextcharge + subscriptions::GRACE : (int) ($opts['timeend'] ?? 0);
            self::enrol($product, $userid, $timeend);
        }
        if ($payment) {
            mailer::paid($product, $userid, $purchaseid, $payment, $sub);
        } else if (!empty($opts['mail'])) {
            mailer::granted($product, $userid, $purchaseid);
        }
        return $purchaseid;
    }

    /**
     * Отозвать доступ по заказу (например, после возврата денег через бухгалтерию):
     * снять запись на курс, остановить подписку на этот товар, пометить заказ.
     *
     * @param int $purchaseid
     * @param int $adminid
     * @param string $note
     * @return bool
     */
    public static function revoke(int $purchaseid, int $adminid, string $note = ''): bool {
        global $DB;
        $purchase = $DB->get_record('local_kabshop_purchase', ['id' => $purchaseid]);
        if (!$purchase || $purchase->status === 'revoked') {
            return false;
        }
        $product = self::get_product($purchase->productid, IGNORE_MISSING);
        if ($product && $product->courseid) {
            $instance = $DB->get_record('enrol', ['courseid' => $product->courseid, 'enrol' => 'manual'],
                '*', IGNORE_MULTIPLE);
            if ($instance) {
                enrol_get_plugin('manual')->unenrol_user($instance, $purchase->userid);
            }
        }
        if ($product && ($sub = subscriptions::get_active($product->id, $purchase->userid))) {
            $DB->update_record('local_kabshop_sub', (object) ['id' => $sub->id, 'status' => 'cancelled',
                'timemodified' => time()]);
        }
        $who = fullname(\core_user::get_user($adminid));
        $text = trim(($purchase->note ? $purchase->note . ' · ' : '') .
            get_string('revokednote', 'local_kabshop', (object) ['who' => $who, 'note' => trim($note)]));
        $DB->update_record('local_kabshop_purchase', (object) ['id' => $purchase->id, 'status' => 'revoked',
            'timerevoked' => time(), 'note' => \core_text::substr($text, 0, 250)]);
        return true;
    }

    /**
     * Запись в курс товара через ручной способ записи курса.
     *
     * @param \stdClass $product
     * @param int $userid
     * @param int $timeend конец доступа по подписке; 0 — по сроку товара или бессрочно
     */
    protected static function enrol(\stdClass $product, int $userid, int $timeend = 0): void {
        global $DB;
        $plugin = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', ['courseid' => $product->courseid, 'enrol' => 'manual'],
            '*', IGNORE_MULTIPLE);
        if (!$instance) {
            $course = get_course($product->courseid);
            $instanceid = $plugin->add_default_instance($course);
            $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
        }
        $roleid = $instance->roleid ?: $DB->get_field('role', 'id', ['shortname' => 'student']);
        $timestart = 0;
        if ($timeend > 0) {
            // Продление существующей записи меняет только конец доступа.
            $timestart = (int) $DB->get_field('user_enrolments', 'timestart',
                ['enrolid' => $instance->id, 'userid' => $userid]) ?: time();
        } else if ($product->enrolperiod) {
            $timestart = time();
            $timeend = $timestart + (int)$product->enrolperiod;
        }
        $plugin->enrol_user($instance, $userid, $roleid, $timestart, $timeend, ENROL_USER_ACTIVE);
    }

    /**
     * Контекст карточки для шаблона.
     *
     * @param \stdClass $product
     * @return array
     */
    public static function card(\stdClass $product): array {
        $context = context_system::instance();
        $url = $product->kind === 'external' && !empty($product->externalurl)
            ? $product->externalurl
            : (new moodle_url('/local/kabshop/product.php', ['id' => $product->id]))->out(false);
        // Рублёвый товар без своего «Коротко» берёт текст пары — описание ведётся в одном месте.
        $summary = (string) ($product->summary ?? '');
        if (trim($summary) === '' && self::market($product) === 'ru' && ($twin = self::twin($product))) {
            $summary = (string) ($twin->summary ?? '');
        }
        return [
            'id' => $product->id,
            'name' => format_string($product->name, true, ['context' => $context]),
            'summary' => format_text($summary, FORMAT_PLAIN, ['context' => $context, 'para' => false]),
            'price' => self::price_string($product),
            'free' => self::is_free($product) && $product->kind !== 'external',
            'image' => self::image_url($product),
            'url' => $url,
            'external' => $product->kind === 'external',
            'hidden' => empty($product->visible),
        ];
    }

    /**
     * Контекст каталога для шаблона.
     *
     * @param string $market 'main' — основная витрина, 'ru' — российская (товары в рублях)
     * @return array
     */
    public static function export_catalog(string $market = 'main'): array {
        $out = [];
        foreach (self::get_sections(false, $market) as $s) {
            if (!$s->products) {
                continue;
            }
            $out[] = [
                'id' => $s->id,
                'name' => format_string($s->name),
                'description' => format_text($s->description ?? '', FORMAT_HTML),
                'products' => array_map([self::class, 'card'], array_values($s->products)),
                'hasproducts' => true,
            ];
        }
        // Первый раздел — витринный: его первый товар рисуется крупно (герой).
        if ($out) {
            $out[0]['featured'] = true;
            $out[0]['hero'] = array_shift($out[0]['products']);
            $out[0]['hasproducts'] = !empty($out[0]['products']);
        }
        return [
            'sections' => $out,
            'shownav' => count($out) > 1,
            'canmanage' => has_capability('local/kabshop:manage', context_system::instance()),
            'manageurl' => (new moodle_url('/local/kabshop/manage.php'))->out(false),
            'isru' => $market === 'ru',
            'switch' => self::switch_link($market),
            'legal' => legal::export($market),
        ];
    }

    /**
     * Почему раздел нельзя удалить. Пусто — можно.
     *
     * Удаляется только пустой раздел: товары удалённого раздела пропали бы и с витрины, и из управления.
     *
     * @param int $id
     * @return string[]
     */
    public static function section_delete_blockers(int $id): array {
        global $DB;
        $n = $DB->count_records('local_kabshop_product', ['sectionid' => $id]);
        return $n ? [get_string('delblock_products', 'local_kabshop', $n)] : [];
    }

    /**
     * Удалить раздел. Проверку section_delete_blockers() делает вызывающий.
     *
     * @param int $id
     */
    public static function delete_section(int $id): void {
        global $DB;
        $DB->delete_records('local_kabshop_section', ['id' => $id]);
    }

    /**
     * Почему товар нельзя удалить. Пусто — можно.
     *
     * Удаляется только товар, которого не касались деньги: без покупок и ручных выдач, подписок и
     * попыток оплаты. Иначе заказы и продление подписки остались бы без товара, а платёж, дошедший
     * после удаления, — без выдачи. Купон, действующий только на этот товар, после удаления
     * стал бы купоном на все товары.
     *
     * @param int $id
     * @return string[]
     */
    public static function product_delete_blockers(int $id): array {
        global $DB;
        $p = 'local_kabshop';
        $out = [];
        if ($n = $DB->count_records('local_kabshop_purchase', ['productid' => $id])) {
            $out[] = get_string('delblock_purchases', $p, $n);
        }
        if ($n = $DB->count_records('local_kabshop_sub', ['productid' => $id])) {
            $out[] = get_string('delblock_subs', $p, $n);
        }
        // Оплата суммы на выбор и со скидкой начинается с intent, по цене товара — с платежа в зоне 'product';
        // незавершённые заказы kbb1 есть только в его таблице.
        $area = ['component' => 'local_kabshop', 'paymentarea' => 'product', 'itemid' => $id];
        $n = $DB->count_records('local_kabshop_intent', ['productid' => $id]) + $DB->count_records('payments', $area);
        if ($DB->get_manager()->table_exists('paygw_kbb1')) {
            $n += $DB->count_records('paygw_kbb1', $area);
        }
        if ($n) {
            $out[] = get_string('delblock_attempts', $p, $n);
        }
        foreach (self::coupons_with_product($id) as $c) {
            if (!array_diff($c->ids, [$id])) {
                $out[] = get_string('delblock_coupon', $p, s($c->code));
            }
        }
        return $out;
    }

    /**
     * Купоны, где товар указан в списке товаров.
     *
     * @param int $id
     * @return \stdClass[] купоны с полем ids — товары купона
     */
    public static function coupons_with_product(int $id): array {
        global $DB;
        $out = [];
        foreach ($DB->get_records('local_kabshop_coupon', null, 'code', 'id, code, productids') as $c) {
            $c->ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $c->productids)))));
            if (in_array($id, $c->ids, true)) {
                $out[] = $c;
            }
        }
        return $out;
    }

    /**
     * Удалить товар с картинкой и убрать его из купонов. Проверку product_delete_blockers() делает вызывающий.
     *
     * @param int $id
     */
    public static function delete_product(int $id): void {
        global $DB;
        $tx = $DB->start_delegated_transaction();
        foreach (self::coupons_with_product($id) as $c) {
            $DB->update_record('local_kabshop_coupon', (object) ['id' => $c->id,
                'productids' => implode(',', array_diff($c->ids, [$id])), 'timemodified' => time()]);
        }
        $DB->delete_records('local_kabshop_product', ['id' => $id]);
        $tx->allow_commit();
        get_file_storage()->delete_area_files(context_system::instance()->id, 'local_kabshop', 'image', $id);
    }
}
