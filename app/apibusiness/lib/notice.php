<?php
/**
* 通知
*
* @category apibusiness
* @package apibusiness/lib/
* @author chenping<chenping@shopex.cn>
* @version $Id: notice.php 2013-3-12 14:37Z
*/
class apibusiness_notice
{
    const _APP_NAME = 'ome';
    /**
     * 通知作业单
     *
     * @param Array $tgOrder 原订单
     * @param Array $newOrder 更新后的订单
     * @return void
     * @author 
     **/
    public function notice_process_order($tgOrder,$newOrder)
    {
        // 原来是退款中或退款申请中的订单,更新后变已支付||部分支付||部分退款||全额退款
        if (in_array($tgOrder['pay_status'], array('6','7')) && $newOrder['pay_status']) {
            // 全额退款取消作业单
            if ($newOrder['pay_status'] == '5') {
                define('FRST_TRIGGER_OBJECT_TYPE','订单：未发货订单全额退款导致订单取消');
                define('FRST_TRIGGER_ACTION_TYPE','ome_order_func：update_order_pay_status');
                
                $refund_applyObj = app::get(self::_APP_NAME)->model('refund_apply');
                $refund_applyObj->check_iscancel($tgOrder['order_id']);

                $logModel = app::get(self::_APP_NAME)->model('operation_log');
                $logModel->write_log('order_edit@ome',$tgOrder['order_id'],'全额退款');

                return ;
            }
        }

        $is_reback = $this->notice_process_delivery($tgOrder,$newOrder);
        if ($is_reback) {
            //不管打回成功还是有失败，根据当前情况更新订单状态
            kernel::single('ome_order')->resumeOrdStatus($tgOrder);
        }
    }

    /**
     * 通知发货单
     *
     * @param Array $tgOrder 原订单
     * @param Array $newOrder 更新后的订单
     * @return void
     * @author 
     **/
    public function notice_process_delivery($tgOrder,$newOrder)
    {   
        // 收货人是否发生变更
        $consignee_change = false;
        $consignee_column = array('ship_name','ship_area','ship_addr','ship_zip','ship_tel','ship_email','ship_time','ship_mobile');
        foreach ($consignee_column as $key => $column) {
            if ($newOrder[$column]) {
                $consignee_change = true; break;
            }
        }

        $is_reback = false;
        if (in_array($tgOrder['pay_status'], array('6','7')) && $newOrder['pay_status']) {
            if ($newOrder['pay_status'] == '4') {
                // 发货单叫回
                $is_reback = $this->rebackdelivery($tgOrder);
            }
        } elseif ($consignee_change == true || $newOrder['consignee']) {
            // 收货地址发生变更
            $is_reback = $this->rebackdelivery($tgOrder);
        } elseif ($newOrder['order_objects']) {
            // 订单明细发生变更
            $is_reback = $this->rebackdelivery($tgOrder);
        }

        return $is_reback;
    }

    private function rebackdelivery($tgOrder)
    {
        if(in_array($tgOrder['process_status'], array('splitting','splited')) && $tgOrder['ship_status'] == '0'){

            $dlyObj  = app::get(self::_APP_NAME)->model('delivery');
            $dlyOrdObj = app::get(self::_APP_NAME)->model("delivery_order");
            $opObj = app::get(self::_APP_NAME)->model('operation_log');

            $data = $dlyOrdObj->getList('*',array('order_id'=>$tgOrder['order_id']),0,-1);
            if ($data){
                foreach ($data as $v){
                    $dly = $dlyObj->dump($v['delivery_id'],'process,status,parent_id,is_bind,delivery_id,delivery_bn');

                    //已经发货或失败失效的发货单跳过处理
                    if ($dly['process'] == 'true' || in_array($dly['status'],array('failed', 'cancel', 'back', 'succ','return_back'))) continue;

                    //暂时保存发货单id与发货单号的对应关系
                    $dlybns[$dly['delivery_id']] = $dly['delivery_bn'];

                    if ($dly['parent_id'] == 0 && $dly['is_bind'] == 'true'){
                        $bind[$v['delivery_id']]['delivery_id'] = $v['delivery_id'];
                    }elseif ($dly['parent_id'] == 0){
                        $dlyos[$v['delivery_id']][] = $v['delivery_id'];
                    }else{
                        $mergedly[$v['delivery_id']] = $v['delivery_id'];
                        $bind[$dly['parent_id']]['items'][] = $v['delivery_id'];
                    }
                }
            }

            $memo = '由于订单明细或支付状态或收货人信息发生变化';
            //如果是合并发货单的话
            if ($bind){
                foreach ($bind as $k => $i){
                    
                    #拆分发货单
                    $result = $dlyObj->splitDelivery($i['delivery_id'], $i['items']);
                    if ($result){
                        $dlyObj->rebackDelivery($i['items'], '', true);
                        $opObj->write_log('order_back@ome', $tgOrder['order_id'], '发货单'.$dlybns[$i['delivery_id']].'打回+'.'备注:'.$memo);
                        foreach ($i['items'] as $i){
                            $dlyObj->updateOrderPrintFinish($i, 1);
                        }
                    }
                }
            }

            //单个发货单
            if ($dlyos){
                foreach ($dlyos as $v){
                    $dlyObj->rebackDelivery($v, '', true);
                    $opObj->write_log('order_back@ome', $tgOrder['order_id'], '发货单'.$dlybns[$v[0]].'打回+'.'备注:'.$memo);
                    $dlyObj->updateOrderPrintFinish($v, 1);
                }
            }

            return true;
        }
    }

}