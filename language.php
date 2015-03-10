<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

if (!class_exists('Base'))
{
  exit;
}

class language extends Base {
    const ALL_LANGUAGES_ID = "cops:languages";

    public $id;
    public $lang_code;
    public $print_name;

    public function __construct($pid, $plang_code, $print_name) {
        $this->id = $pid;
        $this->lang_code = $plang_code;
        $this->print_name = $print_name;
    }

    public function getUri () {
        return "?page=".parent::PAGE_LANGUAGE_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_LANGUAGES_ID.":".$this->id;
    }

    public static function getLanguageString ($code) {
        $string = localize("languages.".$code);
        if (preg_match ("/^languages/", $string)) {
            return $code;
        }
        return $string;
    }

    public static function getCount() {
        // str_format (localize("languages.alphabetical", count(array))
        return parent::getCountGeneric ("languages", self::ALL_LANGUAGES_ID, parent::PAGE_ALL_LANGUAGES);
    }

    public static function getLanguageById ($languageId) {
        $result = parent::getDb ()->prepare('select languages.id, languages.lang_code, language_codes.print_name
from languages
join language_codes on language_codes.lang_id = languages.lang_code
where languages.id = ?');
        $result->execute (array ($languageId));
        if ($post = $result->fetchObject ()) {
            return new Language ($post->id, $post->lang_code, $post->print_name);
        }
        return NULL;
    }

    public static function getLanguagesByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select languages.lang_code, languages.id, language_codes.print_name
from books_languages_link, languages
join language_codes on language_codes.lang_id = languages.lang_code
where books_languages_link.lang_code = languages.id
and book = ?
order by item_order');
        $result->execute (array ($bookId));
        $languageArray = array ();
        while ($post = $result->fetchObject ()) {
            array_push ($languageArray, new Language ($post->id, $post->lang_code, $post->print_name));
        }
        return $languageArray;
    }

    public static function getAllLanguages() {
        $result = parent::getDb ()->query('select languages.id as id, languages.lang_code as lang_code, language_codes.print_name, count(*) as count
from languages, books_languages_link
join language_codes on language_codes.lang_id = languages.lang_code
where languages.id = books_languages_link.lang_code
group by languages.id, books_languages_link.lang_code
order by language_codes.print_name');
        $entryArray = array();

        while ($post = $result->fetchObject ())
        {
            $language = new Language ($post->id, $post->lang_code, $post->print_name);
            array_push ($entryArray, new Entry ($language->print_name, $language->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($language->getUri ())), "", $post->count));
        }
        return $entryArray;
    }
}
