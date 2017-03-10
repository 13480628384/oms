<?php
$db['order_items']=array (
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
    'order_id' => 
    array (
     'type' => 'table:orders@archive',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单ID',
    ),
    'obj_id' => 
    array (
      'type' => 'table:order_objects@archive',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单商品对象ID',
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
      'comment' => '货品名称',
    ),
    'cost' => 
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '成本价',
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
    'amount' => 
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '合计',
    ),
    'weight' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '重量',
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
    'sendnum' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => '已发数量',
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '附加信息',
    ),
    'item_type' => 
    array (
      'type' => 'varchar(50)',
      'default' => 'product',
      'required' => true,
      'editable' => false,
      'comment' => '货品明细类型',
    ),
    'score' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '积分',
    ),
    'sell_code' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'default' => '',
      'comment' => '销售编码',
    ),
    'promotion_id' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'comment' => '优惠编码',
    ),
    'return_num' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
      'label' => '已退货量',
    ),
     'delete' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
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
    'idx_c_obj_id' =>
    array (
        'columns' =>
        array (
          0 => 'obj_id',
        ),
    ),
  ),
  'comment' => '归档订单商品明细表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);