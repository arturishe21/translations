<?php namespace Vis\Translations;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Vis\Translations\GoogleTranslate;
use Vis\Translations\Translate;

class TranslateController extends Controller
{
    /*
     * index page
     */
    public function fetchIndex()
    {
        $search_q = Input::get("search_q");

        $count_show = Input::get("count_show") ? Input::get("count_show"): "20";

        $allpage = Trans::orderBy('id', "desc");

        if ($search_q) {
            $allpage = $allpage->where("phrase", 'LIKE', '%' . $search_q . '%');
        }

        $allpage = $allpage->paginate($count_show);

        $breadcrumb[Config::get('translations::config.title_page')] = "";

        $view = 'translations::trans';
        if (Request::ajax()) {
            $view = "translations::part.table_center";
        }

        $langs = Config::get('translations::config.alt_langs');

        return View::make($view)
            ->with('title', Config::get('translations::config.title_page'))
            ->with('breadcrumb', $breadcrumb)
            ->with("data", $allpage)
            ->with("langs", $langs)
            ->with("search_q", $search_q)
            ->with("count_show", $count_show);
    }//end fetchIndex

    /*
     * create translate popup
     */
    public function fetchCreate()
    {
        $langs = Config::get('translations::config.alt_langs');
        return View::make('translations::part.form_trans')->with(
            "langs", $langs
        );
    }//end fetchCreate

    /*
     * save translate
     */
    public function doSaveTranslate()
    {
        parse_str(Input::get('data'), $data);

        $validator = Validator::make($data, Trans::$rules);
        if ($validator->fails()) {
            return Response::json(
                array(
                    'status' => 'error',
                    "errors_messages" => $validator->messages()
                )
            );
        }

        $model = new  Trans;
        $model->phrase = trim($data['phrase']);
        $model->save();

        $langs = Config::get('translations::config.alt_langs');

        foreach ($data as $k => $el) {
            if (in_array($k, $langs) && $el && $model->id) {
                $model_trans = new  Translate;
                $model_trans->translate = trim($el);
                $model_trans->lang = $k;
                $model_trans->id_translations_phrase = $model->id;
                $model_trans->save();
            }
        }

        Trans::reCacheTrans();

        return Response::json(
            array('status' => 'ok', "ok_messages" => "Фраза успешно добавлена")
        );
    }// end doSaveTranslate

    public function doDelelePhrase()
    {
        $id_record = Input::get("id");
        $record = Trans::find($id_record)->delete();

        Trans::reCacheTrans();

        return Response::json(array('status' => 'ok'));
    } //end doDelelePhrase

    /*
     * autotranslate
     */
    public function doGoogleTranslate()
    {

        $lang = Input::get("lang");
        $phrase = Input::get("phrase");
        $langs_def = Config::get('translations::config.def_locale');

        $lang = str_replace("ua", "uk", $lang);
        $langs_def = str_replace("ua", "uk", $langs_def);

        $text = GoogleTranslate::staticTranslate($phrase, $langs_def, $lang);

        $lang = str_replace("uk", "ua", $lang);
        $arr_res = array("lang" => $lang, "text" => $text);

        echo json_encode($arr_res);

    }//end doGoogleTranslate

    /*
     * save phrase
     */
    public function doSavePhrase()
    {
        $lang = Input::get("name");
        $phrase = Input::get("value");
        $id = Input::get("pk");

        if ($id && $phrase && $lang) {
            $phrase_change = Translate::where("id_translations_phrase", $id)->where("lang", $lang)->first();
            $phrase_change->translate = $phrase;
            $phrase_change->save();
        }
        Trans::reCacheTrans();
    }//end doSavePhrase
}