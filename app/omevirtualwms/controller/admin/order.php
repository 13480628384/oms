<?php

define('WAIT_TIME',5);//finder中的数据多长时间重复展示(队列失败的情况)，单位 分
define('TIP_INFO','此数据已模拟发送，请注意是否要再次模拟发送');//再次展示时的提示信息
date_default_timezone_set('Etc/GMT-0');
class omevirtualwms_ctl_admin_order extends desktop_controller{

    public function __construct($app){
        parent::__construct($app);
        $this->app        = $app;
        $this->objBhc     = kernel::single('base_httpclient');
        $thi->objBcf      = kernel::single('base_certificate');
        $api_url        = kernel::base_url(1).kernel::url_prefix().'/api';
        $certificate    = base_shopnode::node_id('ome');
        $token          = base_shopnode::token('ome');
        $this->api_url    = $api_url;//api地址
        $this->token      = $token;//证书token
    }

    public function virtual_order(){
        $this->page('order/virtual_order_index.html');
    }


    //新建/编辑订单
    public function virtual_order_edit($order_id){
        
        $shopObj = app::get('ome')->model("shop");
        $shopData = $shopObj->getList('*');
        $this->pagedata['shopData'] = $shopData;
        $this->pagedata['creatime'] = date("Y-m-d H:i:s",time());
        if($order_id){
            $orders_mdl = app::get('ome')->model('orders');
            $order_items_mdl = app::get('ome')->model('order_items');
            $ordersData = $orders_mdl->dump($order_id,"*",array("order_objects"=>array("*")));
            //优惠方案
            $oOrderPmt = app::get('ome')->model('order_pmt');
            $order_pmt = $oOrderPmt->getList('pmt_amount,pmt_describe',array('order_id'=>$order_id));
            $ordersData['pmt_detail'] = $order_pmt;

            //订单明细
            $order_objects = $ordersData['order_objects'];
            foreach($order_objects as &$v){
                $order_items = $order_items_mdl->getlist('*',array('obj_id'=>$v['obj_id']));
                foreach($order_items as $v1){
                    $v['order_items'][] = array(
                        'bn' => $v1['bn'],
                        'quantity' => $v1['nums'],
                        'pmt_price' => $v1['pmt_price'],
                        'sale_price' => $v1['sale_price'],
                    );
                }
            }
            $ordersData['order_objects'] = $order_objects;


            //print_r($ordersData);exit;
            $this->pagedata['ordersData'] = $ordersData;
        }
        $this->singlepage("order/virtualwms_edit_order.html");
    }


    public function virtual_order_edit_save(){

        //$_SESSION['orders'] = $_POST['orders'];
        $sdf = $_POST['orders'];

        //$sdf['order_bn'] = 'M2012103011250011';
        //收货人省市区
        if($sdf['consignee']['area']){
            $_area = explode(':',$sdf['consignee']['area']);
            $area = explode('/',$_area[1]);
            $sdf['consignee']['area_state'] = $area[0];
            $sdf['consignee']['area_city'] = $area[1];
            $sdf['consignee']['area_district'] = $area[2];
            unset($sdf['consignee']['area']);
        }

        //发货人省市区
        if($sdf['consigner']['area']){
            $_area = explode(':',$sdf['consigner']['area']);
            $area = explode('/',$_area[1]);
            $sdf['consigner']['area_state'] = $area[0];
            $sdf['consigner']['area_city'] = $area[1];
            $sdf['consigner']['area_district'] = $area[2];
            unset($sdf['consigner']['area']);
        }

        //优惠方案
        $_pmt_detail = $sdf['pmt_detail'];
        $pmt_detail = array();
        unset($sdf['pmt_detail']);
        if($_pmt_detail){
            foreach($_pmt_detail['pmt_amount'] as $k=>$v){
                $pmt_detail[$k] = array(
                    'pmt_amount' => $v,
                    'pmt_describe' => $_pmt_detail['pmt_describe'][$k],
                );
            }
        }
        $sdf['pmt_detail'] = json_encode($pmt_detail);

        //order_object
        $products_mdl = app::get('ome')->model('products');
        $goods_mdl = app::get('ome')->model('goods');
        $order_items = $sdf['order_items'];
        $order_objects = array();
        $object = array();
        foreach($order_items['bn'] as $k=>$v){
            $product = $products_mdl->getlist('*',array('bn'=>$v),0,1);
            $goods = $goods_mdl->getlist('*',array('goods_id'=>$product[0]['goods_id']),0,1);
            $object[$goods[0]['goods_id']]['obj_type'] ='goods';
            $object[$goods[0]['goods_id']]['obj_alias'] = '';
            $object[$goods[0]['goods_id']]['bn'] = $goods[0]['bn'];
            $object[$goods[0]['goods_id']]['oid'] = $goods[0]['bn'];
            $object[$goods[0]['goods_id']]['name'] = $goods[0]['name'];
            $object[$goods[0]['goods_id']]['price'] = '';
            //$object[$goods[0]['goods_id']]['amount'] = '';
            $object[$goods[0]['goods_id']]['pmt_price'] = '';
            $object[$goods[0]['goods_id']]['sale_price'] = '';
            //$object[$goods[0]['goods_id']]['quantity'] = '';
            //$object[$goods[0]['goods_id']]['weight'] = '100';
            $object[$goods[0]['goods_id']]['score'] = '0';
            $spec_info = array();
            if($product[0]['spec_info']){
                $_spec_info = explode('、',$product[0]['spec_info']);
                foreach($_spec_info as &$v1){
                    $_si = explode('：',$v1);
                    $spec_info[] = array('label' => $_si[0],'value' => $_si[1]);
                }

            }
            $order_items_amount = $product[0]['mktprice']*$order_items['nums'][$k];
            $order_items_quantity = $order_items['nums'][$k];
            $order_items_weight = $product[0]['weight'];
            $order_items_price = $product[0]['mktprice'];
            $order_items_pmt_price = $order_items['pmt_price'][$k];
            $object[$goods[0]['goods_id']]['order_items'][] = array(
                'item_type' => 'product',
                'bn' => $v,
                'name' => $product[0]['name'],
                'product_attr' => $spec_info,
                'quantity' => $order_items_quantity,
                'price' => $order_items_price,
                'amount' => $order_items_amount,
                'pmt_price' => $order_items_pmt_price,
                'sale_price' => $order_items['sale_price'][$k],
                'weight' => $order_items_weight,
                'score' => '0',
                'status' => 'active',

            );

            if($object[$goods[0]['goods_id']]['obj_type'] == 'goods'){
                $object[$goods[0]['goods_id']]['amount'] += $order_items_amount;
                $object[$goods[0]['goods_id']]['quantity'] += $order_items_quantity;
                $object[$goods[0]['goods_id']]['weight'] += $order_items_weight;

            }elseif($object[$goods[0]['goods_id']]['obj_type'] == 'pkg'){
                
            }
        }
        unset($sdf['order_items']);

        //计算支付手续费
        if($sdf['pay_status'] != 0 && $sdf['payments']){
            foreach($sdf['payment'] as $v){
                $sdf['payinfo']['cost_payment'] += $v['pay_cost'];
            }
        }else{
            $sdf['payinfo']['cost_payment'] = 0;
        }

        //计算商品总额
        $sdf['cost_item'] = 0;
        foreach($object as $o1){
            $sdf['cost_item'] += $o1['amount'];
        }
        //计算订单总金额
        $sdf['total_amount'] = $sdf['cost_item']+$sdf['shipping']['cost_shipping']+$sdf['cost_tax']+$sdf['shipping']['cost_protect']+$sdf['payinfo']['cost_payment']+$sdf['discount']-$sdf['pmt_goods']-$sdf['pmt_order'];
        $sdf['cur_amount'] = $sdf['total_amount'];
    
        $sdf['order_objects'] = json_encode($object);

        //配送信息
        $sdf['shipping'] = json_encode($sdf['shipping']);
        //支付信息
        $sdf['payinfo'] = json_encode($sdf['payinfo']);
        //收货人信息
        $sdf['consignee'] = json_encode($sdf['consignee']);
        //发货人信息
        $sdf['consigner'] = json_encode($sdf['consigner']);
        //代销人信息
        $sdf['selling_agent'] = json_encode($sdf['selling_agent']);
        //买家会员信息
        $sdf['member_info'] = json_encode($sdf['member_info']);
        //支付单(兼容老版本)
        $sdf['payment_detail'] = json_encode($sdf['payment_detail']);
        //支付单(新版本)
        if($sdf['pay_status'] == 0){
            $sdf['payments'] = '';
        }else{
            $sdf['payments'] = json_encode($sdf['payments']);
        }

        error_log(print_r($sdf,1),3,'d:/ordersdf.txt');
        $rs = $this->order_request($sdf);
        print_r($rs);
    }

    public function order_request($sdf){
        $method = 'ome.order.add';
        $node_id = $sdf['node_id'];
        unset($sdf['node_id']);
        $params = $sdf;
        $query_params = array(
            'method'=>$method,
            'date'=>'',
            'format'=>'json',
            'node_id'=> $node_id,
            'app_id' => 'ecos.ome',
        );
        $query_params = array_merge((array)$params,$query_params);
        $query_params['sign'] = $this->_gen_sign($query_params,$this->token);

        $rs = $this->objBhc->post($this->api_url,$query_params);
        return $rs;
    }


     private function _assemble($params){
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params as $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? self::_assemble($val) : $val);
        }
        return $sign;
    }
    private function _gen_sign($params,$token){
        return strtoupper(md5(strtoupper(md5($this->_assemble($params))).$token));
    }





































    /*编辑订单
    public function virtual_order_edit($order_id){

        $oOrder = app::get('ome')->model('orders');
        $order = $oOrder->dump($order_id);

        $item_list = $oOrder->getItemBranchStore($order_id);

        if(!preg_match("/^mainland:/", $order['consignee']['area'])){
            $region='';
            $newregion='';
            foreach(explode("/",$order['consignee']['area']) as $k=>$v){
                $region.=$v.' ';
            }
        }else{
            $newregion = $order['consignee']['area'];
        }

        $this->pagedata['region'] = $region;
        $this->pagedata['newregion'] = $newregion;
        $this->pagedata['order_id'] = $order_id;
        $order['custom_mark'] = unserialize($order['custom_mark']);
        if ($order['custom_mark'])
        foreach ($order['custom_mark'] as $k=>$v){
            if (!strstr($v['op_time'], "-")){
                $v['op_time'] = date('Y-m-d H:i:s',$v['op_time']);
                $order['custom_mark'][$k]['op_time'] = $v['op_time'];
            }
        }
        $order['mark_text'] = unserialize($order['mark_text']);
        if ($order['mark_text'])
        foreach ($order['mark_text'] as $k=>$v){
            if (!strstr($v['op_time'], "-")){
                $v['op_time'] = date('Y-m-d H:i:s',$v['op_time']);
                $order['mark_text'][$k]['op_time'] = $v['op_time'];
            }
        }

        if(app::get('omepkg')->is_installed()){
            $flag = 'true';
        }else{
            $flag = 'false';
        }

        //订单代销人会员信息
        $oSellagent = app::get('ome')->model('order_selling_agent');
        $sellagent_detail = $oSellagent->dump(array('order_id'=>$order_id));
        if (!empty($sellagent_detail)){
            $this->pagedata['sellagent'] = $sellagent_detail;
        }
        //发货人信息
        $order_consigner = false;
        if ($order['consigner']){
            foreach ($order['consigner'] as $shipper){
                if (!empty($shipper)){
                    $order_consigner = true;
                    break;
                }
            }
        }
        $oShop = app::get('ome')->model('shop');
        $shop_detail = $oShop->dump(array('shop_id'=>$order['shop_id']));
        $b2b_shop_list = ome_shop_type::b2b_shop_list();
        if (in_array($shop_detail['node_type'], $b2b_shop_list)){
            $this->pagedata['b2b'] = true;
        }

        //购买人信息
        $memberObj = app::get('ome')->model('members');
        $members_detail = $memberObj->dump($order['member_id']);

        //优惠方案
        $oOrderPmt = app::get('ome')->model('order_pmt');
        $order_pmt = $oOrderPmt->getList('pmt_amount,pmt_describe',array('order_id'=>$order_id));
        $this->pagedata['order_pmt'] = $order_pmt;

        $this->pagedata['order'] = $order;
        $this->pagedata['member'] = $members_detail;
        ome_order_func::order_sdf_extend($item_list);
        $obj_config = kernel::single("ome_order_edit")->get_config_list();
        ksort($obj_config);
        $conf_list = array();
        foreach ($item_list as $obj => $idata){
            if (in_array($obj, array('goods','gift'))){
                $obj_alias = $obj == 'goods' ? '商品' : '礼包';
                $obj = 'goods';
                $is_add = false;
            }else{
                $obj = 'pkg';
                $obj_alias = '捆绑商品';
            }
            $tmp = array(
                'load' => true,
                'is_add' => false,
                'objs' => $idata,
                'obj_alias' => $obj_alias,
            );
            $conf_list[$obj][] = array_merge($obj_config[$obj], $tmp);
        }
        ksort($conf_list);
        $invoice = kernel::single('ome_invoice')->dumpByOID($order_id);#todo dongjiujin
        $this->pagedata['conf_list'] = $conf_list;
        $this->pagedata['pkg_installed'] = $flag;
        $this->pagedata['obj_config'] = $obj_config;
        $this->pagedata['item_list'] = $item_list;
        $this->pagedata['finder_id'] = $_GET['finder_id'];
        $this->pagedata['invoice'] = $invoice;
        $this->pagedata['is_invoice'] = $order['is_tax'];

        $this->singlepage("order/order_edit.html");
    }

    public function virtual_order_edit_send(){
        error_log(print_r($_POST,1),3,'d:/order.txt');
        $oOrder = app::get('ome')->model('orders');
        $order = $oOrder->dump($_POST['order_id'],"*",array("order_items"=>array("*")));

        ######组织订单接口所需数据结构######
        $sdf = array();
        $sdf['order_bn'] = $_POST['order_bn'];
        $sdf['status'] = $order['status'];
        $sdf['pay_status'] = $order['pay_status'];
        $sdf['shipping'] = array(
            'shipping_name' => $order['shipping']['shipping_name'],
            'cost_shipping' => $order['shipping']['cost_shipping'],
            'is_protect' => $order['shipping']['is_protect'] == false ? 'false' : 'true',
            'cost_protect' => $order['shipping']['cost_protect'],
            'is_cod' => $order['shipping']['is_cod'] == false ? 'false' : 'true',
        );
        $sdf['payinfo'] = $order['payinfo'];
        $sdf['pay_bn'] = $order['pay_bn'];
        $sdf['weight'] = $order['weight'];
        $sdf['title'] = $order['title'];
        $sdf['createtime'] = date('Y-m-d H:i:s',$order['createtime']);

        $_consignee_area = explode(':',$order['consignee']['area']);
        $consignee_area = explode('/',$_consignee_area[1]);
        $sdf['consignee'] = array(
            'name' => $order['consignee']['name'],
            'area_state' => $consignee_area[0],
            'area_city' => $consignee_area[1],
            'area_district' => $consignee_area[2],
            'addr' => $order['consignee']['addr'],
            'zip' => $order['consignee']['zip'],
            'telephone' => $order['consignee']['telephone'],
            'mobile' => $order['consignee']['mobile'],
            'email' => $order['consignee']['email'],
            'r_time' => $order['consignee']['r_time'],
        );

        $_consigner_area = explode(':',$order['consigner']['area']);
        $consigner_area = explode('/',$_consigner_area[1]);
        $sdf['consigner'] = array(
            'name' => $order['consigner']['name'],
            'area_state' => $consigner_area[0],
            'area_city' => $consigner_area[1],
            'area_district' => $consigner_area[2],
            'addr' => $order['consigner']['addr'],
            'zip' => $order['consigner']['zip'],
            'telephone' => $order['consigner']['telephone'],
            'mobile' => $order['consigner']['mobile'],
            'email' => $order['consigner']['email'],
        );

        $sdf['selling_agent'] = array('website'=>array(),'member_info'=>array());

        $sdf['member_info'] = array(
            'name' => '',
            'mobile' => '',
            'zip' => '',
            'addr' => '',
            'uname' => '',
            'telephone' => '',
            'email' => '',
        );

        $sdf['pmt_detail'] = array();

        if($_POST['payment']['trade_no']){
            $sdf['payments'][] = array(
                'trade_no' => $_POST['payments']['trade_no'],
                'money' => $_POST['payments']['money'],
                'pay_time' => $_POST['payments']['pay_time'],
                'account' => $_POST['payments']['account'],
                'bank' => $_POST['payments']['bank'],
                'pay_bn' => $_POST['payments']['pay_bn'],
                'paycost' => $_POST['payments']['paycost'],
                'pay_account' => $_POST['payments']['pay_account'],
                'paymethod' => $_POST['payments']['paymethod'],
                'memo' => $_POST['payments']['memo'],
            );
        }else{
            $sdf['payments'] = array();
        }

        $sdf['cost_item'] = $_POST['cost_item'];
        $sdf['is_tax'] = $order['is_tax'];
        $sdf['cost_tax'] = $order['cost_tax'];
        $sdf['tax_title'] = $order['tax_title'];
        $sdf['currency'] = 'CNY';
        $sdf['cur_rate'] = '1.0000';
        $sdf['score_u'] = '0';
        $sdf['score_g'] = '0';
        $sdf['discount'] = $_POST['discount'];
        $sdf['pmt_goods'] = $_POST['pmt_goods'];
        $sdf['pmt_order'] = $_POST['pmt_order'];
        $sdf['total_amount'] = $_POST['total_amount'];
        $sdf['cur_amount'] = $_POST['total_amount'];
        $sdf['payed'] = $_POST['payed'];
        $sdf['custom_mark'] = $_POST['custom_mark'];
        $sdf['mark_text'] = $_POST['mark_text'];
        $sdf['mark_type'] = $_POST['mark_type'];
        $sdf['tax_no'] = $_POST['tax_no'];
        $sdf['lastmodify'] = date('Y-m-d H:i:s');
        
        $objtype = $_POST['objtype'];
        $post = $_POST;
        if ($objtype && is_array($objtype)){
            $rs = kernel::single("ome_order_edit")->process_order_objtype($objtype,$post);
        }

        $

        $sdf['order_objects'] = array(
            array(
                'obj_type' => 'goods',
                'obj_alias' => '',
                'bn' => '',
                'oid' => '',
                'name' => '',
                'price' => '',
                'amount' => '',
                'pmt_price' => '',
                'sale_price' => '',
                'quantity' => '',
                'weight' => '',
                'score' => '',
                'order_items' => array(
                    array(
                        'item_type' => 'product',
                        'bn' => '',
                        'name' => '',
                        'product_attr' => array(
                            array('label'=>'颜色','value'=>'红色'),
                        ),
                        'quantity' => '',
                        'price' => '',
                        'amount' => '',
                        'pmt_price' => '',
                        'sale_price' => '',
                        'weight' => '',
                        'score' => '',
                        'status' => 'active',
                    ),
                ),
            ),
        );
        error_log(print_r($sdf,1),3,'d:/order.txt');
        error_log(print_r($rs,1),3,'d:/post.txt');
        

        ######组织订单接口所需数据结构######


    }
*/
    public function get_order_id($order_bn){
        $oOrder = app::get('ome')->model('orders');
        $order = $oOrder->getlist('order_id',array('order_bn'=>$order_bn),0,-1);
        print_r(json_encode($order[0]));
        exit;
    }



}