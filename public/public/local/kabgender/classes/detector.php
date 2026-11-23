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

namespace local_kabgender;

/**
 * Определение пола по имени/фамилии (кириллица RU/UA + латинская транслитерация).
 *
 * Чистый PHP без зависимостей от Moodle — можно тестировать автономно.
 * Возвращает 'male' | 'female' | null (нет уверенности → null, поле не трогаем).
 *
 * @package    local_kabgender
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class detector {

    /** @var string[] Мужские имена-исключения (не определяются эвристикой окончаний). Нормализованы. */
    const MALE = [
        // Кириллица: мужские на -а/-я (полные и уменьшительные).
        'никита', 'илья', 'данила', 'кузьма', 'фома', 'лука', 'савва', 'сава', 'добрыня',
        'дима', 'вова', 'володя', 'коля', 'толя', 'ваня', 'петя', 'вася', 'костя', 'сережа',
        'леша', 'алеша', 'гриша', 'миша', 'паша', 'гоша', 'жора', 'боря', 'федя', 'митя',
        'сеня', 'тема', 'рома', 'степа', 'сема', 'юра', 'лева', 'гена', 'кеша', 'тима',
        'валера', 'витя', 'веня', 'гера', 'моня', 'фима', 'изя', 'яша', 'левушка',
        // Украинские мужские формы (после нормализации і→и).
        'микола', 'михайло', 'дмитро', 'павло', 'петро', 'данило', 'василь', 'олесь',
        'игор', 'евген', 'сергий', 'андрий', 'олексий', 'назарий', 'кость',
        // Мужские, совпадающие с фамильными суффиксами (нужны для распознавания
        // перепутанного порядка «Фамилия Имя»).
        'лев', 'яков', 'константин', 'валентин', 'мартин', 'устин', 'северин', 'карен',
        'lev', 'yakov', 'iakov', 'konstantin', 'kostiantyn', 'valentin', 'valentyn',
        'martin', 'ustin', 'severin',
        // Мужские на -ь / -о / гласную (иврит, идиш и пр.).
        'игорь', 'лазарь', 'шауль', 'рауль', 'мигель', 'даниэль', 'габриэль', 'михаэль',
        'эммануэль', 'самуэль', 'моше', 'шломо', 'цви', 'эли', 'ави', 'коби', 'йоси',
        'рами', 'шай', 'бени', 'дани', 'гади', 'узи',
        // Латиница: мужские на -a и прочие исключения из правил.
        'nikita', 'ilya', 'illia', 'ilia', 'illya', 'danila', 'danylo', 'kuzma', 'foma',
        'luka', 'sava', 'savva', 'misha', 'pasha', 'dima', 'vova', 'volodya', 'vanya',
        'vania', 'petya', 'petia', 'vasya', 'vasia', 'kostya', 'kostia', 'seryozha',
        'serezha', 'lyosha', 'lesha', 'grisha', 'gosha', 'zhora', 'borya', 'boria',
        'fedya', 'fedia', 'mitya', 'mitia', 'senya', 'senia', 'tyoma', 'roma', 'styopa',
        'stiopa', 'yura', 'iura', 'lyova', 'tolya', 'tolia', 'kolya', 'kolia', 'gena',
        'valera', 'vitya', 'vitia', 'yasha', 'mykola', 'mikola', 'mykhailo', 'mikhailo',
        'dmytro', 'dmitro', 'pavlo', 'petro', 'moshe', 'shlomo', 'kuzia',
    ];

    /** @var string[] Женские имена-исключения (на согласную/-ь/-о и латинские особые). Нормализованы. */
    const FEMALE = [
        // Кириллица: женские на -ь и согласную.
        'любовь', 'любов', 'нинель', 'жизель', 'николь', 'изабель', 'мишель', 'рашель',
        'руфь', 'эсфирь', 'юдифь', 'суламифь', 'рахель', 'рахиль', 'эстер', 'эсфир',
        'тамар', 'яэль', 'мириам', 'мирьям', 'ширин', 'элен', 'ирэн', 'кармен', 'инес',
        'айгуль', 'гузель', 'гузэль', 'асель', 'гульназ', 'гульнур', 'айнур', 'айсулу',
        'гаухар', 'меруерт', 'акмарал', 'нургуль', 'жибек', 'айгерим', 'перизат',
        'ляззат', 'карлыгаш', 'айжан', 'жанар', 'асем', 'назгуль', 'айнагуль',
        'гульжан', 'раушан',
        // Ивритские женские.
        'галит', 'орит', 'ирит', 'нурит', 'ронит', 'лиат', 'эйнат', 'анат', 'михаль',
        'авиталь', 'орли', 'рути', 'тали', 'шира', 'наоми', 'керен', 'шарон', 'ципи',
        // Армянские женские.
        'анаит', 'ануш', 'сирануш', 'гаяне', 'лусине', 'армине', 'татев',
        // Западноевропейские женские (в т.ч. на -ин, чтобы не путать с фамилиями).
        'карин', 'катрин', 'кристин', 'эвелин', 'жаклин', 'каролин', 'селин', 'сабин',
        'элин', 'хелен', 'элеанор', 'джанет', 'сюзан', 'фарах',
        // Кириллица: уменьшительные/формы, которые не решить окончанием.
        'лера', 'мира', 'ника', 'соня', 'ася', 'нюта',
        // Женские, не решаемые окончанием (часто при мужской форме фамилии:
        // «Натали Фролов» — израильский стиль записи).
        'натали', 'нелли', 'люси', 'ирен', 'ирис', 'мейталь', 'ревиталь', 'таис',
        'асмик', 'гоар', 'лали', 'русудан', 'салтанат', 'нурай', 'чарос', 'нигяр',
        'нигар',
        // Латиница: женские, которые правила отдали бы мужчинам или не решили.
        'lyubov', 'liubov', 'lubov', 'nelly', 'nelli', 'natali', 'nataly', 'mary',
        'ruth', 'esther', 'ester', 'tamar', 'yael', 'miriam', 'rachel', 'rakhel',
        'nicole', 'nikol', 'michelle', 'carmen', 'karmen', 'aigul', 'guzel', 'asel',
        'gulnaz', 'gulnur', 'lera', 'nika', 'sonya', 'sonia', 'olesya', 'olesia',
        'ainur', 'aynur', 'perizat', 'lyazzat', 'karlygash', 'aizhan', 'nazgul',
        'galit', 'orit', 'irit', 'nurit', 'ronit', 'liat', 'einat', 'anat', 'michal',
        'avital', 'orly', 'ruti', 'tali', 'shira', 'naomi', 'keren', 'sharon',
        'anahit', 'anush', 'siranush', 'gayane', 'lusine', 'armine', 'tatev',
        'karin', 'katrin', 'kristin', 'evelin', 'evelyn', 'jacqueline', 'karolin',
        'caroline', 'celine', 'sabine', 'elin', 'helen', 'eleanor', 'janet', 'susan',
        'jane', 'holly', 'jennifer', 'elizabeth', 'margaret', 'farah', 'natalie',
        'gohar', 'asmik', 'lali', 'rusudan', 'saltanat', 'nuray', 'charos', 'nigar',
        'tais', 'eirini', 'oksi', 'oxi', 'lusi', 'lucy', 'iris', 'meital', 'revital',
    ];

    /** @var string[] Неоднозначные имена: решаются только по фамилии. Нормализованы. */
    const AMBIGUOUS = [
        'саша', 'шура', 'женя', 'валя', 'слава', 'sasha', 'shura', 'zhenya', 'zhenia',
        'valya', 'valia', 'slava', 'alex', 'karen',
    ];

    /** @var string[] Мусорные/тестовые имена — пол не определяем вовсе. */
    const JUNK = [
        'test', 'тест', 'admin', 'админ', 'administrator', 'user', 'demo', 'демо',
        'smoke', 'проба', 'аноним', 'anonymous', 'noname', 'qwerty', 'kab',
    ];

    /** @var string[] Женские суффиксы фамилий, кириллица (проверяются раньше мужских). */
    const LN_FEMALE_CYR = ['ова', 'ева', 'ина', 'ына', 'ая', 'яя'];

    /** @var string[] Мужские суффиксы фамилий, кириллица. */
    const LN_MALE_CYR = ['ов', 'ев', 'ин', 'ын', 'ий', 'ый'];

    /** @var string[] Женские суффиксы фамилий, латиница. */
    const LN_FEMALE_LAT = ['ova', 'eva', 'ina', 'yna', 'aya', 'aia', 'ska'];

    /** @var string[] Мужские суффиксы фамилий, латиница. */
    const LN_MALE_LAT = ['ov', 'ev', 'in', 'yn', 'iy', 'yi', 'ski', 'sky', 'skyi', 'skiy', 'skii'];

    /**
     * Определить пол по имени и фамилии.
     *
     * @param string $firstname имя (как ввёл пользователь)
     * @param string $lastname фамилия
     * @return string|null 'male' | 'female' | null если нет уверенности
     */
    public static function detect(string $firstname, string $lastname = ''): ?string {
        $first = self::first_token(self::normalize($firstname));
        if ($first === '' || mb_strlen($first) < 2 || in_array($first, self::JUNK, true)) {
            return null;
        }

        // 1. Словари исключений.
        if (in_array($first, self::MALE, true)) {
            return 'male';
        }
        if (in_array($first, self::FEMALE, true)) {
            return 'female';
        }
        // 2. Неоднозначные имена (Саша, Женя…) решает только фамилия.
        if (in_array($first, self::AMBIGUOUS, true)) {
            return self::by_lastname(self::normalize($lastname));
        }

        $byending = self::by_ending($first);
        if ($byending === 'female') {
            // Имя на -а/-я — сильный женский сигнал, унисекс-фамилии
            // (Литвин, Волошин, Цой…) его не перебивают.
            return 'female';
        }

        $ln = self::normalize($lastname);

        // 3. Фамилия на -ова/-ева/-ина/-ая — почти стопроцентный женский маркер,
        // сильнее слабого сигнала «имя на согласную → муж» (Бикамал Есиркегенова).
        if (self::by_lastname($ln, true) === 'female') {
            return 'female';
        }

        // 4. Перепутанный порядок полей «Фамилия Имя» (Гладышев Маргарита).
        $reversed = self::by_reversed($first, $ln);
        if ($reversed !== null) {
            return $reversed;
        }

        // 5. Имя на согласную → муж.
        if ($byending === 'male') {
            return 'male';
        }

        // 6. Имя не решило (напр. на -ь/-о) — суффикс фамилии.
        return self::by_lastname($ln);
    }

    /**
     * Нормализация: trim, lowercase, ё→е, украинские буквы к общему виду, без апострофов.
     *
     * @param string $s исходная строка
     * @return string
     */
    public static function normalize(string $s): string {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $s = str_replace(
            ['ё', 'і', 'ї', 'є', 'ґ', "'", 'ʼ', '’', '`', '´'],
            ['е', 'и', 'и', 'е', 'г', '', '', '', '', ''],
            $s
        );
        return $s;
    }

    /**
     * Первый токен строки (до пробела/дефиса), только буквы.
     *
     * @param string $s нормализованная строка
     * @return string
     */
    protected static function first_token(string $s): string {
        $parts = preg_split('/[\s\-_.,]+/u', $s, -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) {
            return '';
        }
        $token = $parts[0];
        // Токены с цифрами и прочим мусором не считаем именем.
        if (!preg_match('/^[a-zа-яеиэюяьъ]+$/u', $token)) {
            return '';
        }
        return $token;
    }

    /**
     * Пол по суффиксу фамилии; фамилии из нескольких слов проверяются пословно.
     *
     * @param string $lastname нормализованная фамилия
     * @param bool $femaleonly проверять только женские суффиксы
     * @return string|null
     */
    protected static function by_lastname(string $lastname, bool $femaleonly = false): ?string {
        foreach (preg_split('/[\s\-]+/u', $lastname, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $token) {
            // Тюркские патронимические маркеры — однозначный сигнал.
            if (in_array($token, ['кызы', 'кизи', 'kyzy', 'qyzy', 'kizi'], true)) {
                return 'female';
            }
            if (!$femaleonly && in_array($token, ['улы', 'уулу', 'оглы', 'огли', 'угли',
                    'uly', 'uulu', 'ogly', 'oglu', 'ugli'], true)) {
                return 'male';
            }
            if (mb_strlen($token) < 4) {
                continue;
            }
            $cyrillic = (bool) preg_match('/[а-я]/u', $token);
            $femalesuffixes = $cyrillic ? self::LN_FEMALE_CYR : self::LN_FEMALE_LAT;
            $malesuffixes = $cyrillic ? self::LN_MALE_CYR : self::LN_MALE_LAT;
            foreach ($femalesuffixes as $suffix) {
                if (self::ends_with($token, $suffix)) {
                    return 'female';
                }
            }
            if ($femaleonly) {
                continue;
            }
            foreach ($malesuffixes as $suffix) {
                if (self::ends_with($token, $suffix)) {
                    return 'male';
                }
            }
        }
        return null;
    }

    /**
     * Распознавание перепутанных полей «Фамилия Имя»: имя выглядит как мужская
     * фамилия (-ов/-ев/-ин/-ын), тогда пол определяем по полю фамилии как по имени.
     * Мужские имена с такими окончаниями (Константин, Мартин, Лев, Яков…)
     * перехватываются словарём раньше.
     *
     * @param string $first нормализованное имя (первый токен)
     * @param string $lastname нормализованная фамилия
     * @return string|null
     */
    protected static function by_reversed(string $first, string $lastname): ?string {
        $cyrillic = (bool) preg_match('/[а-я]/u', $first);
        $suffixes = $cyrillic
            ? ['ов', 'ев', 'ин', 'ын', 'ский', 'цкий']
            : ['ov', 'ev', 'in', 'yn', 'sky', 'ski', 'skyi', 'skiy'];
        $lookslikesurname = false;
        foreach ($suffixes as $suffix) {
            if (self::ends_with($first, $suffix)) {
                $lookslikesurname = true;
                break;
            }
        }
        if (!$lookslikesurname) {
            return null;
        }
        $token = self::first_token($lastname);
        if ($token === '' || mb_strlen($token) < 2
                || in_array($token, self::AMBIGUOUS, true) || in_array($token, self::JUNK, true)) {
            return null;
        }
        if (in_array($token, self::MALE, true)) {
            return 'male';
        }
        if (in_array($token, self::FEMALE, true)) {
            return 'female';
        }
        return self::by_ending($token);
    }

    /**
     * Эвристика окончания имени.
     *
     * @param string $first нормализованное имя (первый токен)
     * @return string|null
     */
    protected static function by_ending(string $first): ?string {
        if (preg_match('/[а-я]/u', $first)) {
            $last = mb_substr($first, -1, null, 'UTF-8');
            if ($last === 'а' || $last === 'я') {
                return 'female';
            }
            if (preg_match('/[бвгджзйклмнпрстфхцчшщ]$/u', $first)) {
                return 'male';
            }
            return null; // -ь, -о, -е, -и, -у и т.п. — без словаря не решаем.
        }
        if (preg_match('/^[a-z]+$/', $first)) {
            foreach (['ia' => 'female', 'ii' => 'male', 'iy' => 'male', 'yi' => 'male',
                      'ey' => 'male', 'ay' => 'male', 'oy' => 'male', 'uy' => 'male'] as $suffix => $gender) {
                if (self::ends_with($first, $suffix)) {
                    return $gender;
                }
            }
            $last = substr($first, -1);
            if ($last === 'a') {
                return 'female';
            }
            if (strpos('bcdfghjklmnpqrstvwxzyi', $last) !== false) {
                return 'male';
            }
            return null; // -o, -e, -u — без словаря не решаем.
        }
        return null;
    }

    /**
     * Мультибайтовый str_ends_with.
     *
     * @param string $haystack строка
     * @param string $needle суффикс
     * @return bool
     */
    protected static function ends_with(string $haystack, string $needle): bool {
        $len = strlen($needle);
        return $len > 0 && substr($haystack, -$len) === $needle;
    }
}
