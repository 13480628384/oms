<?php
/**
 +----------------------------------------------------------
 * [拆单]第三方仓储版无WMS_APP_现挪动拆单配置到Ome_APP中
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2015-11-18 $
 * [Ecos!] (C)2003-2015 Shopex Inc.
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

$db           = kernel::database();
$data_list    = array();

    /*------------------------------------------------------ */
    //-- 更新拆单配置
    /*------------------------------------------------------ */
    $split_config   = &app::get('wms')->getConf('wms.delivery.status.cfg');
    $split_seting   = array('split'=>intval($split_config['set']['split']), 'split_model'=>intval($split_config['set']['split_model']), 
                            'split_type'=>intval($split_config['set']['split_type']));

    
    if(empty($split_seting['split']) || empty($split_seting['split_model']) || empty($split_seting['split_type']))
    {
        return '';
    }
    
    #新Ome变量_配置拆单
    app::get('ome')->setConf('ome.order.split', $split_seting['split']);
    app::get('ome')->setConf('ome.order.split_model', $split_seting['split_model']);
    app::get('ome')->setConf('ome.order.split_type', $split_seting['split_type']);
    app::get('ome')->setConf('ome.order.split_class', $split_seting['split_type']);
    app::get('ome')->setConf('ome.order.split_send', $split_seting['split_type']);
    
    #配置日志
    $log_msg   = '开启拆单功能;';
    $log_msg   .= ($split_seting['split_model'] == '1' ? '按子订单方式进行拆分订单' : '按sku方式拆分订单');
    $log_msg   .= '-';
    $log_msg   .= ($split_seting['split_type'] == '1' ? '回写第一张' : '回写最后一张');
    
    ilog($log_msg, true);

/**
 * 日志
 */
function ilog($str, $flag = false)
{
    global $domain;
    $filename = dirname(__FILE__) . '/../logs/update_ome_split_step_' . date('Y-m-d') . '.log';
        
    if($flag)
    {
        $filename = dirname(__FILE__) . '/../logs/update_ome_split_step_' . date('Y-m-d') . '.log';
    }
    
    $fp = fopen($filename, 'a');
    fwrite($fp, date("m-d H:i") . "\t" . $domain . "\t" . $str . "\n");
    fclose($fp);
}


