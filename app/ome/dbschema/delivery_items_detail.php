<?php
$db['delivery_items_detail']=array (
  'columns' => 
  array (
    'item_detail_id' => 
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
      'editable' => false,
      'comment' => '发货单id',
    ),
    'delivery_item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'editable' => false,
      'comment' => '发货单明细id',
    ),
    'order_id' => 
    array (
      'type' => 'table:orders@ome',
      'required' => true,
      'editable' => false,
      'comment' => '订单id',
    ),
    'order_item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'editable' => false,
      'comment' => '订单明细id',
    ),
    'order_obj_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'editable' => false,
      'comment' => '订单obj id',
    ),
    'item_type' => 
    array (
      'type' => 
      array (
        'product' => '商品',
        'gift' => '赠品',
        'pkg' => '捆绑商品',
        'adjunct' => '配件',
      ),
      'default' => 'product',
      'required' => true,
      'editable' => false,
      'comment' => '明细类型',
    ),
    'product_id' => 
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'editable' => false,
      'comment' => '货品id',
    ),
    'bn' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'is_title' => true,
      'comment' => '货品编号',
    ),
    'number' => 
    array (
      'type' => 'number',
      'required' => true,
      'editable' => false,
      'comment' => '数量',
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
      'comment' => '小计',
    ),
  ),
  'index' =>
  array (
    'ind_delivery_item_id' =>
    array (
        'columns' =>
        array (
          0 => 'delivery_item_id',
        ),
    ),
    'ind_order_item_id' =>
    array (
        'columns' =>
        array (
          0 => 'order_item_id',
        ),
    ),
    'ind_order_obj_id' =>
    array (
        'columns' =>
        array (
          0 => 'order_obj_id',
        ),
    ),
   ),
  'comment' => '发货单明细详情',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);