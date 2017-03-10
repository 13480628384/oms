<?php
/**
 * @copyright shopex.cn
 * @author hanbingshu sanow@126.com
 * @version ocs
 */
class finance_finder_monthly_report{

    var $column_edit = "月末结账";
    var $column_edit_width = "100";
    var $column_edit_order=5;
    function column_edit($row){
        $report_mdl = app::get("finance")->model("monthly_report");
        if(!$row['begin_time'] || !$row['status']){
            $row_data = $report_mdl->db->selectrow("SELECT `begin_time`,`status` from `sdb_finance_monthly_report` where monthly_id=".intval($row['monthly_id']));
            $row['begin_time'] = $row_data['begin_time'];
        }
        $begin_time = $row['begin_time'];
        $finance_monthly_colsebook = kernel::single("finance_monthly_colsebook");
        $asc_row_status = $finance_monthly_colsebook->get_last_month_status($begin_time);
        $confhref = '<a target="dialog::{title:\'月末结账确认页面\',width:400,height:400}" href="index.php?app=finance&ctl=monthend&act=closebook&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['monthly_id'].'">月末结账</a>';
        $canfhref = '<a href="index.php?app=finance&ctl=monthend&act=cancelbook&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['monthly_id'].'">撤销结账</a>';
         if($row['status'] == 2)
            return $canfhref;
         elseif($asc_row_status==='2' || $asc_row_status==='0')
            return $confhref;

    }
}