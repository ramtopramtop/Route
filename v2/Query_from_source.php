<?php
class query_from_source implements say
{
    private string $query_name;

    function __construct(array $source_query)
    {
        try
        {
            if (!isset($source_query ["query"]))
            {
                throw new Exception('Нет ключа запроса');
            }
            $this -> query_name = $source_query ["query"];            
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