<?php
$db['order_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' => 
    array (
      'type' => 'table:orders@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单号',
    ),
    'obj_id' => 
    array (
      'type' => 'table:order_objects@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单对象id',
    ),
    'shop_goods_id' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '前端店铺的商品id',
    ),
    'product_id' => 
    array (
      'type' => 'table:products@ome',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '货品号',
    ),
    'shop_product_id' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'required' => true,
      'default' => 0,
      'comment' => '前端店铺的货品id',
    ),    
    'bn' => 
    array (
      'type' => 'varchar(40)',
      'editable' => false,
      'is_title' => true,
      'comment' => '货品编号',
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => '货品名称',
    ),
    'cost' => 
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '成本价',
    ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '购买单价',
    ),
    'pmt_price' => 
    array (
      'type' => 'money',
      'default' => '0',
    'editable' => false,
    'comment' => '商品优惠金额',
    ),
    'sale_price' => 
    array (
      'type' => 'money',
      'default' => '0',
        'editable' => false,
        'comment' => '销售单价(销售总价)',
    ),
    'amount' => 
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '商品小计 = 单价 x 数量',
    ),
    'weight' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '重量',
    ),
    'nums' => 
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'sdfpath' => 'quantity',
      'comment' => '商品数量',
    ),
    'sendnum' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => '已发货数量',
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => '附加信息',
    ),
    'item_type' => 
    array (
      'type' => 'varchar(50)',
      'default' => 'product',
      'required' => true,
      'editable' => false,
      'comment' => '标识是什么类型',
    ),
    'score' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '积分',
    ),
    'sell_code' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'default' => '',
      'comment' => '销售编码',
    ),
    'promotion_id' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,

      'comment' => '优惠编码',
    ),
    'return_num' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
      'label' => '已退货量',
    ),
    'delete' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
      'comment' => '是否删除',
    ),
    
  ), 
  'comment' => '订单商品表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);