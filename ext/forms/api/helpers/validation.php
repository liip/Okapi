<?php

/**
 * validates some common field types like 'phonenumber' or 'url'
 * all methods are static
 */
class api_helpers_validation {
    
    private static $valid_tlds = 'aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';
        
    /**
     * Usage:
     * $errors = arrray();
     * $normalizedDate = api_helpers_validation::validateDate('1.2.2008', 'f_start_date', &$errors);
     *
     * @param $value mixed  the value of the field
     * @param $fieldName    string the fieldname (used to write into $errors).
     * @param $errors array the errors so far. If this field doesn't validate, an error is added into $errors
     *
     * @return string the normalized time yyyy-mm-dd or '' in case of validation error
     */
    public static function validateDate($value, $fieldName, $errors) {
        $parts = explode('.', $value);
        if (count($parts) != 3) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        list($day, $month, $year) = $parts;
        if (!is_numeric($day) || !is_numeric($month) || !is_numeric($year)) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        if (!checkdate($month, $day, $year)) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        $date = intval($year).'-'.str_pad(intval($month), 2, "0", STR_PAD_LEFT).'-'.str_pad(intval($day), 2, "0", STR_PAD_LEFT);
        return $date;
    }

    /**
     * Usage:
     * $errors = arrray();
     * $normalizedTime = api_helpers_validation::validateTime('23:12', 'f_start_time', &$errors);
     *
     * @param $value mixed  the value of the field
     * @param $fieldName    string the fieldname (used to write into $errors).
     * @param $errors array the errors so far. If this field doesn't validate, an error is added into $errors
     *
     * @return string the normalized time hh:mm:00 or '' in case of validation error
     */
    public static function validateTime($value, $fieldName, $errors) {
        $parts = explode(':', $value);
        if (count($parts) != 2) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        list($hour, $minute) = $parts;
        if (!is_numeric($hour) || !is_numeric($minute)) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        $hour = intval($hour);
        $minute = intval($minute);
        if ($hour < 0 || $hour > 24 || $minute < 0 || $minute > 60) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        
        $time = str_pad($hour, 2, "0", STR_PAD_LEFT).':'.str_pad($minute, 2, "0", STR_PAD_LEFT).':00';
        return $time;
    }

    /**
     * Usage:
     * $errors = arrray();
     * $normalizedPhone = api_helpers_validation::validatePhone('056 123 45 45', 'f_phone', &$errors);
     *
     * @param $value mixed  the value of the field
     * @param $fieldName    string the fieldname (used to write into $errors).
     * @param $errors array the errors so far. If this field doesn't validate, a 'Malformed'-error is added into $errors
     *
     * @return string the normalized phone number (spaces and extra characters removed) or '' in case of validation error
     */
    public static function validatePhone($value, $fieldName, $errors) {
        $checkValue = str_replace(array('(', ')'), '', $value);
        $checkValue = str_replace(array("/", "-", ":", "_", "."), ' ', $checkValue);
        
        if (!preg_match('#^[\+]?[0-9\ ]*$#',$checkValue)) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        
        $testNumber = str_replace(' ', '', $checkValue);
        if (strlen($testNumber) < 10) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }

        // Check if it's a Swiss number. If so, we enforce max length.
        $isSwiss = true;
        if ($testNumber[0] == '+' and substr($testNumber, 1, 2) != '41') {
            $isSwiss = false;
        } else if (substr($testNumber, 0, 2) == '00' and substr($testNumber, 2, 2) != '41') {
            $isSwiss = false;
        }

        if ($isSwiss) {
            if ($testNumber[0] == '+') {
                $testNumber = '0' . substr($testNumber, 3);
            } else if (substr($testNumber, 0, 2) == '00') {
                $testNumber = '0' . substr($testNumber, 4);
            }
                    
            if (strlen($testNumber) > 10) {
                $errors[$fieldName] = 'Malformed';
                return '';
            }
        }
        return $testNumber;
    }

    /**
     * Usage:
     * $errors = arrray();
     * $normalizedEmail = api_helpers_validation::validateEmail('peter@hotmail.com', 'f_email', &$errors);
     *
     * @param $value mixed  the value of the field
     * @param $fieldName    string the fieldname (used to write into $errors).
     * @param $errors array the errors so far. If this field doesn't validate, a 'Malformed'-error is added into $errors
     *
     * @return string the normalized email  or '' in case of validation error
     */
    public static function validateEmail($value, $fieldName, $errors) {
        $email_regex = '%^[\.a-zA-Z\-_0-9]+\@[a-zA-Z\-_0-9\.]+\.('.self::$valid_tlds.')$%';
        $email = trim($value);
        if (!preg_match($email_regex, $email)) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        return $email;
    }


    /**
     * Usage:
     * $errors = arrray();
     * $normalizedTime = api_helpers_validation::validateEmail('www.local.ch', 'f_web', &$errors);
     *
     * @param $value mixed  the value of the field
     * @param $fieldName    string the fieldname (used to write into $errors).
     * @param $errors array the errors so far. If this field doesn't validate, a 'Malformed'-error is added into $errors
     *
     * @return string the normalized url  or '' in case of validation error
     */
    public static function validateUrl($value, $fieldName, $errors) {
        $url_regex = '%^(https?://)?[\.a-zA-Zçäöü\-_0-9]+\.('.self::$valid_tlds.')(/.*)?$%';
        $url = trim($value);
        if (!preg_match($url_regex, $url)) {
            $errors[$fieldName] = 'Malformed';
            return '';
        }
        return $url;
    }
}
