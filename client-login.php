<?php
#Поключаем данные авторизации БД
include '../conn/dbase.php';
//Подключаем ключ сервера
include '../conn/key.php';
//подключаем классы для работы с jwt
require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//получение данных из джсон потока

$postData = file_get_contents('php://input');

//Провверка на пустые данные

if ($postData=="")
{
    http_response_code(204);
    exit();

}
$json_data = json_decode($postData, true);

//Проверка на корректность json

if (is_null($json_data))
{
    http_response_code(400);
    exit();
}

# Соединямся с БД PHP_PDO

try {
    $dbh = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
        array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    
  } catch (Exception $e) {
    die("Не удалось подключиться: " . $e->getMessage());
  }

//обработка джсона

//проверка регистрации пользователя

if (!isset($json_data["login"]) or !isset($json_data["password"]))
{
    http_response_code(206);
    exit();
}
else
{
    
    $password=md5(md5($json_data["password"]));
    //поиск пользователя
                 
    $query=$dbh->prepare("SELECT User.ID, User.Access_Rights FROM User WHERE User.Login=:PDO_Login and User.Password=:PDO_Password");
    $query->bindparam(':PDO_Login',$json_data["login"]);
    $query->bindparam(':PDO_Password',$password);
    $query->execute();
    $Registered_user=$query->fetch(PDO::FETCH_ASSOC);
    
    //если пользователь нашелся

    if ($Registered_user)
    {
        //генерация токенов
        //содержательная часть - id и право
        //var_dump($Registered_user);
        $json_output = ['access_token'=> token_payload($Registered_user['ID'],$Registered_user['Access_Rights'],'access'),
                        'refresh_token'=>token_payload($Registered_user['ID'],$Registered_user['Access_Rights'],'refresh')];
        //$json_output=token_payload($Registered_user('ID'),$Registered_user('Access_Rights'),'access');
        //$payload_access_token = [
       //     'access_level' => $Registered_user['Access_Rights'],
       //     'user_id' => $Registered_user['ID'],
       //     'token_type'=>'access'
      //              ];
       // $json_output = JWT::encode($payload_access_token, $privateKey, 'RS256');
        header('Content-Type: application/json');
        echo json_encode($json_output);
                           
    }
    else
    {
        http_response_code(401);
    }
} 

function token_payload($f_ID,$f_access_rights, $token_type)
{
    global $privateKey;
    $payload_access_token= [
        'access_level' => $f_access_rights,
        'user_id' => $f_ID,
        'token_type'=>$token_type
                ];
    return JWT::encode($payload_access_token, $privateKey, 'RS256');
    
}  
?>