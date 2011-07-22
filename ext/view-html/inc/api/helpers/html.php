<?php

class api_helpers_html {
    /**
     * Template parameters set during the execution of template rendering
     */
    static protected $raw;

    /**
     * Template command set during the execution of template rendering
     */
    static protected $command = null;

    /**
     * Template theme set during the execution of template rendering
     */
    static protected $theme = 'default';

    /**
     * Default escaping
     */
    static protected $escaping = 'html';

    /**
     * Escaping for the current execution
     */
    static protected $escapingTmp = 'html';

    /**
     * 2 Letter code of the language set during the execution of template rendering
     */
    static public $lang;

    /**
     * Template translation strings set during the execution of template rendering
     */
    static public $trans;

    /**
     * set default escaping
     *
     * @param  string  escaping (html, json, javascript ..)
     *
     * @return void
     */
    public static function setEscaping($escaping) {
        self::$escaping = $escaping;
    }

    /**
     * set default escaping
     *
     * @param  mixed   raw value to be escaped
     * @param  string  escaping (html, json, javascript ..)
     *
     * @return void
     */
    public static function escapeVariable($raw, $escaping) {
        // TODO: add support for objects
        if (is_array($raw)) {
            foreach ($raw as $key => $var) {
                $raw[$key] = self::escapeVariable($var, $escaping);
            }
        } else {
            switch ($escaping) {
            case 'html':
                if (is_string($raw) || is_object($raw)) {
                    $raw = htmlentities($raw, ENT_QUOTES, 'UTF-8');
                }
                break;
            // javascript: note http://www.rooftopsolutions.nl/article/197
            case 'js':
                $raw = json_encode($raw);
                break;
            default:
                break;
            }
        }
        return $raw;
    }

    /**
     * find a template
     *
     * @param  string  the template file name
     * @param  string  command name
     * @param  string  theme name
     *
     * @return string  the absolute path to the template file
     */
    public static function findTemplate($name, $command = null, $theme = 'default') {
        $template = API_THEMES_DIR.$theme.DIRECTORY_SEPARATOR;

        if (isset($command)) {
            $template.= $command.DIRECTORY_SEPARATOR;
        }

        $template.= $name.'.php';
        if (file_exists($template)) {
            return $template;
        }

        if ($theme === 'default') {
            return false;
        }

        $template = API_THEMES_DIR.'default'.DIRECTORY_SEPARATOR;
        if (isset($command )) {
            $template.= $command.DIRECTORY_SEPARATOR;
        }
        $template.= $name.'.php';
        if (file_exists($template)) {
            return $template;
        }

        return false;
    }

    /**
     * render a template
     *
     * @param  string  the template file to be compiled.
     * @param  array   this is the array of variables to pass to the template
     * @param  string  escaping (html, json, javascript ..)
     * @param  string  command name
     * @param  string  theme name
     *
     * @return string  the rendered content
     */
    public static function renderTemplate() {
        self::$escapingTmp = func_get_arg(2);
        if (func_num_args() > 1 && is_array(func_get_arg(1))) {
            self::$raw = func_get_arg(1);
            if (func_get_arg(2) !== null) {
                extract(self::escapeVariable(unserialize(serialize(self::$raw)), self::$escapingTmp));
            } else {
                extract(self::escapeVariable(unserialize(serialize(self::$raw)), self::$escaping));
            }
        }

        $lang = self::$lang;

        if (func_get_arg(2) !== null) {
            $trans = self::escapeVariable(unserialize(serialize(self::$trans)), self::$escapingTmp);
        } else {
            $trans = self::escapeVariable(unserialize(serialize(self::$trans)), self::$escaping);
        }

        self::$command = (func_num_args() >= 4 && func_get_arg(3) !== null) ? func_get_arg(3) : null;
        self::$theme = (func_num_args() >= 5 && func_get_arg(4) !== null) ? func_get_arg(4) : 'default';

        ob_start();
        include func_get_arg(0);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * include a template inside another template
     *
     * @param  string  the template file name
     * @param  bool    if an error should be triggered for a missing template file
     * @param  mixed   this is the array of variables to pass to the template
     *                 or "true" to pass through the current params
     *                 or anything else to pass no params
     *
     * @return string  the rendered content
     */
    public static function includeTemplate($name, $required = false, $params = true) {
        $template = self::findTemplate($name, self::$command, self::$theme);
        if (!$template) {
            if ($required) {
                trigger_error("Missing template '$name' for command '".self::$command."' and theme '".self::$theme."'");
            }
            return false;
        }

        if (!is_array($params)) {
            if ($params === true) {
                $params = self::$raw;
            } else {
                $tmpRaw = self::$raw;
                $params = array();
            }

            $escaping = self::$escapingTmp;
        } else {
            $escaping = '';
            $tmpRaw = self::$raw;
            $tmpEscaping = self::$escapingTmp;
        }

        $data = self::renderTemplate($template, $params, $escaping, self::$command, self::$theme);

        if (isset($tmpRaw)) {
            self::$raw = $tmpRaw;
        }

        if (isset($tmpEscaping)) {
            self::$escapingTmp = $tmpEscaping;
        }

        return $data;
    }
}
