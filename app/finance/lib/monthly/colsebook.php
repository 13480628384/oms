<?php
/**
 * @copyright shopex.cn
 * @author hanbingshu sanow@126.com
 * @version ocs  处理月末结账操作
 */
class finance_monthly_colsebook
{
    /*获取月末结账月的上一个月的结账状态*/
    function get_last_month_status($begin_time=''){
        if(empty($begin_time)) return false;
        $report_mdl = app::get("finance")->model("monthly_report");
        $asc_data = $report_mdl->db->select("SELECT `status` from  `sdb_finance_monthly_report` where begin_time<".intval($begin_time)." ORDER BY  `begin_time` DESC limit 0,1");
        $asc_row_status = $asc_data[0]['status'];
        return $asc_row_status;
    }

    /*获取月末结账月的下一个月的结账状态*/
    function get_next_month_status($begin_time=''){
        if(empty($begin_time)) return false;
        $report_mdl = app::get("finance")->model("monthly_report");
        $asc_data = $report_mdl->db->select("SELECT `status` from  `sdb_finance_monthly_report` where begin_time>".intval($begin_time)." ORDER BY  `begin_time` ASC limit 0,1");
        $asc_row_status = $asc_data[0]['status'];
        return $asc_row_status;
    }

    /*月末结账月的账期内应收，实收，费用，容错 等账单的状态*/
    function get_monthly_book_status($begin_time,$end_time,&$msg="结账失败!"){
        $ar = app::get('finance')->model('ar');
        $bill = app::get('finance')->model('bill');
        $bill_confirm = app::get('finance')->model('bill_confirm');
        if($row = $bill_confirm->getList('confirm_id',array('trade_time|bthan'=>$begin_time,'trade_time|lthan'=>$end_time)))
        {
            $msg = "该月存在容错待确认账单";
            return false;
        }
        if($row = $ar->db->selectrow("select ar_id from sdb_finance_ar where trade_time>=".intval($begin_time)." and trade_time<".intval($end_time)." and charge_status=0"))
        {
            $msg = "该月账期内存在应收账单为未记账状态";
            return false;
        }
        if($row = $bill->db->selectrow("select bill_id from sdb_finance_bill where trade_time>=".intval($begin_time)." and trade_time<".intval($end_time)." and charge_status=0"))
        {
            $msg = "该月账期内存在实收账单为未记账状态";
            return false;
        }
        return true;
    }

    /*查看账期内应收单是否存在未核销账单*/
    function get_auto_flag_status($begin_time,$end_time,&$msg)
    {
        $bill = app::get('finance')->model('bill');
        $bill_row = $bill->db->selectrow("SELECT bill_id from sdb_finance_bill where trade_time>=".intval($begin_time)." and trade_time<".intval($end_time).' and status !=2 and fee_type_id=1');
        $ar_row = $bill->db->selectrow("SELECT ar_id from sdb_finance_ar where trade_time>=".intval($begin_time)." and trade_time<".intval($end_time).' and status !=2');
        $flag = 'false';
        if(time() < $end_time){
            $msg .= "未到账期结束时间  ";
            $flag = 'true';
        }
        if($bill_row){
            $msg .= "销售收退款中存在未完全核销的单据  ";
            $flag = 'true';
        }
        if($ar_row){
            $msg .= "销售应收单中存在未完全核销的单据  ";
            $flag = 'true';
        }
        $msg .= '是否月结?';
        return $flag;
    }

    /*月末结账/取消*/
    function colse_book($monthly_id,$status=2)
    {
        $monthly_report = app::get("finance")->model("monthly_report");
        $bill = app::get("finance")->model("bill");
        $aData = $monthly_report->getList("*",array('monthly_id'=>$monthly_id));
        $begin_time = $aData[0]['begin_time'];
        $end_time = $aData[0]['end_time'];
        $monthly_status = $status == 2 ? 1 : 0;
        if($this->update_ar_status($monthly_id,$begin_time,$end_time,$monthly_status)){
            if($this->update_bill_status($monthly_id,$begin_time,$end_time,$monthly_status)){
                $update['status'] = $status;
                $filter['monthly_id'] = $monthly_id;
                return $monthly_report->update($update,$filter);
            }
        }
        
    }
    /*修改ar表的月末结账状态*/
    function update_ar_status($monthly_id,$begin_time,$end_time,$monthly_status=1){
        $ar = app::get("finance")->model("ar");
        $update_data['monthly_id'] = $monthly_id;
        $update_data['monthly_status'] = $monthly_status;
        $filter['trade_time|bthan'] = $begin_time;
        $filter['trade_time|lthan'] = $end_time;
        return $ar->update($update_data,$filter);
    }

    /*修改bill表的月末结账状态*/
    function update_bill_status($monthly_id,$begin_time,$end_time,$monthly_status=1){
        $bill = app::get("finance")->model("bill");
        $update_data['monthly_id'] = $monthly_id;
        $update_data['monthly_status'] = $monthly_status;
        $filter['trade_time|bthan'] = $begin_time;
        $filter['trade_time|lthan'] = $end_time;
        return $bill->update($update_data,$filter);
    }
}