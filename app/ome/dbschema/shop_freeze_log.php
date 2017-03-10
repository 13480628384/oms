<?php
/**
 * 店铺冻结增减明细日志_只用于测试
 *
 * @author wangbiao@shopex.cn
 * @version 0.1
 */

$db['shop_freeze_log']=array (
  'columns' =>
  array (
    'log_id' =>
    array (
        'type' => 'int unsigned',
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
        'order'=>10,
    ),
    'shop_id' => array(
        'type' => 'varchar(32)',
        'required' => true,
        'width' => 150,
        'in_list' => true,
        'default_in_list' => true,
        'label' => '店铺名称',
        'order'=>20,
    ),
    'product_id' =>
    array (
        'type' => 'int unsigned',
        'required' => true,
        'width' => 110,
        'searchtype' => 'nequal',
        'filtertype' => 'normal',
        'filterdefault' => true,
        'in_list' => true,
        'default_in_list' => true,
        'label' => '货号',
        'order'=>30,
    ),
    'type_id' =>
    array (
        'type' => 'tinyint(2)',
        'required' => true,
        'in_list'=>true,
        'label' => '冻结类型',
        'order'=>40,
    ),
    'nums' =>
    array (
        'type' => 'number',
        'label' => '冻结数量',
        'required' => true,
        'in_list'=>true,
        'order'=>50,
    ),
    'balance_nums' =>
    array (
        'type' => 'number',
        'label' => '库存结余',
        'required' => true,
        'in_list'=>true,
        'order'=>60,
    ),
    'operator' =>
    array (
      'type' => 'varchar(100)',
      'comment' => '操作人员',
      'in_list'=>true,
      'label' => '操作人员',
      'order'=>70,
    ),
    'create_time' =>
    array (
      'type' => 'time',
      'comment' => '出入库时间',
      'filtertype' => 'time',
      'filterdefault' => true,
      'default_in_list'=>true,
	  'in_list'=>true,
      'label' => '出入库时间',
      'order'=>80,
    ),
    'memo' =>
    array (
      'type' => 'text',
      'comment' => '备注',
      'label'=>'备注',
      'in_list'=>true,
      'order'=>90,
    ),
  ),
    'index' =>
    array (
        'ind_shop_product_id' =>
        array (
            'columns' =>
            array (
                0 => 'shop_id',
                1 => 'product_id',
            ),
        ),
    ),
  'comment' => '店铺冻结增减明细日志',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
