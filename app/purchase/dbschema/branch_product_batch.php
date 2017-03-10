<?php
$db['branch_product_batch']=array (
  'columns' => 
  array (
    'product_id' => 
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'supplier_id' => 
    array (
      'type' => 'table:supplier',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'eo_id' => 
    array (
      'type' => 'table:eo',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'eo_bn' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'editable' => false,
      'comment' => '入库单编号',
    ),
    'branch_id' => 
    array (
      'type' => 'table:branch@ome',
      'required' => true,
      'editable' => false,
      'comment' => '仓库ID',
    ),
    'purchase_price' => 
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '采购价格',
    ),
    'purchase_time' => 
    array (
      'type' => 'time',
      'editable' => false,
      'comment' => '采购时间',
    ),
    'store' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '库存数量',
    ),
    'in_num' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '入库数量',
    ),
    'out_num' => 
    array (
      'type' => 'number',
      'editable' => false,
      'default' => 0,
      'comment' => '出库数量',
    ),
  ),
  'comment' => '货品价格历史记录',
  'engine' => 'innodb',
  'version' => '$Rev: 44513 $',
);
