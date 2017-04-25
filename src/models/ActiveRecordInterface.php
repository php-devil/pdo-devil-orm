<?php
namespace PhpDevil\ORM\models;

interface ActiveRecordInterface
{
    /**
     * Установка коллекции записей для выборок данных
     * @param ActiveRecordCollectionInterface $collection
     * @return mixed
     */
    public function setCollection(ActiveRecordCollectionInterface $collection);

    /**
     * Имя поля (атрибута) с заданной ролью
     * @param $roleName
     * @return mixed
     */
    public function getRoleField($roleName);

    /**
     * Имя поля (атрибута) с заданной ролью без создания экземпляра класса модели
     * @param $roleName
     * @return mixed
     */
    public static function getRoleFieldStatic($roleName);

    /**
     * Значение поля (атрибута) с заданной ролью
     * @param $roleName
     * @return mixed
     */
    public function getRoleValue($roleName);

    /**
     * Установка значения атрибута по имени роли
     * @param $role
     * @param $value
     * @return $this
     */
    public function setRoleValue($role, $value);

    /**
     * Поиск по значению первичного ключа
     * @param $value
     * @return null|ActiveRecordInterface
     */
    public static function findByPK($value);

    /**
     * Поиск первой записи, удовлетворяющей критерию
     * @param $arrayOrCriteria
     * @return null|static
     */
    public static function findOne($arrayOrCriteria);

    public static function findAll($columns = null);


}