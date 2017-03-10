<?php
/**
 +----------------------------------------------------------
 * 货品对应店铺冻结批量转换脚本
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2016-01-25 $
 * [Ecos!] (C)2003-2016 Shopex Inc.
 +----------------------------------------------------------
 */
error_reporting(E_ALL ^ E_NOTICE);

$domain     = $argv[1];
$order_id   = $argv[2];
$host_id    = $argv[3];

if (empty($domain) || empty($order_id) || empty($host_id) ) {
    die('No Params');
}

set_time_limit(0);

require_once(dirname(__FILE__) . '/../../lib/init.php');

cachemgr::init(false);

$db     		= kernel::database();

/*------------------------------------------------------ */
//-- 获取未拆分完的货品数量
/*------------------------------------------------------ */
$shopFreezeLib       = kernel::single('ome_shop_freeze');
$shopFreezeLogLib    = kernel::single('ome_shop_freeze_log');

#第一步：清空店铺冻结表与店铺冻结日志明细表
$sql    = "DELETE FROM sdb_ome_shop_freeze WHERE 1=1";
$db->exec($sql);

$sql    = "DELETE FROM sdb_ome_shop_freeze_log WHERE 1=1";
$db->exec($sql);

#第二步
$page     = 0;#开始页
$limit    = 100;#每次执行数量
function batch_count_product_shop_freeze()
{
	global $db, $page, $limit, $shopFreezeLib, $shopFreezeLogLib;
	
	#获取店铺冻结系统配置
	$is_shop_freeze_log    = $shopFreezeLogLib->get_product_shop_freeze_config();
	
	$dataList	= array();
	$sql		= "SELECT o.order_id, o.order_bn, o.process_status, o.confirm, o.shop_id, i.item_id, i.product_id, i.bn, i.nums, i.sendnum 
			       FROM sdb_ome_order_items AS i LEFT JOIN sdb_ome_orders AS o ON i.order_id=o.order_id 
	               WHERE o.process_status in ('unconfirmed', 'is_retrial', 'is_declare', 'splitting') AND o.ship_status in('0', '2') 
			       AND o.status='active' AND i.`delete`= 'false' AND i.product_id>0 LIMIT ". ($page * $limit) .", " . $limit;
	$dataList	= $db->select($sql);
	
	if(empty($dataList))
	{
		ilog("执行完成;\r\n\r\n\r\n", true);
		echo("\r\n Come Over...\r\n");
		return false;
	}
	
	foreach ($dataList as $key => $val)
	{
		#订单部分拆分&&部分发货
		if($val['confirm'] != 'N' && $val['process_status'] != 'splitting')
		{
			ilog("订单号：". $val['order_bn'] .",货号:". $val['bn'] .",process_status:". $val['process_status'] .",confirm:". $val['confirm'] .";\r\n");
			continue;//跳过
		}
		
		#剩余未生成发货单的货品数量
		$num	= 0;
		if($val['process_status'] == 'splitting')
		{
			#部分拆分时_直接减去发货单上货品数量
			$dly_sql	= "SELECT SUM(number) AS num FROM `sdb_ome_delivery_items_detail` AS did 
					      JOIN `sdb_ome_delivery` AS d ON d.delivery_id=did.delivery_id 
	                      WHERE did.order_item_id='". $val['item_id'] ."' AND did.product_id='". $val['product_id'] ."' 
	                      AND d.status NOT IN('back', 'cancel', 'return_back') AND d.is_bind='false'";
			$deliveryNum	= $db->selectrow($dly_sql);
			$num			= $val['nums'] - intval($deliveryNum['num']);
		}
		elseif($val['nums'] > $val['sendnum'])
		{
			$num	= $val['nums'] - $val['sendnum'];
		}
		
		#添加到店铺冻结
		if($num)
		{
			ilog("订单号：". $val['order_bn'] .",货号:". $val['bn'] .",冻结数量：". $num .";\r\n", true);
			
			$shop_id	  = $val['shop_id'];
			$product_id	  = $val['product_id'];
			
			#[增加]货品店铺冻结 ExBOY
			$shopFreezeLib->freeze($shop_id, $product_id, $num);
			
			#店铺冻结增减明细日志
			if($is_shop_freeze_log)
			{
			    $shopFreezeLogLib->changeLog($shop_id, $product_id, $num, 1, '');
			}
			
			usleep(1000000);#暂停一秒_防止明细日志计算不正确
		}
		else 
		{
			ilog("执行失败订单号：". $val['order_bn'] .",货号:". $val['bn'] .",冻结数量：". $num .";\r\n");
		}
	}
	
	$page++;
	usleep(1000000);#暂停一秒
	
	unset($sql, $dataList, $dly_sql, $deliveryNum);
	
	#递归调用
	batch_count_product_shop_freeze();
}

#开始执行
batch_count_product_shop_freeze();

/**
 * 日志
 */
$log_i    = 0;
$log_p    = 0;
function ilog($str, $flag = false)
{   
    global $domain, $log_i, $log_p;
    
    $filename = dirname(__FILE__) . '/../logs/batch_product_shop_freeze_fail_' . date('Y-m-d') . '.log';
    if($flag)
    {
    	$log_i++;
    	
    	if($log_i == 5000)
    	{
    		$log_p++;
    		$log_i    = 0;
    	}
    	
    	$filename = dirname(__FILE__) . '/../logs/batch_product_shop_freeze_success_' . date('Y-m-d') . '_'. $log_p .'.log';
    }
    
    $fp = fopen($filename, 'a');
    fwrite($fp, date("m-d H:i") . "\t" . $domain . "\t" . $str . "\n");
    fclose($fp);
}
