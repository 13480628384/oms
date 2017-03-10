<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$db['analysis']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'service' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'comment' => '报表服务名',
    ),
    'interval' => 
    array (
      'type' => 
          array (
            'hour' => 'hour',
            'day' => 'day',
          ),
      'required' => true,
      'comment' => '间隔时间',
    ),
    'modify' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'comment' => '修改时间',
    ),
  ),
  'comment' => '电商商务通用应用分析',
  'engine' => 'innodb',
  'ignore_cache' => true,
);
