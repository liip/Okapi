<?php

abstract class api_testing_case_phpunit extends PHPUnit_Framework_Testcase {

    function assertEqual($exp, $given, $msg = '') {
        $this->assertEquals($exp, $given, $msg);
    }

    function expectException(Exception $e) {
        $this->setExpectedException(get_class($e), $e->getMessage(), $e->getCode());
    }

    function assertIsA($object, $type, $msg = '') {
        $this->assertType($type, $object, $msg);
    }

    function assertIdentical($obj1, $obj2, $msg = '') {
        $this->assertSame($obj1, $obj2, $msg);
    }

    function assertNotEqual($obj1, $obj2, $msg = '') {
        $this->assertNotSame($obj1, $obj2, $msg);
    }

    function assertPattern($pattern, $string, $msg = '') {
        $this->assertRegExp($pattern, $string, $msg);
    }

    /**
     * Expect the next request to redirect to the given page.
     * @param $path string: Path to the page where the redirect should go to.
     * @param $absolute bool: True if the given path is absolute. Otherwise
     *                 the language and mountpath will be added automatically
     * @param $lang string: Language to which the redirect is expected. Only
     *                      relevant is $absolute=false.
     */
    public function expectRedirect($path, $code = 301, $absolute = false, $lang = 'de') {
        if (!$absolute) {
            $path = '/' . $lang . API_MOUNTPATH . substr($path, 1);
        }
        $this->expectException(new api_testing_exception("Redirect $code => $path"));
    }


    protected function prepareTemplate(PHPUnit_Util_Template $template) {
        parent::prepareTemplate($template);
        // use custom process-isolation template
        $template->setFile(dirname(__FILE__).'/template.tpl');
        $template->setVar(array('bootstrap' => API_PROJECT_DIR . 'tests/bootstrap.php'));
    }
}