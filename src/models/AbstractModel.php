<?php
namespace PhpDevil\ORM\models;
use PhpDevil\Common\Configurator\Loader;
use PhpDevil\ORM\attributes\IntegerAttribute;
use PhpDevil\ORM\attributes\StringAttribute;
use PhpDevil\ORM\relations\AbstractRelation;

/**
 * Class AbstractModel
 * @package PhpDevil\ORM\models
 */
abstract class AbstractModel
{
    /**
     * Путь к файлу конфигурации модели
     * @return null
     */
    public static function getConfigSource(){ return null; }

    /**
     * Доступ к данным конфигурационного массива
     * @return mixed
     */
    public static function getConfig()  { return (Loader::getInstance()->load(static::getConfigSource()))['data']; }
    public static function attributes() { return (static::getConfig())['attributes'] ?: []; }
    public static function relations()  { return (static::getConfig())['relations']  ?: []; }
    public static function rules()      { return (static::getConfig())['rules']      ?: []; }

    public static function typeName()  { return (static::mainBehavior())::typeName();   }
    public static function typeClass() { return (static::mainBehavior())::typeClass();  }

    public static function getValidationClass() {return null;}

    public static function labelOf($name)
    {
        if (isset((static::getConfig())['labels'][$name])) {
            return (static::getConfig())['labels'][$name];
        } else {
            return ucwords(str_replace('_', ' ', $name));
        }
    }

    protected $_attributes = [];

    protected $_roles = [];

    protected $_keys  = [];

    protected $_provider = null;

    public function getRelation($name)
    {
        return AbstractRelation::create((static::relations())[$name], get_class($this));
    }

    public function __get($attribute)
    {
        return $this->getAttribute($attribute);
    }

    public function __set($name, $value)
    {
        $this->setAttributeValue($name, $value);
    }

    public function getAttributes()
    {
        $result = [];
        foreach ($this->_attributes as $k=>$v) {
            $result[$k] = $v->getValue();
        }
        return $result;
    }

    public function getAttribute($attribute)
    {
        if (false !== ($dot = strpos($attribute, '.'))) {
            return $this->_provider->getAttributeFor($this, $attribute);
        } else {
            if (isset($this->_attributes[$attribute])) {
                if ($this->_provider) {
                    $this->_provider->checkIfQueried($attribute);
                }
                return $this->_attributes[$attribute];
            } else {
                if (null !== $this->_provider) {
                    return $this->_provider->getAttributeFor($this, $attribute);
                }
            }
        }
    }

    public function setOwner($provider)
    {
        $this->_provider = $provider;
        return $this;
    }

    public function setAttributes($arr)
    {
        foreach ($arr as $k=>$v) {
            $this->setAttributeValue($k, $v);
        }
    }

    public function setAttributeValue($name, $value)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            if (isset($this->_attributes[$name])) {
                $this->_attributes[$name]->setValue($value);
            }
        }
    }

    /**
     * Имя атрибута модели с заданной ролью
     * @param $roleName
     * @return int|null|string
     */
    public static function getRoleFieldStatic($roleName)
    {
        foreach (static::attributes() as $name=>$attr) {
            if (isset($attr['role']) && $roleName == $attr['role']) return $name;
        }
        return null;
    }

    public function getRoleField($roleName)
    {
        if (!isset($this->_roles[$roleName])) {
            $this->_roles[$roleName] = static::getRoleFieldStatic($roleName);
        }
        return $this->_roles[$roleName];
    }

    public function getRoleValue($roleName)
    {
        if (($attr = $this->getRoleField($roleName)) && isset($this->_attributes[$attr])) {
            return $this->_attributes[$attr]->getValue();
        }
        return null;
    }

    /**
     * Установка значения атрибута по имени роли
     * @param $role
     * @param $value
     * @return $this
     */
    public function setRoleValue($role, $value)
    {
        if ($field = $this->getRoleField($role)) {
            $this->setAttributeValue($field, $value);
        }
        return $this;
    }

    /**
     * Создание экземпляра класса модели возможно вызовом метода model()
     * AbstractModel constructor.
     */
    protected function __construct()
    {
        $this->discoverAttributes();
        $this->appendValidationRules();
    }

    final public function __clone()
    {
        foreach ($this->_attributes as $k=>$v) if (is_object($v)) {
            $newObj = clone($v);
            $newObj->setOwner($this);
            $this->_attributes[$k] = $newObj;
        }
    }

    /**
     * Создание экземпляра класса модели
     */
    public static function model()
    {
        return new static();
    }

    protected function discoverAttributes()
    {
        $attributes = static::attributes();
        foreach ($attributes as $name=>$config) {
            $type = $config['type'];
            if (false !== ($sp = strrpos($type, '('))) $type = substr($type, 0, $sp);
            $class = static::getAttributeClass($type);
            $this->_attributes[$name] = new $class($name, $config);
            $this->_attributes[$name]->setOwner($this);
        }
    }

    public function appendValidationRules()
    {
        if (!empty($rules = static::rules())) foreach ($rules as $config) {
            $ruleCallable = null;
            $rule = ucfirst($config['rule']);
            if ($class = static::getValidationClass($config['rule'])) {
                $ruleCallable = [$class, 'validateValue'];
            } else {
                $ruleCallable = [$this, 'validate' . $rule];
            }
            if (is_callable($ruleCallable)) {
                foreach ($config['attributes'] as $attr) if (isset($this->_attributes[$attr])){
                    $this->_attributes[$attr]->appendValidationRule($ruleCallable, $config);
                }
            }
        }
    }

    protected $_validationErrors = [];

    final public function addValidationError($attributeName, $message)
    {
        if (!isset($this->_validationErrors[$attributeName])) $this->_validationErrors[$attributeName] = [];
        if (!in_array($message, $this->_validationErrors[$attributeName])) {
            $this->_validationErrors[$attributeName][] = $message;
        }
    }

    final public function getValidationErrors()
    {
        return $this->_validationErrors;
    }

    final public function validate()
    {
        $this->_validationErrors = [];
        foreach ($this->_attributes as $attr) {
            $attr->validate();
        }
        return empty($this->_validationErrors);
    }

    /**
     * Допустимые типы атрибутов
     * @var array
     */
    protected static $attributeTypes = [
        'integer' => IntegerAttribute::class,
        'string'  => StringAttribute::class,
    ];

    protected static function getAttributeClass($typeName)
    {
        return static::$attributeTypes[$typeName];
    }

    /**
     * @var null
     */
    protected $_replacements = null;

    /**
     * Составной атрибут по шаблону
     * @param $template
     * @return string
     */
    public function fromTemplate($template)
    {
        if (null === $this->_replacements) {
            foreach ($this->_attributes as $k=>$v) {
                $this->_replacements['${' . $k . '}'] = $v->getValue();
            }
        }
        return strtr($template, $this->_replacements);
    }

    #====== Общие правила валидации автрибутов

    public function validateRequired($attribute, $options)
    {
        $value = $attribute->getValue();
        return !empty($value);
    }
}