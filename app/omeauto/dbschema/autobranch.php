<?php
$db['autobranch']=array (
  'columns' => 
  array (
    'tid' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      ),
    'bid' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
    ),
   'weight'=>array(
   'type' => 'tinyint',
   'default'=>0,
   'comment'=>'重量',

   ),
   'is_default' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '是否默认',
    ),
  ),
  
  'comment' => '自动审单仓库规则',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);