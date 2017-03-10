<?php
/**
 * User: jintao
 * Date: 2016/2/2
 * Time: 14:41
 */
class crm_finder_gift
{
    var $column_edit = '操作';
    var $column_edit_width = 90;
    function column_edit($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $gift_id = $row[$this->col_prefix.'gift_id'];

        $button1 = '<a href="index.php?app=crm&ctl=admin_gift&act=edit&p[0]='.$gift_id.'&finder_id='.$finder_id.'" target="dialog::{width:600,height:250,title:\'赠品设置\'}">设置赠品数量</a>';

        return $button1;
    }

}