<?php
/**
 * View factory.
 *
 * Instantiates and returns a view object according to name.
 *
 * @author   Silvan Zurbruegg
 */
class api_view {
    /** Prefix for view class names. */
    private static $classNameBase    = "_views_";    
    
    /* FIXME: make this somehow global/config option */
    private static $defaultNamespace = API_NAMESPACE;
    
    /** Protected constructor. Use api_view::factory(). */
    protected function __construct() {
    }
    
    /**
     * Return view object according to name.
     *
     * If a filename with extension is part of the request uri, the
     * class name of the view is attempted to be resolved  considering
     * the extension (i.e. /foo.rss -> api_views_default_rss). If no
     * subview matching the extension is available, try again for a
     * standard view for the particular extension (i.e. api_views_ext).
     * Default is to instantiate the configured view.
     *
     * @param $name string: View name to instantiate.
     * @param $request api_request: Request object.
     * @param $route hash: Route which matched the current request.
     * @param $response api_response: Response object.
     * @todo  Is omitextension still needed here?
     */
    public static function factory($name, $request, $route, $response) {
        $rgNamespace = Array();
        $ext = null;
        if ($request->getFilename() != '') {
            preg_match("#\.([a-z]{3,4})$#", $request->getFilename(), $matches);
            if (isset($matches[1]) && !empty($matches[1])) {
                $ext = $matches[1];
            }
        }
        
        if (isset($route['namespace'])) {
            $rgNamespace[] = $route['namespace'];
        }
        $rgNamespace[] = api_view::$defaultNamespace;
        
        
        foreach ($rgNamespace as $ns) {
            if (($obj = api_view::getViewWithNamespace($ns, $ext, $name, $route, $response)) != false) {
                return $obj;
            }
        }
        
        return false;
    }
    
    
    private static function getViewWithNamespace($ns, $ext, $name, $route, $response) {
        $omitExt = (!empty($route['view']['omitextension']) && $route['view']['omitextension']) ? true : false;
        $className = $ns.api_view::$classNameBase.strtolower($name);
        
        if ($ext != null && $omitExt === false) {
            /**
             * Try with view api_views_viewname_ext.
             * View is a subview of defined view
             **/
            $classNameExt = $className."_".$ext;
            if (class_exists($classNameExt)) {
                $obj = new $classNameExt($route);
                $obj->setResponse($response);
                if ($obj instanceof $classNameExt) {
                    return $obj;
                }
            } else {
                
                /**
                 * Try with api_views_ext 
                 * View is a standard view for ext 
                 */
                $classNameExt = api_view::$classNameBase.$ext;
                if (class_exists($classNameExt)) {
                    $obj = new $classNameExt($route);
                    $obj->setResponse($response);
                    if ($obj instanceof $classNameExt) {
                        return $obj;
                    }           
                }
            
            }
        }
        
        if (class_exists($className)) {
            $obj = new $className($route);
            $obj->setResponse($response);
            if ($obj instanceof $className) {
                return $obj;
            }
        }
        
        
        
        return false;
    }
}
