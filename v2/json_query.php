<?php //обработка json-запросов/json-query processing
//Поключение данных авторизации БД
require '../../conn/dbase.php';

class User_registering implements What_is_you_name //регистрирующийся пользователь/registering user
{
    private $name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

    function __construct()
    {
        try
        {
            $json_data=json_decode(file_get_contents('php://input'),true);
            if (!isset($json_data["login"])||!isset($json_data["password"])||!isset($json_data["name"]))
            {
                throw new Exception('Не хватает параметров');
            }
            $this -> name = $json_data["name"];
            $this -> login = $json_data["login"];
            $this -> password = $json_data["password"];
            echo $this -> name.' '.$this -> login.' '.$this -> password;
        }
        catch (Exception $e)
        {
            exit ($e->getMessage());
            
        }
    }

    function What_is_you_name()
    {
        return $this->name;
    }
}

class What_is_you_name
{
    function What_is_you_name()
    {
        
    }
}

class DB_connection
{
    function __construct()
    {
        global $PDO_Host;
        global $PDO_DB_Name;
        global $PDO_DB_User;
        global $PDO_DB_Pass;
        try
        {
            $this = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
             array(PDO::ATTR_PERSISTENT => true));
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    
        }
        catch (Exception $e)
        {
            exit("Не удалось подключиться: ".$e->getMessage());
        }
    }
}

class User_from_DB
{
    private $name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

    function __construct($db_connect, $searchng_user)
    {
        try
        {
            $query=$db_connect->prepare("SELECT User.ID, User.Access_Rights FROM User WHERE User.Login=:PDO_Login");
            $query->bindparam(':PDO_Login',$searchng_user -> What_is_you_name());
            $query->execute();
            $Registered_user = $query->fetch(PDO::FETCH_ASSOC);  
    
        }
        catch (Exception $e)
        {
            exit("Не удалось подключиться: ".$e->getMessage());
        }
    }
}

class Compared_user implements DB
{
    function Record_to_base()
    {
        global $PDO_Host;
        global $PDO_DB_Name;
        global $PDO_DB_User;
        global $PDO_DB_Pass;
        $dbh = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
            array(PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try
                {
                    //$hased_password = password_generation($json_data["password"]);
                    $dbh->beginTransaction();
                    $registration=$dbh->prepare("INSERT INTO User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name");
                    $registration->bindvalue(':PDO_Login','login');
                    $registration->bindvalue(':PDO_Password',"password");
                    $registration->bindvalue(':PDO_Name',"name");
                    $registration->execute();
                    $dbh->commit();
                }
                catch (Exception $e)
                {
                    $dbh->rollBack();
                    exit($e->getMessage());
                }
    }
}

interface DB
{
    function Record_to_base();
}

//$record=new Compared_user();
//$record->Record_to_base();

$user_reg = new User_registering;
$connection = new DB_connection;
var_dump($user_reg, $connection);
$user_from_db = new User_from_DB($connection, $user_reg);

?>