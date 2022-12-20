<?php
class user_registerung implements ask //регистрирующийся пользователь/registering user
{
    private $name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

    function __construct($source_query)
    {
        try
        {
            require 'hashed_password.php';
            if (!isset($source_query["login"])||!isset($source_query["password"])||!isset($source_query["name"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> name = $source_query["name"];
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
        try
        {
            require 'connection_to_storage.php';
            $dbh = new connection_to_storage();
            $dbh -> say() -> beginTransaction();
            $registration = $dbh -> say() -> prepare("INSERT INTO User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name");
            $registration -> bindparam(':PDO_Login',$this -> login);
            $registration -> bindvalue(':PDO_Password',$this -> password -> say());
            $registration -> bindvalue(':PDO_Name',$this -> name);
            $registration -> execute();
            $dbh -> say() -> commit();
        }
        catch (Throwable $e)
        {
            $dbh -> say() -> rollBack();
            http_response_code(400);
            exit($e->getMessage());
        }        
    }
}
?>