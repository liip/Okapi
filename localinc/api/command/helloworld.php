<?php
class api_command_helloworld extends api_command {
    public function __construct($attribs) {
        parent::__construct($attribs);
    }
    
    public function bye() {
        $redir = new api_response();
        $redir->redirect('/helloworld');
    }
}
?>
