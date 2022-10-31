<?php
class query_from_source implements say
{
    private $query_name;

    function __construct($source_query)
    {
        try
        {
            if (!isset($source_query ["query"]))
            {
                throw new Exception('Нет ключа запроса');
            }
            $this -> query_name = $source_query ["query"];
            require $this -> Say().'.php';//динамическое подключение файла с классом
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }        
    }
    
    function say()
    {
        return $this -> query_name;
    }
}
?>