<?php
/**
 * Конфигурационный массив модели "Структура сайта"
 */
return [
    /**
     * Определение атрибутов модели
     * имя атрибута => [тип, флаги, роль, доп.свойства]
     */
    'attributes' => [
        'id'        => ['type' => 'integer(10) unsigned', 'nullable' => false, 'extra' => 'auto_increment', 'role'=>'id'],
        'key_left'  => ['type' => 'integer(10) unsigned', 'nullable' => false, 'role'=>'tree-left'],
        'key_level' => ['type' => 'integer(10) unsigned', 'nullable' => false, 'role'=>'tree-level'],
        'key_right' => ['type' => 'integer(10) unsigned', 'nullable' => false, 'role'=>'tree-right'],
        'parent_id' => ['type' => 'integer(10) unsigned', 'nullable' => false, 'role'=>'tree-parent', 'relation'=>'parent', 'template'=>'{$name}'],
        'name'      => ['type' => 'string(255)'],
        'url'       => ['type' => 'string(255)', 'role'=>'url'],
        'test'      => ['type' => 'string(255)']
    ],

    /**
     * Определение таблицы
     * connection - имя соединения с БД
     * имя таблицы в указанной БД
     * keys - ключи таблицы
     */
    'table' => [
        'connection' => 'main',
        'name'       => 'pages_structure',
        'keys' => [
            'primary'     => 'id',
            'nested_set'  => ['key_left', 'key_level', 'key_right'],
            'parent_node' => 'parent_id',
        ],
    ],

    /**
     * Определение связей
     * имя связи, по которму можно обращаться из модели как к атрибуту
     * конфигурация связи
     */
    'relations' => [
        'parent' => ['type' => 'BelongsTo', 'model'=>'self', 'here'=>'parent_id', 'there'=>'id'],
    ],
];