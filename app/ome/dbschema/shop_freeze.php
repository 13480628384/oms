<?php
/**
 * 店铺冻结数据结构
 *
 * @author wangbiao@shopex.cn
 * @version 0.1
 */

$db['shop_freeze']=array (
  'columns' =>
  array (
    'product_id' =>
    array (
        'type' => 'int unsigned',
        'label' => '货号',
        'required' => true,
        'width' => 110,
        'searchtype' => 'nequal',
        'filtertype' => 'normal',
        'filterdefault' => true,
        'in_list' => true,
        'default_in_list' => true,
        'order'=>10,
    ),
    'shop_id' => array(
        'type' => 'varchar(32)',
        'label' => '店铺名称',
        'required' => true,
        'in_list'=>true,
        'default_in_list' => true,
        'width' => 150,
        'order'=>20,
    ),
    'shop_freeze' => array(
        'type' => 'number',
        'label' => '店铺冻结数量',
        'default' => 0,
        'required' => true,
        'width' => 110,
        'in_list'=>true,
        'editable' => true,
        'order'=>30,
    ),
    'last_modified' =>
    array (
        'type' => 'last_modify',
        'label' => '最后修改日期',
        'in_list'=>true,
        'default_in_list' => true,
        'order'=>40,
    ),
  ),
    'index' =>
    array (
        'ind_shop_product_id' =>
        array (
            'columns' =>
            array (
                0 => 'product_id',
                1 => 'shop_id',
            ),
        ),
    ),
  'comment' => '店铺冻结表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
