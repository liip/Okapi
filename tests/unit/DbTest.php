<?php
/**
 * Tests the api_db class which returns database instances.
 */
class DbTest extends OkapiTestCase {
    public function testGet() {
        $db = api_db::factory('testdb');
        $this->assertIsA($db, "api_db_dummy");
    }
    
    public function testGetWithoutDSN() {
        $db = api_db::factory('testdb_nodsn');
        $this->assertIdentical($db, false);
    }
}
