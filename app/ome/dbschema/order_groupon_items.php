<?php

/**
 *团购订单批次和订单号的关联 
 *
 * @author shiyao744@sohu.com
 * @version 0.1b 
 */
$db['order_groupon_items'] = array(
    'columns' =>
    array(
        'order_groupon_id' =>
        array(
            'type' => 'number',
            'required' => true,
        	'default' => 0,
            'editable' => false,
            'comment' => '批量导入主键',
        ),
         'order_id' => 
	    array (
	      'type' => 'table:orders@ome',
	      'required' => true,
	      'default' => 0,
	      'editable' => false,
	      'comment' => '订单ID',
	    ),
    ),
    'comment' => '团购订单批次和订单号的关联',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);