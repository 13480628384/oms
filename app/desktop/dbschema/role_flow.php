<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['role_flow']=array (
  'columns' => 
  array (
    'role_id' => array (
      'type' => 'table:roles',
      'required' => true,
      'pkey' => true,
      'comment' => '角色id',
    ),
    'flow_id' => array (
      'type' => 'table:flow',
      'required' => true,
      'pkey' => true,
      'comment' => '信息id',
    ),
  ),
  'comment' => '角色和信息关联表',
  'version' => '$Rev$',
);
