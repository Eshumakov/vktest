<?php
class Base_ShareMQ
{
    protected static $_instance = null;
    protected $channel, $queue;

    const MQ_EXC_NAME = 'amq.direct';
    const MQ_Q_NAME = 'direct_messages';
    const RT_KEY = 'rt_reciver';

    private function __construct()
    {
        $this->queue = new AMQPConnection(Base_Config::getMqConfig());
        $this->queue->connect();
        $this->channel = new AMQPChannel($this->queue);
    }

    public static function i()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getChnl()
    {
        return $this->channel;
    }

    public function getExchange()
    {
        $exchange = new AMQPExchange($this->getChnl());
        $exchange->setName(self::MQ_EXC_NAME);
        return $exchange;
    }

    public function disconnect()
    {
        $this->queue->disconnect();
    }

    /**
     * @return AMQPQueue
     */
    public function getQueue()
    {
        $q = new AMQPQueue($this->channel);
        $q->setName(self::MQ_Q_NAME);
        $q->declareQueue();
        $q->bind(self::MQ_EXC_NAME, self::RT_KEY);

        return $q;
    }
}