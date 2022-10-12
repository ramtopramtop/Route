<?php //обработка json-запросов/json-query processing
//Поключение данных авторизации БД
require '../../conn/dbase.php';

class User_registering //регистрирующийся пользователь/registering user
{
    private $Name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

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
                    $e->getMessage();
                    exit;
                }
    }
}

interface DB
{
    function Record_to_base();
}

$record=new Compared_user();
$record->Record_to_base();

//try
//{
//    $Name=json_decode(file_get_contents('php://input'),true);
//    echo'результат'.$Name;
//}
//catch (Exception $e)
//{
//    echo $e->getMessage();
//}
?>