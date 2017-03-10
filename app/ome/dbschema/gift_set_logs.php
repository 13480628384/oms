<?php
/**
 * User: jintao
 * Date: 2016/2/2
 * Time: 16:02
 */
$db['gift_set_logs']=array (
    'columns' => array (
        'id' => array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'order' => 10
        ),
        'op_user' => array(
            'type' => 'varchar(50)',
            'label'=>'操作人',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'order' => 50,
        ),
        'set_type' => array(
            'type' => array(
                'include' => '叠加',
                'exclude' => '排他',
            ),
            'label'=>'赠品设置',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 100,
        ),
        'create_time' => array(
            'type' => 'time',
            'label'=>'操作时间',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 150,
        ),
    ),
    'index' => array (
        'ind_op_user' => array (
            'columns' => array (
                'op_user',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);