<?php
class hashed_password implements say, compare
{
    private $password;
    private $hashed_password;

    function __construct($password)
    {
        $this -> password = $password;
        $this -> hashed_password = password_hash($password, PASSWORD_BCRYPT);
    }

    function say()
    {
        return $this -> hashed_password;
    }

    function compare($password)
    {
        return password_verify($this -> password, $password);
    }

}
interface compare
{
    function compare($password);
}
?>