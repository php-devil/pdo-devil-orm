<?php
\PhpDevil\ORM\Connector::getInstance()->createConnection('main', [
    'dsn'      => 'mysql:host=localhost;dbname=test_demo',
    'user'     => 'root',
    'password' => '',
    'engine'   => 'InnoDB',
    'charset'  => 'utf8'
]);