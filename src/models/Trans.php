<?php namespace Vis\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yandex\Translate\Translator;

class Trans extends Model {

    protected $table = 'translations_phrases';

    public static $rules = array(
        'phrase' => 'required|unique:translations_phrases'
    );

    protected $fillable = array('phrase');

    public $timestamps = false;

    public function getTrans()
    {
        $res = $this->hasMany('Vis\Translations\Translate', 'id_translations_phrase')->get()->toArray();

        if ($res) {
            $trans = [];
            foreach ($res as $k=>$el) {
                $trans[$el['lang']] = $el['translate'];
            }

            return $trans;
        }
    }

    /*
     * filling cache translate
     *
     * @return array
     */
    public static function fillCacheTrans()
    {
        if (Cache::get('translations')) {
            $array_translate = Cache::get('translations');
        } else {
            $array_translate = self::getArrayTranslation();
            Cache::forever('translations', $array_translate);
        }

        return $array_translate;
    }

    public static function generateTranslation($phrase, $thisLang)
    {
        if ($phrase && $thisLang) {

            $checkPresentPhrase = self::where("phrase", "like", $phrase)->first();
            if (!isset($checkPresentPhrase->id)) {
                $newPhrase = self::create (["phrase" => $phrase]);

                try {

                    $langsDef = Config::get ('translations::config.def_locale');
                    $langsAll = Config::get ('translations::config.alt_langs');

                    foreach ($langsAll as $lang) {

                        $lang = str_replace ("ua", "uk", $lang);
                        $langsDef = str_replace ("ua", "uk", $langsDef);

                        $translator = new Translator(Config::get ('builder::translate_cms.api_yandex_key'));
                        $translation = $translator->translate ($phrase, $langsDef . '-' . $lang);
                        $lang = str_replace ("uk", "ua", $lang);

                        if (isset($translation->getResult ()[0])) {
                            Translate::create (
                                [
                                    "id_translations_phrase" => $newPhrase->id,
                                    "lang" => $lang,
                                    "translate" => $translation->getResult ()[0],
                                ]
                            );

                        } else {
                            return "error.No get results";
                        }
                    }

                } catch (Yandex\Translate\Exception $e) {
                    return $e->getMessage ();
                    // handle exception
                }
                self::reCacheTrans ();
                $arrayTranslate = Trans::fillCacheTrans ();

                if (isset($arrayTranslate[$phrase][$thisLang])) {
                    $phraseReturn = $arrayTranslate[$phrase][$thisLang];
                } else {
                    $phraseReturn = "Ошибка перевода";
                }

                return $phraseReturn;
            } else {
               $translatePhrase = Translate::where("id_translations_phrase", $checkPresentPhrase->id)
                           ->where("lang", "like", $thisLang)->first();
               if (isset($translatePhrase->translate))  {

                   return $translatePhrase->translate;
               }
            }
        }

    }

    /* recache translate
     *
     * @return void
     */
    public static function reCacheTrans()
    {
        Cache::forget("translations");
        self::fillCacheTrans();
    }

    private static function getArrayTranslation()
    {
        $translations_get = DB::table("translations_phrases")
            ->leftJoin('translations', 'translations.id_translations_phrase', '=', 'translations_phrases.id')
            ->get(array("translate", "lang", "phrase"));

        $array_translate = array();
        foreach ($translations_get as $el) {
            $array_translate[$el['phrase']][$el['lang']]= $el['translate'];
        }

        return $array_translate;
    }

}