<?php

class omeanalysts_mdl_ordersPrice extends dbeav_model{

    function price_interval($data = null,$interval_list){
        $db = kernel::database();
        $order_price = array();
        $data['to'] = $data['to'] + 86400;
        foreach($interval_list as $v){
        	$sql_filter="where interval_id = ".$v['interval_id']." AND dates >= ".$data['from']." AND dates <= ".$data['to'];
            if($data['shop_id']){
            	$sql_filter .= " AND shop_id = '".$data['shop_id']."'";
            }
            $sql_count_sum = "select sum(num) as num from sdb_omeanalysts_ordersPrice ".$sql_filter;
            $info = $db->selectrow($sql_count_sum);
            $order_price[] = $info['num'];
        }
        return $order_price;
    }

    function del(){
        $sql = "truncate table sdb_omeanalysts_interval";
        kernel::database()->exec($sql);
    }
}
?>