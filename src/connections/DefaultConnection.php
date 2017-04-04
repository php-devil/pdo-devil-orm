<?php
namespace PhpDevil\ORM\connections;
use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;

class DefaultConnection
{
    /**
     * Ссылки на диалекты SQL для сборки запросов построителем
     * @var array
     */
    protected static $schemas = [
        'mysql' => QueryBuilderInterface::MYSQL,
    ];

    /**
     * Имя подключения
     * @var static
     */
    protected $name;

    /**
     * Начальная конфигурация подключения
     * @var array
     */
    protected $config;

    /**
     * Хендлер соединения
     * @var \PDO|null
     */
    protected $handler = null;

    /**
     * Открывает соединение с БД при первом вызове или после закрытия соединения
     */
    final public function open()
    {

    }



    /**
     * Закрытие соединения с БД
     */
    final public function close()
    {
        $this->handler = null;
    }

    /**
     * Соединение с БД.
     * Будет переопределено при разделении соединений на разные классы
     * @param $name
     * @param $config
     * @return DefaultConnection
     */
    final public static function connect($name, $config)
    {
        return new DefaultConnection($name, $config);
    }

    protected function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
    }
}