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

#запрос прав для получения данных администратора
    
$query_rights=$dbh->prepare("SELECT User.Access_Rights FROM User WHERE User.ID=:PDO_UserID");
$query_rights->bindparam(':PDO_UserID',$_SESSION['user_id']);
$query_rights->execute();
$User_rights=$query_rights->fetch();

#проверка прав доступа
if($User_rights['Access_Rights']==2)
{
  

 #обработка изменения прав доступа

  if(isset($_POST['user_rights_change']))
  {
  
  try {           
    $dbh->beginTransaction();

    #выбираем вариант замены прав в зависимости от текущих прав доступа

    if ($_POST['user_rights_change_current']==1)
    {
      $registration=$dbh->prepare("UPDATE User SET User.Access_Rights=2 WHERE User.ID=:PDO_UserID");
    }
    else
    {
      $registration=$dbh->prepare("UPDATE User SET User.Access_Rights=1 WHERE User.ID=:PDO_UserID");
    }
    
    $registration->bindparam(':PDO_UserID',$_POST['user_rights_change_ID']);
    $registration->execute();
    $dbh->commit();
       }
   catch (Exception $e)
    {
    $dbh->rollBack();
    echo "Ошибка: " . $e->getMessage();
    }
  }

  #обработка изменения города
  if(isset($_POST['town_change']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("UPDATE Town SET Town.Name=:PDO_TownName WHERE Town.ID=:PDO_TownID");
      $registration->bindparam(':PDO_TownID',$_POST['town_id']);
      $registration->bindparam(':PDO_TownName',$_POST['town_name']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
  }

  #обработка удаления города
  
  if(isset($_POST['town_delete']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("DELETE FROM Town WHERE Town.ID=:PDO_TownID");
      $registration->bindparam(':PDO_TownID',$_POST['town_id']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
  }

  #обработка создания города
  if(isset($_POST['town_create']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("INSERT INTO Town SET Town.Name=:PDO_TownName");
      $registration->bindparam(':PDO_TownName',$_POST['town_name']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
  }
  
  
  #приветствие для администратора

  echo'<html>
  <body>
  Привет, '.$_SESSION['user'].', ты на секретной странице!!! :)';
  
  #вывод раздела администратора 
  
  #Формирование списка пользователей

  $query_users_list=$dbh->prepare("SELECT User.ID, User.Name, User.Login, User.Access_Rights FROM User");
  $query_users_list->execute();
  $Users_list=$query_users_list->fetchAll();
  
  #вывод раздела администратора 
  
  echo '<h1>Управление пользователями</h1>';
  
  #перебор массива пользователей

  foreach($Users_list as list($list_User_ID, $list_User_Name, $list_User_Login, $list_User_Access_Rights))
  {
    echo '<form id="User'.$list_User_ID.'" method="POST" onsubmit="return add_scroll(User'.$list_User_ID.')">
    Пользователь: '.$list_User_Name.', логин: '.$list_User_Login.', права: '.$list_User_Access_Rights.'
    <input name="user_rights_change_ID" type="hidden" value="'.$list_User_ID.'">
    <input name="user_rights_change_current" type="hidden" value="'.$list_User_Access_Rights.'">
    <input name="user_rights_change" type="submit" value="Изменить права">
    <input type="hidden" name="scroll" value="0">
    </form>';

  }

  echo '<h1>Ведение справочника городов</h1>';  
  
  #Формирование списка городов
  $query_towns_list=$dbh->prepare("SELECT Town.ID, Town.Name FROM Town");
  $query_towns_list->execute();
  $Towns_list=$query_towns_list->fetchAll();

  #Выгрузка списка городов
  foreach($Towns_list as list($list_Town_ID, $list_Town_Name))
  {
    echo '<form id="Town'.$list_Town_ID.'" method="POST" onsubmit="return add_scroll(Town'.$list_Town_ID.')">
    Город <input name="town_name" type="text" value="'.$list_Town_Name.'">
    <input name="town_id" type="hidden" value="'.$list_Town_ID.'">
    <input name="town_change" type="submit" value="Изменить название города">
    <input name="town_delete" type="submit" value="Удалить город">
    <input type="hidden" name="scroll" value="0">
    </form>';
  }
  echo '<form id="Town0" method="POST" onsubmit="return add_scroll(Town0)">
  Город <input name="town_name" type="text">
  <input name="town_create" type="submit" value="Добавить город">
  <input type="hidden" name="scroll" value="0">
  </form>';
  echo '<h1>Ведение справочника сезонов</h1>';

  #Формирование списка сезонов

  $query_seazons_list=$dbh->prepare("SELECT Seazon.ID, Seazon.Name, Seazon.Town_ID  FROM Seazon");
  $query_seazons_list->execute();
  $Seazons_list=$query_seazons_list->fetchAll();

  #Выгрузка списка сезонов
  foreach($Seazons_list as list($list_Seazon_ID,$list_Seazon_Name,$list_Seazon_Town_ID))
  {
    echo '<form id="Seazon'.$list_Seazon_ID.'" method="POST" onsubmit="return add_scroll(Town'.$list_Seazon_ID.')">
    Сезон <input name="seazon_name" type="text" value="'.$list_Seazon_Name.'">
    <select name="seazon_town">';
    foreach($Towns_list as list($list_Town_ID, $list_Town_Name))
    {
      echo '<option value="'.$list_Town_ID.'" ';
      if ($list_Town_ID==$list_Seazon_Town_ID)
      {
        echo 'selected';
      }
      echo '>'.$list_Town_Name.'</option>';
    }
    echo '</select>
    <input name="seazon_id" type="hidden" value="'.$list_Seazon_ID.'">
    <input name="seazon_change" type="submit" value="Изменить сезон">
    <input name="seazon_delete" type="submit" value="Удалить сезон">
    <input type="hidden" name="scroll" value="0">
    </form>';
  }

}
else
{
  #приветствие для пользователя
  echo'<html>
  <body>
  Привет, '.$_SESSION['user'].', ты на секретной странице!!! :)';
}  

#обработка нажатий кнопок пользователя и администратора

#смена имени пользователя и администратора
?>
<h1>Изменение личных данных</h1>
<form id="Name_change" method="POST" onsubmit="return add_scroll(Name_change)">
Имя <input name="new_user_name" type="text" value="<?php echo $_SESSION['user']; ?>">
<input name="submit_name" type="submit" value="Изменить">
<input type="hidden" name="scroll" value="0">
</form>

<?
#смена пароля пользователя и администратора
?>

<form id="Pass_change" method="POST" onsubmit="return add_scroll(Pass_change)">
Пароль <input name="new_user_pass1" type="text">
Пароль еще раз <input name="new_user_pass2" type="text">
<input name="submit_pass" type="submit" value="Изменить пароль">
<input type="hidden" name="scroll" value="0">
</form>


  
  <script>
  
  <?
  #генерация скрипта положения на странице
 
  echo 'Координаты:'.$_POST['scroll'];
  if ($_POST['scroll'])
  {
    echo 'window.scrollTo(0,'.$_POST['scroll'].');';
  }
  else
  {
    echo 'window.scrollTo(0,0);';
  }

  #функция сохранения положения на странице
  
  ?>
  function add_scroll(Form_name)
  {
    Form_name.scroll.value = window.pageYOffset;
    
  }
  </script>  
  
</body>
</html>
