<?php
class create_order extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        //本测试用例正对于单仓库使用
    }
    
    function getOrder($order_bn){
        
        #复制测试数据源，从后台找到商家的 管理后台--》系统--》同步日志管理--》淘管标准$sdf结构      
        
        $shop_id    = '93dd4de5cddba2c733c65f233097f05a';
        $shop_type = 'taobao';
        $node_id    = '1971939335';
        $order_source = 'taobao';
        $goods_bn ='S0241';
        $product_bn ='10301';
        $goods_name ='7.1差旅费用报销单';
        $product_name ='7.1差旅费用报销单';
        $member_id       = 1;
        $member_uname    = 'kkkio2';
        $member_name     = '测试';
        $member_alipay_no    = 'shopex@126.com';
        $member_email    = 'shopex@126.com';
        $is_cod = false;
        $order_weight = 5000;
        
        $sdf = array ( 
            'shop_id' => $shop_id, 
            'shop_type' => $shop_type, 
            'order_bn' => $order_bn, 
            'cost_item' => 200, 
            'discount' => 0, 
            'total_amount' => 200, 
            'pmt_goods' => 0, 
            'pmt_order' => 0, 
            'cur_amount' => 200, 
            'cost_freight' => 0, 
            'score_u' => 0, 'score_g' => 0, 'currency' => 'CNY', 'source' => 'matrix', 
            'status' => 'active', 
            'ship_status'=>'0', 
            'weight' => $order_weight, 
            'order_source' => $order_source,
            'cur_rate' => '1.0', 
            'title' => '', 
            'coupons_name' => NULL,
            'createway' => 'matrix', 
            'download_time' => time(), 
            'createtime' => time(),
            'outer_lastmodify' => time(), 
            'order_limit_time' => time(),
            'pay_bn' => 'online', 
            'pay_status' => '1', 
            'payed' => 200, 
            'payinfo' => array ( 'pay_name' => '在线支付', 'cost_payment' => '', ),
            'paytime' => time(), 
            'is_tax' => 'false', 
            'cost_tax' => 0, 
            'tax_no' => NULL, 
            'tax_title' => '', 
            'order_objects' => array (                 
                0 => array (
                    'obj_type' => 'goods',
                    'obj_alias' => 'goods',
                    'shop_goods_id' => '201500110002',
                    'goods_id' => '11', 
                    'bn' => $goods_bn,
                    'name' => $goods_name,
                    'price' => 200,
                    'amount' => 200,
                    'quantity' => '1',
                    'weight' => 0, 
                    'score' => 0,
                    'pmt_price' => 0,
                    'sale_price' => 200,
                    'order_items' => array (
                        0 => array ( 'shop_goods_id' => '201500110002',
                            'product_id' => '7',
                            'shop_product_id' => '0',
                            'bn' => $product_bn, 
                            'name' => $product_name,
                            'cost' => Null, 
                            'price' => 200, 
                            'pmt_price' => 0,
                            'sale_price' => 200, 
                            'amount' => 200,
                            'weight' => 0,
                            'quantity' => '1', 
                            'addon' => '',
                            'item_type' => 'product',
                            'score' => 0, 
                            'delete' => 'false',
                            'sendnum' => 0, 
                            'original_str' => '', 
                            'product_attr' => '', 
                            'promotion_id' => '', 
                        ),
                    ),
                    'is_oversold' => 0,
                    'oid' => '201500110002',
                ),
            ),
            'shipping' => array (
                'shipping_name' => '',
                'cost_shipping' => 0,
                'is_protect' => 'false',
                'cost_protect' => 0, 
                'is_cod' => $is_cod, 
            ), 
            'member_info' => array (
                'tel' => '',
                'uname' => $member_uname, 
                'area_district' => '', 
                'area_state' => '', 
                'addr' => '', 
                'name' => $member_name, 
                'zip' => '', 
                'mobile' => '', 
                'area_city' => '', 
                'alipay_no' => $member_alipay_no, 
                'email' => $member_email,
            ),
            'consignee' => array (
                'name' => '王先生', 
                'area_state'=>'上海', 
                'area_city'=>'上海市',
                'area_district'=>'黄浦区',
                'area' => '上海/上海市/黄浦区', 
                'addr' => '某一某街道这是测试收货地址不要寄送', 
                'zip' => '230055', 
                'telephone' => '021-12345678', 
                'email' => 'abc@126.com',
                'r_time' => '', 
                'mobile' => '13612345678', 
            ),
            'consigner' => array ( 
                'name' => '王先生', 
                'area_state'=>'上海', 
                'area_city'=>'上海市',
                'area_district'=>'黄浦区',
                'area' => '上海/上海市/黄浦区', 
                'addr' => '某一某街道这是测试收货地址不要寄送', 
                'zip' => '230055', 
                'tel' => '021-12345678', 
                'email' => 'abc@126.com',
                'mobile' => '13612345678', 
            ),
            'member_id' => $member_id,
            'order_job_no' => $order_bn,         
        );
        
        base_rpc_service::$node_id = $node_id;

        return $sdf;
    }
    public function testOrder()
    {
        $limit        = 4;//新建订单数量
        $oResponse    = kernel::single('apibusiness_router_response');
        
        for($i=1; $i <= $limit; $i++)
        {
            $order_bn    = '2015091500'.date('is',time()).'00'.$i;
            $sdf         = $this->getOrder($order_bn);
            
            $oResponse->dispatch('order','add',$sdf);
            
            echo('新建订单：'.$sdf['order_bn']."\n\n");
        }
        
        die('====执行完成,共创建 '.$limit.' 个订单====');
    }
}
