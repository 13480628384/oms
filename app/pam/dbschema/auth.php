<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['auth'] = array(
    'columns'=>array(
        'auth_id'=>array('type'=>'number','pkey'=>true,'extra' => 'auto_increment',),
        'account_id'=>array('type'=>'table:account','comment'=>'账户id'),
        'module_uid'=>array('type'=>'varchar(50)','comment'=>'模块id'),
        'module'=>array('type'=>'varchar(50)','comment'=>'模块'),
        'data'=>array('type'=>'text','comment'=>'数据'),
    ),
  'index' => array (
    'account_id' => array ('columns' => array ('module','account_id'),'prefix' => 'UNIQUE'),
    'module_uid' => array ('columns' => array ('module','module_uid'),'prefix' => 'UNIQUE'),
  ),
  'comment' => '授权数据表',
);
