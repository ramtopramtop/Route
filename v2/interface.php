<?php
interface say
{
    public function say();
}

interface ask
{
    public function ask();
}

interface post
{
    public function add_post($output);
    public function send_post();
}
?>