<?php
$db['order_objects']=array (
  'columns' =>
  array (
    'obj_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'order_bn' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单号',
    ),
    'obj_type' =>
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'editable' => false,
      'comment' => '商品对象类型',
    ),
    'goods_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '商品ID',
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
    'quantity' =>
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'comment' => '数量',
    ),
    'pmt_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'comment' => '优惠金额',
    ),
  ),
  'comment' => '订单商品对象表',
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);