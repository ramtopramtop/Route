<?php
class Json_source implements Say_source
{
    private $json_input;
    
    function __construct()
    {
        try
        {
            require 'Query_from_source.php'; //динамическое подключение файла с классом
            $this -> json_input = json_decode(file_get_contents('php://input'),true);
        }
        catch (Error $e)
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

//разделение создание объектов нужно для динамического подключения файлов с классами,
//т.к. компилятор инициализирует объекты по порядку следования, а не по логике вложенности
$source = new Json_source;
$query_tag = new Query_from_source($source -> Say_source()); 
$request_name = $query_tag -> Say_query();
$query_object = new $request_name($source -> Say_source());
?>
