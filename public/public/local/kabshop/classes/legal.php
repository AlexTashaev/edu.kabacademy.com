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
 * Продавцы, контакты и документы витрины.
 *
 * Продавцов двое: рубли принимает российский фонд, карту и PayPal — израильская ассоциация.
 * Блок внизу каталога и товара показывает продавца того рынка, к которому относится страница.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class legal {

    /** Документы: имя файла в legal/ без расширения. */
    const DOCS = ['offer', 'offer_ru', 'privacy'];

    /** Значения по умолчанию, пока настройки плагина не сохранены. */
    const DEFAULTS = [
        'seller_main' => 'Ассоциация «Бней Барух Каббала ла-Ам», ул. Неватим 12 (хаРабаш 12), '
            . 'Петах-Тиква, Израиль, POB 7422, индекс 4934829',
        'seller_ru' => 'Некоммерческий фонд «Институт перспективных исследований», '
            . 'ИНН 7701582423, ОГРН 1057746228517',
        'email_main' => 'support@kabacademy.com',
        'phone_main' => '1-700-509-209',
        'email_ru' => 'support@kabacademy.com',
        'phone_ru' => '+7 (495) 1200134',
    ];

    /**
     * Настройка плагина или значение по умолчанию.
     *
     * @param string $name ключ из DEFAULTS
     * @return string
     */
    public static function setting(string $name): string {
        $value = get_config('local_kabshop', $name);
        if ($value === false || trim((string) $value) === '') {
            return self::DEFAULTS[$name];
        }
        return trim((string) $value);
    }

    /**
     * Адрес страницы документа.
     *
     * @param string $doc
     * @return moodle_url
     */
    public static function doc_url(string $doc): moodle_url {
        return new moodle_url('/local/kabshop/legal.php', ['doc' => $doc]);
    }

    /**
     * Контекст юридического блока для шаблона.
     *
     * @param string $market 'main' или 'ru'
     * @return array
     */
    public static function export(string $market): array {
        $ru = $market === 'ru';
        $suffix = $ru ? '_ru' : '_main';
        $offer = $ru ? 'offer_ru' : 'offer';
        $phone = self::setting('phone' . $suffix);
        return [
            'paylabel' => get_string($ru ? 'legal_pay_ru' : 'legal_pay_main', 'local_kabshop'),
            'seller' => self::setting('seller' . $suffix),
            'email' => self::setting('email' . $suffix),
            'phone' => $phone,
            'phonehref' => preg_replace('/[^0-9+]/', '', $phone),
            'offerurl' => self::doc_url($offer)->out(false),
            'offerlabel' => get_string('doc_' . $offer, 'local_kabshop'),
            'privacyurl' => self::doc_url('privacy')->out(false),
            'privacylabel' => get_string('doc_privacy', 'local_kabshop'),
        ];
    }

    /**
     * HTML документа из каталога legal/ плагина.
     *
     * @param string $doc
     * @return string|null null — такого документа нет
     */
    public static function doc_html(string $doc): ?string {
        if (!in_array($doc, self::DOCS, true)) {
            return null;
        }
        $file = dirname(__DIR__) . '/legal/' . $doc . '.html';
        if (!is_readable($file)) {
            return null;
        }
        return file_get_contents($file);
    }
}
