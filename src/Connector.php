<?php
namespace PhpDevil\ORM;

use PhpDevil\ORM\connections\DefaultConnection;
use PhpDevil\ORM\relations\BelongsTo;

class Connector
{
    private static $instance = null;

    private $connectionsAvailable = [];

    private $connectionsEnabled = [];

    /**
     * Конкретные классы отношений по типам
     * @var array
     */
    protected $relationsClasses = [
        'BelongsTo' => BelongsTo::class,
    ];

    public function getRelationsClasses()
    {
        return $this->relationsClasses;
    }

    public function setRelationClasses($arr)
    {
        $this->relationsClasses = $arr;
        return $this;
    }

    /**
     * Добавление конфигурации известных соединений
     * @param $name
     * @param $config
     * @throws \Exception
     */
    public function createConnection($name, $config)
    {
        if (!isset($this->connectionsAvailable[$name])) {
            $this->connectionsAvailable[$name] = $config;
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