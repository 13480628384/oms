<?php
$db['goods_type']=array (
  'columns' => 
  array (
    'type_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => '类型序号',
      'width' => 110,
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => '类型名称',
      'is_title' => true,
      'width' => 150,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filterdefault' => true,
      'searchtype' => 'has',
      'filtertype' => 'normal',
    ),
    'alias' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'label' => '类别别名',
      'width' => 150,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'is_physical' => 
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'label' => '实体商品',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filterdefault' => true,
      'searchtype' => 'has',
      'filtertype' => 'yes',
    ),
    'schema_id' => 
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => 'custom',
      'hidden' => 1,
      'width' => 110,
      'editable' => false,
      'comment' => '商品插件',
    ),
    'props' => 
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => '商品属性序列化存储',
    ),
    'setting' => 
    array (
      'type' => 'serialize',
      'comment' => '类型设置',
      'width' => 110,
      'editable' => false,
      'label' => '类型设置',
      'in_list' => true,
    ),
    'minfo' => 
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => '用户购买时所需输入信息的字段定义序列化数组方式',
    ),
    'params' => 
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => '参数表结构',
    ),
    'dly_func' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '是否包含发货函数',
    ),
    'ret_func' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '是否包含退货函数',
    ),
    'reship' => 
    array (
      'default' => 'normal',
      'required' => true,
      'type' => 
      array (
        'disabled' => '不支持退货',
        'func' => '通过函数退货',
        'normal' => '物流退货',
        'mixed' => '物流退货+函数式动作',
      ),
      'editable' => false,
      'comment' => '退货方式',
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
      //'in_list' => true,
     // 'default_in_list' => true,
    ),
    'is_def' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => '类型标示',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'lastmodify' => 
    array (
      'label' => '供应商最后更新时间',
      'width' => 150,
      'type' => 'time',
      'hidden' => 1,
      'in_list' => true,
    ),
  ),
  'comment' => '商品类型表',
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40654 $',
);