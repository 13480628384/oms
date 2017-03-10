<?php
/**
* @copyright shopex.cn
* @author Kris.wang 406048119@qq.com
* @version ocs
*/
class finance_verification
{
    /*
    **保存核销日志
    **@params array $sdf = array(
    ‘op_time’=>’操作时间’,
    ‘op_name’=>’操作人’,
    ‘type=>’核销还是应收互冲’,
    ‘money’=>‘核销金额’,
    ‘content’=>’详情’,
    ‘items’=>array(
    '0'=>array(
    ‘bill_id’=>’单据id’,
    ‘bill_bn’=>’单据bn’,
    ‘type’=>’是应收单据还是实收单据’, 
    ‘money’=>’核销金额’,
    ‘trade_time’=>‘账期’,
    ),     
    ),
    );
    **@return array('status'=> 'success/fail','msg'=>'错误信息') 
    */
    public function do_save(&$sdf){
        $rs = $this->verify_data($sdf);
        if($rs['status'] == 'fail') return $rs;
        $res = array('status'=> 'success','msg'=>'');
        $veriObj = app::get('finance')->model('verification');
        $veriitemObj = app::get('finance')->model('verification_items');
        //开启事务
        $db = kernel::database();
        $db->beginTransaction();
        $main = array(
            'log_bn'=>$this->gen_log_bn(),
            'op_time'=>$sdf['op_time'],
            'op_name'=>$sdf['op_name'],
            'type'=>$sdf['type'],
            'money'=>$sdf['money'],
            'content'=>serialize($sdf['content']),
            );
        if(!$veriObj->save($main)){
            $res = array('status'=> 'fail','msg'=>'主数据保存失败');
            $db->rollBack();
            return ($res);
        }
        if($sdf['items']){
            foreach($sdf['items'] as $v){
              $items = array(
                'log_id'=>$main['log_id'],
                'bill_id'=>$v['bill_id'],
                'bill_bn'=>$v['bill_bn'],
                'type'=>$v['type'],
                'money'=>$v['money'],
                'trade_time'=>$v['trade_time'],
                );
              if(!$veriitemObj->save($items)){
                $res = array('status'=> 'fail','msg'=>'明细数据保存失败');
                $db->rollBack();
                return ($res);
            }
        }
    }
    $db->commit();
    return $res;
    }

    /*
    *生成销售应收单据号
    */
    public function gen_log_bn(){
        $i = rand(0,99999);
        $veriObj = app::get('finance')->model('verification');
        do{
            if(99999==$i){
                $i=0;
            }
            $i++;
            $log_bn="LOG".date('Ymd').str_pad($i,5,'0',STR_PAD_LEFT);
            $row = $veriObj->getlist('log_id',array('log_bn'=>$log_bn));
        }while($row);
        return $log_bn;
    }


    /*
    **验证数据的正确性
    **@params array $sdf = array(
    ‘op_time’=>’操作时间’,
    ‘op_name’=>’操作人’,
    ‘type=>’核销还是应收互冲’,
    ‘money’=>‘核销金额’,
    ‘content’=>’详情’,
    ‘items’=>array(
    ‘bill_id’=>’单据id’,
    ‘bill_bn’=>’单据bn’,
    ‘type’=>’是应收单据还是实收单据’, 
    ‘money’=>’核销金额’,
    ‘trade_time’=>‘账期’,
    ),     
    );
    **@return array('status'=>'success/fail','msg'=>'');
    */
    public function verify_data(&$data){
        $res = array('status'=>'success','msg'=>'');
        if(empty($data['op_name'])){
            $res = array('status'=>'fail','msg'=>'操作人不能为空');
        }
        if($data['type'] == ''){
            $res = array('status'=>'fail','msg'=>'业务类型不能为空');
        }
        if($data['money'] ==''){
            $res = array('status'=>'fail','msg'=>'应收金额不能为空');
        }
        foreach ($data['items'] as $v) {
            if($v['bill_id'] == ''){
                $res = array('status'=>'fail','msg'=>'账单id不能为空');
            }
            if($v['bill_bn'] == ''){
                $res = array('status'=>'fail','msg'=>'账单bn不能为空');
            }
            if($v['trade_time'] == ''){
                $res = array('status'=>'fail','msg'=>'账单日不能为空');
            }
        }
        return $res;
    }

    //获取类型的名称 应收互冲核销(0)，应收实收核销(1)
    public function get_name_by_type($type='',$flag = ''){
        $data = array(
            '0'=>'应收互冲核销',
            '1'=>'应收实收核销',
            );
        if($flag) return $data;
        return $data[trim($type)];
    }

    /*
    **撤销核销
    **@params $log 核销日志id
    **@return true/false 字符窜
    */
    public function do_cancel($log_id){
    //开启事务
        $db = kernel::database();
        $db->beginTransaction();
        $veriObj = app::get('finance')->model('verification');
        $veriitemObj = app::get('finance')->model('verification_items');
        $arObj = app::get('finance')->model('ar');
        $billObj = app::get('finance')->model('bill');
        $log_data = $veriitemObj->getList('item_id,bill_id,type,money',array('log_id'=>$log_id));
        $tmp = $delelt_item = array();
        foreach($log_data as $v){
            $tmp[$v['type']][] = $v;
            $delelt_item[] = $v['item_id'];
        }
        foreach($tmp as $type=>$v){
            if($type==0){
                foreach($v as $value){
                    $rs_bill = $billObj->do_cancel($value['bill_id'],$value['money']);
                    if($rs_bill == 'false'){
                        $bill_flag = 'true';
                        break;
                    }
                }
            }else{
                foreach($v as $value){
                    $rs_ar = $arObj->do_cancel($value['bill_id'],$value['money']);
                    if($rs_ar == 'false'){
                        $ar_flag = 'true';
                        break;
                    }
                }
            }
        }
        if($ar_flag == 'true' || $bill_flag == 'true'){
            $db->rollBack();
            return 'false';
        }
        $rs = $veriObj->delete(array('log_id'=>$log_id));
        $rs_item = $veriitemObj->delete(array('item_id'=>$delelt_item));
        if(!$rs || !$rs_item){
            $db->rollBack();
            return 'false';
        }
        $db->commit();
        return 'true';
    }

    /*
    **获取下一单数据
    **@params $bill_id 时候单据id
    **
    */
    public function get_next_data($bill_id){
        $billObj = app::get('finance')->model('bill');
        $next_data =  $billObj->getList('bill_id',array('status|noequal'=>2,'charge_status'=>1,'fee_type_id'=>'1'));        
        foreach($next_data as $k=>$v){
            if($v['bill_id'] == $bill_id){
                return $next_data[$k+1];
            }
        }
    }
}