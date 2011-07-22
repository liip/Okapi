<?php
/**
 * Abstract class for CRUD principle design
 *
 * This basically maps the REST principle to CRUD commands if there is no method available
 * The REST magic
 *
 * This is quite cumbersome.. but we provide the following interface from REST to CRUD:
 *
 *          POST     GET     URL
 * Create:    x              /{baseurl}/
 * Read:              x      /{baseurl}/{id}?{queryparams}
 * Update:    x       x      /{baseurl}/{id}
 * Delete:    x       x      /{baseurl}/{id}/delete
 *
 * Note: Create / Delete are special in REST/CRUD because they have not yet full support
 * of browsers (there are however DELETE and PUT requests defined in HTTP..)
 * PUT would actually do "delete and create" whereas POST can do whatever it wants
 *
 * It also provides a usage function which gives information about what parameters are
 * expected by the command with their type, need and through what method.
 *
 * You need to set a route like to following in order to let this work:
 * "/blah/:command/:id/:method
 *
 */
abstract class api_crud extends api_command {
    protected $attribs;

    protected $rgPost = Array();

    /**
     * This constructor basically matches the Rest magic to Crud magic
     *
     */
    public function __construct(&$attribs) {
        parent::__construct($attribs);
        $this->attribs = &$attribs;

        $this->rgPost = $this->request->getParameters()->post(); // Saves the post data

        if (!method_exists($this, $this->attribs['method'])) {
            // If id == create (that is, no id is set, but create)
            if ($attribs['id'] == "create") {
                $this->attribs['method'] = "create";
                $this->attribs['id'] = "";
            }

            // If id == usage (that is, no id is set, but usage)
            if ($attribs['id'] == "usage") {
                $this->attribs['method'] = "usage";
                $this->attribs['id'] = "";
            }
            
            // If id == form (that is, no id is set, but form)
            if ($attribs['id'] == "form") {
                $this->attribs['method'] = "form";
                $this->attribs['id'] = "";
            }
            
            // If we have post data it can either be create, update, delete
            // This means, that post data is not allowed on a read request (i.e. login stuff) which needs to be set via url
            if (count($this->rgPost)) {
                if (empty($attribs['id']) && empty($attribs['method'])) { // if id and method are empty, it must be create
                    $this->attribs['method'] = "create";
                } else if (empty($attribs['method'])) { // if only methd is empty, we have update
                    $this->attribs['method'] = "update";
                }
            } else {
                if (empty($attribs['id']) && empty($attribs['method'])) { // if no id and no method are set, and no post data, it must be index
                    $this->attribs['method'] = "index";
                } else if (empty($attribs['method'])) { // if no method is set but an id,  it must be read
                    $this->attribs['method'] = "read";
                }
            }
        }
    }


    /**
     * This function is able to return basic usage of a class. In future
     * versions this will include reflection about the called class.
     * If possible, it fills the usage with the already known parameters
     * This is helpful in case somebody doesn't know about the api and tries
     * to implement a frontend for your Application
     *
     * @return trure
     */
    public function usage() {
        $resource = (isset($this->attribs['command'])) ? $this->attribs['command'] : "{resource}";
        $id = (isset($this->attribs['id'])) ? $this->attribs['id'] : "{id}";

        // Throw some usage screen
        $this->data[] = new api_model_array(Array(
                                "index"=>  Array("url"=>"$resource/index",        "verb"=>"GET"),
                                "read"=>   Array("url"=>"$resource/$id/read",    "verb"=>"GET"),
                                "create"=> Array("url"=>"$resource/create",       "verb"=>"POST"),
                                "update"=> Array("url"=>"$resource/$id/update",  "verb"=>"POST"),
                                "delete"=> Array("url"=>"$resource/$id/delete",  "verb"=>"POST")),
                                'CRUD');

        $this->data[] = new api_model_array(Array(
                                "index"=>  Array("url"=>"$resource/",             "verb"=>"GET"),
                                "read"=>   Array("url"=>"$resource/$id",         "verb"=>"GET"),
                                "create"=> Array("url"=>"$resource/",              "verb"=>"POST"),
                                "update"=> Array("url"=>"$resource/$id",         "verb"=>"POST"),
                                "delete"=> Array("url"=>"$resource/$id/delete",  "verb"=>"POST")),
                                'REST');

        return True;
    }


    /**
     * Method to be called when we request the base url without specifying an id
     * example.com/resource/?query=something
     */
    abstract function index();

    /**
     * Method to be called when we send a request with post data to the base url
     * example.com/resource/ POST: name = foobar
     */
    abstract function create();

    /**
     * Method to be called when we send a request with an id in url to the base url
     * example.com/resource/13
     */
    abstract function read();

    /**
     * Method to be called when we send a request with an id in the url and post
     * data
     * example.com/resource/13 POST: name = barfoo
     */
    abstract function update();

    /**
     * Method to be called when a delete request occures
     * example.com/resource/13/delete POST: method = delete, id = 13
     */
    abstract function delete();

}
