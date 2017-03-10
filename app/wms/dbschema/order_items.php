<?php
$db['order_items']=array (
  'columns' =>
  array (
    'item_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'obj_id' =>
    array (
      'type' => 'table:order_objects@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '商品对象ID',
    ),
    'product_id' =>
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '货品ID',
    ),
    'bn' =>
    array (
      'type' => 'varchar(40)',
      'editable' => false,
      'is_title' => true,
      'comment' => '货号',
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '名称',
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '单价',
    ),
    'pmt_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'comment' => '优惠金额',
    ),
    'sale_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'comment' => '销售金额',
    ),
    'nums' =>
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'sdfpath' => 'quantity',
      'comment' => '数量',
    ),
    'item_type' =>
    array (
      'type' => 'varchar(50)',
      'default' => 'product',
      'required' => true,
      'editable' => false,
      'comment' => '货品类型',
    ),
  ),
  'comment' => '订单货品明细表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);