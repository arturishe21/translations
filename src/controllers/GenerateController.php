<?php namespace Vis\Translations;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Illuminate\Support\Str;
use Yandex\Translate\Translator;
use Vis\Translations\Translate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Vis\Translations\Trans;



class GenerateController extends Controller
{
    private $arr_fraze = array();
    private $files = array();
    private $arr_fraze_tags = array();

    public function getIndex()
    {
        include('simple_html_dom.php');


        $this->scan(app_path()."/views/");


        $this->check_db();

        $this->file_replace();
    }



    //сканим папки и файлы в них
    private function scan($path){

        $file = scandir($path);
        $file = array_slice($file,2);
        $i=0;

        //парсер файлов и занесение фраз в масив
        foreach ($file as $el)
        {

            //если каталог
            if(strpos(basename($path.$el),".")===false){

                $this->scan($path.$el."/");
            }




            $html = file_get_html($path.$el);

            $this->files[] =  $path.$el;


            if($html){
                foreach($html->find('a,span,li,td,p,option,em,h1,h2,h3,h4,h5,h6,strong,div,label,input,u') as $e){
                    //   foreach($html->find('option') as $e){
                    $fraze = trim($e->innertext);

                    if($e->value){
                        $fraze = trim($e->value);
                    }
                    if($e->title){
                        $fraze = trim($e->title);
                    }
                    if($e->placeholder){
                        $fraze = trim($e->placeholder);
                    }



                    $frase_input = "";

                    preg_match_all("/<input.*>(.*)<\/label>/", $fraze, $matches);

                    if(isset($matches[1][0])) {
                        $frase_input = strip_tags(trim($matches[1][0]));
                    }


                    preg_match_all("/<option.*>(.*)<\/option>/", $fraze, $matches2);
                    if(isset($matches2[1][0])) {
                        $frase_input = strip_tags(trim($matches2[1][0]));
                    }


                    if(isset($frase_input)) {

                        if ($frase_input&&strlen($frase_input)>2&&strpos($frase_input, "#") === false&&strpos($frase_input, ")}}") === false&&strpos($frase_input, "<") === false&&strpos($frase_input, "<?") === false&&!is_numeric($frase_input)&&preg_match('~[а-яА-ЯЁё]~', $frase_input)) {
                            $this->arr_fraze[$frase_input] = $frase_input;
                            $this->arr_fraze_tags[$frase_input] = "{{__('" . $frase_input . "')}}";
                        }
                    }

                    if($fraze && strlen($fraze)>2 && strpos($fraze,"#")===false && strpos($fraze,"<")===false  && strpos($frase_input,")}}")===false && strpos($fraze,"{{")===false && !is_numeric($fraze) && preg_match( '~[а-яА-ЯЁё]~', $fraze )){
                        $this->arr_fraze[$fraze] = $fraze;
                        $this->arr_fraze_tags[$fraze] = "{{__('" .$fraze. "')}}";
                    }elseif(strpos($fraze,"<")!==false){

                        $fraze = strip_tags(trim(substr($fraze,0,strpos($fraze,"<"))));
                        if($fraze && strlen($fraze)>2 && strpos($fraze,"#")===false && strpos($fraze,"<")===false && strpos($frase_input,")}}")===false && strpos($fraze,"{{")===false && !is_numeric($fraze) && preg_match( '~[а-яА-ЯЁё]~', $fraze )){
                            $this->arr_fraze[$fraze] = $fraze;
                            $this->arr_fraze_tags[$fraze] = "{{__('" . $fraze. "')}}";

                        }
                    }elseif(strpos($fraze,">")!==false){

                        $fraze = strip_tags(trim(substr($fraze,strpos($fraze,">"),strlen($fraze))));

                        if($fraze && strlen($fraze)>2 && strpos($fraze,"#")===false && strpos($frase_input,")}}")===false && strpos($fraze,"<")===false && strpos($fraze,"{{")===false && !is_numeric($fraze) && preg_match( '~[а-яА-ЯЁё]~', $fraze )){
                            $this->arr_fraze[$fraze] = $fraze;
                            $this->arr_fraze_tags[$fraze] = "{{__('".$fraze."')}}";

                        }
                    }elseif(strpos($fraze,"<")!==false && strpos($fraze,">")!==false){
                        $fraze = strip_tags(trim(substr($fraze,strpos($fraze,">"),strpos($fraze,"<"))));

                        if($fraze && strlen($fraze)>2 && strpos($fraze,"#")===false && strpos($frase_input,")}}")===false && strpos($fraze,"<")===false && strpos($fraze,"{{")===false && !is_numeric($fraze) && preg_match( '~[а-яА-ЯЁё]~', $fraze )){
                            $this->arr_fraze[$fraze] = $fraze;
                            $this->arr_fraze_tags[$fraze] = "{{__('".$fraze."')}}";

                        }
                    }
                }

            }


        }

    }


    private function check_db(){
        $langs = Config::get('translations::config.alt_langs');

        foreach($this->arr_fraze as $el){

             $el_slashes = addslashes($el);

             $check_phrase =  DB::table("translations_phrases")->whereRaw(" phrase like BINARY '$el_slashes' ")->count();


            if($check_phrase==0){


                $model = new  Trans;
                $model->phrase = trim($el_slashes);
                $model->save();

                $id_last = $model->id;

                foreach($langs as $k=>$ellg) {

                    $lg = $ellg;
                    if ($ellg == "ua") {
                        $ellg = "uk";
                    }

                    $def_lang = Config::get('translations::config.def_locale');
                    if ($def_lang == "ua") {
                        $def_lang = "uk";
                    }


        
                    $translator = new Translator(Config::get('builder::translate_cms.api_yandex_key'));

                    $translation = $translator->translate($el, $def_lang.'-'.$ellg);

                   

                    if (isset($translation->getResult()[0])) {
                        $text = $translation->getResult()[0];
                        
                    }
                    
                    

                    if ($ellg == "uk") {
                        $ellg = "ua";
                    }

                    if ($text) {
                        $translate_rec = new Translate;
                        $translate_rec->lang = $ellg;
                        $translate_rec->translate = $text;
                        $translate_rec->id_translations_phrase = $id_last;
                        $translate_rec->save();

                    }
                }

                }

        }


    }

    private function file_replace(){

        $arr_fraze = array_keys($this->arr_fraze) ;



        uasort($arr_fraze,'cmp');


        $arr_fraze = array_reverse($arr_fraze);


        $arr_fraze_tags = array_values($this->arr_fraze_tags) ;

        uasort($arr_fraze_tags,'cmp');
        $arr_fraze_tags = array_reverse($arr_fraze_tags);


        foreach($this->files as $el){

            $text = file_get_contents($el);

            $text = str_replace($arr_fraze, $arr_fraze_tags, $text);

            $arr_frazi_for_zamenni = array();
            $arr_frazi_for_zamenni_clear = array();


            preg_match_all("/\{#(.*\{#.*#\}.*)#\}/", $text, $matches);

            $arr_frazi_for_zamenni = $matches[1];


            foreach($arr_frazi_for_zamenni as $k=>$el_fr){
                $arr_frazi_for_zamenni_clear[] = str_replace("#}","",str_replace("{#","",$el_fr));
            }

            $text = str_replace($arr_frazi_for_zamenni, $arr_frazi_for_zamenni_clear, $text);

            if(is_file($el)) {
                $fp = fopen($el, 'w');
                fwrite($fp, $text);
                fclose($fp);
            }

        }

    }


}