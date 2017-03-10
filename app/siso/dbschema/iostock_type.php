<?php
$db['iostock_type']=array (
  'columns' =>
  array (
    'type_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
    ),
    'type_name' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'comment' => '类型名称',
      'is_title' => true,
    ),
  ),
  'comment' => '出入库类型',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);