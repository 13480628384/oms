<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['hasrole']=array (
  'columns' => 
  array (
    'user_id' => 
    array (
      'type' => 'table:users',
      'required' => true,
      'pkey' => true,
      'comment'=> '后台用户ID',
    ),
    'role_id' => 
    array (
      'type' => 'table:roles',
      'required' => true,
      'pkey' => true,
      'comment' => '角色ID',
    ),
  ),
  'comment' => '后台权限角色和用户关联表',
  'version' => '$Rev: 40654 $',
);

