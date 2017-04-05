<?php
require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/models/_connection.config.php';
require __DIR__ . '/models/classes/PagesStructure.php';

\PhpDevil\Common\Configurator\Loader::getInstance()->enableFileCaching(__DIR__ . '/cache');

$select = new \PhpDevil\ORM\providers\RecordSet([
    // для создания рекордсета указываются поля модели или
    // алиасы данных, которые могут быть получены по связям этой модели
    // Для создания произвольного сета используем RecordSetArray
    'prototype' => PagesStructure::class,
    'query'     => PagesStructure::findAll(['id', 'url', 'name', 'parent.url', 'parent.name']),
]);

$rows = $select->all();


