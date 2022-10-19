<?php
class Rename
{
    private $name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password
    private $password_new;
    private $name_new;
    private $ID;

    function __construct($source_query)
    {
        try
        {
            require 'Hashed_password.php';
            require 'Connection_to_storage.php';
            if (!isset($source_query["login"])||!isset($source_query["password"])||!isset($source_query["name"])
             ||!isset($source_query["password_new"])||!isset($source_query["name_new"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> name = $source_query["name"];
            $this -> name_new = $source_query["name_new"];
            $this -> login = $source_query["login"];
            $hashed_password = new Hashed_password($source_query["password"]);
            $hashed_password_new = new Hashed_password($source_query["password_new"]);
            $this -> password = $hashed_password;
            $this -> password_new = $hashed_password_new;
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

        //поиск пользователя
        try
        {
            $dbh = new Connection_to_storage();
            $query = $dbh -> Say_connection() -> prepare("SELECT User.ID, User.Password FROM User WHERE User.Login=:PDO_Login");
            $query -> bindparam(':PDO_Login',$this -> login);
            $query -> execute();
            $Registered_user=$query->fetch(PDO::FETCH_ASSOC);            
        }
        catch (Error $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
        try
        {
            //если пользователь нашелся и пароль подходит
            if (!$Registered_user||password_verify($this -> password, $Registered_user['Password']))
            {
                throw new Exception('Нет пользователя/неправильный пароль');
            }
            //изменение данных
            $this -> ID = $Registered_user['ID'];
            $dbh -> Say_connection()-> beginTransaction();
            $registration = $dbh -> Say_connection() -> prepare("UPDATE User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name WHERE User.ID=:PDO_UserID");
            $registration->bindparam(':PDO_Login',$this -> login_new);
            $registration->bindparam(':PDO_Password',$hased_password_new);
            $registration->bindparam(':PDO_Name',$this -> name);
            $registration->bindparam(':PDO_UserID',$this -> ID);
            $registration->execute();
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