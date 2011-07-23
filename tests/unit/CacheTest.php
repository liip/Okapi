<?php
/**
 * Tests api_cache - just test we get the right instance
 */
class CacheTest extends api_testing_case_phpunit {
    
    function testGetInstance() {
        if (extension_loaded('memcached') || extension_loaded('memcache')) {
            $this->assertIsA(api_cache::getInstance(), 'api_cache');
        } else {
            $this->markTestIncomplete(
                "Can't test api_cache without memcache or memcached extension loaded"
            );
        }
    }
    
}