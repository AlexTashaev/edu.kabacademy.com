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

use moodle_url;

/**
 * Письма покупателю. Квитанцию шлёт бухгалтерия отдельно — здесь только подтверждение.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mailer {

    /**
     * Номер транзакции шлюза для платежа, если шлюз его хранит.
     *
     * @param \stdClass $payment
     * @return string
     */
    protected static function txnid(\stdClass $payment): string {
        global $DB;
        if ($payment->gateway === 'kbb1' && $DB->get_manager()->table_exists('paygw_kbb1')) {
            return (string) $DB->get_field('paygw_kbb1', 'txnid', ['paymentid' => $payment->id], IGNORE_MULTIPLE);
        }
        return '';
    }

    /**
     * Текст письма «оплата прошла».
     *
     * @param \stdClass $product
     * @param \stdClass $user
     * @param int $purchaseid
     * @param \stdClass $payment
     * @param \stdClass|null $sub
     * @return array [тема, текст, html]
     */
    public static function build_paid(\stdClass $product, \stdClass $user, int $purchaseid,
            \stdClass $payment, ?\stdClass $sub): array {
        global $DB, $SITE;
        $p = 'local_kabshop';
        $renewal = $sub && (int) $sub->firstpaymentid !== (int) $payment->id;
        $a = (object) [
            'firstname' => $user->firstname,
            'product' => format_string($product->name),
            'amount' => \core_payment\helper::get_cost_as_string((float) $payment->amount, $payment->currency),
            'order' => $purchaseid,
            'txn' => self::txnid($payment),
            'site' => format_string($SITE->fullname),
        ];

        $lines = [get_string('mail_hello', $p, $a), '',
            get_string($renewal ? 'mail_renewal_body' : 'mail_paid_body', $p, $a), '',
            get_string('mail_order', $p, $a)];
        if ($a->txn !== '') {
            $lines[] = get_string('mail_txn', $p, $a);
        }

        if ($product->courseid && ($course = $DB->get_record('course', ['id' => $product->courseid]))) {
            $a->course = format_string($course->fullname);
            $a->courseurl = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
            $lines[] = '';
            $lines[] = get_string($renewal ? 'mail_course_extended' : 'mail_course', $p, $a);
            $lines[] = $a->courseurl;
        }
        if ($sub && $sub->status === 'active') {
            $a->next = userdate($sub->nextcharge, get_string('strftimedate', 'langconfig'));
            $a->subamount = \core_payment\helper::get_cost_as_string((float) $sub->amount, $sub->currency);
            $a->subsurl = (new moodle_url('/local/kabshop/subscriptions.php'))->out(false);
            $lines[] = '';
            $lines[] = get_string('mail_next', $p, $a);
            $lines[] = $a->subsurl;
        }
        $lines[] = '';
        $lines[] = get_string('mail_receipt', $p);
        $lines[] = '';
        $lines[] = get_string('mail_signature', $p, $a);

        $text = implode("\n", $lines);
        // HTML: тот же текст, адреса — ссылками.
        $html = nl2br(preg_replace('#(https?://[^\s<]+)#', '<a href="$1">$1</a>', s($text)));
        $subject = get_string($renewal ? 'mail_renewal_subject' : 'mail_paid_subject', $p, $a);
        return [$subject, $text, $html];
    }

    /**
     * Отправить письмо «оплата прошла».
     *
     * @param \stdClass $product
     * @param int $userid
     * @param int $purchaseid
     * @param \stdClass $payment
     * @param \stdClass|null $sub
     */
    public static function paid(\stdClass $product, int $userid, int $purchaseid, \stdClass $payment,
            ?\stdClass $sub): void {
        $user = \core_user::get_user($userid);
        if (!$user || $user->deleted) {
            return;
        }
        [$subject, $text, $html] = self::build_paid($product, $user, $purchaseid, $payment, $sub);
        email_to_user($user, \core_user::get_noreply_user(), $subject, $text, $html);
    }

    /**
     * Письмо «доступ открыт» — ручная выдача администратором или купон на 100%.
     *
     * @param \stdClass $product
     * @param int $userid
     * @param int $purchaseid
     */
    public static function granted(\stdClass $product, int $userid, int $purchaseid): void {
        global $DB, $SITE;
        $user = \core_user::get_user($userid);
        if (!$user || $user->deleted) {
            return;
        }
        $p = 'local_kabshop';
        $a = (object) [
            'firstname' => $user->firstname,
            'product' => format_string($product->name),
            'order' => $purchaseid,
            'site' => format_string($SITE->fullname),
        ];
        $lines = [get_string('mail_hello', $p, $a), '', get_string('mail_granted_body', $p, $a), '',
            get_string('mail_order', $p, $a)];
        if ($product->courseid && ($course = $DB->get_record('course', ['id' => $product->courseid]))) {
            $a->course = format_string($course->fullname);
            $lines[] = '';
            $lines[] = get_string('mail_course', $p, $a);
            $lines[] = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
        }
        $lines[] = '';
        $lines[] = get_string('mail_signature', $p, $a);
        $text = implode("\n", $lines);
        $html = nl2br(preg_replace('#(https?://[^\s<]+)#', '<a href="$1">$1</a>', s($text)));
        email_to_user($user, \core_user::get_noreply_user(), get_string('mail_granted_subject', $p, $a), $text, $html);
    }

    /**
     * Письмо о неудачном автосписании: после первой неудачи — «повторим», после последней —
     * «подписка остановлена». Промежуточные неудачи — без писем.
     *
     * @param \stdClass $product
     * @param \stdClass $user
     * @param \stdClass $sub
     */
    public static function charge_failed(\stdClass $product, \stdClass $user, \stdClass $sub): void {
        global $SITE;
        $p = 'local_kabshop';
        $final = $sub->status === 'failed';
        if (!$final && (int) $sub->failcount !== 1) {
            return;
        }
        $a = (object) [
            'firstname' => $user->firstname,
            'product' => format_string($product->name),
            'amount' => \core_payment\helper::get_cost_as_string((float) $sub->amount, $sub->currency),
            'producturl' => (new moodle_url('/local/kabshop/product.php', ['id' => $product->id]))->out(false),
            'site' => format_string($SITE->fullname),
        ];
        $text = get_string('mail_hello', $p, $a) . "\n\n"
            . get_string($final ? 'mail_failed_final' : 'mail_failed_retry', $p, $a) . "\n\n"
            . get_string('mail_signature', $p, $a);
        $html = nl2br(preg_replace('#(https?://[^\s<]+)#', '<a href="$1">$1</a>', s($text)));
        email_to_user($user, \core_user::get_noreply_user(),
            get_string($final ? 'mail_failed_final_subject' : 'mail_failed_subject', $p, $a), $text, $html);
    }
}
