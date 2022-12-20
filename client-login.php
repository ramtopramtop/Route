<?
//обработка json запросов
//Подключение универсальных функций
require 'functions.php';
//Подключение настроек сервера
require 'server_settings.php';
//Поключение данных авторизации БД
require '../conn/dbase.php';
//Подключение ключа сервера
include '../conn/key.php';
//Подключение классов для работы с jwt
require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//получение данных джсон из потока

$postData = file_get_contents('php://input');

//Проверка на пустые данные

if ($postData=="")
{
    error_handler(400, 40003, 'Нет входящих данных');
    exit();

}
$json_data = json_decode($postData, true);

//Проверка на корректность json

if (is_null($json_data))
{
    error_handler(400, 40001, 'Некорректный json');
    exit();
}

//Проверка ключа вида запроса
if (!isset($json_data["json_query"]))
{
    error_handler(400, 40002, 'Нет распознанных ключей запросов json');
    exit();
}

# Соединямся с БД PHP_PDO

try
{
    $dbh = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
        array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    
}
catch (Exception $e)
{
    die("Не удалось подключиться: " . $e->getMessage());
}

//обработка джсона

//обработка ключей запроса
                        
switch ($json_data["json_query"])
{
    //запрос списка ближайших точек
    case 'list_nav_point':
        //проверка полей 
        if (isset($json_data["position_x"])||isset($json_data["position_y"])||isset($json_data["radius"]))
        {
            //проверка токена
            check_token('access',1);
            //выполнение запроса
            $query=$dbh->prepare("SELECT Navpoint.ID, ST_X(Navpoint.Coordinates) as X, ST_Y(Navpoint.Coordinates) as Y, Navpoint.Tag
             FROM Navpoint WHERE ST_Distance_Sphere(Navpoint.Coordinates, PointFromText(concat('POINT(',:PDO_NavpointX,' ',:PDO_NavpointY,')')))<=:PDO_Radius");
            $query->bindparam(':PDO_NavpointX',$json_data["position_x"]);
            $query->bindparam(':PDO_NavpointY',$json_data["position_y"]);
            $query->bindparam(':PDO_Radius',$json_data["radius"]);
            $query->execute();
            $query_result=$query->fetchall(PDO::FETCH_ASSOC);
            error_handler(200, 0, 'Нет ошибок',$query_result);                        
        }
        else
        {
            error_handler(400, 40002, 'Нет распознанных ключей запросов json');
        }
    break;

    //авторизация пользователя
    case 'autorization':
        //проверка наличия полей логина и пароля
        if (isset($json_data["login"]) and isset($json_data["password"]))
        {
            //поиск пользователя
            $query=$dbh->prepare("SELECT User.ID, User.Access_Rights, User.Password, User.Refresh_Token FROM User WHERE User.Login=:PDO_Login");
            $query->bindparam(':PDO_Login',$json_data["login"]);
            $query->execute();
            $Registered_user=$query->fetch(PDO::FETCH_ASSOC);
            //если пользователь нашелся и пароль подходит
            if ($Registered_user)
            {
                if (password_verify($json_data["password"],$Registered_user['Password']))
                {
                //генерация токенов      
                token_generation();
                }
                else
                {
                    error_handler(401, 40105, 'Неправильный логин/пароль');
                }                           
            }
            else
            {
                error_handler(401, 40105, 'Неправильный логин/пароль');
            }
        }
        else
        {
            error_handler(400, 40002, 'Нет распознанных ключей запросов json');
        }
    break;

    //регистарция пользователя
    case 'registration':
        //проверка наличия полей имени, логина и пароля
        if (isset($json_data["login"]) and isset($json_data["password"]) and isset($json_data["name"]))
        {
            //поиск пользователя по логину
            $query=$dbh->prepare("SELECT User.ID, User.Access_Rights FROM User WHERE User.Login=:PDO_Login");
            $query->bindparam(':PDO_Login',$json_data["login"]);
            $query->execute();
            $Registered_user=$query->fetch(PDO::FETCH_ASSOC);
            //если пользователь нашелся
            if ($Registered_user)
            {
                //ошибка      
                error_handler(406, 40601, 'Пользователь уже существует');                           
            }
            else
            {
                //занесение пользователя в базу
                try
                {
                    $hased_password = password_generation($json_data["password"]);
                    $dbh->beginTransaction();
                    $registration=$dbh->prepare("INSERT INTO User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name");
                    $registration->bindparam(':PDO_Login',$json_data["login"]);
                    $registration->bindparam(':PDO_Password',$hased_password);
                    $registration->bindparam(':PDO_Name',$json_data["name"]);
                    $registration->execute();
                    $dbh->commit();
                }
                catch (Exception $e)
                {
                    $dbh->rollBack();
                    error_handler(400, $e->getCode(), $e->getMessage());
                    exit;
                }
                error_handler(201, 0, 'Пользователь создан');
            }
        }
        else
        {
            error_handler(400, 40002, 'Нет распознанных ключей запросов json');
        }
    break;
    
    //обновление токенов
    case 'authorization_reneval':
        check_token('refresh',1);
    break;
    
    //запрос публичного ключа
    case 'public_key':
        $jsonpublicKey = ['server_key'=> $publicKey];
        error_handler(200, 0, 'Нет ошибок', $jsonpublicKey);
    break;
    
    //обновление имени/логина/пароля
    case 'registration_change':
        if (isset($json_data["login"]) and isset($json_data["password"])
         and isset($json_data["login_new"]) and isset($json_data["password_new"]) and isset($json_data["name_new"]))
        {
            //поиск пользователя
            $query=$dbh->prepare("SELECT User.ID, User.Password FROM User WHERE User.Login=:PDO_Login");
            $query->bindparam(':PDO_Login',$json_data["login"]);
            $query->execute();
            $Registered_user=$query->fetch(PDO::FETCH_ASSOC);
            //если пользователь нашелся и пароль подходит
            if ($Registered_user)
            {
                if (password_verify($json_data["password"],$Registered_user['Password']))
                {
                    //изменение данных

                    try
                    {
                        $hased_password = password_generation($json_data["password_new"]);
                        $dbh->beginTransaction();
                        $registration=$dbh->prepare("UPDATE User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name WHERE User.ID=:PDO_UserID");
                        $registration->bindparam(':PDO_Login',$json_data["login_new"]);
                        $registration->bindparam(':PDO_Password',$hased_password);
                        $registration->bindparam(':PDO_Name',$json_data["name_new"]);
                        $registration->bindparam(':PDO_UserID',$Registered_user["ID"]);
                        $registration->execute();
                        $dbh->commit();
                    }
                    catch (Exception $e)
                    {
                        $dbh->rollBack();
                        error_handler(400, $e->getCode(), $e->getMessage());
                        exit;
                    }
                    error_handler (200, 0, 'Нет ошибок');
                }
                else
                {
                    error_handler(401, 40105, 'Неправильный логин/пароль');
                }                           
            }
            else
            {
                error_handler(401, 40105, 'Неправильный логин/пароль');
            }
        }
        else
        {
            error_handler(400, 40002, 'Нет распознанных ключей запросов json');
        }
    break;

    //удаление пользователя
    case 'user_delete':
        if (isset($json_data["login"]) and isset($json_data["password"]))
        {
            //поиск пользователя
            $query=$dbh->prepare("SELECT User.ID, User.Password FROM User WHERE User.Login=:PDO_Login");
            $query->bindparam(':PDO_Login',$json_data["login"]);
            $query->execute();
            $Registered_user=$query->fetch(PDO::FETCH_ASSOC);
            //если пользователь нашелся и пароль подходит
            if ($Registered_user)
            {
                if (password_verify($json_data["password"],$Registered_user['Password']))
                {
                    //изменение данных
                    try
                    {
                        $random_password = password_generation(random_bytes(10));
                        $random_login = random_bytes(10);
                        $dbh->beginTransaction();
                        $registration=$dbh->prepare("UPDATE User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name, User.Refresh_Token=0, User.Access_Rights=0 WHERE User.ID=:PDO_UserID");
                        $registration->bindparam(':PDO_Login',$random_login);
                        $registration->bindparam(':PDO_Password',$random_password);
                        $registration->bindparam(':PDO_Name',$random_login);
                        $registration->bindparam(':PDO_UserID',$Registered_user["ID"]);
                        $registration->execute();
                        $dbh->commit();
                    }
                    catch (Exception $e)
                    {
                        $dbh->rollBack();
                        error_handler(400, $e->getCode(), $e->getMessage());
                        exit;
                    }
                    error_handler (200, 0, 'Нет ошибок');
                }
                else
                {
                    error_handler(401, 40105, 'Неправильный логин/пароль');
                }                           
            }
            else
            {
                error_handler(401, 40105, 'Неправильный логин/пароль');
            }
        }
        else
        {
            error_handler(400, 40002, 'Нет распознанных ключей запросов json');
        }
    break;

    //если нет подходящих аргументов в ключе json_query            
    default:
        error_handler(400, 40002, 'Нет распознанных ключей запросов json');        
}
 
//выгрузка токенов в формате json
function token_generation()
{
    global $access_token_lifetime;
    global $refresh_token_lifetime;
    global $dbh;
    global $Registered_user;
    //генерация токенов
    $refresh_token = token_payload('refresh',$refresh_token_lifetime);            
    $json_output = ['access_token'=> token_payload('access',$access_token_lifetime),
    'refresh_token'=> $refresh_token];
    //добавление/обновление рефреш токена в базе пользователей
    try
    {
        $dbh->beginTransaction();
        $registration=$dbh->prepare("UPDATE User SET User.Refresh_Token=:PDO_Token WHERE User.ID=:PDO_User_ID");
        $registration->bindparam(':PDO_Token',$refresh_token);
        $registration->bindparam(':PDO_User_ID',$Registered_user['ID']);
        $registration->execute();
        $dbh->commit();
    }
    catch (Exception $e)
    {
        $dbh->rollBack();
        error_handler(400, $e->getCode(), $e->getMessage());
        exit;
    }
    error_handler (200, 0, 'Нет ошибок', $json_output);
}
//создание jwt токенов
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
        error_handler(500, 50001, 'проблемы с получением времени, возможно некорректное указание временной зоны в настройках');
        echo $e->getMessage();
    }
    //добавляем время истечения токена
    try
    {
        $date_interval=new DateInterval('PT'.$token_lifetime.'S');
    }
    catch(Exception $e)
    {
        error_handler(500, 50002, 'проблемы с временем жизни токенов, возможно некорректное указание времени жизни токена в настройках');
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
    
    return JWT::encode($payload_access_token, $privateKey, 'RS256');
    
}
//обработка выгрузки сообщений
function error_handler($http_error_code, $error_code, $error, $data_responce='')
{
    http_response_code($http_error_code);
    header('Content-Type: application/json');
    if ($data_responce=="")
        $data_responce = ["error_code"=>$error_code, "error"=>$error];
    else
        $data_responce += ["error_code"=>$error_code, "error"=>$error];
    echo json_encode($data_responce);
}
// проверка 2 типов токенов
function check_token($token_type_must_have, $access_rights)
{
    global $Registered_user;
    global $publicKey;
    global $token_timezone;
    global $dbh;
    //проверка указания авторизации
    if (!isset($_SERVER['HTTP_AUTHORIZATION']))
    {
        error_handler(400,40006,'Необходима авторизация');
        exit;
    }
    else
    {
        //проверка авторизации через токен
        if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches))
        {
            error_handler(400,40005,'Неверный тип авторизации');
            exit;
        }
        else
        {
            $jwt = $matches[1];

            //Проверка непустого токена
             if (! $jwt)
            {
                error_handler(400,40004,'Токен отсутствует');
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
                    error_handler(401,40004,'Токен неверный');
                    echo $e->getMessage();
                    exit;
                }
            
                //создание текущей даты
                $now = new DateTimeImmutable('now', new DateTimeZone($token_timezone));
            
                //обработка токенов
                //проверка истечения срока токена
                if ($token->exp > $now->format('Y-m-d H:i:sP'))
                {
                    //определение типа токена
                    if ($token->token_type == 'refresh' && $token_type_must_have == 'refresh')
                    {
                        //проверка на наличие токена в базе
                        $query=$dbh->prepare("SELECT User.ID, User.Refresh_Token FROM User WHERE User.ID=:PDO_User_ID");
                        $query->bindparam(':PDO_User_ID',$token->user_id);
                        $query->execute();
                        $Registered_user=$query->fetch(PDO::FETCH_ASSOC);
                        
                        //если пользователь нашелся и пароль подходит
                        if ($Registered_user['Refresh_Token'] == $jwt)
                        {
                            //генерация токенов
                            $Registered_user=['ID'=>$token->user_id, 'Access_Rights'=>$token->access_level];      
                            token_generation();
                            exit;
                        }
                        else
                        {
                            error_handler(401,40103,'Токен утратил силу');
                            exit;
                        }
                    }
                    elseif ($token->token_type == 'access' && $token_type_must_have == 'access')
                    {
                        if ($token->access_level < $access_rights)
                        {
                            error_handler(403,40301,'Недостаточно прав');
                            exit;
                        }    
                    }
                    else
                    {
                        error_handler(401,40004,'Токен неверный');
                        exit;
                    }
                }
                else
                {
                    error_handler(401,40101,'Истек срок токена');
                    exit;
                }
                     
            }
        }
    }
}
?>