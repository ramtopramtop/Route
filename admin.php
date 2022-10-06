<?
session_start();
#Поключаем данные авторизации БД
include '../conn/dbase.php';

# обеспечение секретности выкидыванием неавторизированных пользователей на страницу логона

if(!isset($_SESSION['hash']))
{
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
      header("Location: admin.php");
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
  header("Location: admin.php");
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
    header("Location: admin.php");
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
    header("Location: admin.php");
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
    header("Location: admin.php");
  }

  #обработка добавления города

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
    header("Location: admin.php");
  }

  #обработка изменения маршрута

  if(isset($_POST['route_change']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("UPDATE Route SET Route.Name=:PDO_RouteName,Route.Town_ID=:PDO_RouteTownID WHERE Route.ID=:PDO_RouteID");
      $registration->bindparam(':PDO_RouteID',$_POST['route_id']);
      $registration->bindparam(':PDO_RouteName',$_POST['route_name']);
      $registration->bindparam(':PDO_RouteTownID',$_POST['route_town']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка удаления маршрута
  
  if(isset($_POST['route_delete']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("DELETE FROM Route WHERE Route.ID=:PDO_RouteID");
      $registration->bindparam(':PDO_RouteID',$_POST['route_id']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка добавления маршрута
  if(isset($_POST['route_create']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("INSERT INTO Route SET Route.Name=:PDO_RouteName, Route.Town_ID=:PDO_RouteTownID");
      $registration->bindparam(':PDO_RouteName',$_POST['route_name']);
      $registration->bindparam(':PDO_RouteTownID',$_POST['route_town']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }


  #обработка изменения сезона

  if(isset($_POST['seazon_change']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("UPDATE Seazon SET Seazon.Name=:PDO_SeazonName,Seazon.Route_ID=:PDO_SeazonRouteID WHERE Seazon.ID=:PDO_SeazonID");
      $registration->bindparam(':PDO_SeazonID',$_POST['seazon_id']);
      $registration->bindparam(':PDO_SeazonName',$_POST['seazon_name']);
      $registration->bindparam(':PDO_SeazonRouteID',$_POST['seazon_route']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка удаления сезона
  
  if(isset($_POST['seazon_delete']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("DELETE FROM Seazon WHERE Seazon.ID=:PDO_SeazonID");
      $registration->bindparam(':PDO_SeazonID',$_POST['seazon_id']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка добавления сезона
  if(isset($_POST['seazon_create']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("INSERT INTO Seazon SET Seazon.Name=:PDO_SeazonName, Seazon.Route_ID=:PDO_SeazonRouteID");
      $registration->bindparam(':PDO_SeazonName',$_POST['seazon_name']);
      $registration->bindparam(':PDO_SeazonRouteID',$_POST['seazon_route']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка удаления дня из списка дней сезона
  
  if(isset($_POST['seazon_day_delete']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("DELETE FROM Seazon_days WHERE Seazon_days.ID=:PDO_SeazonDayID");
      $registration->bindparam(':PDO_SeazonDayID',$_POST['seazon_day_id']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка добавления даты в маршрут

  if(isset($_POST['seazon_day_create']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("INSERT INTO Seazon_days SET Seazon_days.Day=:PDO_SeazonDayDay, Seazon_days.Seazon_ID=:PDO_SeazonDaySeazonID");
      $registration->bindparam(':PDO_SeazonDayDay',$_POST['seazon_day_day']);
      $registration->bindparam(':PDO_SeazonDaySeazonID',$_POST['seazon_day_seazon']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка изменения навигационной точки

  if(isset($_POST['navpoint_change']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("UPDATE Navpoint SET Navpoint.Tag=:PDO_NavpointTag, Navpoint.Coordinates=PointFromText(concat('POINT(',:PDO_NavpointX,' ',:PDO_NavpointY,')')) WHERE Navpoint.ID=:PDO_NavpointID");
      $registration->bindparam(':PDO_NavpointID',$_POST['navpoint_id']);
      $registration->bindparam(':PDO_NavpointTag',$_POST['navpoint_tag']);
      $registration->bindparam(':PDO_NavpointX',$_POST['navpoint_x']);
      $registration->bindparam(':PDO_NavpointY',$_POST['navpoint_y']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }
 
  #обработка удаления навигационной точки
   
  if(isset($_POST['navpoint_delete']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("DELETE FROM Navpoint WHERE Navpoint.ID=:PDO_NavpointID");
      $registration->bindparam(':PDO_NavpointID',$_POST['navpoint_id']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }
 
  #обработка добавления навигационной точки
 
  if(isset($_POST['navpoint_create']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("INSERT INTO Navpoint SET Navpoint.Tag=:PDO_NavpointTag, Navpoint.Coordinates=PointFromText(concat('POINT(',:PDO_NavpointX,' ',:PDO_NavpointY,')'))");
      $registration->bindparam(':PDO_NavpointTag',$_POST['navpoint_tag']);
      $registration->bindparam(':PDO_NavpointX',$_POST['navpoint_x']);
      $registration->bindparam(':PDO_NavpointY',$_POST['navpoint_y']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка изменения точки графа маршрута

  if(isset($_POST['route_graph_change']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("UPDATE Route_graph SET Route_graph.Visit_order=:PDO_RouteGraphVisitOrder, Route_graph.Route_ID=:PDO_RouteGraphRouteID, Route_graph.Navpoint_ID=:PDO_RouteGraphNavpointID WHERE Route_graph.ID=:PDO_RouteGraphID");
      $registration->bindparam(':PDO_RouteGraphID',$_POST['route_graph_id']);
      $registration->bindparam(':PDO_RouteGraphVisitOrder',$_POST['route_graph_visit_order']);
      $registration->bindparam(':PDO_RouteGraphRouteID',$_POST['route_graph_route']);
      $registration->bindparam(':PDO_RouteGraphNavpointID',$_POST['route_graph_navpoint']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка удаления точки из графа маршрута
  
  if(isset($_POST['route_graph_delete']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("DELETE FROM Route_graph WHERE Route_graph.ID=:PDO_RouteGraphID");
      $registration->bindparam(':PDO_RouteGraphID',$_POST['route_graph_id']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
  }

  #обработка добавления точки в граф маршрута
  if(isset($_POST['route_graph_create']))
  {
    try
    {   
      $dbh->beginTransaction();
      $registration=$dbh->prepare("INSERT INTO Route_graph SET Route_graph.Visit_order=:PDO_RouteGraphVisitOrder, Route_graph.Route_ID=:PDO_RouteGraphRouteID, Route_graph.Navpoint_ID=:PDO_RouteGraphNavpointID");
      $registration->bindparam(':PDO_RouteGraphVisitOrder',$_POST['route_graph_visit_order']);
      $registration->bindparam(':PDO_RouteGraphRouteID',$_POST['route_graph_route']);
      $registration->bindparam(':PDO_RouteGraphNavpointID',$_POST['route_graph_navpoint']);
      $registration->execute();
      $dbh->commit();
    }
    catch (Exception $e)
    {
      $dbh->rollBack();
      echo "Ошибка: " . $e->getMessage();
    }
    header("Location: admin.php");
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
    echo '<form method="POST">
    Пользователь: '.$list_User_Name.', логин: '.$list_User_Login.', права: '.$list_User_Access_Rights.'
    <input name="user_rights_change_ID" type="hidden" value="'.$list_User_ID.'">
    <input name="user_rights_change_current" type="hidden" value="'.$list_User_Access_Rights.'">
    <input name="user_rights_change" type="submit" value="Изменить права">
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
    echo '<form method="POST">
    Город <input name="town_name" type="text" value="'.$list_Town_Name.'">
    <input name="town_id" type="hidden" value="'.$list_Town_ID.'">
    <input name="town_change" type="submit" value="Изменить название города">
    <input name="town_delete" type="submit" value="Удалить город">
    </form>';
  }

  #форма добавления города

  echo '<form method="POST">
  Город <input name="town_name" type="text">
  <input name="town_create" type="submit" value="Добавить город">
  </form>';

  #Формирование списка маршрутов

  echo '<h1>Ведение справочника маршрутов</h1>';
  $query_routes_list=$dbh->prepare("SELECT Route.ID, Route.Name, Route.Town_ID  FROM Route");
  $query_routes_list->execute();
  $Routes_list=$query_routes_list->fetchAll();

  #Выгрузка списка маршрутов

  foreach($Routes_list as list($list_Route_ID,$list_Route_Name,$list_Route_Town_ID))
  {
    echo '<form method="POST">
    Сезон <input name="route_name" type="text" value="'.$list_Route_Name.'">
    <select name="route_town">';
    foreach($Towns_list as list($list_Town_ID, $list_Town_Name))
    {
      echo '<option value="'.$list_Town_ID.'" ';
      if ($list_Town_ID==$list_Route_Town_ID)
      {
        echo 'selected';
      }
      echo '>'.$list_Town_Name.'</option>';
    }
    echo '</select>
    <input name="route_id" type="hidden" value="'.$list_Route_ID.'">
    <input name="route_change" type="submit" value="Изменить маршрут">
    <input name="route_delete" type="submit" value="Удалить маршрут">
    </form>';
  }

  #выгрузка формы добавления маршрута

  echo '<form method="POST">
  Маршрут <input name="route_name" type="text">
  <select name="route_town" required>';
  foreach($Towns_list as list($list_Town_ID, $list_Town_Name))
    {
      echo '<option value="'.$list_Town_ID.'">'.$list_Town_Name.'</option>';
    }
  echo '</select>
  <input name="route_create" type="submit" value="Добавить маршрут">
  </form>';

  #Формирование списка сезонов

  echo '<h1>Ведение справочника сезонов</h1>';
  $query_seazons_list=$dbh->prepare("SELECT Seazon.ID, Seazon.Name, Seazon.Route_ID  FROM Seazon");
  $query_seazons_list->execute();
  $Seazons_list=$query_seazons_list->fetchAll();

  #Выгрузка списка сезонов

  foreach($Seazons_list as list($list_Seazon_ID,$list_Seazon_Name,$list_Seazon_Route_ID))
  {
    echo '<form method="POST">
    Сезон <input name="seazon_name" type="text" value="'.$list_Seazon_Name.'">
    <select name="seazon_route">';
    foreach($Routes_list as list($list_Route_ID,$list_Route_Name,$list_Route_Town_ID))
    {
      echo '<option value="'.$list_Route_ID.'" ';
      if ($list_Route_ID==$list_Seazon_Route_ID)
      {
        echo 'selected';
      }
      echo '>'.$list_Route_Name.'</option>';
    }
    echo '</select>
    <input name="seazon_id" type="hidden" value="'.$list_Seazon_ID.'">
    <input name="seazon_change" type="submit" value="Изменить сезон">
    <input name="seazon_delete" type="submit" value="Удалить сезон">
    </form>';
  }

  #выгрузка формы добавления сезона

  echo '<form method="POST">
  Сезон <input name="seazon_name" type="text">
  <select name="seazon_route" required>';
  foreach($Routes_list as list($list_Route_ID,$list_Route_Name,$list_Route_Town_ID))
    {
      echo '<option value="'.$list_Route_ID.'">'.$list_Route_Name.'</option>';
    }
  echo '</select>
  <input name="seazon_create" type="submit" value="Добавить сезон">
  </form>';
  
  #Формирование списка дат сезона

  echo '<h1>Ведение справочника дат сезонов</h1>';
  $query_days_list=$dbh->prepare("SELECT Seazon_days.ID, Seazon_days.Day, Seazon_days.Seazon_ID FROM Seazon_days");
  $query_days_list->execute();
  $Days_list=$query_days_list->fetchAll();

  #Выгрузка списка дат сезона

  foreach($Days_list as list($list_Seazon_day_ID,$list_Seazon_day_Day,$list_Seazon_day_Seazon_ID))
  {
    echo '<form method="POST">
    Дата <input name="seazon_day_day" type="date" value="'.$list_Seazon_day_Day.'">
    <select name="seazon_day_seazon">';
    foreach($Seazons_list as list($list_Seazon_ID,$list_Seazon_Name,$list_Seazon_Route_ID))
    {
      echo '<option value="'.$list_Seazon_ID.'" ';
      if ($list_Seazon_ID==$list_Seazon_day_Seazon_ID)
      {
        echo 'selected';
      }
      echo '>'.$list_Seazon_Name.'</option>';
    }
    echo '</select>
    <input name="seazon_day_id" type="hidden" value="'.$list_Seazon_day_ID.'">
    <input name="seazon_day_delete" type="submit" value="Удалить дату из сезона">
    </form>';
  }

  #выгрузка формы добавления даты сезона

  echo '<form method="POST">
  Дата <input name="seazon_day_day" type="date">
  <select name="seazon_day_seazon" required>';
  foreach($Seazons_list as list($list_Seazon_ID,$list_Seazon_Name,$list_Seazon_Route_ID))
    {
      echo '<option value="'.$list_Seazon_ID.'">'.$list_Seazon_Name.'</option>';
    }
  echo '</select>
  <input name="seazon_day_create" type="submit" value="Добавить день в сезон">
  </form>';

  echo '<h1>Ведение справочника навигационных точек</h1>';  
  
  #Формирование списка навигационных точек

  $query_navpoints_list=$dbh->prepare("SELECT Navpoint.ID, ST_X(Navpoint.Coordinates), ST_Y(Navpoint.Coordinates), Navpoint.Tag FROM Navpoint");
  $query_navpoints_list->execute();
  $Navpoints_list=$query_navpoints_list->fetchAll();

  #Выгрузка списка навигационных точек

  foreach($Navpoints_list as list($list_Navpoint_ID, $list_Navpoint_X, $list_Navpoint_Y, $list_Navpoint_Tag))
  {
    echo '<form method="POST">
    Нав. точка <input name="navpoint_tag" type="text" value="'.$list_Navpoint_Tag.'">
    Коорд широта <input name="navpoint_x" type="text" value="'.$list_Navpoint_X.'">
    Коорд долгота <input name="navpoint_y" type="text" value="'.$list_Navpoint_Y.'">
    <input name="navpoint_id" type="hidden" value="'.$list_Navpoint_ID.'">
    <input name="navpoint_change" type="submit" value="Изменить нав. точку">
    <input name="navpoint_delete" type="submit" value="Удалить нав. точку">
    </form>';
  }

  #форма добавления навигационной точки

  echo '<form method="POST">
  Нав. точка <input name="navpoint_tag" type="text">
  Коорд широта <input name="navpoint_x" type="text">
  Коорд долгота <input name="navpoint_y" type="text">
  <input name="navpoint_create" type="submit" value="Добавить нав. точку">
  </form>';

  #Формирование списка графа маршрута

  echo '<h1>Ведение справочника графов маршрутов</h1>';
  $query_route_graph_list=$dbh->prepare("SELECT Route_graph.ID, Route_graph.Visit_order, Route_graph.Route_ID, Route_graph.Navpoint_ID  FROM Route_graph");
  $query_route_graph_list->execute();
  $Route_graph_list=$query_route_graph_list->fetchAll();

  #Выгрузка списка графа маршрута

  foreach($Route_graph_list as list($list_Route_graph_ID,$list_Route_graph_Visit_order,$list_Route_graph_Route_ID,$list_Route_graph_Navpoint_ID))
  {
    echo '<form method="POST">
    Маршрут 
    <select name="route_graph_route">';
    foreach($Routes_list as list($list_Route_ID,$list_Route_Name,$list_Route_Town_ID))
    {
      echo '<option value="'.$list_Route_ID.'" ';
      if ($list_Route_ID==$list_Route_graph_Route_ID)
      {
        echo 'selected';
      }
      echo '>'.$list_Route_Name.'</option>';
    }
    echo '</select>
    Нав. точка 
    <select name="route_graph_navpoint">';
    foreach($Navpoints_list as list($list_Navpoint_ID, $list_Navpoint_X, $list_Navpoint_Y, $list_Navpoint_Tag))
    {
      echo '<option value="'.$list_Navpoint_ID.'" ';
      if ($list_Navpoint_ID==$list_Route_graph_Navpoint_ID)
      {
        echo 'selected';
      }
      echo '>'.$list_Navpoint_Tag.'</option>';
    }
    echo '</select>
    Порядок прохождения <input name="route_graph_visit_order" type="text" value="'.$list_Route_graph_Visit_order.'">
    <input name="route_graph_id" type="hidden" value="'.$list_Route_graph_ID.'">
    <input name="route_graph_change" type="submit" value="Изменить порядок прохождения">
    <input name="route_graph_delete" type="submit" value="Удалить">
    </form>';
  }

  #выгрузка формы добавления графа маршрута

  echo '<form method="POST">
  Маршрут
  <select name="route_graph_route" required>';
  foreach($Routes_list as list($list_Route_ID,$list_Route_Name,$list_Route_Town_ID))
    {
      echo '<option value="'.$list_Route_ID.'">'.$list_Route_Name.'</option>';
    }
  echo '</select>
  Нав. точка
  <select name="route_graph_navpoint" required>';
  foreach($Navpoints_list as list($list_Navpoint_ID, $list_Navpoint_X, $list_Navpoint_Y, $list_Navpoint_Tag))
    {
      echo '<option value="'.$list_Navpoint_ID.'">'.$list_Navpoint_Tag.'</option>';
    }
  echo '</select>
  Порядок прохождения <input name="route_graph_visit_order" type="text">
  <input name="route_graph_create" type="submit" value="Добавить точку в граф">
  </form>';
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
<form method="POST">
Имя <input name="new_user_name" type="text" value="<?php echo $_SESSION['user']; ?>">
<input name="submit_name" type="submit" value="Изменить">
</form>

<?
#смена пароля пользователя и администратора
?>

<form  method="POST">
Пароль <input name="new_user_pass1" type="text">
Пароль еще раз <input name="new_user_pass2" type="text">
<input name="submit_pass" type="submit" value="Изменить пароль">
</form>
<script>
  let cords = ['scrollX','scrollY']; 
  // сохраняем позицию скролла в localStorage
  window.addEventListener('unload', e => cords.forEach(cord => localStorage[cord] = window[cord])); 
  // вешаем событие на загрузку (ресурсов) страницы
  window.addEventListener('load', e => 
  {
    // если в localStorage имеются данные
    if (localStorage[cords[0]])
    {
      // скроллим к сохраненным координатам
      window.scroll(...cords.map(cord => localStorage[cord]));
      // удаляем данные с localStorage
      cords.forEach(cord => localStorage.removeItem(cord));
    }
  }); 
</script>  
</body>
</html>
