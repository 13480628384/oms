<?php
$db['tbgift_product']=array (
  'columns' =>
  array (
  'product_id'=>
  array(
  'type'=>'mediumint(8)',
  'comment'=>'货品ID',
  ),
    'bn' =>
    array (
      'type' => 'varchar(200)',
      'comment' => '货号',

    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '名称',
      ),
  'goods_id'=>
  array(
  'type'=>'table:tbgift_goods',
  'comment'=>'商品ID',
  ),
  ),
  'comment' => '捆绑商品',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
  );
?>
