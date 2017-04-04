<?php return array (
  'attributes' => 
  array (
    'id' => 
    array (
      'type' => 'integer(10) unsigned',
      'nullable' => false,
      'extra' => 'auto_increment',
      'role' => 'id',
    ),
    'key_left' => 
    array (
      'type' => 'integer(10) unsigned',
      'nullable' => false,
      'role' => 'tree-left',
    ),
    'key_level' => 
    array (
      'type' => 'integer(10) unsigned',
      'nullable' => false,
      'role' => 'tree-level',
    ),
    'key_right' => 
    array (
      'type' => 'integer(10) unsigned',
      'nullable' => false,
      'role' => 'tree-right',
    ),
    'parent_id' => 
    array (
      'type' => 'integer(10) unsigned',
      'nullable' => false,
      'role' => 'tree-parent',
      'relation' => 'parent',
      'template' => '{$name}',
    ),
    'name' => 
    array (
      'type' => 'string(255)',
    ),
    'url' => 
    array (
      'type' => 'string(255)',
      'role' => 'url',
    ),
  ),
  'table' => 
  array (
    'connection' => 'main',
    'name' => 'pages_structure',
    'keys' => 
    array (
      'primary' => 'id',
      'nested_set' => 
      array (
        0 => 'key_left',
        1 => 'key_level',
        2 => 'key_right',
      ),
      'parent_node' => 'parent_id',
    ),
  ),
  'relations' => 
  array (
    'parent' => 
    array (
      'type' => 'BelongsTo',
      'model' => 'self',
      'here' => 'parent_id',
      'there' => 'id',
    ),
  ),
);