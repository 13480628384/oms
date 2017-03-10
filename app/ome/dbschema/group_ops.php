<?php
$db['group_ops']=array (
  'columns' => 
  array (
    'group_id' => 
    array (
      'type' => 'table:groups@ome',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'op_id' => 
    array (
      'type' => 'table:account@pam',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
  ), 
  'comment' => '小组成员表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);