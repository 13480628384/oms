<?php
/**
* @copyright shopex.cn
* @author Kris.wang 406048119@qq.com
* @version ocs
*/
class finance_ar
{
    //默认开启式事务
    public $isTransaction = true;
    /*
    **保存应收账单
    **@params array $sdf = array(
        ‘trade_time’=>’ 账单日期’,
        ‘member’=>’客户/会员’,
        ‘type=>’业务类型’,
        ‘order_bn’=>‘业务单据bn’,
        ‘relate_order_bn’ ‘=>’关联订单ID，专指售后换货’,
        ‘channel_id’=>’渠道id’,
        ‘channel_name’=>‘渠道名称’,
        ‘sale_money’=>’ 商品成交金额’,
        ‘fee_money’=>’运费’,
        ‘money=>’ 商品成交金额+运费’,
        ‘serial_number’=>‘默认销售单据号，没有自定义规则’,
        ‘unique_id’=>‘md5()’,
        ‘memo’=>‘备注’,
        ‘items’=>array(
        ‘bn’=>’’,
        ‘name’=>’’,
        ‘num’=>’’, 
        ‘money’=>’ 该商品的最终成交金额’,
        ),     
    );
    **@params array('status'=> 'success/fail','msg'=>'错误信息') 
    **@return array('status'=> 'success/fail','msg'=>'错误信息') 
    */
    public function do_save(&$sdf,$isTransaction = true){
        $rs = $this->verify_data($sdf);
        if($rs['status'] == 'fail') return $rs;
        $res = array('status'=> 'success','msg'=>'');
        $receObj = app::get('finance')->model('ar');
        $receitemObj = app::get('finance')->model('ar_items');
        //开启事务
        if($this->isTransaction == true){
            $db = kernel::database();
            $db->beginTransaction();
        }
        $relate_order_bn = $sdf['relate_order_bn'] ? $sdf['relate_order_bn'] : '';
        $addon = array('sale_money'=>$sdf['sale_money'] ? $sdf['sale_money'] : 0,'fee_money'=>$sdf['fee_money'] ? $sdf['fee_money'] : 0);
        $main = array(
            'ar_bn'=>$this->gen_ar_bn(),
            'trade_time'=>$sdf['trade_time'],
            'create_time'=>time(),#单据进入系统的时间
            'member'=>$sdf['member'],
            'type'=>$sdf['type'],
            'order_bn'=>$sdf['order_bn'],
            'crc32_order_bn'=>sprintf('%u',crc32($sdf['order_bn'])),
            'relate_order_bn'=>$relate_order_bn,
            'crc32_relate_order_bn'=> $relate_order_bn ? sprintf('%u',crc32($relate_order_bn)) : '',
            'channel_id'=>$sdf['channel_id'],
            'channel_name'=>$sdf['channel_name'],
            'unconfirm_money'=>$sdf['money'],
            'charge_status'=>$sdf['charge_status'],
            'charge_time'=>$sdf['charge_time'],
            'money'=>$sdf['money'],
            'serial_number'=>$sdf['serial_number'],
            'unique_id'=>$sdf['unique_id'],
            'verification_flag'=>$this->get_verification_flag($sdf['order_bn'],$sdf['money']),
            'addon'=>serialize($addon),
            'memo'=>serialize($sdf['memo']),
        );
        if(!$receObj->save($main)){
            $res = array('status'=> 'fail','msg'=>'主数据保存失败');
            if($this->isTransaction == true){
                $db->rollBack();
            }
            return ($res);
        }
        if($sdf['items']){
            foreach($sdf['items'] as $v){
                $items = array(
                    'ar_id'=>$main['ar_id'],
                    'bn'=>$v['bn'],
                    'name'=>$v['name'],
                    'num'=>$v['num'],
                    'money'=>$v['money'],
                    );
                if(!$receitemObj->save($items)){
                    $res = array('status'=> 'fail','msg'=>'明细数据保存失败');
                    if($this->isTransaction == true){
                        $db->rollBack();
                    }
                    return ($res);
                }
            }
        }
        if($this->isTransaction == true){
            $db->commit();
        }
        return $res;
    }

    /*
    *生成销售应收单据号
    */
    public function gen_ar_bn(){
        $i = rand(0,99999);
        $receObj = app::get('finance')->model('ar');
        do{
            if(99999==$i){
                $i=0;
            }
            $i++;
            $ar_bn="AR".date('Ymd').str_pad($i,5,'0',STR_PAD_LEFT);
            $row = $receObj->getlist('ar_id',array('ar_bn'=>$ar_bn));
        }while($row);
        return $ar_bn;
    }


    /*
    **验证数据的正确性
    **@params array $sdf = array(
        ‘trade_time’=>’ 账单日期’,
        ‘member’=>’客户/会员’,
        ‘type’=>’业务类型’,
        ‘order_id’=>’业务单据ID’,
        ‘order_bn’=>‘业务单据bn’,
        ‘relate_order_id’=>‘’,
        ‘relate_order_bn’ ‘=>’关联订单ID’,
        ‘channel_id’=>’渠道id’,
        ‘channel_name’=>‘渠道名称’,
        ‘sale_money’=>’ 商品成交金额’,
        ‘fee_money’=>’运费’,
        ‘money’=>’ 商品成交金额+运费’,
        ‘serial_number’=>‘默认销售单据号，没有自定义规则,业务流水号’,
        ‘items’=>array(
        ‘bn’=>’’,
        ‘name’=>’’,
        ‘num’=>’’, 
        ‘money’=>’该商品的最终成交金额’,
        ),     
    );
    **@return array('status'=>'success/fail','msg'=>'');
    */
    public function verify_data(&$data){
        $res = array('status'=>'success','msg'=>'');
        if(empty($data['trade_time'])){
            //兼容
            $data['trade_time'] = time();
        }else{
            $data['trade_time'] = kernel::single('ome_func')->date2time($data['trade_time']);
        }

        #api来源 判断是否在合法账期内，更改记账状态
        if(isset($data['charge_status']) && $data['charge_status'] == 1){
            #如果是api来源，若账单的账单日期所属的账期未结帐，则状态置为“已记账”；若账单的账单日期所属的账期为已结账，则状态置为“未记账”
            #通过单据完成时间获取月结状态
            $report_status = kernel::single('finance_monthly_report')->get_monthly_report_status_by_time($data['trade_time']);
            if($report_status == 2){
                #已月结的账单把记账状态改为未记账
                $data['charge_status'] = 0;
                $data['charge_time'] = '';
            }else{
                $data['charge_time'] = time();
            }
        }

        //添加业务类型时更改依据array销售出库(0)、销售退货(1)、销售换货(2)、销售退款(3) 其他(99)
        $type = $this->get_type_by_name($data['type']);
        $data['type'] = $type;
        if(empty($data['order_bn'])){
            $res = array('status'=>'fail','msg'=>'业务单据号不能为空');
            return $res;
        }
        if(empty($data['unique_id'])){
            $res = array('status'=>'fail','msg'=>'唯一标识不能为空');
            return $res;
        }
        
        if(empty($data['fee_money'])){
            $data['fee_money'] = 0;
        }else{
            if(!is_numeric($data['fee_money'])){
                $res = array('status'=>'fail','msg'=>'请输入正确的运费');
                return $res;
            }
        }
        if(empty($data['money'])){
            $data['money'] = 0;
        }else{
            if(!is_numeric($data['money'])){
                $res = array('status'=>'fail','msg'=>'请输入正确的金额');
                return $res;
            }
        }
        if(empty($data['sale_money'])){
            $data['sale_money'] = 0;
        }else{
            if(!is_numeric($data['sale_money'])){
                $res = array('status'=>'fail','msg'=>'请输入正确的成交金额');
                return $res;
            }
        }
        $arObj = app::get('finance')->model('ar');
        $rs = $arObj->getList('ar_id',array('unique_id'=>$data['unique_id']));
        if(!empty($rs)){
            $res = array('status'=>'fail','msg'=>'该单据已存在');
            return $res;
        }
        if($data['items']){
            foreach($data['items'] as $k=>$v){
                if(empty($v['bn'])){
                    $res = array('status'=>'fail','msg'=>'明细商品货号不能为空');
                    return $res;
                }
                //兼容
                if(empty($v['money'])) $data['items'][$k]['money'] = 0;
                if(empty($v['num'])) $data['items'][$k]['num'] = 1;
            }
        }
        return $res;
    }

    /**
    *通过业务类型名称获取业务类型编码
    *
    **/
    public function get_type_by_name($name){
        //销售出库(0)、销售退货(1)、销售换货(2)、销售退款(3)
        $data =array(
            '销售出库'=>0,
            '销售退货'=>1,
            '销售换货'=>2,
            '销售退款'=>3,
            );
        if(isset($data[trim($name)])){
            return $data[trim($name)];
        }
        return 99;
    }

    /**
    *通过业务类型编码获取业务类型名称
    *
    **/
    public function get_name_by_type($type = '',$flag = ''){
        //销售出库(0)、销售退货(1)、销售换货(2)、销售退款(3) 其他(99)
        $data =array(
            '0'=>'销售出库',
            '1'=>'销售退货',
            '2'=>'销售换货',
            '3'=>'销售退款',
            '99'=>'其他',
            );
        if($flag) return $data;
        return $data[trim($type)];
    }

    /**
    *通过记账状态编码获取记账状态名称
    *
    **/
    public function get_name_by_charge_status($charge_status = '',$flag = ''){
        //记账状态  未记账(0)、已记账(1)
        $data =array(
            '0'=>'未记账',
            '1'=>'已记账',
            );
        if($flag) return $data;
        return $data[trim($charge_status)];
    }

    /**
    *通过核销状态编码获取核销状态名称
    *
    **/
    public function get_name_by_status($status = '',$flag = ''){
        //核销状态  未核销(0)、部分核销(1)、已核销(2)
        $data =array(
            '0'=>'未核销',
            '1'=>'部分核销',
            '2'=>'已核销',
            );
        if($flag) return $data;
        return $data[trim($status)];
    }

    /**
    *通过核销状态编码获取核销状态名称
    *
    **/
    public function get_name_by_monthly_status($monthly_status = '',$flag = ''){
        //月结状态 未结帐（0），已结账（1）
        $data =array(
            '0'=>'未结帐',
            '1'=>'已结账',
            );
        if($flag) return $data;
        return $data[trim($monthly_status)];
    }

    /*
    **批量记账
    **@params $ids
    **@return 'succ/fail  字符串'
    */
    public function do_charge($ids){
        $arObj = app::get('finance')->model('ar');
        $res = array('status'=>'succ','msg'=>'');
        //100个一个组成一个组
        if(count($ids) > 100){
            $ids_tmp = array_chunk($ids,100);
            $count = ceil(count($ids)/100);
            for($i=0;$i<$count;$i++){
                foreach((array)$ids_tmp[$i]['ar_id'] as $id){
                    $tmp = $arObj->getList('trade_time,charge_status',array('ar_id'=>$id));
                    if ($tmp[0]['charge_status'] == '0'){
                        $rs = kernel::single('finance_monthly_report')->get_monthly_report_status_by_time($tmp[0]['trade_time']);
                        if($rs == '2'){
                            $res = array('status'=>'fail','msg'=>'单据所属账期已月结，批量记账失败!');
                            return $res;
                        }else{
                            $sdf = array('charge_status'=>'1','charge_time'=>time());
                            $filter = array('ar_id'=>$id);
                            if(!$arObj->update($sdf,$filter)){
                                $res = array('status'=>'fail','msg'=>'更改记账状态失败，批量记账失败!');
                                return $res;
                            }
                        }
                    }
                }
            }
        }else{
            if ($ids){
                foreach((array)$ids['ar_id'] as $id){
                    $tmp = $arObj->getList('trade_time,charge_status',array('ar_id'=>$id));
                    if ($tmp[0]['charge_status'] == '0'){
                        $rs = kernel::single('finance_monthly_report')->get_monthly_report_status_by_time($tmp[0]['trade_time']);
                        if($rs == 2){
                            $res = array('status'=>'fail','msg'=>'单据所属账期已月结，批量记账失败!');
                            return $res;
                        }else{
                            $sdf = array('charge_status'=>'1','charge_time'=>time());
                            $filter = array('ar_id'=>$id);
                            if(!$arObj->update($sdf,$filter)){
                                $res = array('status'=>'fail','msg'=>'更改记账状态失败，批量记账失败!');
                                return $res;
                            }
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
    **判断应收单据是否可以做应收对冲
    **@params $order_bn 订单bn
    **@params $money 应收金额
    **@return 0 不可显示 ,1 可显示
    */
    public function get_verification_flag($order_bn,$money){
        $arObj = app::get('finance')->model('ar');
        $crc32_order_bn = sprintf('%u',crc32($order_bn));
        if($money > 0){
            $rs = $arObj->getList('ar_id',array('crc32_order_bn'=>$crc32_order_bn,'status|noequal'=>2,'money|lthan'=>0));
            if(!empty($rs)){
                foreach($rs as $k=>$v){
                    $filter = array('ar_id'=>$v['ar_id']);
                    $update = array('verification_flag'=>1);
                    $arObj->update($update,$filter);
                }
                return 1;
            }
        }else{
            $rs = $arObj->getList('ar_id',array('crc32_order_bn'=>$crc32_order_bn,'status|noequal'=>2,'money|than'=>0));
            if(!empty($rs)){
                foreach($rs as $k=>$v){
                    $filter = array('ar_id'=>$v['ar_id']);
                    $update = array('verification_flag'=>1);
                    $arObj->update($update,$filter);
                }
                return 1;
            }
        }
        return 0;
    }
}