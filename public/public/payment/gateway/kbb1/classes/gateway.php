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

namespace paygw_kbb1;

/**
 * Шлюз: настройки платёжного счёта.
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {

    /**
     * Валюты, которые принимает external_payments (как в woocommerce-bb: ILS, USD, EUR).
     *
     * @return string[]
     */
    public static function get_supported_currencies(): array {
        return ['ILS', 'USD', 'EUR'];
    }

    /**
     * Поля настройки шлюза в платёжном счёте.
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();
        $p = 'paygw_kbb1';

        $mform->addElement('text', 'apiurl', get_string('apiurl', $p), ['size' => 50]);
        $mform->setType('apiurl', PARAM_URL);
        $mform->setDefault('apiurl', 'https://checkout.kbb1.com');
        $mform->addHelpButton('apiurl', 'apiurl', $p);

        $mform->addElement('text', 'apipath', get_string('apipath', $p), ['size' => 20]);
        $mform->setType('apipath', PARAM_PATH);
        $mform->setDefault('apipath', '/woocommerce');
        $mform->addHelpButton('apipath', 'apipath', $p);

        $mform->addElement('passwordunmask', 'apitoken', get_string('apitoken', $p), ['size' => 50]);
        $mform->setType('apitoken', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('apitoken', 'apitoken', $p);

        $mform->addElement('text', 'sku', get_string('sku', $p), ['size' => 20]);
        $mform->setType('sku', PARAM_TEXT);
        $mform->addHelpButton('sku', 'sku', $p);

        $mform->addElement('advcheckbox', 'method_emv', get_string('method_emv', $p));
        $mform->setDefault('method_emv', 1);
        $mform->addElement('advcheckbox', 'method_paypal', get_string('method_paypal', $p));
        $mform->setDefault('method_paypal', 1);

        $mform->addElement('text', 'installments', get_string('installments', $p), ['size' => 12]);
        $mform->setType('installments', PARAM_TEXT);
        $mform->addHelpButton('installments', 'installments', $p);
    }

    /**
     * Проверка настроек: включённому шлюзу нужны адрес, токен, SKU и хотя бы один способ.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form,
            \stdClass $data, array $files, array &$errors): void {
        if (empty($data->enabled)) {
            return;
        }
        foreach (['apiurl', 'apitoken', 'sku'] as $f) {
            if (trim((string) ($data->$f ?? '')) === '') {
                $errors[$f] = get_string('required');
            }
        }
        if (empty($data->method_emv) && empty($data->method_paypal)) {
            $errors['method_emv'] = get_string('nomethod', 'paygw_kbb1');
        }
        $inst = trim((string) ($data->installments ?? ''));
        if ($inst !== '' && !preg_match('/^\d+(\.\d+)?\s*;\s*\d+$/', $inst)) {
            $errors['installments'] = get_string('installments_invalid', 'paygw_kbb1');
        }
    }

    /**
     * Включённые способы оплаты.
     *
     * @param \stdClass $config
     * @return string[] emv|paypal
     */
    public static function methods(\stdClass $config): array {
        $m = [];
        if (!empty($config->method_emv)) {
            $m[] = 'emv';
        }
        if (!empty($config->method_paypal)) {
            $m[] = 'paypal';
        }
        return $m;
    }
}
