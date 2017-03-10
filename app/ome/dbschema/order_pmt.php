<?php
$db['order_pmt']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' =>
    array (
      'type' => 'table:orders@ome',
      'required' => true,
      'editable' => false,
      'comment' => '订单id',
    ),
    'pmt_amount' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '优惠金额',
    ),
    'pmt_memo' =>
    array (
      'type' => 'longtext',
      'edtiable' => false,
      'comment' => '优惠备注',
    ),
    'pmt_describe' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '优惠描述',
    ),
  ), 
  'comment' => '订单促销规则',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);