<?php
class api_command_helloworld extends api_command {
    public function __construct($attribs) {
        parent::__construct($attribs);
        
        $this->data[] = api_model_factory::get('array', array(array('hello' => 'world')));
    }
    
    public function bye() {
        $redir = new api_response();
        $redir->redirect('/helloworld');
    }
}
?>
