<?php

class hashed_password implements say, compare
{
    private $password;
    private $hashed_password;
    
    function __construct($password)
    {
        try
        {
            if (empty($password)||is_null($password))
            {
                throw new Exception('Пароль не должен быть пустым');
            }
            $this -> password = $password;
            $this -> hashed_password = password_hash($password, PASSWORD_BCRYPT);
        }
        catch (Throwable $t)
        {
            http_response_code(400);
            exit ($t->getMessage());  
        }
    }
        
    /**
     * @testFunction testHashed_passwordSay
     */
    function say()
    {
        return $this -> hashed_password;
    }

    /**
     * @testFunction testHashed_passwordCompare
     */
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