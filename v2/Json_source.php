<?php
require 'interface.php';
require 'json_output.php';
class json_source implements say
{
    private $json_input;
    
    function __construct()
    {
        try
        {
            require 'query_from_source.php'; //динамическое подключение файла с классом
            $this -> json_input = json_decode(file_get_contents('php://input'),true);
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
    }

    function say()
    {
        return $this -> json_input;
    }
}

//разделение создание объектов нужно для динамического подключения файлов с классами,
//т.к. компилятор инициализирует объекты по порядку следования, а не по логике вложенности
$source = new json_source();
$query_tag = new query_from_source($source -> say()); 
$request_name = $query_tag -> say();
$query_object = new $request_name($source -> say());
$output = new json_output($query_object -> say());
$output -> send_post();
//$output -> ask($query_result);
?>
