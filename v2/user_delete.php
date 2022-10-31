<?php
class user_delete implements ask
{
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

    function __construct($source_query)
    {
        try
        {
            require 'hashed_password.php';
            if (!isset($source_query["login"])||!isset($source_query["password"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> login = $source_query["login"];
            $this -> password = new hashed_password($source_query["password"]);
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

        try
        {
            //если пользователь нашелся и пароль подходит
            if (!$Registered_user||!$this -> password -> compare($Registered_user['Password']))
            {
                throw new Exception('Нет пользователя/неправильный пароль');
            }
            //изменение данных
            $this -> ID = $Registered_user['ID'];
            $random_password = new hashed_password(random_bytes(10));
            $password_temp = $random_password -> say();//подавление предупреждающего уведомления компилятора пхп
            $random_login = random_bytes(10);
            $dbh -> say()-> beginTransaction();
            $registration = $dbh -> say() -> prepare("UPDATE User SET User.Login=:PDO_Login, User.Password=:PDO_Password,
             User.Name=:PDO_Name, User.Refresh_Token=0, User.Access_Rights=0 WHERE User.ID=:PDO_UserID");
            $registration->bindparam(':PDO_Login',$random_login);
            $registration->bindparam(':PDO_Password',$password_temp);
            $registration->bindparam(':PDO_Name',$random_login);
            $registration->bindparam(':PDO_UserID',$Registered_user["ID"]);
            $registration->execute();
            $dbh -> say() -> commit();
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit($e->getMessage());
        }
        
    }
}