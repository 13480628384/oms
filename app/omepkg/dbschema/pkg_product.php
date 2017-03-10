<?php
$db['pkg_product']=array (
  'columns' => 
  array (
  'product_id'=>
  array(
  'type'=>'mediumint(8)',
  'comment'=>'货品id',
  ),
    'bn' => 
    array (
      'type' => 'varchar(200)',
      'comment' => '货品bn',
      //'label' => '商品编号',
      //'width' => 120,
      //'searchtype' => 'head',
      //'editable' => false,
      //'filtertype' => 'yes',
      //'filterdefault' => true,
      //'in_list' => true,
      //'default_in_list' => true,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '',
      'comment' => '货品名',
      ),
  'goods_id'=>
  array(
  'type'=>'table:pkg_goods',
  'comment'=>'捆绑商品id',
  ),
//  'discount'=>
//  array(
//  'type'=>'decimal(5,3)',
//  'default' => NULL,
//  ),
  'pkgnum'=>
  array(
  'type'=>'mediumint(8)',
  'default' => 1,
  'comment' => '捆绑数量',
  ),
  ), 
  'comment' => '捆绑商品明细',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
  );
?>
