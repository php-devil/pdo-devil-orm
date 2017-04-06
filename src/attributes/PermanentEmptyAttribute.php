<?php
namespace PhpDevil\ORM\attributes;

/**
 * Постоянно пустой атрибут.
 *
 * Заглушка для передачи в представления, образуется в методе get при получении данных
 * по связям, если запись правой модели - null
 *
 * Class PermanentEmptyAttribute
 * @package PhpDevil\ORM\attributes
 */
class PermanentEmptyAttribute
{
    public function getValue()
    {
        return null;
    }

    public function __toString()
    {
        return '';
    }

    public function __get($name)
    {
        return $this;
    }

    public function __call($name, $param)
    {
        return $this;
    }
}