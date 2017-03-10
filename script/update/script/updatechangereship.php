<?php
/**
 * 淘宝退款完后，对淘管订单还是审请退款中，对这部分数据进行修正
 * 
 * @author chenping@shopex.cn
 * @version 1.0
 */
error_reporting(E_ALL ^ E_NOTICE);

$domain = $argv[1];
$order_id = $argv[2];
$host_id = $argv[3];


if (empty($domain) || empty($order_id) || empty($host_id) ) {

	die('No Params');
}

set_time_limit(0);

require_once(dirname(__FILE__) . '/../../lib/init.php');

cachemgr::init(false);
$sql = "SELECT reship_id FROM sdb_ome_reship WHERE return_type='change' limit 1";
$db = kernel::database();
$reship_list = $db->select($sql);
foreach ($reship_list as $reship ) {

    $reship_id = $reship['reship_id'];
    $change = $db->selectrow("SELECT branch_id FROM sdb_ome_reship_items WHERE return_type='change' AND reship_id=".$reship_id);
    
    $changebranch_id = $change['branch_id'];
    $db->exec("UPDATE sdb_ome_reship SET changebranch_id=".$changebranch_id." WHERE reship_id=".$reship_id);
}


/**
 * 日志
 */
function ilog($str) {	
    global $domain;
    $filename = dirname(__FILE__) . '/../logs/updateReshipchange_' . date('Y-m-d') . '.log';
    $fp = fopen($filename, 'a');
    fwrite($fp, date("m-d H:i") . "\t" . $domain . "\t" . $str . "\n");
    fclose($fp);
}
