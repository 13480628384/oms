<?php
$db['bill']=array (
    'columns' => 
    array (
        'bill_id' => 
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
            ),
        'bill_bn' => 
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'label' => '单据编号',
            'searchtype' => 'nequal',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'editable' => false,
            'order'=>3,
            ),
        'order_bn' => 
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'label' => '业务订单号',
            'width' => 130,
            'searchtype' => 'nequal',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>8,
            ),
        'crc32_order_bn' =>
        array (
            'type' => 'int unsigned',
            'label' => '订单号的crc32表示',
            'comment' => '用于快速搜索订单号'
            ),
        'channel_id' => 
        array (
            'type' => 'varchar(32)',
            'editable' => false,
            'comment'=>'渠道ID',
            ),
        'channel_name' => 
        array (
            'type' => 'varchar(255)',
            'label' => '渠道名称',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'order'=>4,
            ),
        'member' => 
        array (
            'type' => 'varchar(255)',
            'label' => '客户/会员',
            'comment'=>'客户/会员 /交易对方ID',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>6,
            ),
        'status' => 
        array (
            'type' => 'tinyint',
            'required' => true,
            'default' => 0,
            'label' => '核销状态',
            'comment' => '核销状态  未核销(0)、部分核销(1)、已核销(2)',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>12,
            ),
        'verification_time' => 
        array (
            'type' => 'time',
            'default'=>0,
            'comment' => '完全核销时间',
            'editable' => false,
            ),
        'charge_status' => 
        array (
            'type' => 'tinyint',
            'required' => true,
            'default' => 0,
            'label' => '记账状态',
            'comment' => '记账状态  未记账(0)、已记账(1) ',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>16,
            ),
        'charge_time' => 
        array (
            'type' => 'time',
            'label' => '记账日期',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'time',
            'filterdefault' => true,
            'order'=>17,
            ),
        'monthly_id' => 
        array (
            'type' => 'time',
            'label' => '所属账期',
            'editable' => false,
            ),
        'monthly_status' => 
        array (
            'type' => 'tinyint',
            'default' => 0,
            'label' => '月结状态',
            'comment' => '月结状态 未结帐（0），已结账（1）',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>18,
            ),
        'trade_time' => 
        array (
            'type' => 'time',
            'label' => '账单日期',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'time',
            'filterdefault' => true,
            'order'=>7,
            ),
        'create_time' => 
        array (
            'type' => 'time',
            'comment' => '单据生成时间(插入数据库的时间)',
            'required'=>true,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            ),
        'fee_type_id' => 
        array (
            'type' => 'int',
            'required' => true,
            'comment' => '费用类ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            ),
        'fee_type' => 
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'label'=>'费用类',
            'comment' => '费用类',
            'width' => 100,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>9,
            ),
        'fee_item_id' => 
        array (
            'type' => 'int',
            'required' => true,
            'comment' => '费用项ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            ),
        'fee_item' => 
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'label'=>'费用项',
            'comment' => '费用项',
            'width' => 100,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>10,
            ),
        'fee_obj_id' => 
        array (
            'type' => 'bigint',
            'comment' => '费用对象ID',
            'editable' => false,
            ),
        'fee_obj' => 
        array (
            'type' => 'varchar(32)',
            'label'=>'费用对象',
            'width' => 65,
            'comment' => '费用对象',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>5,
            ),
        'money' => 
        array (
            'type' => 'money',
            'required' => true,
            'label'=>'费用金额',
            'comment' => '金额,区分正负号',
            'width' => 70,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>11,
            ),
        'confirm_money' => 
        array (
            'type' => 'money',
            'required' => true,
            'default'=>0,
            'label' => '已核销金额',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>14,
            ),
        'unconfirm_money' => 
        array (
            'type' => 'money',
            'required' => true,
            'label' => '未核销金额',
            'width' => 70,
            'default'=>0,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>13,
            ),
        'credential_number' => 
        array (
            'type' => 'varchar(64)',
            'label'=>'凭据号',
            'comment' => '凭据号',
            'editable' => false,
            'searchtype' => 'nequal',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>15,
            ),
        'unique_id' => 
        array (
            'type' => 'varchar(32)',
            'required'=>true,
            'comment' => '唯一标识',
            'editable' => false,
            ),
        'auto_flag' => 
        array (
            'type' => 'tinyint',
            'comment' => '自动核销标识 未核销（0） 已核销（1）',
            'editable' => false,
            'default'=>0,
            ),
        'memo' =>
        array (
            'type' => 'longtext',
            'label' => '备注',
            'editable' => false,
            ),
        ),
'index'=>array(
    'ind_order_bn' =>
    array (
        'columns' =>
        array (
            0 => 'order_bn',
            ),
        ),
    'ind_auto_flag' =>
    array (
        'columns' =>
        array (
            0 => 'auto_flag',
            ),
        ),
    'ind_channel_id' =>
    array (
        'columns' =>
        array (
            0 => 'channel_id',
            ),
        ),
    'ind_status' =>
    array (
        'columns' =>
        array (
            0 => 'status',
            ),
        ),
    'ind_credential_number' =>
    array (
        'columns' =>
        array (
            0 => 'credential_number',
            ),
        ),
    'ind_unique_id' =>
    array (
        'columns' =>
        array (
            0 => 'unique_id',
            ),
        'prefix' => 'unique',
        ),
    'ind_trade_verification_time' =>
    array (
        'columns' =>
        array (
            0 => 'trade_time',
            1 => 'verification_time',
            ),
        ),
    'ind_create_time' =>
    array (
        'columns' =>
        array (
            0 => 'create_time',
            ),
        ),
    'ind_fee_type_id' =>
    array (
        'columns' =>
        array (
            0 => 'fee_type_id',
            ),
        ),
    'ind_money' =>
    array (
        'columns' =>
        array (
            0 => 'money',
            ),
        ),
    'ind_unconfirm_money' =>
    array (
        'columns' =>
        array (
            0 => 'unconfirm_money',
            ),
        ),
    'ind_confirm_money' =>
    array (
        'columns' =>
        array (
            0 => 'confirm_money',
            ),
        ),
    'ind_crc32_order_bn' =>
    array (
        'columns' =>
        array (
            0 => 'crc32_order_bn',
            ),
        ),
    'ind_charge_status' =>
    array (
        'columns' =>
        array (
            0 => 'charge_status',
            ),
        ),
    'ind_charge_time' =>
    array (
        'columns' =>
        array (
            0 => 'charge_time',
            ),
        ),
    'ind_monthly_id' =>
    array (
        'columns' =>
        array (
            0 => 'monthly_id',
            ),
        ),
    'ind_monthly_status' =>
    array (
        'columns' =>
        array (
            0 => 'monthly_status',
            ),
        ),
    'ind_fee_item_id' =>
    array (
        'columns' =>
        array (
            0 => 'fee_item_id',
            ),
        ),
    ),
'comment' => '销售收退款单',
'engine' => 'innodb',
'version' => '$Rev:  $',
);