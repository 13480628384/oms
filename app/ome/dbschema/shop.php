<?php
$db['shop']=array (
  'columns' =>
  array (
    'shop_id' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'shop_bn' =>
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'comment' => '店铺编号',
    ),
    'name' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'label' => '店铺名称',
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
      'width' => '120',
    ),
    'shop_type' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '店铺类型',
      'in_list' => true,
      'default_in_list' => true,
      'width' => '70'
    ),
    'config' =>
    array (
      'type' => 'text',
      'editable' => false,
      'comment' => '配置',
    ),
    'crop_config' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => '公司配置',
    ),
    'last_download_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '上次下载订单时间(终端)',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'last_upload_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '上次上传订单时间(ome)',
      'in_list' => false,
      'default_in_list' => true,
    ),
    'active' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'in_list' => false,
      'default_in_list' => true,
      'editable' => false,
      'label' => '激活',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
    ),
    'last_store_sync_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '上次库存同步时间',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'area' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '地区',
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
      'comment' => '邮政编码',
    ),
    'addr' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '地址',
    ),
    'default_sender' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '默认发件人',
    ),
    'mobile' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'comment' => '手机号码',
    ),
    'tel' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'comment' => '电话号码',
    ),
    'filter_bn' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '是否过滤货品货号',
    ),
    'bn_regular' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '货号过滤正则',
    ),
    'express_remark' =>
    array (
      'type' => 'text',
      'editable' => false,
      'comment' => '快递单附言',
    ),
    'delivery_template' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '快递单模板文件名',
    ),
    'order_bland_template' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '配货单模板文件名',
    ),
    'node_id' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'comment' => '通信节点id',
    ),
    'node_type' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'comment' => '通信节点类型',
    ),
    'api_version' =>
    array (
      'type' => 'char(6)',
      'editable' => false,
      'comment' => 'api接口版本',
    ),
    'addon' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => '附加信息',
    ),
    'sw_code' =>
    array (
      'type' => 'varchar(32)',
      'comment' => '售达方编码',
      'required' => false,
    ),    'alipay_authorize' =>
    array (
      'type' => array(
         'true' => '已授权',
         'false' => '未授权'
      ),
      'default' => 'false',
      'editable' => false,
      'comment' => '是否授权',
    ),
    'business_type' =>
    array(
      'type' => array(
         'zx' => '直销',
         'fx' => '分销'
      ),
      'label' => '订单类型',
      'default' => 'zx',
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
    ),
    'tbbusiness_type' =>
    array(
      'type' => 'char(6)',
      'label' => '淘宝店铺类型',
      'default' => 'other',    
      'in_list' => true,
      'default_in_list' => true,      
      'editable' => false,
    ),
  ),  'index' =>
  array (
    'ind_shop_bn' =>
    array (
        'columns' =>
        array (
          0 => 'shop_bn',
        ),
        'prefix' => 'unique',
    ),
    'ind_node_id' =>
    array (
        'columns' =>
        array (
          0 => 'node_id',
        ),
    ),
  ),
  'comment' => '店铺表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);