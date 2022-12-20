<?php
/**
 * @testFunction testPublic_key
 */
class public_key implements say
{
    private string $server_key;

    public function __construct()
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

    /**
     * @testFunction testPublic_keySay
     */
    public function say()
    {
        return ['server_key'=> $this -> server_key];
    }
}