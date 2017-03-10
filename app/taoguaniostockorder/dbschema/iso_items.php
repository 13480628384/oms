<?php
$db['iso_items']=array (
  'columns' =>
  array (
     'iso_items_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
    ),
    'iso_id' =>
    array (
      'type' => 'table:iso@taoguaniostockorder',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '出入库单信息主键ID',
    ),
    'iso_bn' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'label' => '出入库单号',
      'is_title' => true,
      'default_in_list'=>true,
	  'in_list'=>true,
      'width' => 125,
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),
     'product_id' =>
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'comment' => '货品ID',
    ),
    'product_name' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => '货品名称',
    ),
    'bn' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => '货号',
    ),
    'unit' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => '单位',
    ),
    'price' =>
    array (
      'type' => 'money',
      'required' => true,
      'editable' => false,
      'comment' => '单价',
    ),
    'nums' =>
    array (
      'type' => 'number',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => '出入库数量',
    ),
    'normal_num' =>
    array (
      'type' => 'number',
      'label' => '良品数量',
	  'default' => 0,
    ),
   'defective_num' =>
    array (
      'type' => 'number',
      'label' => '不良品数量',
	  'default' => 0,
    ),
  ),
  'index' =>
  array (
    'ind_iso_bn' =>
    array (
        'columns' =>
        array (
          0 => 'iso_bn',
        ),
    ),
    'ind_product_id' =>
    array (
        'columns' =>
        array (
          0 => 'product_id',
        ),
    ),
    'ind_bn' =>
    array (
        'columns' =>
        array (
          0 => 'bn',
        ),
    ),
  ),
  'comment' => '出入库单信息明细表',
  'engine' => 'innodb',
  'version' => '$Rev:  51996',
);