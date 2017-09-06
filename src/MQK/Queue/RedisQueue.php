<?php
namespace MQK\Queue;

use Connection\Connection;
use Monolog\Logger;
use MQK\Job;
use MQK\LoggerFactory;
use MQK\RedisProxy;

class RedisQueue implements Queue
{
    /**
     * @var RedisProxy
     */
    private $connection;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $name;

    public function __construct($name, $connection)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->name = $name;
    }

    public function connection()
    {
        return $this->connection;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function key()
    {
        return "queue_{$this->name}";
    }

    public function enqueue(Message $message)
    {
        if (strpos($message->id(), "_")) {
            $this->logger->error("[enqueue] {$message->id()} contains _", debug_backtrace());
        }
        $message->setQueue($this->name);
        $messageJsonObject = $message->jsonSerialize();
        if ($message->retries()) {
            $messageJsonObject['retries'] = $message->retries();
        }
        $messageJson = json_encode($messageJsonObject);
//        $this->logger->debug("[enqueue] {$message->id()}");
//        $this->logger->debug($messageJson);
        $success = $this->connection->lpush("{$this->key()}", $messageJson);

        if (!$success) {
            $error = $this->connection->getLastError();
            $this->connection->clearLastError();
            throw new \Exception($error);
        }
    }

    public function enqueueBatch($messages)
    {
        $this->connection->multi();
        foreach ($messages as $message) {
            $this->enqueue($message);
        }
        $this->connection->exec();
    }

    public function name()
    {
        return $this->name;
    }

    /**
     * 设置队列名
     *
     * @param $name
     * @return void
     */
    function setName($name)
    {
        $this->name = $name;
    }
}