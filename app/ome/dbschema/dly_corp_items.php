<?php
$db['dly_corp_items']=array (
  'columns' =>
  array (

    'corp_id' =>
    array (
      'type' => 'table:dly_corp@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'pkey' => true,
    ),
    'region_id' =>
    array (
      'type' => 'table:regions@eccommon',
      'required' => true,
      'default' => '0',
      'editable' => false,
      'pkey' => true,
    ),

   'areagroupbakid' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => '0',
      'editable' => false,
      'comment' => '区域组ID',

    ),

    'firstunit' =>
    array (
      'type' => 'number',
      'editable' => false,
      'default' => 0,
      'required' => true,
      'comment' => '重量设置',
    ),
    'continueunit' =>
    array (
      'type' => 'number',
      'editable' => false,
      'default' => 0,
      'required' => true,
      'comment' => '续重重量',
    ),
    'firstprice' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '重量价格',
    ),
    'continueprice' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '续重价格',
    ),
    'dt_expressions' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '快递费用',
    ),
    'dt_useexp' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '是否使用公式',
    ),
    ),
  'comment' => '物流公司明细',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);