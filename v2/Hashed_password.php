<?php
class Hashed_password implements Say_password
{
    private $hashed_password;

    function __construct($password)
    {
        $this -> hashed_password = password_hash($password, PASSWORD_BCRYPT);
    }

    function say_password()
    {
        return $this -> hashed_password;
    }
}

interface Say_password
{
    function say_password();
}
?>