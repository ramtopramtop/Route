<?php
namespace PHPTDD\Code\v2;
use PHPTDD\BaseTestCase;
use hashed_password;
require 'interface.php'; 
require 'hashed_password.php';

class Hashed_passwordTest extends BaseTestCase {

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
     * @covers hashed_password::say
     **/
    public function testHashed_passwordSay() {

        $pass = new hashed_password('ramtop');
        $this -> assertEquals(true, password_verify('ramtop', $pass -> say()));
    }

    /**
     * @covers hashed_password::compare
     **/
    public function testHashed_passwordCompare() {
        $pass = new hashed_password('ramtop');
        $hashed_pass = password_hash('ramtop', PASSWORD_BCRYPT);
        $this -> assertEquals(true, $pass -> compare($hashed_pass));
    }    
}
