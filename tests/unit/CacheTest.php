<?php
/**
 * Tests api_cache - just test we get the right instance
 */
class CacheTest extends api_testing_case_phpunit {
    
    function testGetInstance() {
        $this->assertIsA(api_cache::getInstance(), 'api_cache');
    }
    
}