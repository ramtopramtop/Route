<?php
class user_rename implements ask
{
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password
    private $password_new;
    private $name_new;
    private $ID;

    function __construct($source_query)
    {
        try
        {
            require 'hashed_password.php';
            
            if (!isset($source_query["login"])||!isset($source_query["password"])
             ||!isset($source_query["password_new"])||!isset($source_query["name_new"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> name_new = $source_query["name_new"];
            $this -> login = $source_query["login"];
            $this -> password = new hashed_password($source_query["password"]);
            $this -> password_new = new hashed_password($source_query["password_new"]);
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
        catch (Error $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
        try
        {
            
            //если пользователь нашелся и пароль подходит
            if (!$Registered_user||!$this -> password -> compare($Registered_user['Password']))
            {
                throw new Exception('Нет пользователя/неправильный пароль');
            }
            //изменение данных
            $this -> ID = $Registered_user['ID'];
            $new_hashed_password_temp = $this -> password_new -> say();//подавление предупреждающего уведомления компилятора пхп
            $dbh -> say()-> beginTransaction();
            $registration = $dbh -> say() -> prepare("UPDATE User SET User.Password=:PDO_Password, User.Name=:PDO_Name WHERE User.ID=:PDO_UserID");
            $registration->bindparam(':PDO_Password', $new_hashed_password_temp);
            $registration->bindparam(':PDO_Name',$this -> name_new);
            $registration->bindparam(':PDO_UserID',$this -> ID);
            $registration->execute();
            $dbh -> say() -> commit();
            //echo $this -> ID.'   '.$hashed_password_new.'  '.$this -> name;
        }
        catch (Exception $e)
        {
            
            http_response_code(400);
            exit($e->getMessage());
        }
        catch (Error $e)
        {
            //$dbh -> say() -> rollBack();
            http_response_code(400);
            exit ($e->getMessage());
        }
    }
}
?>