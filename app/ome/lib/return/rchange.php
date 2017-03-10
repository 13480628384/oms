<?php
/**
* 退换货处理类
*/
class ome_return_rchange
{

    function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 计算差价
     *
     * @return void
     * @author
     **/
    public function calDiffAmount($post)
    {
        $mathLib = kernel::single('eccommon_math');

        # 应退金额
        $tmoney = 0;
        if (isset($post['return']['goods_bn']) && is_array($post['return']['goods_bn'])) {
            foreach ($post['return']['goods_bn'] as $pbn) {
                $tmoney += $post['return']['price'][$pbn] * $post['return']['num'][$pbn];
            }
        }
        $tmoney = $mathLib->getOperationNumber($tmoney);

        # 换出金额
        $change_amount = 0;
        if (isset($post['change']['goods_bn']) && is_array($post['change']['goods_bn'])) {
            foreach ($post['change']['goods_bn'] as $pbn ) {
                $change_amount += $post['change']['price'][$pbn] * $post['change']['num'][$pbn];
            }
        }

        if (isset($post['change']['product']['price']) && is_array($post['change']['product']['price'])) {
            foreach ($post['change']['product']['price'] as $pbn => $price) {
                $change_amount += $price*$post['change']['product']['num'][$pbn];
            }
        }
        if (isset($post['change']['pkg']['price']) && is_array($post['change']['pkg']['price'])) {
            foreach ($post['change']['pkg']['price'] as $pbn => $price) {
                $change_amount += $price*$post['change']['pkg']['num'][$pbn];
            }
        }
        $change_amount = $mathLib->getOperationNumber($change_amount);

        # 折旧费
        $bmoney = $mathLib->getOperationNumber($post['bmoney']);
        #补偿费用
        $bcmoney = $mathLib->getOperationNumber($post['bcmoney']);

        # 补差价
        $diff_money = $post['diff_money'];
        if ($post['diff_order_bn'] && !$diff_money) {
            $orderModel = $this->app->model('orders');
            $diff_money = $orderModel->select()->columns('total_amount')
                            ->where('order_bn=?',$post['diff_order_bn'])
                            ->where('status=?','active')
                            ->where('pay_status=?','1')
                            ->where('ship_status=?','0')
                            ->instance()->fetch_one();
        }
        $diff_money = $mathLib->getOperationNumber($diff_money);

        # 邮费
        $cost_freight_money = $mathLib->getOperationNumber($post['cost_freight_money']);

        # 公式: 合计金额=应退金额+补偿费用＋补差价费用-换出商品金额-折旧(其他费用)-买家承担的邮费
        $totalmoney = $tmoney+$bcmoney+$diff_money - $bmoney - $change_amount - $cost_freight_money;
        $totalmoney = $mathLib->getOperationNumber($totalmoney);

        $result = array(
            'tmoney' => $tmoney,
            'change_amount' => $change_amount,
            'bmoney' => $bmoney,
            'diff_money' => $diff_money,
            'totalmoney' => $totalmoney,
            'bcmoney'=>$bcmoney,
            'cost_freight_money' => $cost_freight_money,
        );

        return $result;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author
     **/
    public function accept_returned($reship_id,$status,&$msg)
    {
        $oOperation_log = $this->app->model('operation_log');
        $Oreship        = $this->app->model('reship');
        $oProduct_pro   = $this->app->model('return_process');

        $oProduct_pro_detail = $oProduct_pro->product_detail($reship_id);
        $reship              = $Oreship->dump(array('reship_id'=>$reship_id),'is_check,return_id,reason');
        if($reship['is_check'] == '3'){
            $msg = '改单据已验收过!';
            return false;
        }

        //增加售后收货前的扩展
        foreach(kernel::servicelist('ome.aftersale') as $o){
            if(method_exists($o,'pre_sv_charge')){
                if(!$o->pre_sv_charge($_POST,$memo)){
                     $msg = $memo;
                    return false;
                }
            }
        }

        $data['branch_name'] = $oProduct_pro_detail['branch_name'];
        $data['memo'] = $_POST['info']['memo'];
        $data['shipcompany'] = $_POST['info']['shipcompany'];
        $data['shiplogino'] = $_POST['info']['shiplogino'];
        $data['shipmoney'] = $_POST['info']['shipmoney'];
        $data['shipdaofu'] = $_POST['info']['daofu'] == 1 ? 1 : 0;
        $data['shiptime'] = time();



        if($status == '4'){
            $addmemo = ',拒绝收货';
            $refuse_memo = unserialize($reship['reason']);
            //$refuse_memo .= '#收货原因#'.$_POST['info']['refuse_memo'];
            $refuse_memo['receive'] = $_POST['info']['refuse_memo'];
            $prodata = array('reship_id'=>$reship_id,'reason'=>serialize($refuse_memo));
            $oProduct_pro->cancel_process($prodata);
        }elseif($status == '3'){
            $prodata = array('reship_id'=>$reship_id,'process_data'=>serialize($data));
            $addmemo = ',收货成功';
            $oProduct_pro->save_return_process($prodata);
        }
        $filter = array(
            'is_check'=>$status,
            //'return_logi_name'=>$data['shipcompany'],
            //'return_logi_no'=>$data['shiplogino'],
        );
        $Oreship->update($filter,array('reship_id'=>$reship_id));

        if($reship['return_id']){
            $Oproduct = $this->app->model('return_product');
            $recieved = 'false';
            if($status == '3'){
               $recieved = 'true';
            }
            $Oproduct->update(array('process_data'=>serialize($data),'recieved'=>$recieved),array('return_id'=>$reship['return_id']));
        }


        $Oreship_items = $this->app->model('reship_items');
        $oBranch = $this->app->model('branch');
        $reship_items = $Oreship_items->getList('branch_id',array('reship_id'=>$reship_id,'return_type'=>'return'));
        $branch_name = array();
        foreach($reship_items as $k=>$v){
            $branch_name[] = $oBranch->Get_name($v['branch_id']);
        }
        $add_name = array_unique($branch_name);
        $memo='仓库:'.implode(',', $add_name).$addmemo;
        $oOperation_log = $this->app->model('operation_log');
        if($reship['return_id']){
            $oOperation_log->write_log('return@ome',$reship['return_id'],$memo);
        }
        $oOperation_log->write_log('reship@ome',$reship_id,$memo);

       if($oProduct_pro_detail['return_id']){
           //售后申请状态更新
            foreach(kernel::servicelist('service.aftersale') as $object=>$instance){
                if(method_exists($instance,'update_status')){
                    $instance->update_status($oProduct_pro_detail['return_id']);
                }
            }
       }


       //增加售后收货前的扩展
        foreach(kernel::servicelist('ome.aftersale') as $o){
            if(method_exists($o,'after_sv_charge')){
                $o->after_sv_charge($_POST);
            }
        }
    }

    /**
     * @description 验证补差价订单
     * @access public
     * @param void
     * @return void
     */
    public function diffOrderValidate($post,&$errormsg)
    {
        if (!$post['order_id']) {
            $errormsg = $this->app->_('请先选择补差价订单!');
            return false;
        }

        if (!$post['return_order_id']) {
            $errormsg = $this->app->_('请先选择退换货订单!');
            return false;
        }

        $orderModel = $this->app->model('orders');
        $order = $orderModel->getList('*',array('order_id'=>$post['order_id']),0,1);
        $order = $order[0];
        if (!$order) {
            $errormsg = $this->app->_("订单号【{$post['order_id']}】不存在!");
            return false;
        }

        $reshipModel = $this->app->model('reship');
        $reship = $reshipModel->getList('*',array('diff_order_bn'=>$order['order_bn'],'is_check|noequal'=>'5'),0,1);
        if ($reship) {
            $errormsg = $this->app->_("补差价订单已经被其他售后换货单据使用!");
            return false;
        }

        $orderItemModel = $this->app->model('order_items');
        $order['items'] = $orderItemModel->getList('*',array('order_id'=>$order['order_id']));

        $memberModel = $this->app->model('members');
        $member = $memberModel->getList('*',array('member_id'=>$order['member_id']));
        $order['member'] = $member[0];

        return $order;
    }


    
    /**
     * 格式化换货数据
     * @param  
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function format_Rchangedata($change)
    {
        $data = array();
       
        $productObj = app::get('omepkg')->model('pkg_product');
        if ($change) {
            foreach ($change as $ck=>$cv ) {
            $item_type = $ck;
          
            foreach ($cv['bn'] as $products ) {
                
                $bn = $products;
                $product_id = $cv['product_id'][$bn];
                $num = $cv['num'][$bn];
                $name = $cv['name'][$bn];
                $price = $cv['price'][$bn];
                $item_id = $cv['item_id'][$bn]; 
                $items = array();
                if ($item_type=='pkg') {
                    $filter['goods_id'] = $product_id;
                    $pkgproducts = $productObj->getList('*',$filter,0,-1);
                    foreach ( $pkgproducts as $pkg ) {
                        $items[] = array(
                        'bn'=>$pkg['bn'],
                        'product_id'=>$pkg['product_id'],
                        'num'=>$pkg['pkgnum']*$num,
                        'name'=>$pkg['name'],
                        'price'=>$price,
                        
                        );
                    }
                }
                $objects = array(
                    'bn'=>$bn,
                    'product_id'=>$product_id,
                    'num'=>$num,
                    'name'=>$name,
                    'price'=>$price,
                    'obj_type'=>$item_type,
                    'item_type'=>$item_type,
                    'items'=>$items,
                    'item_id'=>$item_id,
                );
                $data['objects'][] = $objects;
            }
            
        }
        }
        return $data;
    }

    
    /**
     * 获取换货明细
     * @param   
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function getChangelist($reship_id,$branch_id)
    {
        $db = kernel::database();
        $bProductObj = app::get('ome')->model('branch_product');
        $item_list = $db->select("SELECT i.* FROM sdb_ome_reship_items as i WHERE i.reship_id=".$reship_id." AND i.obj_id=0 AND i.return_type='change'");
       
        $changelist = array();
        foreach ($item_list as &$items ) {
            $items['type'] = 'change';
            $items['item_type'] = 'product';
            $items['name'] = $items['product_name'];
            $product_id = $items['product_id'];
            $sale_store = $bProductObj->getAvailableStore($branch_id,array($product_id));
            $items['sale_store'] = $sale_store[$product_id];
            $changelist[] = $items;
            
        }
        $obj_list = $db->select("SELECT * FROM sdb_ome_reship_objects WHERE reship_id=".$reship_id."");
        $objrow = array();
        foreach ($obj_list as &$obj ) {
            $obj['type'] = 'change';
            $obj['item_type'] = 'pkg';
            $obj_id = $obj['obj_id'];
            $obj['product_id'] = $obj['product_id'];
            $obj['name'] = $obj['product_name'];
            $obj['item_id'] = $obj_id;
            $product_list = $db->select("SELECT *  FROM sdb_ome_reship_items WHERE reship_id=".$reship_id." AND obj_id=".$obj_id." AND return_type='change'");
            $items = array();
            foreach ($product_list as $products ) {
                $product_id = $products['product_id'];
                $sale_store = $bProductObj->getAvailableStore($branch_id,array($product_id));
                
                $products['sale_store'] = $sale_store[$product_id];
                $items[] = $products;
            }
            
            $obj['items'] = $items;
            $changelist[] = $obj;
        }
       
        return $changelist;
    }
}
