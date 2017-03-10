<?php
/**
 * User: jintao
 * Date: 2016/1/29
 * Time: 18:09
 */
class ome_ctl_admin_crm_list extends desktop_controller
{
    public function ajax_get_gifts(){
        $filter = array('gift_num|bthan'=>1, 'is_del' => 0);
        $s_gift_bn = isset($_POST['s_gift_bn']) ? trim($_POST['s_gift_bn']) : '';
        $s_gift_name = isset($_POST['s_gift_name']) ? trim($_POST['s_gift_name']) : '';
        $sel_goods = empty($_POST['sel_goods']) ?  '' : explode(',', $_POST['sel_goods']);
        if($s_gift_bn) $filter['gift_bn|has'] = $s_gift_bn;
        if($s_gift_name) $filter['gift_name|has'] = $s_gift_name;
        $rs = app::get('crm')->model('gift')->getList('gift_id,gift_bn,gift_name', $filter);
        foreach($rs as $k=>$v){
            if(in_array($v['gift_id'], $sel_goods)){
                unset($rs[$k]);
            }
        }

        echo(json_encode(array_values($rs)));
    }
}
