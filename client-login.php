<?php
#Поключаем данные авторизации БД

include '../conn/dbase.php';

# Соединямся с БД PHP_PDO

try {
    $dbh = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
        array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    # echo "Подключились\n";
  } catch (Exception $e) {
    die("Не удалось подключиться: " . $e->getMessage());
  }

//получение данных из джсон потока

$postData = file_get_contents('php://input');
$json_data = json_decode($postData, true);
//var_dump($data);

//обработка джсона

foreach($json_data as list($json_key, $json_value))
{
    //проверка авторизации пользователя
    if ($json_key=="login")
    {
        $json_user=$json_value;
        foreach($json_data as list($json_key_2, $json_value_2))
        {
            if ($json_key_2=="password")
            {
                $json_password=$json_value_2;
                
                //поиск пользователя
                 
                $registration=$dbh->prepare("SELECT * FROM User WHERE User.Login=:PDO_UserLogin and User.Password=:PDO_UserPassword");
                $registration->bindparam(':PDO_UserLogin',$json_user);
                $registration->bindparam(':PDO_User_password',$json_password);
                $registration->execute();
                $Registered_user=$registration->fetch();
                if ($Registered_user)
                {
                    header('Content-Type: application/json');
                    echo json_encode($Registered_user);                    
                }
                

            }
        }
    }


}
?>