<?php
class Query_from_source implements Say_query
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
            require $this -> Say_query().'.php';//динамическое подключение файла с классом
        }
        catch (Exception $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
        catch (Error $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
    }
    
    function Say_query()
    {
        return $this -> query_name;
    }
}

interface Say_query
{
    public function Say_query();
}
?>