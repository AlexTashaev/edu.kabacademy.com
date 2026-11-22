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
 * Страница товара. Открыта гостям; покупка — после входа.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kabshop\catalog;

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$paid = optional_param('paid', 0, PARAM_BOOL);
$intentid = optional_param('intent', 0, PARAM_INT);
$cur = optional_param('cur', '', PARAM_ALPHA);

$context = context_system::instance();
$record = catalog::get_product($id);
$canmanage = has_capability('local/kabshop:manage', $context);
if (!$record->visible && !$canmanage) {
    throw new moodle_exception('productnotfound', 'local_kabshop');
}
$loggedin = isloggedin() && !isguestuser();

// Зафиксированная цена (сумма на выбор или цена по купону) — только своя и для этого товара.
$intent = ($loggedin && $intentid) ? catalog::get_intent($intentid, IGNORE_MISSING) : null;
if ($intent && ((int) $intent->userid !== (int) $USER->id || (int) $intent->productid !== $id)) {
    $intent = null;
}
// Валюта страницы: у выбранной суммы — её валюта, иначе выбранная в адресе или витрины,
// с которой пришёл посетитель. Дальше $product — товар в этой валюте (цена, суммы, счёт).
$variants = catalog::variants($record);
$product = $intent ? (catalog::variant($record, $intent->currency) ?: $record) : catalog::pick_variant($record, $cur);
$market = catalog::market($product);
if ($cur !== '' && count($variants) > 1 && catalog::variant($record, $cur)) {
    catalog::remember_market($market);
}

$url = catalog::product_url($product);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$name = format_string($product->name, true, ['context' => $context]);
$PAGE->set_title($name);
$PAGE->set_heading($name);
$catalogurl = catalog::catalog_url($market);
$PAGE->navbar->add(get_string($market === 'ru' ? 'catalogru' : 'catalog', 'local_kabshop'), $catalogurl);
$PAGE->navbar->add($name);

$owned = $loggedin && catalog::is_owned($product, $USER->id);
$activesub = ($loggedin && !empty($product->recurmonths))
    ? \local_kabshop\subscriptions::get_active($product->id, $USER->id) : null;
$free = catalog::is_free($product);
// Условие записи (часть 2 — только учившимся на части 1). Купленному и подписчику не мешает.
$prereqok = $owned || $activesub || $product->kind === 'external'
    || catalog::prerequisite_ok($product, $loggedin ? (int) $USER->id : 0);
// Состояние записи: «ещё не открыта» и «завершена» убирают кнопку у тех, кто ещё не записан.
$salenotice = ($owned || $activesub) ? null : catalog::sale_notice($product);
$saleopen = $salenotice === null;
$prereqnotice = null;
if (!$prereqok && $saleopen) {
    $names = catalog::prerequisite_names($product);
    $prereqnotice = $loggedin
        ? get_string('prereqdenied', 'local_kabshop', (object) ['required' => $names, 'email' => s($USER->email)])
        : get_string('prereqguest', 'local_kabshop', $names);
}

$action = null;
$donateform = null;
$couponform = null;
$couponinfo = null;
$taxinfo = null;
$custom = !empty($product->customamount);
// Пожертвование не в рублях: перед оплатой — данные жертвователя для квитанции (статья 46).
$taxform = catalog::needs_taxid($product) && !$free;
$producturlstr = $url->out(false);
if ($product->kind === 'external') {
    if (!empty($product->externalurl)) {
        $action = ['href' => $product->externalurl, 'label' => get_string('gotoexternal', 'local_kabshop')];
    }
} else if ($owned && $product->courseid) {
    $action = [
        'href' => (new moodle_url('/course/view.php', ['id' => $product->courseid]))->out(false),
        'label' => get_string('gotocourse', 'local_kabshop'),
    ];
} else if ($activesub) {
    $action = [
        'href' => (new moodle_url('/local/kabshop/subscriptions.php'))->out(false),
        'label' => get_string('managesub', 'local_kabshop'),
    ];
} else if (!$saleopen) {
    // Запись ещё не открыта или завершена: вместо кнопки — блок состояния, без оплаты и промокода.
    $action = null;
} else if (!$loggedin) {
    $action = [
        'href' => (new moodle_url('/local/kabshop/enrol.php', ['id' => $id] + (count($variants) > 1
            ? ['cur' => $product->currency] : [])))->out(false),
        'label' => get_string($free ? 'loginandenrol' : ($custom ? 'loginanddonate' : 'loginandbuy'), 'local_kabshop'),
    ];
} else if (!$prereqok) {
    // Вошёл, но условие не выполнено: только объяснение, без оплаты и промокода.
    $action = null;
} else if ($intent && $product->accountid && (float) $intent->amount > 0 && (!$taxform || !empty($intent->taxtype))) {
    // Сумма выбрана или купон применён — кнопка оплаты, окно оплаты открывается сразу.
    if ($taxform) {
        $taxinfo = get_string('taxinfo', 'local_kabshop', catalog::tax_label($intent->taxtype, $intent->taxid));
    }
    $amountstr = \core_payment\helper::get_cost_as_string((float)$intent->amount, $intent->currency);
    $action = [
        'payment' => true,
        'autopay' => true,
        'component' => 'local_kabshop',
        'paymentarea' => 'custom',
        'itemid' => $intent->id,
        'cost' => (float)$intent->amount,
        'successurl' => \local_kabshop\payment\service_provider::get_success_url('custom', $intent->id)->out(false),
        'description' => $name,
        'label' => get_string('payamount', 'local_kabshop', $amountstr),
    ];
    if (!empty($intent->couponid) && ($coupon = $DB->get_record('local_kabshop_coupon', ['id' => $intent->couponid]))) {
        $couponinfo = get_string('couponapplied', 'local_kabshop', (object) [
            'code' => s($coupon->code),
            'off' => \core_payment\helper::get_cost_as_string((float) $intent->amountoff, $intent->currency),
            'was' => \core_payment\helper::get_cost_as_string((float) $product->price, $product->currency),
        ]);
    }
    $changeurl = $producturlstr;
    $PAGE->requires->js_amd_inline("require(['core_payment/gateways_modal'], function(m) {
        m.init();
        var b = document.querySelector('[data-kabshop-autopay]');
        if (b) { b.click(); }
    });");
} else if (($custom || $taxform) && $product->accountid) {
    // Введённое до ошибки (donate.php кладёт его в сессию), иначе прошлое пожертвование.
    $saved = $SESSION->local_kabshop_tax ?? null;
    if (!$saved || (int) ($saved['productid'] ?? 0) !== $id) {
        $saved = null;
    }
    $presets = [];
    $customvalue = '';
    if ($custom) {
        $values = catalog::presets($product);
        $savedamount = $saved ? round((float) $saved['amount'], 2) : null;
        $pick = ($savedamount !== null && in_array($savedamount, $values)) ? $savedamount : null;
        if ($savedamount !== null && $pick === null) {
            $customvalue = format_float($savedamount, 2, false);
        }
        foreach ($values as $i => $v) {
            $presets[] = [
                'value' => $v,
                'label' => \core_payment\helper::get_cost_as_string($v, $product->currency),
                'checked' => $pick !== null ? $v == $pick : ($i === 0 && $customvalue === ''),
            ];
        }
    }
    $taxdata = null;
    if ($taxform) {
        $prev = $saved ?: catalog::last_tax((int) $USER->id);
        $country = strtoupper((string) ($prev['country'] ?? '')) ?: (string) $USER->country;
        $type = in_array($prev['type'] ?? '', catalog::TAX_TYPES, true) ? $prev['type'] : '1';
        $countries = [];
        foreach (get_string_manager()->get_list_of_countries() as $code => $cname) {
            $countries[] = ['code' => $code, 'name' => $cname, 'selected' => $code === $country];
        }
        $types = [];
        foreach (catalog::TAX_TYPES as $t) {
            $types[] = ['value' => $t, 'label' => get_string('taxtype_' . $t, 'local_kabshop'), 'checked' => $t === $type];
        }
        $taxdata = [
            'countries' => $countries,
            'types' => $types,
            'taxidvalue' => $type === '9' ? '' : (string) ($prev['id'] ?? ''),
            'isil' => $country === 'IL',
            'showid' => $country === 'IL' && $type !== '9',
        ];
    }
    $donateform = [
        'url' => (new moodle_url('/local/kabshop/donate.php'))->out(false),
        'id' => $id,
        'cur' => $product->currency,
        'sesskey' => sesskey(),
        'custom' => $custom,
        'presets' => $presets,
        'customvalue' => $customvalue,
        'min' => max((float)$product->price, 0.01),
        'currency' => $product->currency,
        'tax' => $taxdata,
    ];
} else if ($free) {
    $action = [
        'post' => (new moodle_url('/local/kabshop/enrol.php'))->out(false),
        'id' => $id,
        'sesskey' => sesskey(),
        'label' => get_string('enrolfree', 'local_kabshop'),
        'donation' => null,
    ];
    // Окно «Безвозмездная помощь» перед записью, как в корзине WordPress: взнос или без взноса.
    // У пожертвования с двумя ценами — выбор валюты; заранее выбрана валюта витрины посетителя.
    if ($offers = catalog::free_donation_offer($product, (int) $USER->id)) {
        $selected = array_key_first($offers);
        foreach ($offers as $code => $offer) {
            if (catalog::market($offer['product']) === catalog::preferred_market()) {
                $selected = $code;
                break;
            }
        }
        $groups = [];
        foreach ($offers as $code => $offer) {
            $dpresets = [];
            foreach ($offer['presets'] as $v) {
                $dpresets[] = [
                    'value' => $v,
                    'label' => \core_payment\helper::get_cost_as_string($v, $code),
                    'checked' => $v == $offer['default'],
                ];
            }
            $groups[] = [
                'code' => $code,
                'currencyname' => catalog::currency_name($code),
                'hint' => get_string('paywith_' . catalog::market($offer['product']), 'local_kabshop'),
                'selected' => $code === $selected,
                'presets' => $dpresets,
                'min' => max((float) $offer['product']->price, 0.01),
                'max' => catalog::MAX_AMOUNT,
            ];
        }
        $action['donation'] = [
            'text' => format_text(catalog::freedonation_config('text'), FORMAT_HTML, ['context' => $context]),
            'groups' => $groups,
            'switch' => count($groups) > 1,
        ];
    }
} else if ($product->accountid && !$custom) {
    $PAGE->requires->js_call_amd('core_payment/gateways_modal', 'init');
    $area = empty($product->isalt) ? 'product' : 'productalt';
    $action = [
        'payment' => true,
        'component' => 'local_kabshop',
        'paymentarea' => $area,
        'itemid' => $id,
        'cost' => (float)$product->price,
        'successurl' => \local_kabshop\payment\service_provider::get_success_url($area, $id)->out(false),
        'description' => $name,
        'label' => get_string('buy', 'local_kabshop'),
    ];
    $couponform = [
        'url' => (new moodle_url('/local/kabshop/coupon.php'))->out(false),
        'id' => $id,
        'cur' => $product->currency,
        'sesskey' => sesskey(),
    ];
}

// Выбор валюты у товара с двумя ценами: рубли — Robokassa, основная — карта и PayPal. После
// покупки, при действующей подписке или закрытой записи выбирать нечего — вторая цена тогда
// остаётся видна рядом с основной (priceother).
$currencies = [];
if (count($variants) > 1 && !$owned && !$activesub && !$paid && $saleopen) {
    foreach ($variants as $code => $v) {
        $currencies[] = [
            'code' => $code,
            'name' => catalog::currency_name($code),
            'hint' => get_string('paywith_' . catalog::market($v), 'local_kabshop'),
            'url' => catalog::product_url($v)->out(false),
            'current' => $code === $product->currency,
        ];
    }
}

// Пришёл сюда из окна взноса при бесплатной записи — ссылка на курс, куда записался:
// до оплаты (передумал платить) и после неё (тогда в последний раз).
$aftercourse = null;
$after = $SESSION->local_kabshop_aftercourse ?? null;
if ($loggedin && $after && (int) $after['productid'] === $id
        && ($acourse = $DB->get_record('course', ['id' => $after['courseid']], 'id, fullname'))) {
    $aftercourse = [
        'href' => (new moodle_url('/course/view.php', ['id' => $acourse->id]))->out(false),
        'label' => get_string('aftercourse', 'local_kabshop',
            format_string($acourse->fullname, true, ['context' => context_course::instance($acourse->id)])),
    ];
    if ($paid) {
        unset($SESSION->local_kabshop_aftercourse);
    }
}

// Раздел витрины — ленточка над названием, как «Базовый онлайн-курс» на kabacademy.com.
$sectionname = $DB->get_field('local_kabshop_section', 'name', ['id' => $record->sectionid]);

// Вторая цена — всегда рядом с первой: и записанному, и при закрытой записи.
$otherprices = [];
foreach ($variants as $code => $v) {
    if ($code !== strtoupper((string) $product->currency)) {
        $otherprices[] = catalog::price_string($v);
    }
}

// Начало и продолжительность — сверху карточки покупки.
$facts = [];
if ($start = catalog::start_string($record)) {
    $facts[] = [
        'key' => 'start',
        'icon' => $OUTPUT->image_url('icons/calendar', 'local_kabshop')->out(false),
        'label' => get_string('fact_start', 'local_kabshop'),
        'value' => $start,
    ];
}
if (trim((string) ($record->duration ?? '')) !== '') {
    $facts[] = [
        'key' => 'duration',
        'icon' => $OUTPUT->image_url('icons/clock', 'local_kabshop')->out(false),
        'label' => get_string('fact_duration', 'local_kabshop'),
        'value' => format_string($record->duration, true, ['context' => $context]),
    ];
}

// Темы уроков и блок «Этот курс»: строка — тема или абзац.
$format = function(string $line) use ($context): string {
    return format_string($line, true, ['context' => $context]);
};
$program = array_map($format, catalog::lines($record->program ?? '', true));
$about = array_map($format, catalog::lines($record->about ?? ''));

// Запись открыта, но закроется — предупредить заранее.
$saleuntil = null;
if ($saleopen && !$owned && !$activesub && $product->kind !== 'external'
        && ($record->salestate ?? '') !== 'soon' && ($record->salestate ?? '') !== 'closed'
        && !empty($record->saleclose)) {
    $saleuntil = get_string('saleuntil', 'local_kabshop', catalog::day_string((int) $record->saleclose));
}

$data = catalog::card($product);
$data['priceother'] = $otherprices ? get_string('priceor', 'local_kabshop', implode(' · ', $otherprices)) : null;
$data['facts'] = $facts;
$data['hasfacts'] = !empty($facts);
$data['program'] = $program;
$data['hasprogram'] = !empty($program);
$data['about'] = $about;
$data['hasabout'] = !empty($about);
$data['sale'] = $salenotice;
$data['saleuntil'] = $saleuntil;
$data['ownednote'] = ($owned && $product->courseid) ? get_string('youareenrolled', 'local_kabshop') : null;
$data['description'] = trim(strip_tags((string) $record->description, '<img><iframe><video>')) !== ''
    ? format_text($record->description, $record->descriptionformat, ['context' => $context]) : null;
$data['section'] = $sectionname !== false ? format_string($sectionname, true, ['context' => $context]) : null;
$data['action'] = $action;
$data['currencies'] = $currencies;
$data['hascurrencies'] = !empty($currencies);
$data['legal'] = \local_kabshop\legal::export($market);
$data['donateform'] = $donateform;
$data['couponform'] = $couponform;
$data['couponinfo'] = $couponinfo;
$data['taxinfo'] = $taxinfo;
$data['subinfo'] = $activesub ? get_string('subactive', 'local_kabshop',
    userdate($activesub->nextcharge, get_string('strftimedate', 'langconfig'))) : null;
$data['changeurl'] = !empty($action['autopay']) ? $changeurl : null;
$data['changelabel'] = get_string($couponinfo ? 'couponremove' : ($taxinfo ? 'changedonation' : 'changeamount'),
    'local_kabshop');
$data['prereqnotice'] = $prereqnotice;
$data['nopayment'] = $saleopen && $prereqok && !$free && !$product->accountid && $product->kind !== 'external';
$data['paid'] = $paid;
$data['aftercourse'] = $aftercourse;
$data['canmanage'] = $canmanage;
$data['editurl'] = (new moodle_url('/local/kabshop/edit_product.php', ['id' => $id]))->out(false);
$data['catalogurl'] = $catalogurl->out(false);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kabshop/product', $data);
echo $OUTPUT->footer();
