<?php
class finance_finder_bill{
    var $column_edit = "操作";
    var $column_edit_width = "65";
    var $column_edit_order = "2";
    function column_edit($row){
        $bill_id = $row['bill_id'];
        $status = $row['status'];#判断核销状态
        $charge_status = $row['charge_status'];#判断核销状态
        $render = app::get('finance')->render();
        $render->pagedata['bill_id'] = $bill_id;
        $render->pagedata['finder_id'] = $_GET['_finder']['finder_id'];
        if($charge_status == 0){
            return $render->fetch('bill/do_cancel.html');
        }
        if($_GET['ctl'] == 'bill' && $_GET['act'] == 'sale'){
            if($status <> 2 && $charge_status == 1){
                $href = sprintf('<a href="index.php?app=finance&ctl=bill&act=verificate&finder_id=%s&bill_id=%s" target="dialog::{width:1048,height:400}">核销</a>',$_GET['_finder']['finder_id'],$row['bill_id']);
                return $href;
            }
        }
    }
}
?>