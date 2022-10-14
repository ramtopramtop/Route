<?php //обработка json-запросов/json-query processing

class User_registering implements Ask //регистрирующийся пользователь/registering user
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
            $hashed_password = new hashed_password($json_data["password"]);
            $this -> password = $hashed_password;
            //echo $this -> name.' '.$this -> login.' '.$this -> password;
        }
        catch (Exception $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
            
        }
    }
    
    function Save_to_base()
    {
        //Поключение данных авторизации БД
        require '../../conn/dbase.php';
        //global $PDO_Host;
        //global $PDO_DB_Name;
        //global $PDO_DB_User;
        //global $PDO_DB_Pass;
        try
        {
            $dbh = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
             array(PDO::ATTR_PERSISTENT => true));
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$hased_password = password_generation($this -> password);
            $dbh->beginTransaction();
            $registration=$dbh->prepare("INSERT INTO User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name");
            $registration->bindparam(':PDO_Login',$this -> login);
            $registration->bindvalue(':PDO_Password',$this -> password -> say_password());
            $registration->bindvalue(':PDO_Name',$this -> name);
            $registration->execute();
            $dbh->commit();
        }
        catch (Exception $e)
        {
            $dbh->rollBack();
            http_response_code(400);
            exit($e->getMessage());
        }
    }
}

interface Ask
{
    public function Save_to_base();
}

class hashed_password implements Tell
{
    private $hashed_password;

    function __construct($password)
    {
        $this -> hashed_password = password_hash($password, PASSWORD_BCRYPT);
    }

    function say_password()
    {
        return $this -> hashed_password;
    }
}

interface Tell
{
    function say_password();
}

$user_reg = new User_registering;
$user_reg -> Save_to_base();

?>