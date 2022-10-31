<?php
class authorization implements ask
{
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password
    
    function __construct()
    {
        try
        {
            require 'hashed_password.php';
            if (!isset($source_query["login"])||!isset($source_query["password"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> login = $source_query["login"];
            $hashed_password = new hashed_password($source_query["password"]);
            $this -> password = $hashed_password;
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit ($e->getMessage());  
        }        
    }

    function ask()
    {
        //поиск пользователя
        try
        {
            require 'connection_to_storage.php';
            $dbh = new connection_to_storage();
            $query = $dbh -> say() -> prepare("SELECT User.ID, User.Password FROM User WHERE User.Login=:PDO_Login");
            $query -> bindparam(':PDO_Login',$this -> login);
            $query -> execute();
            $Registered_user=$query->fetch(PDO::FETCH_ASSOC);            
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
    }
}