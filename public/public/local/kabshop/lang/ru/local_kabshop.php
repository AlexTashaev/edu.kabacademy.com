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
 * Language strings for local_kabshop.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['accountrequired'] = 'Для платного товара выберите платёжный счёт.';
$string['addproduct'] = 'Добавить товар';
$string['addsection'] = 'Добавить раздел';
$string['amountrange'] = 'Укажите сумму от {$a->min} до {$a->max}.';
$string['backtocatalog'] = 'Все курсы';
$string['buy'] = 'Купить';
$string['catalog'] = 'Курсы';
$string['changeamount'] = 'Изменить сумму';
$string['chooseamount'] = 'Выберите сумму';
$string['courseid'] = 'ID курса Moodle';
$string['courseid_help'] = 'Курс, в который записывается покупатель. 0 — без курса (пожертвование, сервис).';
$string['coursenotfound'] = 'Курс не найден';
$string['customamount'] = 'Сумма на выбор';
$string['customamount_help'] = 'Покупатель сам выбирает сумму. Поле «Цена» — минимальная сумма.';
$string['description'] = 'Описание';
$string['editproduct'] = 'Редактировать товар';
$string['editsection'] = 'Редактировать раздел';
$string['emptycatalog'] = 'Каталог пока пуст.';
$string['enrolfree'] = 'Записаться бесплатно';
$string['enrolled'] = 'Вы записаны';
$string['enrolperiod'] = 'Срок доступа';
$string['enrolperiod_help'] = 'Сколько длится доступ к курсу после покупки. Выключено — бессрочно.';
$string['externalurl'] = 'Внешняя ссылка';
$string['free'] = 'Бесплатно';
$string['gotocourse'] = 'Перейти к курсу';
$string['gotoexternal'] = 'Перейти';
$string['hidden'] = 'скрыт';
$string['image'] = 'Картинка';
$string['image_help'] = '16:9. Если не задана — берётся обложка курса.';
$string['invalidamount'] = 'Неверная сумма';
$string['invalidprice'] = 'Цена не может быть отрицательной';
$string['kabshop:manage'] = 'Управлять витриной';
$string['kind'] = 'Вид';
$string['kind_course'] = 'Курс';
$string['kind_donation'] = 'Пожертвование';
$string['kind_external'] = 'Внешняя ссылка';
$string['kind_service'] = 'Сервис';
$string['loginandbuy'] = 'Войти и купить';
$string['loginanddonate'] = 'Войти и поддержать';
$string['loginandenrol'] = 'Войти и записаться';
$string['mail_course'] = 'Вы записаны на курс «{$a->course}»:';
$string['mail_course_extended'] = 'Доступ к курсу «{$a->course}» продлён:';
$string['mail_failed_final'] = 'Нам не удалось списать {$a->amount} за «{$a->product}» после нескольких попыток, поэтому подписка остановлена. Чтобы возобновить её, оформите подписку заново: {$a->producturl}';
$string['mail_failed_final_subject'] = 'Подписка остановлена: {$a->product}';
$string['mail_failed_retry'] = 'Нам не удалось списать {$a->amount} за «{$a->product}». Мы попробуем ещё раз через сутки. Если карта сменилась или на ней не хватает средств, проверьте её — или оформите подписку заново: {$a->producturl}';
$string['mail_failed_subject'] = 'Не удалось списать оплату: {$a->product}';
$string['mail_hello'] = 'Здравствуйте, {$a->firstname}!';
$string['mail_next'] = 'Следующее списание {$a->subamount} — {$a->next}. Отменить подписку можно в разделе «Мои подписки»:';
$string['mail_order'] = 'Номер заказа: {$a->order}';
$string['mail_paid_body'] = 'Оплата {$a->amount} за «{$a->product}» прошла успешно.';
$string['mail_paid_subject'] = 'Оплата прошла: {$a->product}';
$string['mail_receipt'] = 'Квитанцию об оплате пришлёт бухгалтерия отдельным письмом.';
$string['mail_renewal_body'] = 'Автоплатёж {$a->amount} за «{$a->product}» прошёл успешно.';
$string['mail_renewal_subject'] = 'Автоплатёж прошёл: {$a->product}';
$string['mail_signature'] = 'С уважением, {$a->site}';
$string['mail_txn'] = 'Номер транзакции: {$a->txn}';
$string['mailpreview'] = 'Письмо об оплате';
$string['mailpreview_subject'] = 'Тема:';
$string['mailpreview_to'] = 'Кому:';
$string['manage'] = 'Витрина';
$string['managesub'] = 'Управлять подпиской';
$string['more'] = 'Подробнее';
$string['mysubscriptions'] = 'Мои подписки';
$string['nopayment'] = 'Оплата для этого товара пока не настроена.';
$string['noproducts'] = 'В разделе нет товаров.';
$string['nosubscriptions'] = 'Подписок с автосписанием нет.';
$string['otheramount'] = 'Или своя сумма';
$string['payamount'] = 'Оплатить {$a}';
$string['per1'] = 'в месяц';
$string['per12'] = 'в год';
$string['per3'] = 'раз в 3 месяца';
$string['pluginname'] = 'Витрина академии';
$string['presets'] = 'Готовые суммы';
$string['presets_help'] = 'Через запятую, например 10, 25, 50, 100. Суммы меньше цены не показываются.';
$string['price'] = 'Цена';
$string['price_help'] = '0 — бесплатно.';
$string['pricefrom'] = 'от {$a}';
$string['pricelabel'] = 'Подпись к цене';
$string['pricelabel_help'] = 'Показывается после цены, например «в месяц».';
$string['privacy:metadata'] = 'Витрина хранит покупки: товар, пользователь, платёж, сумма, время.';
$string['productnotfound'] = 'Товар не найден';
$string['recur_1'] = 'Раз в месяц';
$string['recur_12'] = 'Раз в год';
$string['recur_3'] = 'Раз в 3 месяца';
$string['recur_none'] = 'Разово';
$string['recurfree'] = 'Автосписание — только для платного товара.';
$string['recurring'] = 'Регулярность';
$string['recurring_help'] = 'Автосписание с сохранённой карты: первая оплата — как обычно, дальше списывается само раз в выбранный период. Доступ к курсу — до следующего списания плюс 3 дня. Работает со шлюзом «Карта / PayPal».';
$string['section'] = 'Раздел';
$string['sortorder'] = 'Порядок';
$string['subactive'] = 'Подписка активна. Следующее списание — {$a}.';
$string['subamount'] = 'Сумма';
$string['subcancel'] = 'Отменить';
$string['subcancelfailed'] = 'Подписку не удалось отменить — возможно, она уже отменена.';
$string['subcancelled'] = 'Подписка отменена. Доступ сохранится до конца оплаченного периода.';
$string['subcancelnote'] = 'После отмены деньги больше не списываются, а доступ сохраняется до конца оплаченного периода.';
$string['subfails'] = 'Неудачи';
$string['subnext'] = 'Следующее списание';
$string['subpaiduntil'] = 'оплачено до {$a}';
$string['subproduct'] = 'Что';
$string['subscriptions'] = 'Подписки';
$string['substatus'] = 'Статус';
$string['substatus_active'] = 'Активна';
$string['substatus_cancelled'] = 'Отменена';
$string['substatus_failed'] = 'Остановлена: не удалось списать';
$string['summary'] = 'Короткое описание';
$string['task_charge'] = 'Автосписание подписок витрины';
$string['thankyou'] = 'Спасибо! Оплата получена.';
