<?php
namespace Services;

class Helper {

    public static function load() : void {

        $helperDir = ROOT_PATH. '/backend/Helpers';

        foreach (glob("$helperDir/*.php") as $filename)
        {
            if(file_exists($filename)){
                include $filename;
            }
        }

    }

}

