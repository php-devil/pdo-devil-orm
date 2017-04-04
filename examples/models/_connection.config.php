<?php
\PhpDevil\ORM\Connector::getInstance()->createConnection('demo', [
    'dsn'      => 'mysql:host=localhost;dbname=test_demo',
    'user'     => 'root',
    'password' => '',
    'engine'   => 'InnoDB',
    'charset'  => 'utf8'
]);