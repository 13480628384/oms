<?php
$db['ar_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'ar_id' => 
    array (
      'type' => 'table:ar@finance',
      'required' => true,
      'editable' => false,
      'comment' => '销售应收单主键',
    ),
    'bn' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'is_title' => true,
      'comment' => '货品编码',
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '货品名称',
    ),
    'num' => 
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'comment' => '数量',
    ),
    'money' => 
    array (
      'type' => 'money',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => '金额',
    ),
  ),
  'comment' => '销售应收单明细',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);