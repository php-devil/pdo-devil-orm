<?php
namespace PhpDevil\ORM\connections;
use PhpDevil\ORM\queries\QueryPlain;
use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;

class DefaultConnection
{
    /**
     * Ссылки на диалекты SQL для сборки запросов построителем
     * @var array
     */
    protected static $schemes = [
        'mysql' => QueryBuilderInterface::MYSQL,
    ];

    /**
     * Имя подключения
     * @var static
     */
    protected $name;

    protected $scheme = null;

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

    public function getDefaultEngine()
    {
        return 'InnoDB';
    }

    public function getDefaultCharset()
    {
        return 'utf8';
    }

    /**
     * Открывает соединение с БД при первом вызове или после закрытия соединения
     */
    final public function open()
    {
        if (null === $this->handler) {
            $this->handler = new \PDO($this->config['dsn'], $this->config['user'], $this->config['password']);
            $this->handler->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
    }

    final public function getHandler()
    {
        $this->open();
        return $this->handler;
    }

    final public function prepare($statement)
    {
        $this->open();
        return new QueryPlain($this->handler, $statement);
    }

    final public function getDialect()
    {
        if (null === $this->scheme) {
            $dsn = parse_url($this->config['dsn']);
            if (isset(static::$schemes[$dsn['scheme']])) {
                $this->scheme = static::$schemes[$dsn['scheme']];
            }
        }
        return $this->scheme;
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