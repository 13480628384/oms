<?php
$db['orders']=array (
  'columns' =>
  array (
    'order_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'extra' => 'auto_increment',
    ),
    'order_bn' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'label' => '订单号',
      'is_title' => true,
      'width' => 125,
      'searchtype' => 'nequal',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'archive' =>
    array (
      'type' => 'tinyint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => '订单归档',
    ),
    'member_id' =>
    array (
      'type' => 'table:members@ome',
      'label' => '会员用户名',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
//      'default_in_list' => false,
    ),
    'confirm' =>
    array (
      'type' => 'tinybool',
      'default' => 'N',
      'required' => true,
      'label' => '确认状态',
      'width' => 75,
      'hidden' => true,
      'editable' => false,
    ),
    'process_status' =>
    array (
      'type' =>
      array (
        'unconfirmed' => '未确认',
        'confirmed' => '已确认',
        'splitting' => '部分拆分',
        'splited' => '已拆分完',
        'cancel' => '取消',
        'remain_cancel' => '余单撤销',
        'is_retrial' => '复审订单',
        'is_declare' => '跨境申报订单',
      ),
      'default' => 'unconfirmed',
      'required' => true,
      'label' => '确认状态',
      'width' => 70,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'status' =>
    array (
      'type' =>
      array (
        'active' => '活动订单',
        'dead' => '已作废',
        'finish' => '已完成',
      ),
      'default' => 'active',
      'required' => true,
      'label' => '订单状态',
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'pay_status' =>
    array (
      'type' =>
      array (
        0 => '未支付',
        1 => '已支付',
        2 => '处理中',
        3 => '部分付款',
        4 => '部分退款',
        5 => '全额退款',
        6 => '退款申请中',
        7 => '退款中',
        8 => '支付中',
      ),
      'default' => '0',
      'required' => true,
      'label' => '付款状态',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'ship_status' =>
    array (
      'type' =>
      array (
        0 => '未发货',
        1 => '已发货',
        2 => '部分发货',
        3 => '部分退货',
        4 => '已退货',
      ),
      'default' => '0',
      'required' => true,
      'label' => '发货状态',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'is_delivery' =>
    array (
      'type' => 'tinybool',
      'default' => 'Y',
      'required' => true,
      'editable' => false,
      'comment' => '是否实体配送',
    ),
    'shipping' =>
    array (
      'type' => 'varchar(100)',
      'label' => '配送方式',
      'width' => 75,
      'editable' => false,
//      'filtertype' => 'normal',
//      'filterdefault' => true,
      'sdfpath' => 'shipping/shipping_name',
      'in_list' => true,
    ),
    'pay_bn' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'label' => '支付编号',
    ),
    'payment' =>
    array (
      'type' => 'varchar(100)',
      'label' => '支付方式',
      'width' => 65,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'sdfpath' => 'payinfo/pay_name',
    ),
    'weight' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => '净重',
    ),
    'tostr' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'sdfpath' => 'title',
      'comment' => '文字描述',
    ),
    'itemnum' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '货物总数量',
    ),
    'createtime' =>
    array (
      'type' => 'time',
      'label' => '下单时间',
      'width' => 130,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'download_time' =>
    array (
      'type' => 'time',
      'label' => '订单下载时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
    ),
    'up_time' =>
    array (
      'type' => 'time',
      'label' => '订单回传时间',
      'width' => 130,
      'editable' => false,
    ),
    'last_modified' =>
    array (
      'label' => '最后更新时间',
      'type' => 'last_modify',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
    ),
    'outer_lastmodify' =>
    array (
      'label' => '前端店铺最后更新时间',
      'type' => 'time',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ome',
      'label' => '来源店铺',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),
    'shop_type' =>
    array (
      'type' => 'varchar(50)',
      'label' => '店铺类型',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),
    'ip' =>
    array (
      'type' => 'varchar(15)',
      'editable' => false,
      'comment' => '下单ip地址',
    ),
    //收货人信息
    'ship_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => '收货人',
      'sdfpath' => 'consignee/name',
      'width' => 60,
      'searchtype' => 'head',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'ship_area' =>
    array (
      'type' => 'region',
      'label' => '收货地区',
      'width' => 170,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'sdfpath' => 'consignee/area',
      'in_list' => true,
    ),
    'ship_addr' =>
    array (
      'type' => 'varchar(100)',
      'label' => '收货地址',
      'width' => 180,
      'editable' => false,
      'filtertype' => 'normal',
      'sdfpath' => 'consignee/addr',
      'in_list' => true,
    ),
    'ship_zip' =>
    array (
      'label' => '收货邮编',
      'type' => 'varchar(20)',
      'editable' => false,
      'sdfpath' => 'consignee/zip',
      'in_list' => true,
    ),
    'ship_tel' =>
    array (
      'type' => 'varchar(30)',
      'label' => '收货人电话',
      'width' => 75,
      'editable' => false,
      'sdfpath' => 'consignee/telephone',
      'in_list' => true,
    ),
    'ship_email' =>
    array (
      'type' => 'varchar(150)',
      'editable' => false,
      'sdfpath' => 'consignee/email',
      'comment' => '收货人Email',
    ),
    'ship_time' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'sdfpath' => 'consignee/r_time',
      'comment' => '要求到货时间',
    ),
    'ship_mobile' =>
    array (
      'label' => '收货人手机',
      'hidden' => true,
      'type' => 'varchar(50)',
      'editable' => false,
      'width' => 85,
      'sdfpath' => 'consignee/mobile',
      'in_list' => true,
    ),
    //发货人信息
    'consigner_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => '发货人',
      'sdfpath' => 'consigner/name',
      'width' => 60,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'consigner_area' =>
    array (
      'type' => 'region',
      'label' => '发货人地区',
      'width' => 170,
      'editable' => false,
      'sdfpath' => 'consigner/area',
      'in_list' => true,
    ),
    'consigner_addr' =>
    array (
      'type' => 'varchar(100)',
      'label' => '发货人地址',
      'width' => 180,
      'editable' => false,
      'sdfpath' => 'consigner/addr',
      'in_list' => true,
    ),
    'consigner_zip' =>
    array (
      'label' => '发货人邮编',
      'type' => 'varchar(20)',
      'editable' => false,
      'sdfpath' => 'consigner/zip',
      'in_list' => true,
    ),
    'consigner_email' =>
    array (
      'type' => 'varchar(150)',
      'label' => '发货人e-mail',
      'editable' => false,
      'sdfpath' => 'consigner/email',
    ),
    'consigner_mobile' =>
    array (
      'label' => '发货人手机',
      'hidden' => true,
      'type' => 'varchar(50)',
      'editable' => false,
      'width' => 85,
      'sdfpath' => 'consigner/mobile',
      'in_list' => true,
    ),
    'consigner_tel' =>
    array (
      'type' => 'varchar(30)',
      'label' => '发货人电话',
      'width' => 75,
      'editable' => false,
      'sdfpath' => 'consigner/tel',
      'in_list' => true,
    ),
    //商品信息
    'cost_item' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'label' => '商品金额',
      'width' => 75,
    ),
   'is_tax' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'label' => '是否开发票',
      'width' => 80,
      'filtertype' => 'yes',
      'filterdefault' => true,
    ),
    'cost_tax' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'width' => 65,
      'label' => '税金',
    ),
    'tax_company' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'sdfpath' => 'tax_title',
      'comment' => '发票抬头',
    ),
    'cost_freight' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '配送费用',
      'width' => 70,
      'editable' => false,
      'filtertype' => 'number',
      'sdfpath' => 'shipping/cost_shipping',
      'in_list' => true,
    ),
    'is_protect' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'sdfpath' => 'shipping/is_protect',
      'comment' => '是否保价',
    ),
    'cost_protect' =>
    array (
      'type' => 'money',
      'default' => '0',
      'label' => '保价费用',
      'required' => true,
      'editable' => false,
      'sdfpath' => 'shipping/cost_protect',
    ),
    'is_cod' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'label' => '货到付款',
      'sdfpath' => 'shipping/is_cod',
      'in_list' => true,
      'default_in_list' => false,
      'width' => 60,
    ),
    'is_fail' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'label' => '失败订单',
    ),
    'edit_status' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '失败订单恢复时是否正在批量编辑恢复',
    ),
    'cost_payment' =>
    array (
      'type' => 'money',
      'editable' => false,
      'sdfpath' => 'payinfo/cost_payment',
      'comment' => '支付费用',
    ),
    'currency' =>
    array (
      'type' => 'varchar(8)',
      'editable' => false,
      'comment' => '支付币别',
    ),
    'cur_rate' =>
    array (
      'type' => 'decimal(10,4)',
      'default' => '1.0000',
      'editable' => false,
      'comment' => '汇率',
    ),
    'score_u' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '积分使用',
    ),
    'score_g' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '积分增加',
    ),
    'discount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '折扣，负的代表优惠，正的代表多收',
    ),
    'pmt_goods' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '商品促销优惠',
    ),
    'pmt_order' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => '订单促销优惠',
    ),
    'total_amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'label' => '订单总额',
      'width' => 70,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'final_amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'sdfpath' => 'cur_amount',
      'comment' => '实际货币所需付款',
    ),
    'payed' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'label' => '已付金额',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'custom_mark' =>
    array (
      'type' => 'longtext',
      'label' => '买家留言',
      'editable' => false,
    ),
    'mark_text' =>
    array (
      'type' => 'longtext',
      'label' => '订单备注',
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
    ),
    'mark_type' =>
    array (
      'type' => 'varchar(3)',
      'label' => '订单备注图标',
      'hidden' => true,
      'width' => 85,
      'editable' => false,
      'in_list' => true,
    ),
    'tax_no' =>
    array (
      'type' => 'varchar(50)',
      'label' => '发票号',
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'dt_begin' =>
    array (
      'type' => 'time',
      'label' => '分派开始时间',
      'editable' => false,
      'width' => 110,
    ),
    'is_anti' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
      'comment' => '发货信息是否已经回写前台店铺',
    ),
    'group_id' =>
    array (
      'type' => 'table:groups@ome',
      'label' => '确认组',
      'editable' => false,
      'width' => 90,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'op_id' =>
    array (
      'type' => 'table:account@pam',
      'label' => '确认人',
      'editable' => false,
      'width' => 60,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'dispatch_time' =>
    array (
      'type' => 'time',
      'label' => '分派时间',
      'editable' => false,
      'width' => 130,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_limit_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'comment' => '订单失效时间，到几点几分几秒失效(时间戳)',
    ),
    'coupons_name' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => '发货人',
    ),
    'refer_id' =>
    array (
      'type' => 'varchar(50)',
      'label' => '首次来源ID',
      'width' => 75,
      'editable' => false,
    ),
    'refer_url' =>
    array (
      'type' => 'varchar(200)',
      'label' => '首次来源URL',
      'width' => 150,
      'editable' => false,
    ),
    'refer_time' =>
    array (
      'type' => 'time',
      'label' => '首次来源时间',
      'width' => 110,
      'editable' => false,
    ),
    'c_refer_id' =>
    array (
      'type' => 'varchar(50)',
      'label' => '本次来源ID',
      'width' => 75,
      'editable' => false,
    ),
    'c_refer_url' =>
    array (
      'type' => 'varchar(200)',
      'label' => '本次来源URL',
      'width' => 150,
      'editable' => false,
    ),
    'c_refer_time' =>
    array (
      'type' => 'time',
      'label' => '本次来源时间',
      'width' => 110,
      'editable' => false,
    ),
    'abnormal' =>
    array (
      'type' => 'bool',
      'label' => '异常处理状态',
      'editable' => false,
      'in_list' => true,
      'default' => 'false',
    ),
    'print_finish' =>
    array (
      'type' => 'bool',
      'editable' => false,
      'default' => 'false',
      'required' => true,
      'comment' => '是否打印结束',
    ),
    'source' =>
    array (
      'type' => 'varchar(50)',
      'default' => 'matrix',
      'editable' => false,
      'comment' => '订单来源',
    ),
    'pause' =>
    array (
      'type' =>'bool',
      'default' => 'false',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'label' => '暂停',
    ),
    'is_modify' =>
    array (
      'type' =>'bool',
      'default' => 'false',
      'editable' => false,
      'in_list' => true,
      'label' => '商品信息编辑',
      'width' => 60,
      'filtertype' => 'yes',
      'filterdefault' => true,
    ),
    'old_amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'comment' => '编辑前订单金额',
    ),
    'order_type' =>
    array (
      'type' => array(
            'normal' => '订单',
            'sale'   => '销售单'
        ),
      'default' => 'normal',
      'editable' => false,
      'comment' => '订单类型',
    ),
    'order_job_no' =>
    array (
      'type' => 'varchar(16)',
      'label' => '批次索引号',
      'editable' => false,
    ),
    'order_combine_idx' =>
    array (
      'type' => 'bigint(13)',
      'label' => '合并索引号',
      'editable' => false,
    ),
    'order_combine_hash' =>
    array (
      'type' => 'char(32)',
      'label' => '合并识别号',
      'editable' => false,
    ),
    'auto_status' =>
    array (
      'type' => 'bigint(20)',
      'label' => '状态标识位',
      'editable' => false,
      'default' => '0',
    ),
    'abnormal_status' =>
    array (
      'type' => 'bigint(20)',
      'label' => '异常状态标识位',
      'editable' => false,
      'default' => '0',
    ),
    'refund_status' =>
    array (
      'type' => 'integer',
      'default' => 0,
      'label' => '退款状态',
      'editable' => false,
    ),
    'print_status' =>
    array (
      'type' => 'tinyint',
      'required' => true,
      'editable' => false,
      'default' => '0',
      'width' =>75,
      'comment' => '打印状态 ',
      'label' => '打印状态',
      //'in_list' => true,
    ),
    'logi_id' =>
    array (
      'type' => 'table:dly_corp@ome',
      'comment' => '物流公司ID',
      'editable' => false,
      'label' => '物流公司',
      'in_list' => true,
      'default_in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),
    'logi_no' =>
    array (
      'type' => 'varchar(50)',
      'label' => '物流单号',
      'comment' => '物流单号',
      'editable' => false,
      'width' =>110,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'sync' =>
    array (
      'type' => array(
          'none' => '未回写',
          'run' => '运行中',
          'fail' => '回写失败',
          'succ' => '回写成功',
      ),
      'default' => 'none',
      'label' => '回写状态',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
    ),
    'paytime' =>
    array (
      'type' => 'time',
      'label' => '付款时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
    ),
    'modifytime' =>
    array (
      'type' => 'time',
      'label' => '最后修改时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
    ),
	'is_auto' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => '是否自动处理',
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'self_delivery' => array(
        'type' => 'bool',
        'default' => 'true',
        'required' => true,
        'label' => '是否自发货',
        'editable' => false,
        'filtertype' => 'normal',
        'in_list' => true,
    ),    
    'order_source' =>
    array(
      'type' => 'varchar(50)',
      'label' => '订单类型',
      'default'=>'direct',
      'filtertype' => 'yes',
      'filterdefault' => true, 
      'in_list' => true,   
      'default_in_list' => true,           
    ),
       'relate_order_bn' =>
    array (
      'type' => 'varchar(32)',
      'label' => '关联订单号',
      'width' => 130,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'createway' => array(
        'type' => array(
            'matrix' => '平台获取',
            'local' => '手工新建',
            'import' => '批量导入',
            'after' => '售后自建',
        ),
        'label' => '订单生成类型',
        'default' => 'matrix',
        'required' => true,
        'in_list' => true,
        'filtertype' => 'normal',
    ),
    'sync_fail_type' => array(
      'type' => array(
          'none' => '未知错误',
          'shipped' => '前端已发货',
          'unbind' => '店铺未绑定',
          'params' => '参数错误',
      ),
      'default' => 'none',
      'label' => '回写失败类型',
      'editable' => false,
    ),
  ),
  'index' =>
  array (
    'ind_order_bn_shop' =>
    array (
        'columns' =>
        array (
          0 => 'order_bn',
          1 => 'shop_id',
        ),
        'prefix' => 'unique',
    ),
    'ind_order_bn' =>
    array (
        'columns' =>
        array (
          0 => 'order_bn',
        ),
    ),
    'ind_archive' =>
    array (
      'columns' =>
      array (
        0 => 'archive',
      ),
    ),
    'ind_ship_status' =>
    array (
      'columns' =>
      array (
        0 => 'ship_status',
      ),
    ),
    'ind_pay_status' =>
    array (
      'columns' =>
      array (
        0 => 'pay_status',
      ),
    ),
    'ind_status' =>
    array (
      'columns' =>
      array (
        0 => 'status',
      ),
    ),
    'ind_process_status' =>
    array (
      'columns' =>
      array (
        0 => 'process_status',
      ),
    ),
    'ind_shop_type' =>
    array (
      'columns' =>
      array (
        0 => 'shop_type',
      ),
    ),
    'ind_is_cod' =>
    array (
      'columns' =>
      array (
        0 => 'is_cod',
      ),
    ),
    'ind_createtime' =>
    array (
      'columns' =>
      array (
        0 => 'createtime',
      ),
    ),
    'ind_order_job_no' =>
    array (
      'columns' =>
      array (
        0 => 'order_job_no',
      ),
    ),
    'ind_pay_bn' =>
    array (
      'columns' =>
      array (
        0 => 'pay_bn',
      ),
    ),
	'ind_is_auto' =>
    array (
      'columns' =>
      array (
        0 => 'is_auto',
      ),
    ),
    'ind_is_tax' =>
    array (
      'columns' =>
      array (
        0 => 'is_tax',
      ),
    ),
    'ind_outer_lastmodify' =>
    array (
      'columns' =>
      array (
        0 => 'outer_lastmodify',
      ),
    ),
    'ind_pause' =>
    array (
      'columns' =>
      array (
        0 => 'pause',
      ),
    ),
    'ind_abnormal' =>
    array (
      'columns' =>
      array (
        0 => 'abnormal',
      ),
    ),
  ),
  'comment' => '订单表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);