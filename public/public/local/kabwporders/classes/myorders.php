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
 * Блок «Прежние оплаты» для самого человека: его оплаты на старом сайте.
 *
 * Показывается на «Моих заказах» кассы local_kabshop и на своей странице my.php.
 * Отдаёт только строки того пользователя, которого ему передали, — вызывающая страница
 * передаёт текущего ($USER). Номер транзакции, почту и имя плательщика не показывает.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class myorders {

    /** Статусы без пометки: оплата прошла, а возврат виден по сумме возврата. */
    const PLAIN_STATUSES = ['completed', 'processing', 'refunded'];

    /**
     * HTML блока; пустая строка, если прежних оплат у человека нет.
     *
     * @param \stdClass $user пользователь Moodle (id, email)
     * @return string
     */
    public static function render(\stdClass $user): string {
        $orders = api::records_for_user((int) ($user->id ?? 0), (string) ($user->email ?? ''));
        if (!$orders) {
            return '';
        }
        $p = 'local_kabwporders';
        $fmt = get_string('strftimedate', 'langconfig');

        $t = new \html_table();
        $t->head = [get_string('col_order', $p), get_string('my_col_date', $p), get_string('col_products', $p),
            get_string('col_amount', $p), get_string('col_gateway', $p)];
        $t->attributes['class'] = 'generaltable';
        foreach ($orders as $o) {
            $amount = self::cost((float) $o->amount, $o->currency);
            $notes = [];
            if ($o->refunded > 0) {
                $notes[] = get_string('my_refunded', $p, self::cost((float) $o->refunded, $o->currency));
            }
            if (!in_array($o->status, self::PLAIN_STATUSES, true)) {
                $key = 'my_note_' . str_replace('-', '', $o->status);
                $notes[] = get_string_manager()->string_exists($key, $p) ? get_string($key, $p) : s($o->status);
            }
            $t->data[] = [
                (int) $o->wporderid,
                userdate($o->timepaid ?: $o->timecreated, $fmt),
                s(api::products($o)),
                $amount . ($notes ? ' · ' . implode(' · ', $notes) : ''),
                s($o->gatewaytitle !== '' ? $o->gatewaytitle : $o->gateway),
            ];
        }
        return \html_writer::tag('h3', get_string('my_heading', $p), ['class' => 'mt-4']) .
            \html_writer::tag('p', get_string('my_help', $p), ['class' => 'text-muted']) .
            \html_writer::table($t);
    }

    /**
     * Сумма с валютой, как её показывает касса.
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    protected static function cost(float $amount, string $currency): string {
        try {
            return \core_payment\helper::get_cost_as_string($amount, $currency);
        } catch (\Throwable $e) {
            return format_float($amount, 2) . ' ' . s($currency);
        }
    }
}
