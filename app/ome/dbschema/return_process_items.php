<?php
$db['return_process_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'extra' => 'auto_increment',
    ),
    'order_id' =>
    array (
      'type' => 'table:orders@ome',
      //'required' => true,
      'editable' => false,
      'comment' => '内部订单号',
    ),
    'reship_id' =>
    array (
      'type' => 'table:reship@ome',
     // 'required' => true,
      'editable' => false,
      'comment' => '退换货单号',
    ),
    'return_id' =>
    array (
      'type' => 'table:return_product@ome',
      'editable' => false,
      'comment' => '内部退换货申请号',
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
      'type' => 'varchar(50)',
      'editable' => false,
      'required' => true,
      'comment' => '货号',
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '商品名',
    ),
    'is_problem' =>
    array (
      'type' => 'bool',
      'editable' => false,
      'required' => true,
      'default' => 'false',
      'comment' => '是否是质量问题',
    ),
    'problem_type' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '问题类型',
    ),
    'memo' =>
    array (
      'type' => 'text',
      'editable' => false,
      'comment' => '质量问题备注',
    ),
    'op_id' =>
    array (
      'type' => 'table:account@pam',
      'editable' => false,
      'comment' => '操作人员id',
    ),
    'acttime' =>
    array (
      'type' => 'time',
      'editable' => false,
      'comment' => '操作时间',
    ),
    'branch_id' => 
    array (
      'type' => 'table:branch@ome',
      'editable' => false,
      'comment' => '发货点id',
    ),
    'need_money' =>
    array (
      'type' => 'money',
      'ediatble' => false,
      'comment' => '应付金额',
    ),
    'other' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '其他',
    ),
    'store_type' =>
    array (
      'type' => 
      array (
        0 => '新仓',
        1 => '残仓',
        2 => '报废',
      ),
      'editable' => false,
      'default' => '0',
      'required' => true,
      'comment' => '入库类型',
    ),
    'is_check' =>
    array (
      'type' => 'bool',
      'editable' => false,
      'required' => true,
      'default' => 'false',
      'comment' => '是否质检',
    ),
    'status' =>
    array (
      'type' => 
      array (
        0 => '默认',
        1 => '退',
        2 => '换',
        3 => '拒绝',
      ),
      'editable' => false,
      'required' => true,
      'default' => '0',
      'comment' => '最终处理状态',
    ),
    'problem_belong' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '问题归属',
    ),
    'por_id' =>
    array (
      'type' => 'table:return_process@ome',
      'editable' => true,
      'comment' => '中间表sdb_return_product_pro 关联id',
    ),
    'num' =>
    array (
      'type' => 'number',
      'editable' => false,
      'default' => 1,
      'comment' => '货品数量(默认为1)',
    ),
  ), 
  'comment' => '收货服务中间表明细',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);