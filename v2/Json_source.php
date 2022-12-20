<?php
require 'interface.php';
require 'json_output.php';
class json_source implements say
{
    private array $json_input;
    
    public function __construct()
    {
        try
        {
            $this -> json_input = json_decode(file_get_contents('php://input'),true);
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
    }

    public function say()
    {
        return $this -> json_input;
    }
}

//разделение создание объектов нужно для динамического подключения файлов с классами,
//т.к. компилятор инициализирует объекты по порядку следования, а не по логике вложенности
$source = new json_source();
require 'query_from_source.php'; //динамическое подключение файла с классом
$query_tag = new query_from_source($source -> say());
$request_name = $query_tag -> say();
require $request_name.'.php';//динамическое подключение файла с классом
$query_object = new $request_name($source -> say());
$output = new json_output($query_object -> say());
$output -> send_post();
?>
