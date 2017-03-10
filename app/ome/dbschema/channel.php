<?php
$db['channel']=array (
  'columns' => 
  array (
    'channel_id' => 
    array (
      'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false
    ),
    'channel_bn' => 
    array (
        'type' => 'varchar(20)',
        'required' => true,
        'label' => '应用编号',
        'editable' => false,
        
        'in_list' => true,
        'default_in_list' => true,
        'is_title' => true,
        'width' => '120',
    ),
    'channel_name' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'label' => '应用名称',
      'editable' => false,
      
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
      'width' => '120',
    ),
          
    'channel_type' =>
        array (
          'required' => false,
          'label' => '应用类型',
          'in_list' => true,
          'default_in_list' => true,
          'width' => '70',
           'type' => array (
               'crm' => 'crm',
               'wms' => 'wms',
               'shop' => 'shop',
        )
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
      'in_list' => false,
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
      'in_list' => false,
      'default_in_list' => true,
    ),
    'area' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '区域',
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
      'comment' => '邮编',
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
      'comment' => '手机',
    ),
    'tel' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'comment' => '电话',
    ),
    'filter_bn' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '过滤项',
    ),
    'bn_regular' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '规则',
    ),
    'express_remark' =>
    array (
      'type' => 'text',
      'editable' => false,
      'comment' => '有效期备注',
    ),
    'delivery_template' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '发货模板',
    ),
    'order_bland_template' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '订单模板',
    ),
    'node_id' =>
    array (
        'type' => 'varchar(32)',
        'editable' => false,
        'label' => '绑定状态',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'is_title' => true,
        'width' => '120',
    ),
    'node_type' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'comment' => '节点类型',
    ),
          
    'secret_key' =>
      array (
          'type' => 'varchar(100)',
          'editable' => false,
          'comment' => '密钥',
    ),
  'memo' =>
      array (
          'type' => 'varchar(255)',
          'editable' => false,
          'comment' => '备注',
      ),
          
          
          
    'api_version' =>
    array (
      'type' => 'char(6)',
      'editable' => false,
      'comment' => 'api 版本',
    ),    
    'addon' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => '扩展信息',
    ),
  ),
  'index' =>
  array (
    'ind_channel_bn' =>
    array (
        'columns' =>
        array (
          0 => 'channel_bn',
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
  'comment' => '应用绑定',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);