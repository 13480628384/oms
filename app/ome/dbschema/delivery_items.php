<?php
$db['delivery_items']=array (
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
    'delivery_id' => 
    array (
      'type' => 'table:delivery@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '发货单号',
    ),
    'product_id' => 
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => 'ome 货品id',
    ),
    'shop_product_id' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => '前端店铺的货品id',
    
    ),
    'bn' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'is_title' => true,
      'comment' => '货品编号',
    ),
    'product_name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '货品名称',
    ),
    'number' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '货品数量',
    ),
    'verify' => 
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '是否验证',
    ),
    'verify_num' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '验证数量',
    ),
  ), 
  'comment' => '发货单商品明细',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);