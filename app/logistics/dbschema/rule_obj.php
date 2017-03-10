<?php
$db['rule_obj']=array (
  'columns' =>
  array (
    'obj_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'extra' => 'auto_increment',
    ),
     'rule_id' =>
    array (
      'type' => 'table:rule@logistics',
      'required' => true,
      'default' => 0,
      //'pkey' => true,
      'editable' => false,
      'comment' => '规则ID',
    ),
    'region_grade' =>
        array (
            'type' => 'number',
            'editable' => false,
            'comment' => '区域级别',
        ),

    'region_id' =>
        array (
            'type' => 'number',
            'editable' => false,
            'comment' => '区域ID',
            //'pkey' => true,
        ),
        'region_name' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',

            'label'=>'区域',
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
            'editable' => false,
        ),
    'rule_type'=>array (
      'type' =>
      array (
        'default' => '默认',
        'other' => '排它',

      ),
      'label' => '规则类型',
    'in_list'         => true,
    'default_in_list' => true,
      'editable' => false,

    ),
    'set_type'=>array (
     'type' =>
      array (
        'weight' => '重量区间',
        'noweight' => '任意重量',

      ),
      'label' => '设置类型',
    'in_list'         => true,
    'default_in_list' => true,
      'editable' => false,

    ),
'branch_id' =>
    array (
      'type' => 'table:branch@ome',
      'editable' => false,
      'label' => '仓库',
      'width' => 110,

    ),
'rule_group_hash' =>
    array (
      'type' => 'char(32)',
      'label' => '分组识别号',
      'editable' => false,
    ),

),
'index' =>array (
 'ind_region_id' =>
    array (
      'columns' =>
      array (
        0 => 'region_id',
      ),
    ),

 ),
  'comment' => '规则对象',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);