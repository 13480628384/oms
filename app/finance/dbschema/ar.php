<?php
$db['ar']=array (
    'columns' => 
    array (
        'ar_id' => 
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
            ),
        'ar_bn' => 
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'label' => '单据编号',
            'searchtype' => 'nequal',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'editable' => false,
            'width' => 130,
            'order'=>1,
            ),
        'order_bn' => 
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'label' => '业务订单号',
            'width' => 140,
            'searchtype' => 'nequal',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>6,
            ),
        'crc32_order_bn' =>
        array (
            'type' => 'int unsigned',
            'label' => '订单号的crc32表示',
            'comment' => '用于快速搜索订单号'
            ),
        'relate_order_bn' => 
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'label' => '关联订单号',
            'width' => 140,
            'searchtype' => 'nequal',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'order'=>7,
            ),
        'crc32_relate_order_bn' =>
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
            'width' => 130,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>2,
            ),
        'member' => 
        array (
            'type' => 'varchar(255)',
            'label' => '客户/会员',
            'comment'=>'客户/会员 /交易对方ID',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 60,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>4,
            ),
        'status' => 
        array (
            'type' => 'tinyint',
            'required' => true,
            'default' => 0,
            'label' => '核销状态',
            'comment'=>'核销状态  未核销(0)、部分核销(1)、已核销(2)',
            'editable' => false,
            'width' => 65,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>11,
            ),
        'verification_time' => 
        array (
            'type' => 'time',
            'default'=>0,
            'comment' => '完全核销时间',
            'editable' => false,
            ),
        'type' => 
        array (
            'type' => 'tinyint',
            'required' => true,
            'label' => '业务类型',
            'comment'=>'销售出库、销售退货、销售换货、销售退款',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>5,
            ),
        'charge_status' => 
        array (
            'type' => 'tinyint',
            'required' => true,
            'default' => 0,
            'label' => '记账状态',
            'comment'=>'记账状态  未记账(0)、已记账(1)',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order'=>14,
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
            'order'=>15,
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
            'filtertype' => 'normal',
            'filterdefault' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>16,
            ),
        'create_time' => 
        array (
            'type' => 'time',
            'label' => '进入系统日期',
            'editable' => false,
            ),
        'trade_time' => 
        array (
            'type' => 'time',
            'label' => '账单日期',
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'time',
            'filterdefault' => true,
            'order'=>3,
            ),
        'money' => 
        array (
            'type' => 'money',
            'required' => true,
            'label'=>'应收金额',
            'comment' => '金额,区分正负号',
            'width' => 65,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>10,
            ),
        'confirm_money' => 
        array (
            'type' => 'money',
            'required' => true,
            'label' => '已核销金额',
            'width' => 65,
            'default'=>0,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>12,
            ),
        'unconfirm_money' => 
        array (
            'type' => 'money',
            'required' => true,
            'label' => '未核销金额',
            'width' => 65,
            'default'=>0,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>13,
            ),
        'addon' => 
        array (
            'type' => 'serialize',
            'comment' => '附加字段Serialize(array(‘sale_money’=>’商品成交金额’,’fee_money’=>’运费收入’))',
            'editable' => false,
            ),
        'auto_flag' => 
        array (
            'type' => 'tinyint',
            'required' => true,
            'comment' => '自动核销标识 未核销（0） 已核销（1）',
            'default'=>0,
            'editable' => false,
            ),
        'verification_flag' => 
        array (
            'type' => 'tinyint',
            'required' => true,
            'comment' => '能否应收对冲标识 不可（0） 可（1）',
            'default'=>0,
            'editable' => false,
            ),
        'serial_number' =>
        array (
            'type' => 'varchar(64)',
            'required' => true,
            'label'=>'业务流水号',
            'comment' => '应收流水号',
            'in_list' => true,
            'default_in_list' => true,
            'editable' => false,
            'order'=>18,
            ),
        'unique_id' => 
        array (
            'type' => 'varchar(32)',
            'required'=>true,
            'comment' => '唯一标识',
            'editable' => false,
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
    'ind_crc32_order_bn' =>
    array (
        'columns' =>
        array (
            0 => 'crc32_order_bn',
            ),
        ),
    'ind_relate_order_bn' =>
    array (
        'columns' =>
        array (
            0 => 'relate_order_bn',
            ),
        ),
    'ind_crc32_relate_order_bn' =>
    array (
        'columns' =>
        array (
            0 => 'crc32_relate_order_bn',
            ),
        ),
    'ind_auto_flag' =>
    array (
        'columns' =>
        array (
            0 => 'auto_flag',
            ),
        ),
    'ind_verification_flag' =>
    array (
        'columns' =>
        array (
            0 => 'verification_flag',
            ),
        ),
    'ind_serial_number' =>
    array (
        'columns' =>
        array (
            0 => 'serial_number',
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
    'ind_channel_id' =>
    array (
        'columns' =>
        array (
            0 => 'channel_id',
            ),
        ),
    'ind_member' =>
    array (
        'columns' =>
        array (
            0 => 'member',
            ),
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
    'ind_status' =>
    array (
        'columns' =>
        array (
            0 => 'status',
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
    'ind_monthly_status' =>
    array (
        'columns' =>
        array (
            0 => 'monthly_status',
            ),
        ),
    'ind_monthly_id' =>
    array (
        'columns' =>
        array (
            0 => 'monthly_id',
            ),
        ),
    ),
'comment' => '销售应收单',
'engine' => 'innodb',
'version' => '$Rev:  $',
);