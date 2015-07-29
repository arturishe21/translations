<?php
use Vis\Translations\Trans;

//get translate
function __($phrase) {
    $this_lang = Lang::locale();

    $array_translate =  Trans::fillCacheTrans();

    if (isset($array_translate[$phrase][$this_lang])) {
        return $array_translate[$phrase][$this_lang];
    } else {
        return $phrase;
    }
}
