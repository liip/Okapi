<?php
abstract class api_testing_case_phpunit extends PHPUnit_Framework_Testcase {
    
    function assertEqual($exp, $given, $msg = ''){
        $this->assertEquals($exp, $given, $msg);
    }
    
    function expectException(Exception $e){
        $this->setExpectedException(get_class($e));
    }
    
    function assertIsA($object, $type, $msg = ''){
        $this->assertType($type, $object, $msg);
    }
    
    function assertIdentical($obj1, $obj2, $msg = ''){
        $this->assertSame($obj1, $obj2, $msg);
    }
    
    function assertNotEqual($obj1, $obj2, $msg = ''){
        $this->assertNotSame($obj1, $obj2, $msg);        
    }
    
    function assertPattern($pattern, $string, $msg = ''){
        $this->assertRegExp($pattern, $string, $msg);
    }
    
    
    
}