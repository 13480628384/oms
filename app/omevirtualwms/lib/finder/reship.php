<?php
class omevirtualwms_finder_reship{

    
    var $column_control = '操作';
    var $column_control_width = 100;
    function column_control($row){
    	$flag = "reship";
        return '<a href="index.php?app=omevirtualwms&ctl=admin_wms&act=getinfo&p[0]='.$row['reship_bn'].'&p[1]='.$flag.'">开始模拟</a>';
    }

    function row_style($row)
    {
        if (in_array($row['reship_bn'],app::get('omevirtualwms')->model('reship')->queue)) {
            return "\" style=\"background-color:#ffe3e7;\"";
        }
    }

    var $column_msg = '备注';
    var $column_msg_width = 400;
    var $column_msg_order = 200;
    function column_msg($row)
    {
        if (in_array($row['reship_bn'],app::get('omevirtualwms')->model('reship')->queue)) {
            return app::get('omevirtualwms')->_(TIP_INFO);
        }
    }

}
