<?php //обработка json-запросов/json-query processing
class User_registration //регистрирующийся пользователь/registering user
{
    private $Name; //имя пользователя/user name
    private $login; //логин пользователя/user login
    private $password;//пароль пользователя/user password

}
try
{
    $Name=json_decode(file_get_contents('php://input'),true);
    echo'результат'.$Name;
}
catch (Exception $e)
{
    echo $e->getMessage();
}
?>