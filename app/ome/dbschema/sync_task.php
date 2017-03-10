<?php
$db['sync_task']=array (
  'columns' => 
  array (
    'sync_task_id' => 
    array (
      'type' => 'number',
      'extra' => 'auto_increment',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'params' =>
    array (
      'type' => 'text',
      'editable' => false,
      'comment' => '参数',
    ),
    'action' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => '动作',
    ),
    'retry' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '重试次数',
    ),
  ),
  'comment' => '同步任务',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);