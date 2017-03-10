<?php
$db['dly_corp']=array (
  'columns' =>
  array (
    'corp_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'extra' => 'auto_increment',
    ),
    'branch_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '仓库ID',
    ),
    'all_branch' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'true',
      'editable' => false,
      'comment' => '是否所有仓库',
    ),
    'type' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
      'comment' => '类型',
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'label' => '物流公司名称',
      'in_list' => true,
      'default_in_list' => true,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'is_title' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
    ),
    'website' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '物流公司网址',
    ),
    'request_url' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '查询url',
    ),
    'daily_process' =>
    array (
      'type' => 'number',
      'editable' => false,
      'default' => 100,
      'comment' => '日处理能力',
    ),
    'firstunit' =>
    array (
      'type' => 'number',
      'editable' => false,
      'default' => 0,
      'required' => true,
      'comment' => '首重重量',
    ),
    'continueunit' =>
    array (
      'type' => 'number',
      'editable' => false,
      'default' => 0,
      'required' => true,
      'comment' => '续重单位',
    ),
    'protect' =>
    array (
      'type' => 'bool',
      'editable' => false,
      'required' => true,
      'default' => 'false',
      'comment' => '是否保价',
    ),
    'protect_rate' =>
    array (
      'type' => 'decimal(6,3)',
      'editable' => false,
      'comment' => '费率',
    ),
    'minprice' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '最低保价费',
    ),
    'setting' =>
    array (
      'type' =>
      array (
        0 => '指定地区费用',
        1 => '统一设置',
      ),
      'editable' => false,
      'required' => true,
      'default' => '1',
      'label' => '地区费用类型',
      'in_list' => true,
      'default_in_list' => true,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),
    'firstprice' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '首重费用',
    ),
    'continueprice' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '续重费用',
    ),
    'dt_expressions' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '公式',
    ),
    'dt_useexp' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '是否使用公式',
    ),
    'area_fee_conf' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '指定配送地区和费用的详细信息，序列化字段',
    ),
    'is_cod' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '是否货到付款',
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ome',
      'editable' => false,
      'label' => '适用店铺',
      'width' => 130,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'channel_id' =>
    array (
      'type' => 'table:channel@logisticsmanager',
      'editable' => false,
      'comment' => '来源主键',
      'label' => '面单来源',
      'width' => 130,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'tmpl_type' =>
    array (
      'type' => array(
        'normal' => '普通面单',
        'electron' => '电子面单',
      ),
      'editable' => false,
      'required' => true,
      'default' => 'normal',
      'label' => '快递模板类型',
      'in_list' => true,
      'default_in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),
    'prt_tmpl_id' =>
    array (
      'type' => 'table:print_tmpl@wms',
      'default' => '0',
      'editable' => false,
      'comment' => '快递单模板',
    ),
    'weight' =>
    array (
      'type' => 'number',
      'edtiable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => '权重',
    ),
  ),
  'comment' => '物流公司配送信息',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);