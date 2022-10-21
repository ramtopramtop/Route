<?php
class user_in_storage
{
    private int $user_id_in_storage; //ид пользователя/user id
    //private $hashed_password_in_storage;
    
    function __construct(string $login, object $password)
    {
        try
        {
            require 'hashed_password.php';
            if (!isset($login)||!isset($password))
            {
                throw new Exception('Не хватает параметров');
            }
            require 'connection_to_storage.php';
            $dbh = new connection_to_storage();
            $query = $dbh -> say() -> prepare("SELECT User.ID, User.Password FROM User WHERE User.Login=:PDO_Login");
            $query -> bindparam(':PDO_Login',$login);
            $query -> execute();
            $Registered_user=$query->fetch(PDO::FETCH_ASSOC);            
            if (!$Registered_user||!$password -> compare($Registered_user['Password']))
            {
                throw new Exception('Нет пользователя/неправильный пароль');
            }
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
    
    function check_password()
    {

    }
}
