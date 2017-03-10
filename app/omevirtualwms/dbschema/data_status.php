<?php
/*

*/

$db['data_status'] = array(

    'columns' => array(
        'id' => array(
            'type' => 'mediumint unsigned',
            'required' => true,
            'extra' => 'auto_increment',
            'label' => 'ID',
            'pkey' => true,
            'editable' => false,
        ),
        'bn' => array(
            'type' => 'varchar(100)',
            'required' => true,
            'label' => '单号',
            'editable' => false,
        ),
        'type' => array(
            'type' => array(
                    'stockin' => app::get('omevirtualwms')->_('入库单'),
                    'stockout' => app::get('omevirtualwms')->_('出库单'),
                    'delivery' => app::get('omevirtualwms')->_('发货单'),
                    'reship' => app::get('omevirtualwms')->_('退货单'),
                     'stockdump' => app::get('omevirtualwms')->_('转储单')
                ),
            'required' => true,
            'label' => '类型',
            'editable' => false,
        ),
        'status' => array(
            'type' => 'varchar(100)',
            'required' => true,
            'label' => '单据状态',
            'editable' => false,
        ),
        'create_time' => array(
            'type' => 'time',
            'required' => true,
            'label' => '创建时间',
            'editable' => false,
        )
    ),
    'index' => array(
        'idx_bn' => array(
            'columns' => array('bn','type'),
        ),
        
        'idx_type' => array(
            'columns' => array('type')
        ),
        'idx_create_time' => array(
            'columns' => array('create_time')
        )
    ),
    'comment' => '数据状态',
    'engine' => 'Innodb',
    'version' => '$Rev: $'

);
