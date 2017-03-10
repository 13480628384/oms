<?php
$db['appropriation_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'appropriation_id' => 
    array (
      'type' => 'table:appropriation',
      'required' => true,
      'editable' => false,
      'comment' => '库存调拨单主键',
    ),
    'product_id' =>
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'editable' => false,
      'comment' => '货品ID',
    ),
    'bn' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'comment' => '货号',
    ),
    'product_name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '货品名称',
    ),
    'from_branch_id' => 
    array (
      'type' => 'table:branch@ome',
      'editable' => false,
      'comment' => '调出仓库ID',
    ),
    'from_pos_id' => 
    array (
      'type' => 'table:branch_pos@ome',
      'editable' => false,
      'comment' => '调出货位',
    ),
    'to_branch_id' => 
    array (
      'type' => 'table:branch@ome',
      'editable' => false,
      'required' => true,
      'comment' => '调入仓库ID',
    ),
    'to_pos_id' => 
    array (
      'type' => 'table:branch_pos@ome',
      'editable' => false,
      'required' => true,
      'comment' => '调入货位',
    ),
    'num' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '数量',
    ),
  ), 
  'comment' => '库存调拨单明细',
  'engine' => 'innodb',
  'version' => '$Rev: 44513 $',
);
