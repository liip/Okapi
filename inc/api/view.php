<?php
/**
* View factory
*
* Instantiates and returns a view object according to name.
*
* @author   Silvan Zurbruegg
*/
class api_view {
	private static $xslAttributeKeys = array('view','theme','xsl','css','contenttype','encoding','passdom','omitextension','commandParams');
    private static $classNameBase 	 = "api_views_";
    
    protected function __construct() {
    }
    
    
    /**
    * Return view object according to name
    *
    * If a filename with extension is part of the request uri, 
    * the classname of the view is attempted to be resolved 
    * considering the extension (i.e. /foo.rss -> api_views_default_rss)
    * If no subview matching the extension is available, try
    * again for a standard view for the particular extension
    * (i.e. api_views_ext). Default is to instantiate the configured
    * view.
    */
    public static function factory($name, $request, $route, $response) {
        
        $ext = null;
        if ($request->getFilename() != '') {
        	preg_match("#\.([a-z]{3,4})$#", $request->getFilename(), $matches);
        	if (isset($matches[1]) && !empty($matches[1])) {
        		$ext = $matches[1];
        	}
        }
        
        $omitExt = (!empty($route['view']['omitextension']) && $route['view']['omitextension']) ? true : false;
        $className = api_view::$classNameBase.strtolower($name);
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
    
    
    public static function getXslAttributeKeys() {
    	return self::$xslAttributeKeys;
    }
    
    
}
