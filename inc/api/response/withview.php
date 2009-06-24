<?php
class api_response_withview extends api_response {

    /**
     * Enter description here...
     *
     * @var api_views_common a view object
     */
    protected $view = null;

    public function __construct(api_views_common $view = null) {
        $this->view = $view;
        $this->view->setResponse($this);

        parent::__construct();
    }

    public function setXsl($xsl) {
        $this->setViewParam("xsl", $xsl);
    }

    public function runView() {
        ob_start();
        $this->view->prepare();
        $this->view->dispatch($this->getInputData());
    }



}

