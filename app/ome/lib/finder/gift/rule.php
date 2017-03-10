<?php
/**
 * User: jintao
 * Date: 2016/2/1
 * Time: 17:15
 */
class ome_finder_gift_rule
{
    var $column_edit = '操作';
    var $column_width = 100;
    public function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $id = $row['id'];
        $shop_id = $row['shop_id'];
        $lv_id = $row['lv_id'];
        $gift_bn = $row['gift_bn'];
        $button = '';
        //$button .= '<a href="index.php?app=ecorder&ctl=admin_gift_rule&act=edit_rule&p[0]='.$id.'&p[1]='.$_GET['view'].'&finder_id='.$finder_id . '" target="dialog::{title:\''.app::get('ecorder')->_('编辑促销规则').'\', width:700, height:380}">编辑</a>';
        $button .= '<a href="index.php?app=ome&ctl=admin_crm_gift&act=addAndEdit&p[0]=add&id='.$id.'&finder_id='.$finder_id . '">编辑</a>';

        $button .= ' | <a href="index.php?app=ome&ctl=admin_crm_gift&act=priority&p[0]='.$id.'&p[1]='.$_GET['view'].'&finder_id='.$finder_id . '" target="dialog::{title:\''.app::get('ecorder')->_('设置优先级').'\', width:550, height:250}">优先级</a>';

        return $button;
    }
}
