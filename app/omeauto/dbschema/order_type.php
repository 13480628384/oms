<?php
$db['order_type'] = array(
    'columns' =>
    array(
        'tid' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'oid' =>
        array(
            'type' => 'number',
            'required' => true,
            'default' => '0',
            'editable' => false,
            'comment' => '规则ID',
        ),
        'did' =>
        array(
            'type' => 'number',
            'required' => true,
            'default' => '0',
            'editable' => false,
            'comment' => '订单分组',
        ),
        'bid' =>
        array(
            'type' => 'number',
            'required' => true,
            'default' => '0',  
            'editable' => false,
            'comment' => '仓库ID',
        ),
        'name' =>
        array(
            'type' => 'varchar(200)',
            'required' => true,
            'editable' => false,
            'is_title' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 130,
            'label' => '规则名称',
        ),
        'config' =>
        array(
            'type' => 'serialize',
            'editable' => false,
            'comment' => '规则配置',
        ),
        'memo' =>
        array(
            'type' => 'text',
            'editable' => false,
            'comment' => '备注',
        ),
        'weight' =>
        array(
            'type' => 'number',
            'required' => true,
            'editable' => false,
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'label' => '权重',
        ),
        'delivery_group' =>
        array(
            'type' => 'bool',
            'required' => true,
            'editable' => false,
            'default' => 'false',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '是否发货单分组',
        ),
        'group_type' =>
        array(
            'type' => array('order'=>'订单','sms'=>'短信','branch'=>'仓库'),
            'required' => true,
            'editable' => false,
            'default' => 'order',
            'width' => 100,
            'in_list' => false,
            'default_in_list' => false,
            'label' => '分组类型',
        ),
        'disabled' =>
        array(
            'type' => 'bool',
            'required' => true,
            'editable' => false,
            'default' => 'false',
        ),
    ),
    'comment' => '分组规则设置',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);