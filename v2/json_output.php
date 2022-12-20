<?php
class json_output implements post
{
    private array $post;

    public function __construct(array $output)
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

    public function add_post(array $output)
    {
        $this -> post = $this -> post + $output;
    }

    public function send_post()
    {
        header('Content-Type: application/json');
        echo json_encode($this -> post);
    }
}