<?php
/**
 * Tests api_cache - just test we get the right instance
 */
class CacheTest extends UnitTestCase {
    
    function testGetInstance() {
        $this->assertIsA(api_cache::getInstance(), 'api_cache');
    }
    
}