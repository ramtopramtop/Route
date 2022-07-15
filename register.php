<?

// Страница регситрации нового пользователя

#Поключаем данные авторизации БД
include '../conn/dbase.php';
# Соединямся с БД PHP_PDO

try {
    $dbh = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
        array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
     #echo "Подключились\n";
  } catch (Exception $e) {
    die("Не удалось подключиться: " . $e->getMessage());
  }

# mysql_connect("mysql-ramtop.alwaysdata.net", "ramtop", "Negrov_365");

# mysql_select_db("ramtop_route");



if(isset($_POST['submit']))

{

    $err = array();


    # проверям логин

    if(!preg_match("/^[a-zA-Z0-9]+$/",$_POST['login']))

    {

        $err[] = "Логин может состоять только из букв английского алфавита и цифр";

    }

    

    if(strlen($_POST['login']) < 3 or strlen($_POST['login']) > 30)

    {

        $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";

    }

    

    # проверяем, не сущестует ли пользователя с таким именем

    $query=$dbh->prepare("SELECT COUNT(User.ID) FROM User WHERE User.Login=:PDO_Login");
    $query->bindparam(':PDO_Login',$_POST['login']);
    $query->execute();
    #$query = mysql_query("SELECT COUNT(User.ID) FROM User WHERE User.Login='".mysql_real_escape_string($_POST['login'])."'");

    if($query->fetchColumn() > 0)

    {

        $err[] = "Пользователь с таким логином уже существует в базе данных";

    }

    

    # Если нет ошибок, то добавляем в БД нового пользователя

    if(count($err) == 0)

    {

       
        # Убираем лишние пробелы и делаем двойное шифрование

        $password = md5(md5(trim($_POST['password'])));

        try {
            
          
            $dbh->beginTransaction();
            $registration=$dbh->prepare("INSERT INTO User SET User.Login=:PDO_Login, User.Password=:PDO_Password, User.Name=:PDO_Name");
            $registration->bindparam(':PDO_Login',$_POST['login']);
            $registration->bindparam(':PDO_Password',$password);
            $registration->bindparam(':PDO_Name',$_POST['name']);
            $registration->execute();
            $dbh->commit();
          
          } catch (Exception $e) {
            $dbh->rollBack();
            echo "Ошибка: " . $e->getMessage();
          }

        #mysql_query("INSERT INTO User SET User.Login='".$login."', User.Password='".$password."', User.Name='".$name."'");

        header("Location: login.php"); exit();

    }

    else

    {

        print "<b>При регистрации произошли следующие ошибки:</b><br>";

        foreach($err AS $error)

        {

            print $error."<br>";

        }

    }

}

?>




<form method="POST">

Имя <input name="name" type="text"><br>

Логин <input name="login" type="text"><br>

Пароль <input name="password" type="password"><br>

<input name="submit" type="submit" value="Зарегистрироваться">

</form>
