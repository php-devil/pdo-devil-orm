<?php
namespace PhpDevil\ORM;

use PhpDevil\ORM\connections\DefaultConnection;

class Connector
{
    private static $instance = null;

    private $connectionsAvailable = [];

    private $connectionsEnabled = [];

    /**
     * Добавление конфигурации известных соединений
     * @param $name
     * @param $config
     * @throws \Exception
     */
    public function createConnection($name, $config)
    {
        if (!isset($this->connectionsEnabled[$name])) {
            $this->connectionsEnabled[$name] = $config;
        } else {
            throw new \Exception('Connection ' . $name . ' is established and can not be changed');
        }
    }

    /**
     * Соединение с БД по ранее сконфигурированному подключению
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function getConnection($name)
    {
        if (!isset($this->connectionsEnabled[$name])) {
            if (!isset($this->connectionsAvailable[$name])) {
                throw new \Exception('Connection ' . $name . ' is unknown');
            }
            $this->connectionsEnabled[$name] = DefaultConnection::connect($name, $this->connectionsAvailable[$name]);
        }
        return $this->connectionsEnabled[$name];
    }

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (null === self::$instance) self::$instance = new self;
        return self::$instance;
    }
}