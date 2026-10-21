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

/**
 * Строки paygw_kbb1 (русский).
 *
 * @package    paygw_kbb1
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apipath'] = 'Путь API';
$string['apipath_help'] = 'Часть адреса перед /emv и /paypal. У WordPress — /woocommerce. Если сервис выделит отдельный путь для Moodle — поменять здесь.';
$string['apitoken'] = 'Токен API';
$string['apitoken_help'] = 'Выдаётся сервисом оплаты. По нему сервис определяет организацию и префикс номера платежа. Для Moodle нужен свой токен, не токен WordPress — иначе номера платежей пересекутся.';
$string['apiurl'] = 'Адрес сервиса';
$string['apiurl_help'] = 'Адрес external_payments без слеша в конце, например https://checkout.kbb1.com';
$string['choosemethod'] = 'Выберите способ оплаты';
$string['gatewaydescription'] = 'Банковская карта или PayPal';
$string['gatewayerror'] = 'Сервис оплаты не принял платёж: {$a}';
$string['gatewayname'] = 'Карта или PayPal';
$string['installments'] = 'Оплата частями';
$string['installments_help'] = 'Формат «сумма;платежей», например 500;5: если цена больше суммы, разрешить до N платежей (не больше 10). Пусто — всегда один платёж.';
$string['installments_invalid'] = 'Формат: сумма;платежей, например 500;5';
$string['invalidreturn'] = 'Ссылка возврата из оплаты недействительна. Если деньги списаны — напишите в поддержку.';
$string['method_emv'] = 'Банковская карта';
$string['method_paypal'] = 'PayPal';
$string['nomethod'] = 'Не включён ни один способ оплаты.';
$string['paymentcancelled'] = 'Оплата отменена.';
$string['paymentfailed'] = 'Оплата не прошла. Попробуйте ещё раз или выберите другой способ.';
$string['paymentsuccessful'] = 'Оплата прошла успешно. Спасибо!';
$string['pluginname'] = 'Карта / PayPal (сервис организации)';
$string['pluginname_desc'] = 'Оплата картой (Pelecard EMV) и PayPal через сервис организации external_payments — тот же, что у WordPress. Доступы задаются в настройках платёжного счёта.';
$string['privacy:metadata'] = 'Хранит записи о платежах и передаёт данные покупателя в сервис оплаты.';
$string['privacy:metadata:external'] = 'Данные, которые получает сервис оплаты external_payments.';
$string['privacy:metadata:external:country'] = 'Страна покупателя.';
$string['privacy:metadata:external:email'] = 'Email покупателя.';
$string['privacy:metadata:external:fullname'] = 'Имя покупателя.';
$string['privacy:metadata:external:phone'] = 'Телефон покупателя.';
$string['privacy:metadata:paygw_kbb1'] = 'Записи о платежах через сервис оплаты.';
$string['privacy:metadata:paygw_kbb1:amount'] = 'Сумма платежа.';
$string['privacy:metadata:paygw_kbb1:token'] = 'Токен карты или PayPal для автосписаний.';
$string['privacy:metadata:paygw_kbb1:txnid'] = 'Номер транзакции.';
$string['privacy:metadata:paygw_kbb1:userid'] = 'Покупатель.';
$string['sku'] = 'SKU по умолчанию';
$string['sku_help'] = 'Код для Priority, который получает сервис. У WordPress берётся из товара, здесь — один на счёт.';
$string['unsupportedcurrency'] = 'Валюта {$a} не поддерживается: только ILS, USD, EUR.';
