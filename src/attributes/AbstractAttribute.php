<?php
namespace PhpDevil\ORM\attributes;

abstract class AbstractAttribute implements AttributeInterface
{
    protected $config = [];

    protected $owner = null;

    protected $name = null;

    protected $value = null;


    #====== Валидация установленного значения

    /**
     * Стек правил валидации значения
     * @var array
     */
    protected $_validationStack = [];

    /**
     * Флаг выполнения валидации. Сбрасывается в false при установке нового значения атрибута
     * @var bool
     */
    protected $_validationDone = false;

    /**
     * Список ошибок валидации значения атрибута
     * @var array
     */
    protected $_validationErrors = [];

    final public function getAttributeName()
    {
        return $this->name;
    }

    /**
     * Добавление правила валидации значения атрибута
     * @param callable $rule
     * @param array $options
     * @return $this
     */
    final public function appendValidationRule(callable $rule, $options = [])
    {
        $this->_validationStack[] = [
            'rule' => $rule, 'options' => $options
        ];
        return $this;
    }

    public function getErrors()
    {
        return $this->_validationErrors;
    }

    /**
     * Валидация проведена, значение атрибута валидно
     * @return bool
     */
    public function isValid()
    {
        return ($this->_validationDone && empty($this->_validationErrors));
    }

    /**
     * Валидация проведена, атрибут содержит ошибки
     * @return bool
     */
    public function hasErrors()
    {
        return ($this->_validationDone && !empty($this->_validationErrors));
    }

    public function validate($throwTo = null)
    {
        if (!empty($this->_validationStack)) foreach ($this->_validationStack as $rule) {
            $error = null;
            $result = call_user_func($rule['rule'], $this, $rule['options']);
            if (true === $result) {
                // validation ok
            } elseif (false === $result) {
                $error = $rule['options']['message'];
            } else {
                $error = $result;
            }

            if ($error) {
                // $this->addValidationError($error);
                // Сообщение об ошибке добавит владелец
                $this->owner->addValidationError($this->name, $error);
                if (null !== $throwTo) {
                    $throwTo->addValidationError($this->name, $error);
                }
            }
        }
        $this->_validationDone = true;
    }

    public function addValidationError($error)
    {
        if (!in_array($error, $this->_validationErrors)) $this->_validationErrors[] = $error;
    }

    public function beforeSet($value)
    {
        return $value;
    }

    final public function setValue($value)
    {
        $this->value = $this->beforeSet($value);
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->getValue();
    }

    public function setOwner($model)
    {
        $this->owner = $model;
        return $this;
    }

    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
    }
}