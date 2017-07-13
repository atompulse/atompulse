<?php

namespace Atompulse\Component\Data;

/**
 * Class Transform
 * Common Data Transforms
 * @package Atompulse\Component\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class Transform
{

    /**
     * Transform an array's keys from camelCase to under_score
     * @param array $data
     * @return array
     */
    public static function fromCamelToUnder($data)
    {
        $transformed = [];
        foreach ($data as $camelKey => $value) {
            $transformed[self::unCamelize($camelKey)] = $value;
        }

        return $transformed;
    }

    /**
     * Transform arrays keys from camelCase to under_score
     * @param array $arrData
     * @return array
     */
    public static function arrayFromCamelToUnder($arrData)
    {
        $transformed = [];
        foreach ($arrData as $index => $data) {
            foreach ($data as $camelKey => $value) {
                $transformed[$index][self::unCamelize($camelKey)] = $value;
            }
        }
        return $transformed;
    }

    /**
     * Transform a string from camelCase to under_score
     * @param string $string
     * @return string
     */
    public static function unCamelize($string)
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string));
    }

    /**
     * Transliterate special characters to LATIN characters
     * @param string $str
     * @return string
     */
    public static function transliterateStr($str)
    {
//        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
//        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        $rule = 'Any-Latin; Latin-ASCII;';
        $transliterator = \Transliterator::create($rule);

        return $transliterator->transliterate($str);
    }

    /**
     * Get the controller name from a full qualified class name
     * @param $qualifiedControllerName
     * @return mixed
     */
    public static function getControllerName($qualifiedControllerName)
    {
        $nsComponents = explode('\\', $qualifiedControllerName);
        $ctrlName = str_replace('Controller', '', end($nsComponents));

        return $ctrlName;
    }

    /**
     * Pluralizes English nouns.
     *
     * @access public
     * @static
     * @param    string $word English noun to pluralize
     * @return string Plural noun
     */
    public static function pluralize($word)
    {
        $plural = [
            '/(quiz)$/i' => '\1zes',
            '/^(ox)$/i' => '\1en',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)ix|ex$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(hive)$/i' => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/(buffal|tomat)o$/i' => '\1oes',
            '/(bu)s$/i' => '\1ses',
            '/(alias|status)/i' => '\1es',
            '/(octop|vir)us$/i' => '\1i',
            '/(ax|test)is$/i' => '\1es',
            '/s$/i' => 's',
            '/$/' => 's'
        ];

        $uncountable = ['equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep'];

        $irregular = [
            'person' => 'people',
            'man' => 'men',
            'child' => 'children',
            'sex' => 'sexes',
            'move' => 'moves'
        ];

        $lowercased_word = strtolower($word);

        foreach ($uncountable as $_uncountable) {
            if (substr($lowercased_word, (-1 * strlen($_uncountable))) == $_uncountable) {
                return $word;
            }
        }

        foreach ($irregular as $_plural => $_singular) {
            if (preg_match('/(' . $_plural . ')$/i', $word, $arr)) {
                return preg_replace('/(' . $_plural . ')$/i', substr($arr[0], 0, 1) . substr($_singular, 1), $word);
            }
        }

        foreach ($plural as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return false;
    }

    /**
     * Singularizes English nouns.
     *
     * @access public
     * @static
     * @param    string $word English noun to singularize
     * @return string Singular noun.
     */
    public static function singularize($word)
    {
        $singular = [
            '/(quiz)zes$/i' => '\1',
            '/(matr)ices$/i' => '\1ix',
            '/(vert|ind)ices$/i' => '\1ex',
            '/^(ox)en/i' => '\1',
            '/(alias|status)es$/i' => '\1',
            '/([octop|vir])i$/i' => '\1us',
            '/(cris|ax|test)es$/i' => '\1is',
            '/(shoe)s$/i' => '\1',
            '/(o)es$/i' => '\1',
            '/(bus)es$/i' => '\1',
            '/([m|l])ice$/i' => '\1ouse',
            '/(x|ch|ss|sh)es$/i' => '\1',
            '/(m)ovies$/i' => '\1ovie',
            '/(s)eries$/i' => '\1eries',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([lr])ves$/i' => '\1f',
            '/(tive)s$/i' => '\1',
            '/(hive)s$/i' => '\1',
            '/([^f])ves$/i' => '\1fe',
            '/(^analy)ses$/i' => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/([ti])a$/i' => '\1um',
            '/(n)ews$/i' => '\1ews',
            '/s$/i' => '',
        ];

        $uncountable = ['equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep'];

        $irregular = [
            'person' => 'people',
            'man' => 'men',
            'child' => 'children',
            'sex' => 'sexes',
            'move' => 'moves'
        ];

        $lowercased_word = strtolower($word);
        foreach ($uncountable as $_uncountable) {
            if (substr($lowercased_word, (-1 * strlen($_uncountable))) == $_uncountable) {
                return $word;
            }
        }

        foreach ($irregular as $_plural => $_singular) {
            if (preg_match('/(' . $_singular . ')$/i', $word, $arr)) {
                return preg_replace('/(' . $_singular . ')$/i', substr($arr[0], 0, 1) . substr($_plural, 1), $word);
            }
        }

        foreach ($singular as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }

    /**
     * Converts an underscored or CamelCase word into a English
     * sentence.
     *
     * The titleize public static function converts text like "WelcomePage",
     * "welcome_page" or  "welcome page" to this "Welcome
     * Page".
     * If second parameter is set to 'first' it will only
     * capitalize the first character of the title.
     *
     * @access public
     * @static
     * @param    string $word Word to format as tile
     * @param    string $uppercase If set to 'first' it will only uppercase the
     * first character. Otherwise it will uppercase all
     * the words in the title.
     * @return string Text formatted as title
     */
    public static function titleize($word, $uppercase = '')
    {
        $uppercase = $uppercase == 'first' ? 'ucfirst' : 'ucwords';

        return $uppercase(self::humanize(self::underscore($word)));
    }

    /**
     * Returns given word as CamelCased
     *
     * Converts a word like "send_email" to "SendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "WhoSOnline"
     *
     * @access public
     * @static
     * @see variablize
     * @param    string $word Word to convert to camel case
     * @return string UpperCamelCasedWord
     */
    public static function camelize($word)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
    }

    /**
     * Converts a word "into_it_s_underscored_version"
     *
     * Convert any "CamelCased" or "ordinary Word" into an
     * "underscored_word".
     *
     * This can be really useful for creating friendly URLs.
     *
     * @access public
     * @static
     * @param    string $word Word to underscore
     * @return string Underscored word
     */
    public static function underscore($word)
    {
        return mb_strtolower(
            preg_replace(
                '/[^A-Z^a-z^0-9]+/',
                '_',
                preg_replace(
                    '/([a-zd])([A-Z])/',
                    '\1_\2',
                    preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $word)
                )
            )
        );
    }

    /**
     * Returns a human-readable string from $word
     *
     * Returns a human-readable string from $word, by replacing
     * underscores with a space, and by upper-casing the initial
     * character by default.
     *
     * If you need to uppercase all the words you just have to
     * pass 'all' as a second parameter.
     *
     * @access public
     * @static
     * @param    string $word String to "humanize"
     * @param    string $uppercase If set to 'all' it will uppercase all the words
     * instead of just the first one.
     * @return string Human-readable word
     */
    public static function humanize($word, $uppercase = '')
    {
        $uppercase = $uppercase == 'all' ? 'ucwords' : 'ucfirst';

        return $uppercase(mb_strtolower(str_replace('_', ' ', preg_replace('/_id$/', '', $word))));
    }

    /**
     * Same as camelize but first char is underscored
     *
     * Converts a word like "send_email" to "sendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "whoSOnline"
     *
     * @access public
     * @static
     * @see camelize
     * @param    string $word Word to lowerCamelCase
     * @return string Returns a lowerCamelCasedWord
     */
    public static function variablize($word)
    {
        $word = self::camelize($word);

        return mb_strtolower($word[0]) . substr($word, 1);
    }

    /**
     * Converts a class name to its table name according to rails
     * naming conventions.
     *
     * Converts "Person" to "people"
     *
     * @access public
     * @static
     * @see classify
     * @param    string $class_name Class name for getting related table_name.
     * @return string plural_table_name
     */
    public static function tableize($class_name)
    {
        return self::pluralize(self::underscore($class_name));
    }

    /**
     * Converts a table name to its class name according to rails
     * naming conventions.
     *
     * Converts "people" to "Person"
     *
     * @access public
     * @static
     * @see tableize
     * @param    string $table_name Table name for getting related ClassName.
     * @return string SingularClassName
     */
    public static function classify($table_name)
    {
        return self::camelize(self::singularize($table_name));
    }

    /**
     * Converts number to its ordinal English form.
     *
     * This method converts 13 to 13th, 2 to 2nd ...
     *
     * @access public
     * @static
     * @param    integer $number Number to get its ordinal value
     * @return string Ordinal representation of given string.
     */
    public static function ordinalize($number)
    {
        if (in_array(($number % 100), range(11, 13))) {
            return $number . 'th';
        } else {
            switch (($number % 10)) {
                case 1:
                    return $number . 'st';
                    break;
                case 2:
                    return $number . 'nd';
                    break;
                case 3:
                    return $number . 'rd';
                default:
                    return $number . 'th';
                    break;
            }
        }
    }

    /**
     * @param $string
     * @param $start
     * @param $end
     * @return string
     */
    public static function strBetween($string, $start, $end) {
        $string = " ".$string;
        $ini = strpos($string, $start);
        if ($ini == 0) return "";
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    /**
     * Transform csv file into array data
     * @param string $filename
     * @param string $delimiter
     * @return array
     */
    public static function csvFileToArray($filename='', $delimiter=',')
    {
        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    public static function csvFileToArrayGenerator($filename = '', $delimiter = ',')
    {
        $complete = false;
        try {
            $header = null;
            if (($handle = fopen($filename, 'r')) !== false) {
                while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
                    if (!$header) {
                        $header = $row;
                    } else {
                        yield array_combine($header, $row);
                    }
                }
            }
            $complete = true;
        } finally {
            if (!$complete) {
                // cleanup when loop breaks
            } else {
                // cleanup when loop completes
            }
        }
        // Do something only after loop completes
    }

    /**
     * Transform array data to csv file
     * @param $filename
     * @param $data
     */
    public static function arrayToCsvFile($filename, $data)
    {
        $fp = fopen($filename, 'w');

        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

    /**
     * Shorten a string ant transform into capitals 'john van helsing' will be JVH
     * @param $string $string
     * @return string
     */
    public static function shortenAndCapitalize($string)
    {
        $result = '';
        $parts = explode(' ', trim($string));

        foreach ($parts as $part) {
            $result = $result.strtoupper(mb_substr($part, 0, 1));
        }

        return $result;
    }

    /**
     * Given a complex array will create a simple array using as key/value the values from the original array
     * @param $data
     * @param $key
     * @param $value
     * @return array
     */
    public static function remapArrData($data, $key, $value)
    {
        /*
        Array
        (
            [1] => Array
                (
                    [value] => 1
                    [name] => NotSet
                    [label] => N/A
                )

            [2] => Array
                (
                    [value] => 2
                    [name] => Other
                    [label] => Altele
                )
        )
        remapped by (value/name):
        Array
        (
            1 => NotSet,
            2 => Other
        )
        */

        $result = [];
        foreach ($data as $item) {
            $result[$item[$key]] = $item[$value];
        }

        return $result;
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $string);
    }

}