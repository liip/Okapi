<?php

/**
 * Static helper methods for phonenumber Formatting.
 * 
 * @param $number string: The Number to Convert
 * @return $number: the converted String
 */
class api_helpers_formatPhonenumber {
    public static function format($number) {
        //get rid of spaces
        $number = preg_replace("|\s|","",$number);
        switch (strlen($number)) {
            case 10:
                $number = preg_replace("|(\d{3})(\d{3})(\d{2})(\d{2})|","$1 $2 $3 $4",$number);
            break;
        }
        return $number;
    }
}