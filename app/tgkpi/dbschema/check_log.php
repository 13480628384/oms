<?php
$db['check_log']=array (
  'columns' =>
  array (
  'l_id' =>
    array (
        'type' => 'number',
        'required' => true,
        'editable' => false,
        'pkey' => true,
        'label' => 'ID',
        'extra' => 'auto_increment',
    ),
    'delivery_id' =>
    array (
      'type' => 'table:delivery@ome',
      'required' => true,
      'editable' => false,
      'comment' => '发货单ID',
    ),
    'old_op_id' =>
    array (
      'type' => 'table:account@pam',
      'required' => true,
      'editable' => false,
      'comment' => '上次操作员ID',
    ),
    'new_op_id' =>
    array (
      'type' => 'table:account@pam',
      'required' => true,
      'editable' => false,
      'comment' => '本次操作员ID',
    ),
	'addtime' =>
    array (
        'type' => 'time',
        'required' => true,
        'editable' => false,
        'comment' => '添加时间',
    ),
  ),
  'comment' => '拣货校验日志表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);