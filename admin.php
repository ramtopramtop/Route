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
?>

<html>
<body>
Привет, <?php echo $_SESSION['user']; ?>, ты на секретной странице!!! :)

<form method="POST">
Имя <input name="new_user_name" type="text" value="<?php echo $_SESSION['user']; ?>">
<input name="submit_name" type="submit" value="Изменить">
</form>

<?
#обработка изменения имени
if(isset($_POST['submit_name']))
{
    try {           
        $dbh->beginTransaction();
        $registration=$dbh->prepare("UPDATE User SET User.Name=:PDO_Name WHERE User.ID=:PDO_UserID");
        $registration->bindparam(':PDO_UserID',$_SESSION['user_id']);
        $registration->bindparam(':PDO_Name',$_POST['new_user_name']);
        $registration->execute();
        $dbh->commit();
       #echo $_SESSION['user_id'].', '.$_POST['new_user_name']; 
      } catch (Exception $e) {
        $dbh->rollBack();
        echo "Ошибка: " . $e->getMessage();
      }

      #меняем имя в текущей сессии
      $_SESSION['user']=$_POST['new_user_name'];
}
?>

<form method="POST">
Пароль <input name="new_user_pass1" type="text">
Пароль еще раз <input name="new_user_pass2" type="text">
<input name="submit_pass" type="submit" value="Изменить пароль">
</form>

<?
#обработка изменения пароля
if(isset($_POST['submit_pass']))
{
  if($_POST['new_user_pass1']=$_POST['new_user_pass2'])
  {
    $User_password=md5(md5(trim($_POST['new_user_pass1'])));
  
    try {           
        $dbh->beginTransaction();
        $registration=$dbh->prepare("UPDATE User SET User.Password=:PDO_User_password WHERE User.ID=:PDO_UserID");
        $registration->bindparam(':PDO_UserID',$_SESSION['user_id']);
        $registration->bindparam(':PDO_User_password',$User_password);
        $registration->execute();
        $dbh->commit();
       #echo $_SESSION['user_id'].', '.$_POST['new_user_name']; 
      } catch (Exception $e) {
        $dbh->rollBack();
        echo "Ошибка: " . $e->getMessage();
      }

      
  }
  else
  {
    echo 'Пароли не совпадают';
  }
}
#Интерфейс для администраторов

#запрос прав для получения данных администратора
    
$query_rights=$dbh->prepare("SELECT User.Access_Rights FROM User WHERE User.ID=:PDO_UserID");
$query_rights->bindparam(':PDO_UserID',$_SESSION['user_id']);
$query_rights->execute();
$User_rights=$query_rights->fetch();
#echo 'Права:'.$User_rights['Access_Rights'];
#проверка прав доступа
if($User_rights['Access_Rights']==2)
{
  #Формирование списка пользователей
  $query_users_list=$dbh->prepare("SELECT User.ID, User.Name, User.Login, User.Access_Rights FROM User ");
  $query_users_list->execute();
  $Users_list=$query_users_list->fetch();

  #перебор массива пользователей
  foreach($Users_list as list($list_User_ID, $list_User_Name, $list_User_Login, $list_User_Access_Rights))
  {
    echo '<form method="POST">
    Пользователь '.$list_User_Name.' логин '.$list_User_Login.' права '.$list_User_Access_Rights.'
    <input name="user_rights_change" type="submit" value="Изменить права">
    </form>';

  }


  
  
  
  
  #Формирование списка городов
  #Выгрузка списка городов
  echo '<form method="POST">
  Город <input name="town" type="text">
  <input name="town_change" type="submit" value="Изменить название города">
  <input name="town_delete" type="submit" value="Удалить город">
  </form>';


}

?>




</body>
</html>