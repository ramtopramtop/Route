<?php
//подключаем настройки сервера
include 'server_settings.php';
//Поключаем данные авторизации БД
include '../conn/dbase.php';
//Подключаем ключ сервера
include '../conn/key.php';
//подключаем классы для работы с jwt
require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


//получение данных из джсон потока

$postData = file_get_contents('php://input');

//Проверка на пустые данные

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
    echo "json некорректный";
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
//проверка наличия полей логина и пароля

if (isset($json_data["login"]) and isset($json_data["password"]))
{
    //поиск пользователя
    $password=md5(md5($json_data["password"]));
    $query=$dbh->prepare("SELECT User.ID, User.Access_Rights FROM User WHERE User.Login=:PDO_Login and User.Password=:PDO_Password");
    $query->bindparam(':PDO_Login',$json_data["login"]);
    $query->bindparam(':PDO_Password',$password);
    $query->execute();
    $Registered_user=$query->fetch(PDO::FETCH_ASSOC);
    
    //если пользователь нашелся

    if ($Registered_user)
    {
        //генерация токенов      
        token_generation();                           
    }
    else
    {
        http_response_code(401);
        echo "неправильный логин/пароль";
    }
    
}
else
{
    //Проверка наличия токена
    if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches))
    {
        header('HTTP/1.0 400 Bad Request');
        echo 'Token not found in request';
        exit;
    }
    else
    {
        $jwt = $matches[1];
        if (! $jwt)
        {
            // No token was able to be extracted from the authorization header
            header('HTTP/1.0 400 Bad Request');
            echo 'No token was able to be extracted from the authorization header';
            exit;
        }
        else
        {
            try
            {
                //расшифровка токена
                $token = JWT::decode($jwt, new Key($publicKey, 'RS256'));
                
            }
            catch(Exception $e)
            {
                http_response_code(400);
                echo "некорректный токен";
                echo $e->getMessage();
                exit;
            }
            var_dump($token);
            //создание текущей даты
            $now = new DateTimeImmutable('now', new DateTimeZone($token_timezone));
            //обработка ключей токена
            if ($token->token_type =='refresh' and $token->exp < $now->format('Y-m-d H:i:sP'))
            {
                echo 'ghbdtn';
            }
        }
    }
    http_response_code(400);
    echo "нет ключей логина/пароля";
    exit();
}

function token_generation()
{
    global $access_token_lifetime;
    global $refresh_token_lifetime;

    //генерация токенов
                
    $json_output = ['access_token'=> token_payload('access',$access_token_lifetime),
    'refresh_token'=>token_payload('refresh',$refresh_token_lifetime)];
    header('Content-Type: application/json');
    echo json_encode($json_output);
}

function token_payload($token_type, $token_lifetime)
{
    global $privateKey;
    global $token_timezone;
    global $Registered_user;
    //содаем текущую дату
    try
    {
        $current_date=new DateTimeImmutable('now', new DateTimeZone($token_timezone));
    }
    catch(Exception $e)
    {
        http_response_code(500);
        echo "проблемы с получением времени, возможно некорректное указание временной зоны в настройках";
        echo $e->getMessage();
    }
    //добавляем время истечения токена
    try
    {
        $date_interval=new DateInterval('PT'.$token_lifetime.'S');
    }
    catch(Exception $e)
    {
        http_response_code(500);
        echo "проблемы с временем жизни токенов, возможно некорректное указание времени жизни токена в настройках";
        echo $e->getMessage() . '<br />';
    }
   // $exp_date=$current_date->add($date_interval);
    //генерация токена
    $payload_access_token= [
        'access_level' => $Registered_user['Access_Rights'],
        'user_id' => $Registered_user['ID'],
        'token_type'=>$token_type,
        'exp'=> $current_date->add($date_interval)->format('Y-m-d H:i:sP')
                ];
    var_dump($payload_access_token);
    return JWT::encode($payload_access_token, $privateKey, 'RS256');
    
}  
?>