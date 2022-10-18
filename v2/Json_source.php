<?php
class Json_source implements Say_source
{
    private $json_input;
    
    function __construct()
    {
        try
        {
            require 'Query_from_source.php';
            $this -> json_input = json_decode(file_get_contents('php://input'),true);
        }
        catch (Exception $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        
        }
    }

    function Say_source()
    {
        return $this -> json_input;
    }
}

interface Say_source
{
    public function Say_source();
}

//$current_json_source = new Json_source;
//var_dump ($current_json_source -> Say_source());
$source = new Json_source;
$query = new Query_from_source($source -> Say_source());
var_dump ($query -> Say_query());
?>
