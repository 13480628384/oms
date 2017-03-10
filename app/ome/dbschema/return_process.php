<?php
$db['return_process']=array (
  'columns' => 
  array (
    'por_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'extra' => 'auto_increment',
    ),
    'reship_id' =>
    array (
      'type' => 'table:reship@ome',
     // 'required' => true,
      'editable' => false,
      'comment' => '退换货单号',
    ),
    'order_id' =>
    array (
      'type' => 'table:orders@ome',
     // 'required' => true,
      'editable' => false,
      'comment' => '订单id',
    ),
    'return_id' =>
    array (
      'type' => 'table:return_product@ome',
      //'required' => true,
      'editable' => false,
      'comment' => '主表id',
    ),
    'member_id' =>
    array (
      'type' => 'table:members@ome',
      'editable' => false,
      'comment' => '用户id',
    ),
    'title' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
       'label' => '售后服务标题',
         'in_list' => true,
       'default_in_list' => true,
    ),
    'content' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '内容',
    ),
    'add_time' =>
    array (
      'type' => 'time',
      'editable' => false,
       'label' => '售后处理时间',
         'in_list' => true,
       'default_in_list' => true,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ome',
      'editable' => false,
      'comment' => '店铺id',
    ),
    'last_modified' => 
    array (
      'type' => 'last_modify',
      'editable' => false,
      'comment' => '最后更新时间',
    ),
    'memo' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '备注',
    ),
    'branch_id' =>
    array (
      'type' => 'table:branch@ome',
      'editable' => false,
         'in_list' => true,
      'default_in_list' => true,
      'label' => '仓库',
    ),
    'attachment' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '附件',
    ),
    'comment' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '备注',
    ),
    'process_data' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '处理数据',
    ),
    'recieved' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'comment' => '是否已收货',
    ),
    'verify' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'comment' => '是否已质检',
    ),
  ), 
  'comment' => '收货服务中间表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);