<?php
$db['delivery_order']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'table:orders@ome',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'delivery_id' => 
    array (
      'type' => 'table:delivery@ome',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
  ), 
  'index' =>
  array (
    'ind_delivery_id' =>
    array (
        'columns' =>
        array (
          0 => 'delivery_id',
        ),
    ),
    'ind_order_id' =>
    array (
        'columns' =>
        array (
          0 => 'order_id',
        ),
    ),
  ),
  'comment' => '发货单和订单多对多的关系表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);