<?php
class json_output implements post
{
    private $post;

    function __construct($output)
    {
        try
        {
            if (!isset($output))
            {
                throw new Exception('Попытка отправки клиенту пустых значений');
            }
            $this -> post = $output;
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit ($e->getMessage());  
        }        
    }

    function add_post($output)
    {
        $this -> post = $this -> post + $output;
    }

    function send_post()
    {
        header('Content-Type: application/json');
        echo json_encode($this -> post);
    }
}