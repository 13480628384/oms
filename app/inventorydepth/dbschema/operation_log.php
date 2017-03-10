<?php
$db['operation_log'] = array(
    'columns' => array(
        'log_id' => array(
            'type'     => 'int unsigned',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'label'    => 'ID',
        ),
        'obj_type' => array(
            'type'     => 'varchar(30)',
            'required' => true,
            'comment' => '对象类型 sku item shop 分别是货品、商品、店铺操作',
        ),
        'obj_id' => array(
            'type'     => 'varchar(32)',
            'required' => true,
            'comment' => '对象ID 货品、商品或店铺对象ID',
        ),
        'memo' => array(
            'type'     => 'longtext',
            'required' => true,
            'comment' => '备注描述',
        ),
        'create_time' => array(
            'type'     => 'time',
            'required' => true,
            'comment' => '添加时间',
        ),
        'op_id' => array(
            'type'     => 'mediumint unsigned',
            'required' => true,
            'comment' => '操作员ID',
        ),
        'op_name' => array(
            'type'     => 'varchar(100)',
            'required' => true,
            'comment' => '操作员名称',
        ),
        'operation' => array(
            'type' => 'varchar(50)',
            'required' => true,
            'comment' => '操作动作类型 stockup发布库存 stockset开启或关闭库存回写 approve 上下架',
        ),
    ),
    'index' => array(
        'idx_obj' => array('columns' => array('obj_id','obj_type')),
    ),
    'comment' => '手工发布库存回写日志表',
); 