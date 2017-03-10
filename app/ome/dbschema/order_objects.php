<?php
$db['order_objects']=array (
  'columns' =>
  array (
    'obj_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' =>
    array (
      'type' => 'table:orders@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单id',
    ),    'obj_type' =>
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'editable' => false,
      'comment' => '对象类型',
    ),
    'obj_alias' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '对象别名',
    ),
    'shop_goods_id' =>
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '前端店铺商品id',
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
      'comment' => '商品名',
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '价格',
    ),
    'amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '小计',
    ),
    'quantity' =>
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'comment' => '数量',
    ),
    'weight' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '重量',
    ),
    'score' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '积分',
    ),
    'pmt_price' =>
    array (
      'type' => 'money',
      'default' => '0',

      'editable' => false,
      'comment' => '商品优惠金额',
    ),
    'sale_price' =>
    array (
      'type' => 'money',
      'default' => '0',

      'editable' => false,
      'comment' => '销售价格',
    ),
    'oid' => 
    array (
      'type' => 'varchar(50)',
      'default' => 0,
      'editable' => false,
      'label' => '子订单号',
    ),
    'is_oversold' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'comment' => '淘宝超卖标记',
    ),
  ),
  'comment' => '订单对象表',
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);