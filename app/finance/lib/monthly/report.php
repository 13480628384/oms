<?php
/**
* @copyright shopex.cn
* @author Kris.wang 406048119@qq.com
* @version ocs
*/
class finance_monthly_report
{
    /*
    **设置期初数据,只有初始化时调用此方法
    **@return 'true/false 字符串'
    */
    public function set_init_charge(){
        $mrObj = app::get('finance')->model('monthly_report');
        $billObj = app::get('finance')->model('bill');
        $arObj = app::get('finance')->model('ar');
        $time = $this->get_init_time();
        $time = $time['time'];
        $data = array(
            'monthly_date'=>'期初',
            'begin_time'=>'0',
            'end_time'=>$time,
            'status'=>'0',
        );
        if($mrObj->save($data)){
            //将期初数据存入KV，以便以后查询
            $ar_money = $this->get_ar_money('0',$time);
            $br_money = $this->get_br_money('0',$time);
            $total_money = ($ar_money - $br_money);
            $kv_data = array('monthly_id'=>
            $data['monthly_id'],'ar_money'=>$ar_money,'br_money'=>$br_money,'total_money'=>$total_money,'is_delete'=>'false');
            app::get('finance')->setConf('monthly_report_money',$kv_data);
            return 'true';
        }else{
            return 'false';
        }
    }

    /*
    **获取本期应收金额
    **@params $time_from 开始时间,没有开始时间，请传'0'
    **@params $time_to 结束时间
    **@return 应收金额
    */
    public function get_ar_money($time_from,$time_to){
        $arObj = app::get('finance')->model('ar');
        $rs = $arObj->getList('sum(money) as money',array('trade_time|than'=>$time_from,'trade_time|sthan'=>$time_to));
        if(empty($rs['money'])) return '0';
        return $rs[0]['money'];
    }

    /*
    **获取本期实收金额
    **@params $time_from 开始时间,没有开始时间，请传'0'
    **@params $time_to 结束时间
    **@return 实收金额
    */
    public function get_br_money($time_from,$time_to){
        $arObj = app::get('finance')->model('bill');
        $rs = $arObj->getList('sum(money) as money',array('trade_time|than'=>$time_from,'trade_time|sthan'=>$time_to));
        if(empty($rs['money'])) return 0;
        return $rs[0]['money'];
    }

    /*
    **通过时间判断所在账期月结状态
    **@params $time 账单完成时间
    **@return 月结状态未启用(0) 未结算(1) 已结算(2)
    */
    public function get_monthly_report_status_by_time($time){
        $arObj = app::get('finance')->model('monthly_report');
        $rs = $arObj->getList('status',array('begin_time|sthan'=>$time,'end_time|than'=>$time));
        if(empty($rs)){
            return '1';
        }
        return $rs[0]['status'];
    }

    /*插入月末结账数据,从期初时间开始计算到当前时间范围内的月份*/
    function save_monthly_report($now_time){
        $mrObj = app::get('finance')->model('monthly_report');
        $last_row_data = $mrObj->db->select("select end_time from sdb_finance_monthly_report order by end_time DESC limit 0,1");
        $start_time = $last_row_data[0]['end_time'];
        #var_dump($start_time);
        if(!$start_time) return ;
        #echo date('Y-m-d H:i:s',$start_time);
        $months = self::dateMonths($start_time,time());
        for($i=0;$i<=$months;$i++){
            $start_month = self::get_next_month($start_time,$i);
            $next_month = self::get_next_month($start_time,$i+1);
            $sdf = array();
            $sdf['monthly_date'] = date('Y',$start_month)."年".date('m',$start_month)."月";
            $sdf['begin_time'] = $start_month;
            $sdf['end_time'] = $next_month;
            $sdf['status'] = 1;
            #echo date('Y-m-d H:i:s',$sdf['begin_time']).'--'.date('Y-m-d H:i:s',$sdf['end_time'])."<hr/>";
            $mrObj->save($sdf);
        }
    }

    /*计算两个时间间隔相册的月份数
    *@params $d1,$d2为时间戳
    */
    static function dateMonths($d1,$d2)
    {
        $m1 = date("m",$d1);
        $m2 = date("m",$d2);
        $y1 = date("Y",$d1);
        $y2 = date("Y",$d2);
        $months = ($y2-$y1)*12+($m2-$m1);
        return $months;
    }
    /*获取时间间隔月份明细时间戳
    *@params $time起始时间戳 $months偏移量
    */
    static function get_next_month($time,$months){
        return strtotime("+{$months} month", $time);
    }

    /*
    **获取初始化设置数组
    **@return array('flag'=>'true/false 字符串','time'=>'时间戳')
    */
    public function get_init_time(){
        $data = array();
        $kv_data = app::get('finance')->getConf('finance_setting_init_time');
        $data['flag'] = $kv_data['flag'] === 'true' ? $kv_data['flag'] : 'false';
        $time = strtotime($kv_data['year'].'-'.$kv_data['month'].'-'.$kv_data['day'].' 00:00:00');
        $data['time'] = $time;
        return $data;
    }
}