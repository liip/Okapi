<?php
/**
*/
class api_helpers_string {
    public static function bidi($str) {
        // Reverse string UTF-8 compatible
        preg_match_all('/./us', $str, $ar);
        $str = join('',array_reverse($ar[0]));
        return trim($str);
    }   
    
    static function truncate($inStr, $length = 100, $breakWords = false, $etc = '...') {
        if ($length == 0)
        return '';
        
        if (strlen($inStr) > $length) {
            $length -= strlen($etc);
            if (!$breakWords) {
                $inStr = preg_replace('/\s+?(\S+)?$/', '', substr($inStr, 0, $length + 1));
            }
            
            return substr($inStr, 0, $length)." $etc";
        } else
        return $inStr;
    }
    
    
    public static function isUtf8($string) {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        return preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
            )*$%xs', $string);
    }
    
    /*
     * Takes a string which may be UTF-8 or ISO-8859-1/15 and returns the
     * UTF-8 encoded version.
     */
    static function ensureUtf8($string) {
        if (api_helpers_string::isUtf8($string)) {
            return $string;
        } else {
            $string = iconv("ISO-8859-1","UTF-8//ignore", $string);
            $string = preg_replace('/\p{Cc}/u', '', $string);
            return $string;
        }
    }
    
  
    /**
     * Takes an URL and encodes all query string values params.
     * This is used if we have an UTF-8 encoded URL string and want
     * to send it to the browser.
     */
    static public function encodeParamsOfUrl($url) {
        $retval = '';
        
        $pos = strpos($url, "?");
        if ($pos) {
            $retval = str_replace('%2F', '/', substr($url, 0, $pos)) . '?';

            $pathParams = explode('&', substr($url, $pos+1));
            foreach ($pathParams as $pathParam) {
                $pathParamParts = explode('=', $pathParam);
                if (count($pathParamParts) == 2) {
                    $retval .= $pathParamParts[0] . '=' . urlencode($pathParamParts[1]) . '&';
                } else {
                    $retval .= $pathParam . '&';
                }
            }
        } else {
            $retval = str_replace('%2F', '/', urlencode($url));
            return $retval;
        }
        
        if (substr($retval, strlen($retval)-1, 1) == '&') {
            $retval = substr($retval, 0, strlen($retval)-1);
        }
        return $retval;
    }
    
    static function makeUri ($title, $preserveDots = false) {
        $title = html_entity_decode($title,ENT_QUOTES,'UTF-8');
        
        $title = trim($title);
        if (!$title) {
            $title = "none";   
        }
        $newValue= $title;
        if (!$preserveDots) {
            $newValue= str_replace(".","-",$newValue);
        }
        $newValue = str_replace("@","-at-",$newValue);
        $newValue= preg_replace("/[öÖ]/u","oe",$newValue);
        $newValue= preg_replace("/[üÜ]/u","ue",$newValue);
        $newValue= preg_replace("/[äÄ]/u","ae",$newValue);
        $newValue= preg_replace("/[éèê]/u","e",$newValue);
        $newValue= preg_replace("/[Ïïíì]/u","i",$newValue);
        $newValue= preg_replace("/[ñ]/u","n",$newValue);
        $newValue= preg_replace("/[àåáâ]/u","a",$newValue);
        $newValue= preg_replace("/[ùú]/u","u",$newValue);
        $newValue= preg_replace("/[òó]/u","o",$newValue);
        $newValue= preg_replace("/[ß]/u","ss",$newValue);
        $newValue= preg_replace("/[ç]/u","c",$newValue);
        
        
        $newValue= preg_replace("/[\n\r]*/u","",$newValue);
        //removing everything else
        $newValue = strtolower($newValue);
        $newValue = preg_replace("/[^a-z0-9\.\-\_]/","-",$newValue);
        
        
        if (!$preserveDots) {
            $newValue= preg_replace("/_([0-9]+)$/u","-$1",$newValue);
        } else {
            $newValue= preg_replace("/_([0-9]+)\./u","-$1.",$newValue);
        }
		
        $newValue= preg_replace("/-{2,}/u","-",$newValue);

        $newValue = trim($newValue,"-");
        if (!$newValue) {
            $newValue = "none";
        }
        return $newValue;
    }
    
       

    /**
    * replaces all occurrences of the keys of $textfields in $subject.
    *
    * @param string $subject string containing fieldnames sourrounded by {} which should be replaced
    * @param array $textfields array of key=>value containing the field values
    * @return string string with replaced fields
    */
    function replaceTextFields($subject, $textfields) {
        foreach($textfields as $field => $value) {
            $patterns[] = '/\{'.$field.'\}/';
            $replacements[] = $value;
        }
        $subject = preg_replace($patterns, $replacements, $subject);
        return $subject;
    }
    
    /**
    * tidily prints the given fields into a string
    *
    * @param array $fields array of key=>value containing the field values
    * @param boolean $printKey when set to TRUE, the key gets printed as well
    * @return string string with formatted fields
    */
    static function formatTextFields($fields, $printKey = TRUE, $hideFields = array()) {
        $out = '';
        foreach($fields as $key => $value) {
            if($printKey) {
                $out .= sprintf('%-20s: ', $key);
            }
            if (strpos($value,"\n") !== false) {
                $value = "\n\n  ".preg_replace("#([\r\n]+)#","$1  ",$value). "\n****";
            }
            $out .= "$value\n";
        }
        return $out;
    }
    
    /**
    * strips all newlines (\\r and \\n) from the given string (utf8 save),
    * shortens repeating whitespaces to one character and strips ws from 
    * the beginning and the end.
    *
    * @param string $in string to trim
    * @return string trimmed string
    */
    static function trim($in) {
        $in = trim($in);
        $in = preg_replace('/[\s]{2,}/u', ' ', $in);
        $in = preg_replace('/[\r\n]*/u', '', $in);
        return $in;
    }
    
     static function removeDoubleSlashes($str) {
        return preg_replace("#\/{2,}#","/",$str);
    }
    
    /**
     * Escape a string for being used in a VCARD file.
     */
    static function escapeVcard($str) {
        return str_replace(array(',', ';'),
            array('\\,', '\\;'),
            $str);
    }

    /**
     * Escape apostrophs for using in JS values.
     */
    static function escapeApostroph($str) {
        return str_replace("'", "\\'", $str);
    }

    /**
     * Escape string for using in JS values (apostroph and newlines)
     */
    static function escapeJSValue($str) {
        return str_replace(
                array("'", "\n"),
                array("\\'", "\\n"),
                $str);
    }

    /**
     * Escape apostrophs and quotes for using in JS values.
     */
    static function escapeApostrophAndQuote($str) {
        return str_replace(array("'", '"'), array("\\'", '\\"'), $str);
    }
    
    /**
     * Escape apostrophs and HTML special chars
     */
    static function escapeApostrophAndHTML($str) {
        return htmlspecialchars(self::escapeApostroph($str));
    }

    /**
     * Escape value for using in CSV.
     *
     * Double exiting quotes, strip newlines.
     */
    static function escapeCSV($str) {
        return str_replace(
            array('"', "\n"),
            array('""', ''),
            $str);
    }
    
    /**
     * Remove control characters to spaces, convert
     * all whitespace chars to spaces.
     */
    static function clearControlChars($str) {
        if (is_array($str)) {
            foreach($str as $key => $value) {
                $str[$key] = self::clearControlChars($value);
            }
        } else {
            $str = self::ensureUtf8($str);
            $str = str_replace(array("\t", "\n", "\r", NULL, "\x00"), " ", $str);
            $str = preg_replace('/\p{Cc}/u', '', $str);
        }
        return $str;        
    }
    
    /**
     * gets a somehow random ID
     */
     
    static function getRandomID() {
       return substr(md5(time() . rand(0,100000)),0,16);
    }

    /**
    * Returns nice xml
    */
    static function tidyfy ($string) {
        $tidyOptions = array(
        "output-xhtml" => true,
        "show-body-only" => true,
        "clean" => false,
        "wrap" => "0",
        "indent" => false,
        "indent-spaces" => 1,
        "ascii-chars" => false,
        "wrap-attributes" => false,
        "alt-text" => "",
        "doctype" => "loose",
        "numeric-entities" => true,
        "drop-proprietary-attributes" => true
        );

        if (class_exists("tidy")) {
            $tidy = new tidy();
            if(!$tidy) {
                return $string;
            }
        } else {
            return $string;
        }

        // this preg escapes all not allowed tags...
        $tidy->parseString($string,$tidyOptions,"utf8");
        $tidy->cleanRepair();
        return (string) $tidy;
    }

    static public function makeLinksClickable($text) {
        $res = preg_replace( "#([\s\(\.\:]|\A)(http[s]?:\/\/[^\s^>^<^\)]*[^\s^>^<^\)^\.^\,])#m", "$1<a href=\"$2\">$2</a>", $text);
        return $res;
    }

    /**
     * Return an integer from the number.
     * Accepts apostrophs in the original string.
     */
    static public function parseInt($str) {
        return intval(str_replace("'", '', $str));
    }
    
    
    /**
    * takes a string of utf-8 encoded characters and converts it to a string of unicode entities
    * each unicode entitiy has the form &\#nnnnn; n={0..9} and can be displayed by utf-8 supporting
    * browsers
    *
    * from http://ch.php.net/manual/en/function.utf8-decode.php and optimized
    *
    * @param $source string encoded using utf-8 [STRING]
    * @return string of unicode entities [STRING]
    */
    static function utf2entities($source,$force = false) {
        if (!$force && api_config::getInstance()->dbIsUtf8) {
            return $source;
        }
        // array used to figure what number to decrement from character order value
        // according to number of characters used to map unicode to ascii by utf-8
        $decrement[4] = 240;
        $decrement[3] = 224;
        $decrement[2] = 192;
        $decrement[1] = 0;
        
        // the number of bits to shift each charNum by
        $shift[1][0] = 0;
        $shift[2][0] = 6;
        $shift[2][1] = 0;
        $shift[3][0] = 12;
        $shift[3][1] = 6;
        $shift[3][2] = 0;
        $shift[4][0] = 18;
        $shift[4][1] = 12;
        $shift[4][2] = 6;
        $shift[4][3] = 0;
        
        $pos = 0;
        $len = strlen ($source);
        $encodedString = '';
        while ($pos < $len) {
            $thisLetter = substr ($source, $pos, 1);
            $asciiPos = ord ($thisLetter);
            $asciiRep = $asciiPos >> 4;
            
            if ($asciiPos < 128) {
                $pos += 1;
                $thisLen = 1;
            }
            else if ($asciiRep == 12 or $asciiRep == 13) {
                // 2 chars representing one unicode character
                $thisLetter = substr ($source, $pos, 2);
                $pos += 2;
                $thisLen = 2;
            }
            else if ($asciiRep == 15) {
                // 4 chars representing one unicode character
                $thisLetter = substr ($source, $pos, 4);
                $thisLen = 4;
                $pos += 4;
            }
            else if ($asciiRep == 14) {
                // 3 chars representing one unicode character
                $thisLetter = substr ($source, $pos, 3);
                $thisLen = 3;
                $pos += 3;
            }
            
            // process the string representing the letter to a unicode entity
            
            if ($thisLen == 1) {
                $encodedLetter =$thisLetter;
            } else {
                $thisPos = 0;
                $decimalCode = 0;
                while ($thisPos < $thisLen) {
                    $thisCharOrd = ord (substr ($thisLetter, $thisPos, 1));
                    if ($thisPos == 0) {
                        $charNum = intval ($thisCharOrd - $decrement[$thisLen]);
                        $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                    }
                    else {
                        $charNum = intval ($thisCharOrd - 128);
                        $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                    }
                    
                    $thisPos++;
                }
                if ($decimalCode < 65529) {
                    $encodedLetter = "&#". $decimalCode. ';';
                } else {
                    $encodedLetter = "";
                }
            }
            $encodedString .= $encodedLetter;
            
        }
        return $encodedString;
    }

    static function clean($string) {
        return $string = preg_replace("/[^\w^\d^_^-]*/", "", $string);
    }
}
