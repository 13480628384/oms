<?php
/**
 * User: jintao
 * Date: 2016/2/1
 * Time: 14:55
 */

$db['snapshot']=array (
    'columns' => array (
        'id' => array(
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'type' => array(
            'type' => array(
                1 => '赠品规则',
                2 => '分组规则',
                9 => '其它',
            ),
            'label'=>'类型',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'task_id' => array(
            'type' => 'int unsigned',
            'label'=>'业务ID',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'title' => array(
            'type' => 'varchar(100)',
            'label'=>'标题',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'content' => array(
            'type' => 'varchar(1000)',
            'label'=>'内容',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'op_user' => array(
            'type' => 'varchar(50)',
            'label'=>'操作人',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'create_time' => array(
            'type' => 'datetime',
            'label'=>'操作时间',
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' => array (
        'ind_task_id' => array (
            'columns' => array (
                'task_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);