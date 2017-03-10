<?php
$db['monthly_report']=array (
  'columns' => 
  array (
    'monthly_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'monthly_date' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'comment'=>'财务月份',
      'label'=>'财务月份',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>'10',
    ),
    'begin_time' => 
    array (
      'type' => 'time',
      'comment'=>'起始时间',
      'label'=>'起始时间',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>'20',
    ),
    'end_time' =>
    array (
      'type' => 'time',
      'required' => true,
      'comment'=>'结束时间',
      'label'=>'结束时间',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>'30',
    ),
    'status' => 
    array (
      'type' => 'int',
      'required' => true,
      'comment' => '未启用(0) 未结算(1) 已结算(2)',
      'default'=>'1',
      'label'=>'结账状态',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>'40',
    ),
  ),
  'index'=>array(
    'ind_monthly_date' =>
    array (
        'columns' =>
        array (
          0 => 'monthly_date',
        ),
    ),
    'ind_begin_time' =>
    array (
        'columns' =>
        array (
          0 => 'begin_time',
        ),
    ),
    'ind_end_time' =>
    array (
        'columns' =>
        array (
          0 => 'end_time',
        ),
    ),
    'ind_status' =>
    array (
        'columns' =>
        array (
          0 => 'status',
        ),
    ),
  ),
  'comment' => '月结账单',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);