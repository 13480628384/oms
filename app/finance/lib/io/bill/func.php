<?php
/**
 * 账单导入通用函数类
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_func{

    public static function save($sdf = array(),&$mdl){
        $result = array('status'=>'success');
        $service = kernel::single('finance_bill');
        $result = $service->do_save($sdf);
        return $result;
    }

    public static function ar_save($sdf = array(),&$mdl){
        $result = array('status'=>'success');
        $service = kernel::single('finance_ar');
        $service->isTransaction = false;
        $result = $service->do_save($sdf);
        return $result;
    }

    public static function unique_id($arr = array()){
        return finance_func::unique_id($arr);
    }

    public static function get_public($task_id = ''){
        $public_info = array();
        if($task_id){
            $task_model = app::get('omeio')->model('task');
            $taskinfo = $task_model->getList('`public`',array('task_id'=>$task_id),0,1);
            $public_info = $taskinfo[0]['public'];
        }
        return $public_info;
    }

    public static function order_is_exists($order_bn = ''){
        $rs = kernel::single('finance_func')->order_is_exists($order_bn);
        return $rs;
    }

    public static function getShopByShopID($shop_id = ''){
        $rs = kernel::single('finance_func')->getShopByShopID($shop_id);
        return $rs;
    }

}
?>