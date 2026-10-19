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

    /**
     * Разделы с товарами для каталога.
     *
     * @param bool $all true — включая скрытые (для управления)
     * @return array
     */
    public static function get_sections(bool $all = false): array {
        global $DB;
        $where = $all ? [] : ['visible' => 1];
        $sections = $DB->get_records('local_kabshop_section', $where, 'sortorder, id');
        $products = $DB->get_records('local_kabshop_product', $where, 'sortorder, id');
        foreach ($sections as $s) {
            $s->products = [];
        }
        foreach ($products as $p) {
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

    /**
     * Выдать товар: журнал покупки + запись в курс.
     *
     * @param \stdClass $product
     * @param int $userid
     * @param int $paymentid 0 — бесплатная запись
     * @return bool
     */
    public static function grant(\stdClass $product, int $userid, int $paymentid = 0): bool {
        global $DB;
        $amount = 0;
        $currency = $product->currency;
        if ($paymentid && ($payment = $DB->get_record('payments', ['id' => $paymentid]))) {
            $amount = $payment->amount;
            $currency = $payment->currency;
        }
        $DB->insert_record('local_kabshop_purchase', (object)[
            'productid' => $product->id,
            'userid' => $userid,
            'paymentid' => $paymentid,
            'amount' => $amount,
            'currency' => $currency,
            'timecreated' => time(),
        ]);
        if ($product->courseid) {
            self::enrol($product, $userid);
        }
        return true;
    }

    /**
     * Запись в курс товара через ручной способ записи курса.
     *
     * @param \stdClass $product
     * @param int $userid
     */
    protected static function enrol(\stdClass $product, int $userid): void {
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
        $timeend = 0;
        if ($product->enrolperiod) {
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
        return [
            'id' => $product->id,
            'name' => format_string($product->name, true, ['context' => $context]),
            'summary' => format_text($product->summary ?? '', FORMAT_PLAIN, ['context' => $context, 'para' => false]),
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
     * @return array
     */
    public static function export_catalog(): array {
        $out = [];
        foreach (self::get_sections() as $s) {
            if (!$s->products) {
                continue;
            }
            $out[] = [
                'id' => $s->id,
                'name' => format_string($s->name),
                'description' => format_text($s->description ?? '', FORMAT_HTML),
                'products' => array_map([self::class, 'card'], $s->products),
            ];
        }
        return [
            'sections' => $out,
            'canmanage' => has_capability('local/kabshop:manage', context_system::instance()),
            'manageurl' => (new moodle_url('/local/kabshop/manage.php'))->out(false),
        ];
    }
}
