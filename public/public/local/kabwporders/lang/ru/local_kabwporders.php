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
 * Строки local_kabwporders, русский.
 *
 * @package    local_kabwporders
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Оплаты старого сайта (WordPress)';
$string['registry'] = 'Оплаты старого сайта';
$string['registry_help'] = 'Оплаченные заказы kabacademy.com до переезда в Moodle: кто, когда, за что и сколько заплатил. Только для чтения — данные загружаются импортом.';
$string['import'] = 'Импорт оплат старого сайта';
$string['import_help'] = 'Загрузите три файла выгрузки WordPress: orders, items и products (.tsv или .tsv.gz). Импорт заменяет реестр целиком: повторная загрузка не даёт дублей. Пользователи Moodle не создаются и не меняются.';
$string['importfiles'] = 'Файлы выгрузки';
$string['importfiles_help'] = 'orders.tsv, items.tsv и products.tsv — как их пишет скрипт выгрузки. Можно сжатые: orders.tsv.gz. После импорта загруженные файлы удаляются с сервера.';
$string['importrun'] = 'Импортировать';
$string['importdone'] = 'Импорт выполнен. Сверьте числа с итогами скрипта выгрузки.';
$string['importfailed'] = 'Импорт не выполнен, реестр не изменён: {$a}';
$string['importdir_found'] = 'В каталоге на сервере, заданном в config.php, лежит полная выгрузка.';
$string['importdir_run'] = 'Импортировать из каталога на сервере';
$string['empty'] = 'Реестр пуст — импорт ещё не выполнялся.';
$string['notfound'] = 'Ничего не найдено.';
$string['found'] = 'Найдено заказов: {$a}';
$string['searchhint'] = 'Номер заказа, почта, имя, номер транзакции';
$string['exportcsv'] = 'Выгрузить в CSV';
$string['allyears'] = 'Все годы';
$string['allcurrencies'] = 'Все валюты';
$string['allstatuses'] = 'Все статусы';

$string['col_order'] = '№ заказа';
$string['col_date'] = 'Дата оплаты';
$string['col_payer'] = 'Плательщик';
$string['col_email'] = 'Почта';
$string['col_products'] = 'За что';
$string['col_amount'] = 'Сумма';
$string['col_refunded'] = 'Возврат';
$string['col_status'] = 'Статус';
$string['col_gateway'] = 'Способ оплаты';
$string['col_txn'] = 'Номер транзакции';
$string['col_currency'] = 'Валюта';
$string['col_year'] = 'Год';

$string['tot_orders'] = 'Заказов';
$string['tot_amount'] = 'Сумма';
$string['tot_refunds'] = 'Заказов с возвратом';
$string['tot_refunded'] = 'Сумма возвратов';
$string['tot_people'] = 'Привязано к человеку в Moodle: {$a->linked}. Без человека: {$a->unlinked}. Последний импорт: {$a->date}.';

$string['status_completed'] = 'Выполнен';
$string['status_processing'] = 'В обработке';
$string['status_refunded'] = 'Возвращён';
$string['status_cancelled'] = 'Оплачен, позже отменён';
$string['status_failed'] = 'Оплачен, позже помечен неудачным';
$string['status_pending'] = 'Оплачен, статус «ожидает»';
$string['status_onhold'] = 'Оплачен, на удержании';

$string['res_orders'] = 'Заказов в реестре';
$string['res_items'] = 'Позиций';
$string['res_itemswithcourse'] = 'Позиций с курсом Moodle';
$string['res_orderswithoutitems'] = 'Заказов без позиций';
$string['res_itemorphans'] = 'Позиций без заказа (пропущены)';
$string['res_bylink'] = 'Человек найден по связке из WordPress';
$string['res_byemail'] = 'Человек найден по почте';
$string['res_unmatched'] = 'Заказов без человека в Moodle';
$string['res_unmatchedemails'] = 'Адресов почты без человека в Moodle';
$string['res_linkemaildiffers'] = 'Связка есть, почта в Moodle другая';

$string['err_nofile'] = 'нет файла {$a}';
$string['err_empty'] = 'в orders.tsv нет ни одного заказа';
$string['err_header'] = 'заголовок файла {$a} не совпадает с ожидаемым';
$string['err_columns'] = 'в файле {$a} есть строка с неверным числом колонок';

$string['my_title'] = 'Мои прежние оплаты';
$string['my_heading'] = 'Прежние оплаты';
$string['my_help'] = 'Ваши оплаты на сайте kabacademy.com, сделанные до переноса оплаты на эту платформу. Если какой-то оплаты здесь нет, напишите в поддержку.';
$string['my_empty'] = 'Прежних оплат не найдено. Если вы платили на kabacademy.com с другой почты, напишите в поддержку.';
$string['my_col_date'] = 'Дата';
$string['my_refunded'] = 'возврат {$a}';
$string['my_note_cancelled'] = 'заказ отменён';
$string['my_note_failed'] = 'заказ не завершён';
$string['my_note_pending'] = 'заказ ожидает';
$string['my_note_onhold'] = 'заказ на удержании';

$string['kabwporders:view'] = 'Смотреть оплаты старого сайта';
$string['kabwporders:import'] = 'Импортировать оплаты старого сайта';

$string['privacy:metadata:order'] = 'Оплаченные заказы старого сайта kabacademy.com';
$string['privacy:metadata:order:userid'] = 'Пользователь, к которому привязан заказ';
$string['privacy:metadata:order:payeremail'] = 'Почта плательщика на момент оплаты';
$string['privacy:metadata:order:payername'] = 'Имя плательщика на момент оплаты';
$string['privacy:metadata:order:amount'] = 'Сумма и валюта заказа';
$string['privacy:metadata:order:timepaid'] = 'Дата оплаты';
$string['privacy:metadata:order:txnid'] = 'Номер транзакции у платёжного шлюза';
