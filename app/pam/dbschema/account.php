<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['account'] = array(
    'columns'=>array(
        'account_id'=>array('type'=>'number','pkey'=>true,'extra' => 'auto_increment',),
        'account_type'=>array('type'=>'varchar(30)','comment'=>'账户类型'),
        'login_name'=>array(
            'type'=>'varchar(100)',
            'is_title'=>true,
            'required' => true,
            'in_list'=>true,
            'default_in_list'=>true,
            'label'=>'用户名',
        ),
        'login_password'=>array('type'=>'varchar(32)','required' => true,'comment'=>'登录密码'),
        'disabled'=>array('type'=>'bool','default'=>'false'),
        'createtime'=>array('type'=>'time','comment'=>'创建时间'),
    ),
  'index' => array (
    'account' => array ('columns' => array ('account_type','login_name'),'prefix' => 'UNIQUE'),
  ),
  'engine' => 'innodb',
  'comment' => '授权用户表',
);
