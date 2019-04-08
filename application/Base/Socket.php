<?php

class Base_Socket
{
    const HOST = '95.213.200.4';
    const PORT = 2994;

    protected static $_instance = null;
    public $socket;

    const MQ_EXC_NAME = 'amq.direct';

    private function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $res = socket_connect($this->socket, self::HOST, self::PORT);
    }

    public static function i()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function setNoBlocking()
    {
        socket_set_nonblock($this->socket);
    }

    public function recV(&$out)
    {
        socket_recv($this->socket, $out, 100, MSG_DONTWAIT);
    }

    public function write($msg)
    {
        return socket_write($this->socket, $msg, strlen($msg));
    }

    public function getPhpMessage($arr)
    {
        return 'php:' . json_encode($arr);
    }
}