<?php
class console_receipt_reship{
    private static $is_check = array(
        'FINISH'=>'8',
        
    );
    function updateStatus($data,&$msg){
        $oReship = app::get('ome')->model('reship');
        $oReship_items = app::get('ome')->model('reship_items');
        $oProducts = app::get('ome')->model('products');
        $reship_bn = $data['reship_bn'];
        $reship = $this->checkExist($reship_bn);
        $reship_id = $reship['reship_id'];
        if (!$reship) {
            $msg = '退货单号不存在!';
            return false;
        }
        $type = $reship['return_type'];
        
        if ($reship['is_check'] == '1') {
            kernel::single('ome_return_rchange')->accept_returned($reship_id,'3',$error_msg);
        }
        
        $status = $data['status'];
        $items = $data['items'];

        if ($items) {
            foreach ($items as  $item) {
                $bn = $item['bn'];
                $check_num = $item['normal_num'];
                $defective_num = $item['defective_num'];
                $item_add = array();
                $reship_items = $oReship_items->dump(array('reship_id'=>$reship_id,'bn'=>$item['bn'],'return_type'=>array('return','refuse')),'normal_num,defective_num,item_id');
                if (!$reship_items) {
                    $products = $oProducts->dump(array('bn'=>$item['bn']),'name,product_id');
                    $item_add['defective_num'] = $item['defective_num'];
                    $item_add['normal_num'] = $item['normal_num'];
                    $item_add['return_type'] = 'return';
                    $item_add['reship_id'] = $reship_id;
                    $item_add['bn'] = $item['bn'];
                    $item_add['product_name'] = $products['name'];
                    $item_add['product_id'] = $products['product_id'];
                    $item_add['num'] = $item['normal_num']+$item['defective_num'];
                    $item_add['branch_id'] = $reship['branch_id'];
                }else{
                    $item_add['item_id'] = $reship_items['item_id'];
                    $item_add['defective_num'] = $item['defective_num']+$reship_items['defective_num'];
                    $item_add['normal_num'] = $item['normal_num']+$reship_items['normal_num'];
                    #更新收货数量
                    $SQL = "UPDATE sdb_ome_return_process_items SET 
                               is_check='true' WHERE item_id in (SELECT t.item_id FROM 
                                (select * from sdb_ome_return_process_items WHERE reship_id=".$reship_id."  AND bn='$bn' AND is_check='false' LIMIT 0,".$check_num.") as t )";
                   $oReship_items->db->exec($SQL);
                   if ($defective_num>0) {
                       $SQL = "UPDATE sdb_ome_return_process_items SET 
                               is_check='true' WHERE item_id in (SELECT t.item_id FROM 
                                (select * from sdb_ome_return_process_items WHERE reship_id=".$reship_id."  AND bn='$bn' AND is_check='false' LIMIT 0,".$defective_num.") as t )";
                   $oReship_items->db->exec($SQL);
                   }
                }
                $oReship_items->save($item_add);
            }
        }
        if ($status == 'FINISH') {
            $reship_update_data = array('is_check'=>'11');
            
        }else{
            $reship_update_data = array('is_check'=>'13');
        }
        $oReship->update($reship_update_data,array('reship_id'=>$reship_id));
        $reship_autoConf = app::get('ome')->getConf('ome.reship.auto_finish');

        if (in_array($type,array('return','change')) && $reship_autoConf=='true'){
        //判断数量。无差异直接完成
        //判断剩余数量
            $auto_flag = true;
            
            $item_list =  $oReship_items->db->select("select normal_num,defective_num,num,bn FROM sdb_ome_reship_items WHERE reship_id=".$reship_id." AND return_type='return'");
            foreach ($item_list as $reshipitem){
                $branch_num = intval($reshipitem['normal_num']) + intval($reshipitem['defective_num']);
                if (intval($reshipitem['num'])!=$branch_num){
                    $auto_flag = false;
                    break;
                }
                $refund = $oReship_items->Get_refund_count( $reship['order_id'], $reshipitem['bn'] ,$reship_id);
                $total_nums = intval($reshipitem['total_nums']);
             
                if ($total_nums>$refund){
                    $auto_flag = false;
                    break;
                }
            }
            if ($auto_flag){
                if($oReship->finish_aftersale($reship_id)){
                    $normal_reship_item = $oReship_items->getList('*',array('reship_id'=>$reship_id,'normal_num|than'=>0),0,1);
                    $reship_item = $oReship_items->getList('*',array('reship_id'=>$reship_id,'defective_num|than'=>0),0,1);
                    if (count($normal_reship_item)>0){
                        $reshipLib = kernel::single('siso_receipt_iostock_reship');
                        $result = $reshipLib->create(array('reship_id'=>$reship_id), $data, $msg);
                    }

                    if (count($reship_item)>0) {
                        $damagedreshipLib = kernel::single('siso_receipt_iostock_reshipdamaged');
                        $result = $damagedreshipLib->create(array('reship_id'=>$reship_id), $data, $msg);
                    }

                    #更新收货表为入库
                    //反审核质检
                    $process_sql = "UPDATE sdb_ome_return_process_items SET is_check='true' WHERE reship_id=".$reship_id." AND is_check='false'";
                    $oReship->db->exec($process_sql);                    
                   
                }
            }

        }
        if ($type=='refuse') {//拒收时流程
            $this->update_returnreship($reship);
            
        }
        //判断是否
        return true;
    }

    /**
     *
     * 检查退货单是否存在判断
     * @param array $reship_bn 退货单编号
     */
    public function checkExist($reship_bn){
        $oReship = app::get('ome')->model('reship');
        $reship = $oReship->dump(array('reship_bn'=>$reship_bn),'*');

        return $reship;
    }

    public function checkValid($reship_bn,$status,&$msg){
        $reship = $this->checkExist($reship_bn);
        
        $is_check = $reship['is_check'];
        // echo $is_check;
        switch($status){
            case 'PARTIN':
            case 'FINISH':
                
                if ($is_check == '5' || $is_check == '7' || $is_check == '9' || $is_check == '10' || $is_check == '11') {
                    $msg = '所在状态不能入库';
                   
                    return false;
                }else{
                    return true;
                }
                break;
            case 'CANCEL':
            case 'CLOSE':
                if ($is_check == '7' || $is_check == '8' || $is_check == '11' || $is_check == '13') {
                    $msg = '所在状态决定了不可以取消';
                    return false;
                }else{
                    return true;
                }
                break;
        }
        
        return true;
    }

   
    /**
     * 取消退货单
     * @param   array $data
     * @return
     * @access  public or private
     * @author sunjing@shopex.cn
     */
    function cancel($data,&$msg)
    {
        $oReship = app::get('ome')->model('reship');
        $reship = $this->checkExist($data['reship_bn']);
        $reship_id = $reship['reship_id'];
        if (!$reship) {
            $msg = '退货单号不存在!';
        }
        $reship_update_data = array('is_check'=>'5');
        if ($reship['return_type'] =='change' && $reship['is_check']=='1') {
            kernel::single('console_reship')->change_freezeproduct($reship_id,'-');
        }
        $oReship->update($reship_update_data,array('reship_id'=>$reship_id));
        return true;
    } // end func

    
    /**
     * 退货追回入库
     * @param 
     * @return 
     * @access  public
     * @author sunjing@shopex.cn
     */
    function update_returnreship($reship)
    {

        $order_id = $reship['order_id'];
        $logi_no = $reship['logi_no'];
        $reship_id = $reship['reship_id'];
        $items_detailObj = app::get('ome')->model('delivery_items_detail');
        $operationLogObj = app::get('ome')->model('operation_log');
        $deliveryObj = app::get('ome')->model('delivery');
        $orderObj = app::get('ome')->model('orders');
        $productsObj = app::get('ome')->model('products');
        $oReship_item = app::get('ome')->model('reship_items');
        $deliveryInfo = $deliveryObj->dump(array('logi_no'=>$logi_no),'*');
        $delivery_id = $deliveryInfo['delivery_id'];
        $orderItems = $items_detailObj->getlist('*',array('order_id'=>$order_id,'delivery_id'=>$delivery_id));
        $orderdata = $orderObj->dump($order_id);
        //发货单关联订单sendnum扣减
        foreach($orderItems as $orderitem){

            $orderObj->db->exec('UPDATE sdb_ome_order_items SET return_num=return_num+'.$orderitem['number'].' WHERE order_id='.$order_id.' AND bn=\''.$orderitem['bn'].'\' AND obj_id='.$orderitem['order_obj_id']);

        }
        //订单相关状态变更

        kernel::single('ome_delivery_refuse')->update_orderStatus($order_id);
        //增加拒收退货入库明细
        $normal_reship_item = $oReship_item->getList('*',array('reship_id'=>$reship_id,'normal_num|than'=>0),0,1);
        $defective_reship_item = $oReship_item->getList('*',array('reship_id'=>$reship_id,'defective_num|than'=>0),0,1);

        if (count($normal_reship_item)>0){
            $reshipLib = kernel::single('siso_receipt_iostock_refuse');
            $result = $reshipLib->create(array('reship_id'=>$reship_id), $data, $msg);
        }
                    
        if (count($defective_reship_item)>0) {
            $damagedreshipLib = kernel::single('siso_receipt_iostock_reshipdamaged');
            $result = $damagedreshipLib->create(array('reship_id'=>$reship_id), $data, $msg);
        }
        //负销售单
        if ($orderdata['status'] == 'finish') {
            kernel::single('sales_aftersale')->generate_aftersale($reship_id,'refuse');
        }
        //$deliveryObj->db->exec("UPDATE sdb_ome_delivery SET `status`='return_back' WHERE delivery_id=".$delivery_id." AND `status`='succ'");
        $deliveryObj->db->exec("UPDATE sdb_ome_reship SET is_check='7' WHERE reship_id=".$reship_id.""); 
      
        //订单添加相应的操作日志
        $operationLogObj->write_log('order_refuse@ome', $order_id, "发货后退回，订单做退货处理");

    }
}

?>