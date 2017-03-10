<?php
$db['check_memo']=array (
    'columns'=>
    array(
        'm_id' =>
            array (
                'type' => 'number',
                'required' => true,
                'editable' => false,
                'pkey' => true,
                'label' => 'ID',
                'extra' => 'auto_increment',
            ),
        'check_op_id' =>
            array (
                'type' => 'table:account@pam',
                'required' => true,
                'editable' => false,
                'default_in_list' => false,
                'in_list' => false,
                'comment' => '校验员ID',
            ),
    	'check_op_name' =>
            array (
                'type' => 'varchar(30)',
                'editable' => false,
                'default_in_list' => true,
                'in_list' => true,
                'label' => '校验员',
            ),
        'delivery_id' =>
            array (
                'type' => 'table:delivery@ome',
                'required'=> true,
                'default_in_list' => true,
                'in_list' => true,
                'label' => '发货单编号',
                'order' => 40,
            ),
        'reason_id' =>
            array (
                'type' => 'table:reason@tgkpi',
                'editable' => false,
                'default_in_list' => false,
                'in_list' => false,
                'comment' => '原因ID',
            ),
        'memo' =>
            array (
                'type' => 'text',
                'editable' => false,
                'comment' => '备注',
            ),
    	'addtime' =>
            array (
                'type' => 'time',
                'required'=> false,
                'default_in_list' => true,
                'in_list' => true,
                'filterdefault' => true,
                'filtertype' => 'has',
                'label' => '添加时间',
                'order' => 50,
            ),
    ),
    'comment' => '拣货校验表',
    'engine' => 'innodb',
    'version' => '$Rev:121321',
);
