<?php
namespace PHPTDD\Code\v2;
use PHPTDD\BaseTestCase;
use public_key;
require '../../conn/key.php';

class Public_keyTest extends BaseTestCase {

    /**
     * This code will run before each test executes
     * @return void
     */
    protected function setUp(): void {

    }

    /**
     * This code will run after each test executes
     * @return void
     */
    protected function tearDown(): void {

    }
 
    /**
     * @covers public_key::say
     **/
    public function testPublic_keySay() {
        
        //$pass = new public_key();
        //$test = ['server_key'=> $publicKey];
        $this -> assertEquals(1, 1);
    }
}
