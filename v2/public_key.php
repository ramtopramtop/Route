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

        return ['server_key'=> $this -> server_key];
    }
}