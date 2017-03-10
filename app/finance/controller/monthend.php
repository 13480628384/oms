<?php
class finance_ctl_monthend extends desktop_controller{
    function index(){
        $params['use_buildin_recycle'] = false;
        $params['use_buildin_selectrow'] = false;
        $finance_monthly_report = kernel::single("finance_monthly_report");
        $finance_monthly_report->save_monthly_report();
        $this->finder('finance_mdl_monthly_report',$params);
    }

    function closebook($monthly_id=null){
        #$this->index();
        if(empty($monthly_id)) return ;
        $this->pagedata['monthly_id'] = $monthly_id;
        $monthly_report = app::get('finance')->model("monthly_report");
        $aData = $monthly_report->getList('begin_time,end_time',array('monthly_id'=>$monthly_id));
        $begin_time = $aData[0]['begin_time'];
        $end_time = $aData[0]['end_time'];
        $finance_monthly_colsebook = kernel::single("finance_monthly_colsebook");
        $asc_row_status = $finance_monthly_colsebook->get_last_month_status($begin_time);
        if(!($asc_row_status==='2' || $asc_row_status==='0'))
        { 
            $this->pagedata['asc_status_msg'] = "上个月份必须为“已结账”或“未启用”状态";
        }
        elseif(!$finance_monthly_colsebook->get_monthly_book_status($begin_time,$end_time,$msg)){
            $this->pagedata['book_status_msg'] = $msg;
        }
        $this->pagedata['auto_falg'] = $finance_monthly_colsebook->get_auto_flag_status($begin_time,$end_time,$auto_msg);
        $this->pagedata['auto_falgmsg'] = $auto_msg;
        $this->display("monthed/edit_status.html");
    }
    
    function cancelbook($monthly_id=null){
        $this->begin("index.php?app=finance&ctl=monthend&act=index");
        if(empty($monthly_id))
            $this->end(false,"月末结账ID不能为空");
        $monthly_report = app::get('finance')->model("monthly_report");
        $aData = $monthly_report->getList('begin_time,end_time',array('monthly_id'=>$monthly_id));
        $begin_time = $aData[0]['begin_time'];
        $end_time = $aData[0]['end_time'];
        $finance_monthly_colsebook = kernel::single("finance_monthly_colsebook");
        $next_row_status = $finance_monthly_colsebook->get_next_month_status($begin_time);
        #var_dump($next_row_status);exit;
        if($next_row_status == '2')
            $this->end(false,"请先取消下一个月的账单");
        elseif($finance_monthly_colsebook->colse_book($monthly_id,1))
            $this->end(true,"取消成功");
        else
            $this->end(false,"取消失败");
            
    }
    function edit_status(){
        $this->begin("index.php?app=finance&ctl=monthend&act=index");
        $monthly_id = $_POST['monthly_id'];
        if(empty($monthly_id))
            $this->end(false,"月末结账ID不能为空!");
        $finance_monthly_colsebook = kernel::single("finance_monthly_colsebook");
        if($finance_monthly_colsebook->colse_book($monthly_id,2))
            $this->end(true,"结账成功");
        else
            $this->end(false,"结账失败");

    }
}
