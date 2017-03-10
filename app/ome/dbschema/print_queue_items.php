<?php

/**
 * 定义打印批次表项表结构
 *
 * @author hzjsq@msn.com
 * @version 0.1b
 */
$db['print_queue_items'] = array(
    'columns' =>
    array(
        'ident' =>
        array(
            'type' => 'varchar(64)',
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '批次号',
            'comment' => '本次打印的批次号',
        ),
        'delivery_id' => 
        array (
          'type' => 'table:delivery@ome',
          'required' => true,
          'default' => 0,
          'editable' => false,
          'comment' => '发货单id',
        ),
       'ident_dly' =>
        array(
            'type' => 'varchar(64)',
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '批次号序列',
            'comment' => '本次打印的批次号对应的发货单',
        ),
    ),
    'comment' => '打印批次表字表结构',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);