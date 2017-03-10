<?php
$db['operations']=array (
  'columns' => 
  array (
    'operation_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'operation_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'editable' => false,
      'comment' => '操作中文名称',
    ),
  ),
  'comment' => '操作表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);