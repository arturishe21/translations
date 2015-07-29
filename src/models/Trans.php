<?php namespace Vis\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Trans extends Model {

    protected $table = 'translations_phrases';

    //Некоторые правила валидиции
    public static $rules = array(
        'phrase' => 'required|unique:translations_phrases'
    );

    protected $fillable = array('phrase');

    public $timestamps = false;

    public function getTrans()
    {
        $res = $this->hasMany('Vis\Translations\Translate', 'id_translations_phrase')->get()->toArray();

        if ($res) {
            foreach ($res as $k=>$el) {
                $trans[$el['lang']] = $el['translate'];
            }

            return $trans;
        }
    }

    //filling cache
    public static function fillCacheTrans()
    {
        if (Cache::get('translations')) {
            $array_translate = Cache::get('translations');
        } else {
            $translations_get = DB::table("translations_phrases")
                ->leftJoin('translations', 'translations.id_translations_phrase', '=', 'translations_phrases.id')
                ->get(array("translate", "lang", "phrase"));

            $array_translate = array();
            foreach ($translations_get as $el) {
                $array_translate[$el['phrase']][$el['lang']]= $el['translate'];
            }

            Cache::forever('translations', $array_translate);
        }

        return $array_translate;
    }

    //recache translate
    public static function reCacheTrans()
    {
        Cache::forget("translations");
        self::fillCacheTrans();
    }
}