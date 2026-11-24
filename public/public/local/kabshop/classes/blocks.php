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

use context_system;
use moodle_url;

/**
 * Блоки каталога, как на главной kabacademy.com: «Об обучающей платформе…» и «Преподаватели академии».
 * Тексты — настройки «Витрина: блоки каталога»; пока не сохранены, действуют DEFAULTS.
 *
 * @package    local_kabshop
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class blocks {

    /** Значения по умолчанию — тексты главной kabacademy.com (19.11.2026). */
    const DEFAULTS = [
        'about_title' => 'Об обучающей платформе Международной академии каббалы',
        'about_text' => '<p>Наша онлайн-платформа позволит вам познакомиться с уникальными каббалистическими '
            . 'источниками, пройти обучение у лучших преподавателей академии, общаться в онлайн-сообществе, '
            . 'получить индивидуальное сопровождение помощника-тьютора.</p>',
        'about_url' => 'https://kabacademy.com/programm-online/uznat-bolshe-ob-obuchenii-v-mak/',
        'teachers_title' => 'Преподаватели академии',
        'teachers' => "Михаэль Лайтман | /kab/img/teachers/laitman.jpg | https://kabacademy.com/about-us/dr-michael-laitman/\n"
            . "Каббалист, основатель и руководитель Международной академии каббалы «Бней Барух» и института "
            . "исследования каббалы им. Й.Ашлага, член «Всемирного совета мудрецов». Ученик каббалиста Баруха "
            . "Ашлага. Кандидат философских наук.\n\n"
            . "Александр Козлов | /kab/img/teachers/kozlov.png\n"
            . "Ведущий научный сотрудник, преподаватель Международной академии каббалы (МАК), кандидат "
            . "экономических наук, обозреватель МАК ТВ. Занимается подготовкой контента (видео, учебных "
            . "материалов), ведет свой блог, вебинары «Знакомство с каббалой» и «МАК Клуб». Редактор книг: "
            . "«Сборник трудов Бааль Сулама», «Вавилонская башня – последний ярус» и др.\n\n"
            . "Гавриэль Шустерман | /kab/img/teachers/shusterman.png\n"
            . "Выпускающий редактор Международной академии каббалы (МАК), методист и ведущий преподаватель. "
            . "Составитель учебника «Каббала для начинающих» и «Сборника трудов Бааль Сулама». Адаптирует "
            . "учебные курсы по каббале под потребности и интересы современных студентов.\n\n"
            . "Борис Белоцерковский | /kab/img/teachers/belotserkovsky.png\n"
            . "Опытный преподаватель Международной академии каббалы, где с 2017 г. руководит русскоязычным "
            . "направлением в распространении, отвечая за менеджмент всех происходящих в МАКе процессов: "
            . "преподавание, программы, материалы, технология, организация.\n\n"
            . "Михаэль Санилевич | /kab/img/teachers/sanilevich.png\n"
            . "Исполнительный директор международной некоммерческой организации «Бней Барух – Каббала ле Ам». "
            . "Автор и редактор книг по интегральному воспитанию и каббале: «Наука каббала, базовый курс», "
            . "«Построение интегрального общества» и др. Ведущий телепрограмм на 96 канале израильского ТВ и "
            . "«Открытый телеканал». Лектор по каббале с двадцатилетним стажем. Разработчик курсов "
            . "дистанционного обучения.",
    ];

    /**
     * Настройка блока или значение по умолчанию, если настройку ещё не сохраняли.
     *
     * @param string $name
     * @return string
     */
    protected static function setting(string $name): string {
        $value = get_config('local_kabshop', $name);
        return $value === false ? self::DEFAULTS[$name] : (string) $value;
    }

    /**
     * Контекст блока «Об обучающей платформе…»; null — блок выключен (пустой текст).
     *
     * @return array|null
     */
    public static function about(): ?array {
        $text = self::setting('about_text');
        if (trim(strip_tags($text)) === '') {
            return null;
        }
        $context = context_system::instance();
        $url = clean_param(trim(self::setting('about_url')), PARAM_URL);
        return [
            'title' => format_string(self::setting('about_title'), true, ['context' => $context]),
            'text' => format_text($text, FORMAT_HTML, ['context' => $context]),
            'url' => $url !== '' ? $url : null,
        ];
    }

    /**
     * Разобрать список преподавателей: абзацы через пустую строку, первая строка — «Имя | фото | ссылка».
     *
     * @param string $raw
     * @return array [['name', 'photo', 'url', 'bio'], ...]
     */
    public static function parse_teachers(string $raw): array {
        $out = [];
        foreach (preg_split('/\R\s*\R/u', trim($raw)) as $block) {
            $lines = preg_split('/\R/u', trim($block));
            $head = array_map('trim', explode('|', (string) array_shift($lines)));
            if (($head[0] ?? '') === '') {
                continue;
            }
            $out[] = [
                'name' => $head[0],
                'photo' => $head[1] ?? '',
                'url' => $head[2] ?? '',
                'bio' => trim(implode("\n", $lines)),
            ];
        }
        return $out;
    }

    /**
     * Адрес фото: имя файла из настройки «Фото преподавателей» или адрес (http… или /путь на сайте).
     *
     * @param string $photo
     * @return string|null
     */
    public static function photo_url(string $photo): ?string {
        $photo = trim($photo);
        if ($photo === '') {
            return null;
        }
        if (preg_match('#^/[\w./%-]+$#u', $photo)) {
            return $photo; // Путь на этом сайте, например статика /kab/img/teachers/….
        }
        if (preg_match('#^https?://#i', $photo)) {
            return clean_param($photo, PARAM_URL) ?: null;
        }
        $context = context_system::instance();
        $file = get_file_storage()->get_file($context->id, 'local_kabshop', 'teacherphotos', 0, '/', $photo);
        if (!$file) {
            return null;
        }
        // rev — начало хэша содержимого, как у обложек: замена фото с тем же именем сразу видна за Cloudflare.
        $url = moodle_url::make_pluginfile_url($context->id, 'local_kabshop', 'teacherphotos', 0, '/', $photo);
        $url->param('rev', substr($file->get_contenthash(), 0, 8));
        return $url->out(false);
    }

    /**
     * Контекст блока «Преподаватели академии»; null — список пуст. Первый в списке — ведущий (крупно).
     *
     * @return array|null
     */
    public static function teachers(): ?array {
        global $OUTPUT;
        $list = self::parse_teachers(self::setting('teachers'));
        if (!$list) {
            return null;
        }
        $context = context_system::instance();
        $people = array_map(function (array $t) use ($context) {
            $url = $t['url'] !== '' ? clean_param($t['url'], PARAM_URL) : '';
            return [
                'name' => format_string($t['name'], true, ['context' => $context]),
                'photo' => self::photo_url($t['photo']),
                'url' => $url !== '' ? $url : null,
                'bio' => format_text($t['bio'], FORMAT_PLAIN, ['context' => $context, 'para' => false]),
            ];
        }, $list);
        $lead = array_shift($people);
        return [
            'title' => format_string(self::setting('teachers_title'), true, ['context' => $context]),
            'lead' => $lead,
            'people' => $people,
            'haspeople' => !empty($people),
            'logo' => $OUTPUT->image_url('logo', 'local_kabshop')->out(false),
        ];
    }
}
