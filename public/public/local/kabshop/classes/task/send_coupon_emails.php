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

namespace local_kabshop\task;

use local_kabshop\mailer;

/**
 * Письма с промокодом на адреса купона, которым ещё не отправляли. Ставится при сохранении купона
 * с галочкой «отправить письмом»; работает с cron, чтобы длинный список не подвешивал форму.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_coupon_emails extends \core\task\adhoc_task {

    /**
     * Отправить.
     */
    public function execute() {
        global $DB;
        $couponid = (int) ($this->get_custom_data()->couponid ?? 0);
        $c = $DB->get_record('local_kabshop_coupon', ['id' => $couponid]);
        if (!$c) {
            return;
        }
        // Выключенный или истёкший купон не рассылаем: адреса остаются «без письма» до следующего сохранения.
        if (!$c->enabled || ($c->validto && $c->validto < time())) {
            mtrace("local_kabshop: coupon {$c->id} is disabled or expired, no emails sent");
            return;
        }
        $rows = $DB->get_records('local_kabshop_coupon_email', ['couponid' => $c->id, 'timesent' => 0], 'id');
        $sent = 0;
        foreach ($rows as $r) {
            $to = mailer::recipient($r->email);
            [$subject, $text, $html] = mailer::build_coupon($c, $to);
            if (email_to_user($to, \core_user::get_noreply_user(), $subject, $text, $html)) {
                $DB->set_field('local_kabshop_coupon_email', 'timesent', time(), ['id' => $r->id]);
                $sent++;
            } else {
                mtrace("local_kabshop: coupon {$c->id}: email row {$r->id} not sent");
            }
        }
        mtrace("local_kabshop: coupon {$c->id}: sent {$sent} of " . count($rows));
    }
}
