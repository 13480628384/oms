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
      'type' => 'table:delivery@wms',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '发货单ID',
    ),
    'product_id' =>
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '货品ID',
    ),
    'shop_product_id' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => '前端货品ID',
    ),
    'bn' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'is_title' => true,
      'comment' => '货号',
    ),
    'product_name' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '名称',
    ),
    'number' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '数量',
    ),
    'verify' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '是否校验',
    ),
    'verify_num' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '校验数量',
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '单价',
    ),
    'sale_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '销售金额',
    ),
    'pmt_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '优惠金额',
    ),
  ),
  'comment' => '发货单明细表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);