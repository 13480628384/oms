<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['log'] = array(
    'columns'=>array(
        'event_id'=>array('type'=>'number','pkey'=>true,'extra' => 'auto_increment',),
        'event_time'=>array('type'=>'varchar(50)','comment'=>'时间'),
        'event_data'=>array('type'=>'varchar(500)','comment'=>'数据'),
        'event_type'=>array('type'=>'text','comment'=>'类型'),
    ),
    'comment'=>'授权日志',
);
