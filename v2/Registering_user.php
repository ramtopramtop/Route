<?php
class Registering_user //регистрирующийся пользователь/registering user
{
    private $name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

    function __construct($source_query)
    {
        try
        {
            require 'Hashed_password.php';
            require 'Connection_to_storage.php';
            if (!isset($source_query["login"])||!isset($source_query["password"])||!isset($source_query["name"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> name = $source_query["name"];
            $this -> login = $source_query["login"];
            $hashed_password = new Hashed_password($source_query["password"]);
            $this -> password = $hashed_password;
        }
        catch (Exception $e)
        {
            http_response_code(400);
            exit ($e->getMessage());  
        }
        catch (Error $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }

        try
        {
            $dbh = new Connection_to_storage();
            $dbh -> Say_connection() -> beginTransaction();
            $registration = $dbh -> Say_connection() -> prepare("INSERT INTO User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name");
            $registration -> bindparam(':PDO_Login',$this -> login);
            $registration -> bindvalue(':PDO_Password',$this -> password -> say_password());
            $registration -> bindvalue(':PDO_Name',$this -> name);
            $registration -> execute();
            $dbh -> Say_connection() -> commit();
        }
        catch (Exception $e)
        {
            $dbh -> Say_connection() -> rollBack();
            http_response_code(400);
            exit($e->getMessage());
        }
        catch (Error $e)
        {
            $dbh -> Say_connection() -> rollBack();
            http_response_code(400);
            exit ($e->getMessage());
        }
    }
}
?>