<?php
/**
 * 账单导入数据验证类
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_verify{

    //验证订单号是否存在
    public static function isOrder($order_bn='',$msg='订单号不存在'){
        $result['status'] = 'success';
        $isExists = finance_io_bill_func::order_is_exists($order_bn);
        if($isExists == false){
            $result['msg'] = $msg;
            $result['status'] = 'fail';
        }else{
            $result['order'] = $isExists['order'];
        }
        return $result;
    }

    //金额验证
    public static function isPrice($price='',$msg=''){
        $result['status'] = 'success'; 
        if($price!=''){
            if(!preg_match('/^(\d+)(\.\d{1,2})?$|^(-\d+)(\.\d{1,2})?$/',$price,$match) || strlen($match[1]) >= 14){
                $result['msg'] = $msg;
                $result['status'] = 'fail';
            }
        }
        return $result;
    }

    //日期格式验证
    public static function isDate($date='',$msg=''){
        $result['status'] = 'success';
        if($date && !preg_match('/^((\d{2,4})(-|\/)(\d{1,2})(-|\/)(\d{1,2})|(\d{4})(-|\/)(\d{1,2})(-|\/)(\d{1,2}) ((\d{1,2})(:|)(\d{1,2})|(\d{1,2})(:|)(\d{1,2})(:|)(\d{1,2})))$/',$date)){
            $result['msg'] = $msg;
            $result['status'] = 'fail';
        }
        return $result;
    }

    public static function checkInitTime($date='',$checkTime='',$msg=''){
        $result['status'] = 'success';
        $date = $date == '' ? time() : strtotime($date);

        #账期
        $initTime = app::get('finance')->getConf('finance_setting_init_time');
        $_initTime = strtotime($initTime['year'].'-'.$initTime['month'].'-'.$initTime['day']);

        switch($checkTime){
            case 'before':#before只允许导账期之前的数据
                if($date > $_initTime){
                    $result = array(
                        'status' => 'fail',
                        'msg' => '只可导入设置账期之前的数据',
                    );
                }
                break;
            case 'after':#after只允许导账期之后的数据
                if($date < $_initTime){
                    $result = array(
                        'status' => 'fail',
                        'msg' => '只可导入设置账期之后的数据',
                    );
                }
                break;
            default:
                $result = array(
                    'status' => 'fail',
                    'msg' => '账期类型错误',
                );
                break;
        }

        return $result;
    }

    public static function checkOrder($order_bn='',$msg='订单号不存在'){
        $result['status'] = 'success';
        static $hasCheckOrder;

        if ( isset($hasCheckOrder[$order_bn]) ) {
            return $hasCheckOrder[$order_bn];
        }

        $order = finance_io_bill_func::order_is_exists($order_bn);
        if($order == false){
            $result['msg'] = $msg;
            $result['status'] = 'fail';
        }else{
            $result['order'] = $order;
        }
    
        $hasCheckOrder[$order_bn] = $result;

        return $result;
    }

    //账期验证
    public static function isTaskCheckInitTime($date='',$task_id=''){
        $result['status'] = 'success';
        $date = $date == '' ? time() : strtotime($date);
        if($task_id){
            $public = finance_io_bill_func::get_public($task_id);
            $checkTime = $public['checkTime'];
            #账期
            $initTime = app::get('finance')->getConf('finance_setting_init_time');
            $_initTime = strtotime($initTime['year'].'-'.$initTime['month'].'-'.$initTime['day']);

            switch($checkTime){
                case 'before':#before只允许导账期之前的数据
                    if($date > $_initTime){
                        $result = array(
                            'status' => 'fail',
                            'msg' => '只可导入设置账期之前的数据',
                        );
                    }
                break;
                case 'after':#after只允许导账期之后的数据
                    if($date < $_initTime){
                        $result = array(
                            'status' => 'fail',
                            'msg' => '只可导入设置账期之后的数据',
                        );
                    }
                break;
            }
        }
        return $result;
    }

    public static function checkFee($fee_item='',$msg=''){
        $result['status'] = 'success';
        static $fee;

        if ( isset($fee[$fee_item]) ) {
            return $fee[$fee_item];
        }

        $feeExist = kernel::single('finance_bill')->get_fee_by_fee_item($fee_item);
        if(!$feeExist){
            $result['msg'] = $msg;
            $result['status'] = 'fail';
        } else {
            $result['fee'] = $feeExist;
        }
        
        $fee[$fee_item] = $result;
    
        return $result;
    }

    public static function checkEmpty($data='',$msg=''){
        $result = array('status'=>'success','msg'=>'');
        $required = array('order_bn'=>'业务单据号','fee_obj'=>'费用对象','unique_id'=>'唯一标识');
        foreach ($required as $key=>$value) {
            if (empty($data[$key])) {
                $result['status'] = 'fail';
                $result['msg'] = $value.'不能为空！';

                return $result;
            }
        }

        return $result;
    }

    public static function checkUniqueId($unique_id='',$msg=''){
        $result = array('status'=>'success','msg'=>'');
        
        $billObj = app::get('finance')->model('bill');
        $bill = $billObj->getlist('bill_id',array('unique_id'=>$unique_id),0,1);
        if(!empty($bill[0]['bill_id'])){
            $result = array('status'=> 'fail','msg'=>'该单据已存在','msg_code'=>'exists');
            return $result;
        }

        return $result;
    }


}
?>