<?php
class public_key implements say
{
    private $server_key;

    function __construct()
    {
        try
        {
            require '../../conn/key.php';
            $this -> server_key = $publicKey;
        }
        catch (Throwable $e)
        {
            http_response_code(400);
            exit ($e->getMessage());
        }
    }

    function say()
    {
        //require 'json_output.php';
        //$post = new json_output(['server_key'=> $this -> server_key]);
        //$post -> send_post();
        return ['server_key'=> $this -> server_key];
    }
}