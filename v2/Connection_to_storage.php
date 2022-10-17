<?php
class Connection_to_storage implements Say_connection
{
    private $connect_to;

    function __construct()
    {
        try
        {
            require '../../conn/dbase.php';
            $this -> connect_to = new PDO('mysql:host='.$PDO_Host.';dbname='.$PDO_DB_Name, $PDO_DB_User, $PDO_DB_Pass,
             array(PDO::ATTR_PERSISTENT => true));
             $this -> connect_to->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (Exception $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
            
        }
    }

    function Say_connection()
    {
        return $this -> connect_to;
    }
}

interface Say_connection
{
    public function Say_connection();
}
?>