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
    'query'     => PagesStructure::findAll(['id', 'url', 'name', 'test', 'parent.url', 'parent.test', 'parent.name']),
]);

$rows = $select->all();
?>
<table border="1">
    <tr>
        <td>id</td>
        <td>id</td>
        <td>url</td>
        <td>name</td>
        <td>parent.url</td>
        <td>parent.name</td>
    </tr>
<?php
foreach ($rows as $id => $record)
{
    echo '<tr>';
    echo '<td>1. id=' . $id . '</td>';
    echo '<td>2. ' . $record->id . '</td>';
    echo '<td>3. ' . $record->url . ' ' . $record->test . '</td>';
    echo '<td>4. ' . $record->getAttribute('name') . '</td>';
    echo '<td>5. ' . $record->getAttribute('parent.url') . '</td>';
    echo '<td>6. ' . $record->parent->name . '</td>';
    echo '<td>7. ' . $record->parent->key_level . '</td>';
    echo '<td>8. ' . $record->parent->test . '</td>';
}
?>
</table>



