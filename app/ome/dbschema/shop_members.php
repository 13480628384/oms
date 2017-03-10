<?php
$db['shop_members']=array (
  'columns' => 
  array (
    'shop_id' => 
    array (
      'type' => 'table:shop@ome',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'shop_member_id' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'table:members@ome',
      'required' => true,
      'editable' => false,
      'comment' => 'ome 会员id',
    ),
  ),
  'comment' => '前端店铺会员和ome 会员对应关系表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
