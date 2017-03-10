<?php
$db['concurrent']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'type' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'pkey' => true,
      'required' => true,
    ),
    'current_time' =>
    array (
      'type' => 'int unsigned',
      'comment' => '验证时间',
    ),
  ),
  
  'index' =>
  array (
    'ind_current_time' =>
    array (
        'columns' =>
        array (
          0 => 'current_time',
        ),
    ),
  ),
  'comment' => '防止重复记录表',
  'engine' => 'innodb',
  'version' => '$Rev: 41996 $',
);