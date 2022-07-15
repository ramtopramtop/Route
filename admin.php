<?
session_start();

#Поключаем данные авторизации БД
include '../conn/dbase.php';

# обеспечение секретности выкидыванием неавторизированных пользователей на страницу логона
if(!isset($_SESSION['hash'])){
    header("Location: login.php");
    exit;
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

#обработка нажатий кнопок

#обработка изменения имени
if(isset($_POST['submit_name']))
{
    try {           
        $dbh->beginTransaction();
        $registration=$dbh->prepare("UPDATE User SET User.Name=:PDO_Name WHERE User.ID=:PDO_UserID");
        $registration->bindparam(':PDO_UserID',$_SESSION['ID']);
        $registration->bindparam(':PDO_Name',$_POST['new_user_name']);
        $registration->execute();
        $dbh->commit();
      
      } catch (Exception $e) {
        $dbh->rollBack();
        echo "Ошибка: " . $e->getMessage();
      }
}

?>
<html>
<body>
Привет, <?php echo $_SESSION['user']; ?>, ты на секретной странице!!! :)

<form method="POST">

Имя <input name="new_user_name" type="text" value="<?php echo $_SESSION['user']; ?>">
<input name="submit_name" type="submit" value="Изменить">
</form>


</form>
</body>
</html>