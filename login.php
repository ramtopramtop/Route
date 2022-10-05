
<?
session_start();
// Страница авторизации
#Поключаем данные авторизации БД
include '../conn/dbase.php';


# Функция для генерации случайной строки

function generateCode($length=6) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";

    $code = "";

    $clen = strlen($chars) - 1;  
    while (strlen($code) < $length) {

            $code .= $chars[mt_rand(0,$clen)];  
    }

    return $code;

}

# Соединямся с БД PHP_PDO

try {
    $dbh = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
        array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    # echo "Подключились\n";
  } catch (Exception $e) {
    die("Не удалось подключиться: " . $e->getMessage());
  }

if(isset($_POST['submit']))

{

    # Вытаскиваем из БД запись, у которой логин равняеться введенному
    
    $query=$dbh->prepare("SELECT User.ID, User.Password, User.Name FROM User WHERE User.Login=:PDO_Login");
    $query->bindparam(':PDO_Login',$_POST['login']);
    $query->execute();
    $Registered_user=$query->fetch();

    # Соавниваем пароли

    if($Registered_user['Password'] === md5(md5($_POST['password'])))

    {

        # Генерируем случайное число и шифруем его

        $hash = md5(generateCode(10));
        $_SESSION['hash'] = $hash;
        $_SESSION['user'] = $Registered_user['Name'];
        $_SESSION['user_id'] = $Registered_user['ID'];
        #echo "Пользователь:". $Registered_user['Name'];
        header("Location: admin.php");
        
        
        exit();

    }

    else

    {

        print "Вы ввели неправильный логин/пароль";
       

    }

}

?>

<form method="POST">

Логин <input name="login" type="text"><br>

Пароль <input name="password" type="password"><br>



<input name="submit" type="submit" value="Войти">

</form>