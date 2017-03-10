<?php

class taskmgr_whitelist{

    //进队列业务逻辑处理任务
    static public function task_list(){
        return $_tasks = array(
            'autochk'      => array('method'=>'wms_autotask_check', 'threadNum'=>5),
            'autodly'      => array('method'=>'wms_autotask_consign', 'threadNum'=>5),
            'autorder'     => array('method'=>'ome_autotask_combine', 'threadNum'=>5),#自动审单 ExBOY
            'autoretryapi' => array('method'=>'erpapi_autotask_retryapi', 'threadNum'=>5),
        );
    }

    //定时任务，线程数不允许修改
    static public function timer_list(){
        return $_tasks = array(
            'bgqueue' => array('method'=>'ome_autotask_bgqueue', 'threadNum'=>1),
            'misctask' => array('method'=>'ome_autotask_misctask', 'threadNum'=>1),
            'inventorydepth' => array('method'=>'ome_autotask_inventorydepth', 'threadNum'=>1),
            'financecronjob' => array('method'=>'ome_autotask_financecronjob', 'threadNum'=>1),
        );
    }

    //初始化域名进任务队列,这里的命名规范就是实际连的队列任务+domainqueue生成这个初始化任务的数组值，线程数不允许修改
    static public function init_list(){
        return $_tasks = array(
            'bgqueuedomainqueue' => array('threadNum'=>1),
            'misctaskdomainqueue' => array('threadNum'=>1),
            'inventorydepthdomainqueue' => array('threadNum'=>1),
            'financecronjobdomainqueue' => array('threadNum'=>1),
        );
    }

    //导出任务
    static public function export_list(){
        return $_tasks = array(
            'exportsplit' => array('method'=>'ome_autotask_exportsplit', 'threadNum'=>5),
            'dataquerybysheet' => array('method'=>'ome_autotask_dataquerybysheet', 'threadNum'=>5),
            'dataquerybyquicksheet' => array('method'=>'ome_autotask_dataquerybyquicksheet', 'threadNum'=>5),
            'dataquerybywhole' => array('method'=>'ome_autotask_dataquerybywhole', 'threadNum'=>5),
            'createfile' => array('method'=>'ome_autotask_createfile', 'threadNum'=>5),
        );
    }

    //rpc任务
    static public function rpc_list(){
        return $_tasks = array(
            'omecallback' => array('method'=>'ome_autotask_omecallback', 'threadNum'=>5),
            'wmscallback' => array('method'=>'ome_autotask_wmscallback', 'threadNum'=>5),
            'wmsrpc' => array('method'=>'ome_autotask_wmsrpc', 'threadNum'=>5),
            'orderrpc' => array('method'=>'ome_autotask_orderrpc', 'threadNum'=>5),
        );
    }

    //全部任务
    static public function get_all_task_list(){
        return array_merge(self::task_list(),self::timer_list(),self::export_list(),self::rpc_list());
    }

    static public function get_task_types(){
    	return array('task','timer','init','export','rpc');
    } 
}