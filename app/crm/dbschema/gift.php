<?php
$db['gift']=array (
  'columns' =>
  array (
    'gift_id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'label' => '赠品ID',
            'width' => 110,
            'hidden' => true,
            'editable' => false,
        ),
    'product_id' =>
        array (
            'type' => 'varchar(200)',
            'comment' => '货品ID',
            'required' => true,
            'editable' => false,
        ),
    'gift_bn' =>
        array (  
          'type' => 'varchar(200)',
          'required' => true,
          'label' => '货号',
          'comment' => '赠品货号',
          'searchtype' => 'head',
          'editable' => false,
          'filtertype' => 'yes',
          'filterdefault' => true,
           'in_list' => true,
           'default_in_list' => true,
           'is_title' => true,
           'width' => '120',
        ),
    'gift_name' =>
        array (
            'type' => 'varchar(200)',
            'required' => true,
            'label' => '货品名称',
            'comment' => '赠品名称',
            'default_in_list' => true,
            'width' => 260,
            'searchtype' => 'head',
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefalut' => true,
            'in_list' => true,
        ),
    'spec_info' =>
        array (
              'type' => 'varchar(255)',
              'default' => '',
              'label' => '规格',
              'comment' => '赠品规格',
              'default_in_list' => true,
              'width' => 260,
              'editable' => false,
              'in_list' => true,
        ),
      'gift_num' =>
          array(
              'type' => 'number',
              'in_list' => true,
              'default' => 0,
              'default_in_list' => true,
              'required' => false,
              'label' => '赠品库存',
              'filtertype'=>false,
              'searchtype'=>false,
              'width' => 80,
              'order' => 40,
          ),
      'gift_price' =>
          array(
              'type' => 'money',
              'in_list' => true,
              'default' => 0,
              'default_in_list' => true,
              'required' => false,
              'label' => '成本价',
              'width' => 80,
              'order' => 45,
          ),
      'send_num' =>
          array(
              'type' => 'number',
              'in_list' => true,
              'default' => 0,
              'default_in_list' => true,
              'required' => false,
              'label' => '已赠送数量',
              'filtertype'=>false,
              'searchtype'=>false,
              'width' => 80,
              'order' => 45,
          ),
      'is_del' =>
          array(
              'type' => array(
                  '0' => '启用',
                  '1' => '禁用',
              ),
              'default' => '0',
              'in_list' => true,
              'default_in_list' => true,
              'required' => false,
              'label' => '是否启用',
              'filtertype'=>false,
              'searchtype'=>false,
              'width' => 80,
              'order' => 50,
          ),
      'create_time' =>
          array(
              'type' => 'time',
              'in_list' => true,
              'default_in_list' => false,
              'required' => false,
              'label' => '创建时间',
              'filtertype'=>false,
              'searchtype'=>false,
              'width' => 150,
              'order' => 60,
          ),
      'update_time' =>
          array(
              'type' => 'time',
              'in_list' => true,
              'default_in_list' => true,
              'required' => false,
              'label' => '更新日期',
              'filtertype'=>false,
              'searchtype' => false,
              'width' => 150,
              'order' => 70,
          ),
      'shop_id' =>
          array (
              'type' => 'table:shop@ome',
              'required' => false,
              'editable' => false,
              'label' => '来源店铺',
              'in_list' => true,
              'default_in_list' => true,
              'width' => 150,
              'order' => 80
          ),
  ),
  'index' =>
    array (
      'ind_product_bn' =>array ('columns' =>array (0 => 'gift_bn')),
      'ind_product_name' =>array ('columns' =>array (0 => 'gift_name')),
    ),
  'comment' => '赠品表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);