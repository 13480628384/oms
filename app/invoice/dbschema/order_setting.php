<?php
$db['order_setting']=array(
    'columns' =>
        array(
            'sid' => array(
                'type' => 'smallint(6)',
                'required' => true,
                'pkey' => true,
                'label' => '编号',
                'in_list' => true,
                'default_in_list' => true,
                'width' => 60,
                'hidden' => true,
                'order' => 10,
            ),
            'title' => array(
                'type' => 'serialize',
                'required' => true,
                'label' => '发票内容',
            ),
            'tax_rate' => array(
                'type' => 'tinyint(2)',
                'default' => '0',
                'required' => true,
                'label' => '税率',
            ),
            'tax_no' => array(
                'type' => 'varchar(32)',
                'label' => '销方税号',
            ),
            'bank' => array(
                'type' => 'varchar(32)',
                'label' => '销方开户银行',
                'in_list' => true,
                'default_in_list' => true,
                'order' => 30,
            ),
            'bank_no' => array(
                'type' => 'char(32)',
                'label' => '销方银行账号',
                'in_list' => true,
                'default_in_list' => true,
            ),
            'telphone' => array(
                'type' => 'char(32)',
                'label' => '销方联系电话',
                'in_list' => true,
                'default_in_list' => true,
            ),
            'address' => array(
                'type' => 'varchar(255)',
                'label' => '销方联系地址',
            ),
            'dateline' => array(
                'type' => 'time',
                'default' => '0',
                'label' => '添加日期',
                'width' => 130,
                'in_list' => true,
                'default_in_list' => true,
                'filtertype' => 'time',
                'filterdefault' => true,
            ),
    ),
    'comment' => '订单发票配置表',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);