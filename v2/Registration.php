<?php
class Registering_user implements Ask_registering_user //регистрирующийся пользователь/registering user
{
    private $name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

    function __construct()
    {
        try
        {
            require 'Hashed_password.php';
            $json_data=json_decode(file_get_contents('php://input'),true);
            if (!isset($json_data["login"])||!isset($json_data["password"])||!isset($json_data["name"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> name = $json_data["name"];
            $this -> login = $json_data["login"];
            $hashed_password = new Hashed_password($json_data["password"]);
            $this -> password = $hashed_password;
        }
        catch (Exception $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
            
        }
    }
    
    function Save_to_base()
    {
        try
        {
            require 'Connection.php';
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
    }
}

interface Ask_registering_user
{
    public function Save_to_base();
}

$user_reg = new Registering_user;
$user_reg -> Save_to_base();
?>