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

/**
 * Переход на страницу оплаты external_payments.
 *
 * @module     paygw_kbb1/gateways_modal
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const process = (component, paymentArea, itemId, description) => {
    const params = new URLSearchParams({
        sesskey: M.cfg.sesskey,
        component: component,
        paymentarea: paymentArea,
        itemid: itemId,
        description: description,
        returnurl: window.location.href,
    });
    window.location.href = M.cfg.wwwroot + '/payment/gateway/kbb1/pay.php?' + params.toString();
    // Страница уходит на оплату — промис намеренно не завершается.
    return new Promise(() => null);
};
