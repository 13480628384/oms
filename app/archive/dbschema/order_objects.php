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
      'type' => 'table:orders@archive',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单ID',
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
    'amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '合计',
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
    'sale_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'comment' => '销售金额',
    ),
    'oid' => 
    array (
      'type' => 'varchar(50)',
      'default' => 0,
      'editable' => false,
      'label' => '子订单号',
    ),
  ),
  'index' =>
  array (
    'idx_c_order_id' =>
    array (
        'columns' =>
        array (
          0 => 'order_id',
        ),
    ),
  ),
  'comment' => '归档订单商品对象表',
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);