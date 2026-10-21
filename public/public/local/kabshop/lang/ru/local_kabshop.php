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
$string['accessforever'] = 'открыт бессрочно';
$string['accessnone'] = 'нет записи на курс';
$string['accessuntil'] = 'открыт до {$a}';
$string['accountrequired'] = 'Для платного товара выберите платёжный счёт.';
$string['addcoupon'] = 'Добавить купон';
$string['addproduct'] = 'Добавить товар';
$string['addsection'] = 'Добавить раздел';
$string['allproducts'] = 'Все товары';
$string['amountrange'] = 'Укажите сумму от {$a->min} до {$a->max}.';
$string['attempt_cancel'] = 'Отменена покупателем';
$string['attempt_error'] = 'Ошибка оплаты';
$string['attempt_pending'] = 'Ушёл на оплату, не вернулся';
$string['attempts'] = 'Незавершённые оплаты';
$string['attempts_help'] = 'Попытки оплаты картой / PayPal без успешного завершения — для разбора обращений «оплатил, а доступа нет». Номер в сервисе оплаты — тот, что видит бухгалтерия.';
$string['backtocatalog'] = 'Все курсы';
$string['buy'] = 'Купить';
$string['catalog'] = 'Курсы';
$string['changeamount'] = 'Изменить сумму';
$string['chargenow'] = 'Списать сейчас';
$string['chargenow_confirm'] = 'Да, списать {$a} с сохранённой карты покупателя сейчас';
$string['chargenow_failed'] = 'Списать не удалось: {$a}';
$string['chargenow_ok'] = 'Списано. Подписка продлена, покупателю ушло письмо.';
$string['chargenow_unavailable'] = 'Эту подписку нельзя списать отсюда: она не активна или шлюз списывает сам.';
$string['chooseamount'] = 'Выберите сумму';
$string['col_buyer'] = 'Покупатель';
$string['col_email'] = 'Email';
$string['col_order'] = '№ заказа';
$string['col_orderdate'] = 'Дата';
$string['col_ordertype'] = 'Вид';
$string['col_paymethod'] = 'Способ оплаты';
$string['col_serviceref'] = '№ в сервисе оплаты';
$string['col_subamount'] = 'Сумма';
$string['col_subproduct'] = 'Товар';
$string['col_txn'] = 'Транзакция';
$string['coupon_alreadyused'] = 'Вы уже использовали этот промокод.';
$string['coupon_expired'] = 'Срок действия промокода истёк или он отключён.';
$string['coupon_freedone'] = 'Промокод применён, заказ оформлен. Подробности — в письме.';
$string['coupon_freenote'] = 'Промокод {$a} — 100% скидка';
$string['coupon_notforproduct'] = 'Этот промокод не действует для этого товара.';
$string['coupon_notfound'] = 'Такого промокода нет.';
$string['coupon_usedup'] = 'Промокод больше не действует: исчерпан лимит.';
$string['couponapplied'] = 'Промокод {$a->code}: скидка {$a->off} (без скидки — {$a->was}).';
$string['couponapply'] = 'Применить';
$string['couponcode'] = 'Промокод';
$string['couponcode_help'] = 'Буквы, цифры, «-» и «_», без пробелов. Регистр не важен — покупатель может ввести как угодно.';
$string['couponcode_invalid'] = 'Только буквы, цифры, «-» и «_».';
$string['couponcode_taken'] = 'Такой промокод уже есть.';
$string['couponfrom'] = 'Действует с';
$string['couponkind'] = 'Скидка';
$string['couponkind_fixed'] = 'Фиксированная сумма';
$string['couponkind_percent'] = 'Процент';
$string['couponlabel'] = 'Есть промокод?';
$string['couponmaxuses'] = 'Лимит использований';
$string['couponmaxuses_help'] = 'Сколько раз всего можно применить промокод. 0 — без лимита.';
$string['couponnolimit'] = 'бессрочно';
$string['couponnote'] = 'Заметка';
$string['couponperuser'] = 'Один раз на человека';
$string['couponperuser_short'] = 'один раз на человека';
$string['couponproducts'] = 'Товары';
$string['couponproducts_help'] = 'Пусто — на все платные товары. Не действует на «сумму на выбор» и внешние ссылки. Для подписки скидка — только на первый платёж.';
$string['couponremove'] = 'Убрать промокод';
$string['coupons'] = 'Купоны витрины';
$string['couponto'] = 'Действует до';
$string['couponto_invalid'] = 'Конец должен быть позже начала.';
$string['couponuses'] = 'Использован';
$string['couponvalidity'] = 'Срок';
$string['couponvalue'] = 'Размер скидки';
$string['couponvalue_invalid'] = 'Больше 0; процент — не больше 100.';
$string['courseaccess'] = 'Доступ к курсу';
$string['courseid'] = 'ID курса Moodle';
$string['courseid_help'] = 'Курс, в который записывается покупатель. 0 — без курса (пожертвование, сервис).';
$string['coursenotfound'] = 'Курс не найден';
$string['customamount'] = 'Сумма на выбор';
$string['customamount_help'] = 'Покупатель сам выбирает сумму. Поле «Цена» — минимальная сумма.';
$string['datefrom'] = 'С даты';
$string['dateto'] = 'По дату';
$string['description'] = 'Описание';
$string['editcoupon'] = 'Редактировать купон';
$string['editproduct'] = 'Редактировать товар';
$string['editsection'] = 'Редактировать раздел';
$string['emptycatalog'] = 'Каталог пока пуст.';
$string['enrolfree'] = 'Записаться бесплатно';
$string['enrolled'] = 'Вы записаны';
$string['enrolperiod'] = 'Срок доступа';
$string['enrolperiod_help'] = 'Сколько длится доступ к курсу после покупки. Выключено — бессрочно.';
$string['exportcsv'] = 'Выгрузить CSV';
$string['externalurl'] = 'Внешняя ссылка';
$string['free'] = 'Бесплатно';
$string['gotocourse'] = 'Перейти к курсу';
$string['gotoexternal'] = 'Перейти';
$string['grant'] = 'Выдать вручную';
$string['grant_help'] = 'Открыть товар человеку без оплаты на сайте: оплата переводом, подарок, исправление ошибки. Создаётся заказ с пометкой, кто и почему выдал.';
$string['granted'] = 'Выдано. Заказ № {$a}.';
$string['grantedby'] = 'Выдал вручную';
$string['grantemail'] = 'Email покупателя';
$string['grantemail_help'] = 'Аккаунт с этим email должен уже существовать.';
$string['grantmail'] = 'Отправить покупателю письмо «доступ открыт»';
$string['grantmanyusers'] = 'С этим email несколько аккаунтов — выдайте из профиля нужного.';
$string['grantnote'] = 'Причина';
$string['grantnote_help'] = 'Например: «оплата переводом 12.10», «подарок от академии», «ошибка оплаты, тикет 123». Видно в заказе.';
$string['grantnouser'] = 'Нет аккаунта с таким email.';
$string['grantsubmit'] = 'Выдать';
$string['granttimeend'] = 'Доступ до';
$string['granttimeend_help'] = 'Выключено — по сроку товара или бессрочно.';
$string['granttimeend_past'] = 'Дата уже прошла.';
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
$string['mail_granted_body'] = 'Вам открыт доступ к «{$a->product}».';
$string['mail_granted_subject'] = 'Доступ открыт: {$a->product}';
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
$string['mailresend'] = 'Отправить письмо ещё раз';
$string['mailresent'] = 'Письмо отправлено на {$a}.';
$string['manage'] = 'Витрина';
$string['managesub'] = 'Управлять подпиской';
$string['more'] = 'Подробнее';
$string['myorders'] = 'Мои заказы';
$string['mysubscriptions'] = 'Мои подписки';
$string['noattempts'] = 'Незавершённых оплат нет.';
$string['nocoupons'] = 'Купонов пока нет.';
$string['noorders'] = 'Заказов нет.';
$string['nopayment'] = 'Оплата для этого товара пока не настроена.';
$string['noproducts'] = 'В разделе нет товаров.';
$string['nosubscriptions'] = 'Подписок с автосписанием нет.';
$string['orderno'] = 'Заказ № {$a}';
$string['orderrevoked'] = 'Доступ по заказу отозван {$a}.';
$string['orders'] = 'Заказы витрины';
$string['orderstotal'] = 'Заказов: {$a->count}, оплачено: {$a->sum}';
$string['ordertype_coupon'] = 'Промокод 100%';
$string['ordertype_free'] = 'Бесплатно';
$string['ordertype_manual'] = 'Вручную';
$string['ordertype_paid'] = 'Оплата';
$string['ordertype_renewal'] = 'Автоплатёж';
$string['ordertype_revoked'] = 'отозван';
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
$string['revoke'] = 'Отозвать доступ';
$string['revoked'] = 'Доступ отозван: запись на курс снята, подписка на этот товар остановлена.';
$string['revokednote'] = 'Отозвал {$a->who}: {$a->note}';
$string['revokefailed'] = 'Доступ уже отозван.';
$string['revokenote'] = 'Причина (например, возврат через бухгалтерию)';
$string['searchhint'] = 'Номер заказа, имя или email';
$string['section'] = 'Раздел';
$string['sortorder'] = 'Порядок';
$string['subactive'] = 'Подписка активна. Следующее списание — {$a}.';
$string['subamount'] = 'Сумма';
$string['subcancel'] = 'Отменить';
$string['subcancel_admin'] = 'Остановить автосписание';
$string['subcancelfailed'] = 'Подписку не удалось отменить — возможно, она уже отменена.';
$string['subcancelled'] = 'Подписка отменена. Доступ сохранится до конца оплаченного периода.';
$string['subcancelnote'] = 'После отмены деньги больше не списываются, а доступ сохраняется до конца оплаченного периода.';
$string['subdue'] = 'срок подошёл, спишется при ближайшем запуске задачи (каждый час в :17)';
$string['subedit'] = 'Настроить подписку';
$string['subedit_help'] = 'Дата ближайшего платежа — когда задача спишет следующий раз. Доступ к курсу сдвинется на эту дату + 3 дня. Сумма — для всех следующих списаний.';
$string['subfails'] = 'Неудачи';
$string['subgatewayown'] = 'списывает сам шлюз';
$string['subhistory'] = 'Платежи по подписке';
$string['subnext'] = 'Следующее списание';
$string['subnext_help'] = 'Задача списывает каждый час в :17, начиная с этого времени.';
$string['subno'] = 'Подписка № {$a}';
$string['subpaiduntil'] = 'оплачено до {$a}';
$string['subproduct'] = 'Что';
$string['subresetfails'] = 'Сбросить счётчик неудач';
$string['subscriptions'] = 'Подписки';
$string['substatus'] = 'Статус';
$string['substatus_active'] = 'Активна';
$string['substatus_cancelled'] = 'Отменена';
$string['substatus_failed'] = 'Остановлена: не удалось списать';
$string['summary'] = 'Короткое описание';
$string['task_charge'] = 'Автосписание подписок витрины';
$string['thankyou'] = 'Спасибо! Оплата получена.';
