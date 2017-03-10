<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class taoexlib_ietask{

    function doBackgroundExport($app,$model,$filter){
        //接管的model
        $export_model = $app.'_mdl_'.$model;
        if(!array_key_exists($export_model, ome_export_whitelist::allowed_lists())){
            return false;
        }
        $modelObj = app::get($app)->model($model);

        //大致统计下当前记录数
        $count = 0;
        if( method_exists($modelObj,'fcount_csv') ){
            $count = $modelObj->fcount_csv($filter);
        }else{
            $count = $modelObj->count($filter);
        }

        //修正商品批量上传导出商品类型模板的bug
        if($export_model == 'ome_mdl_goods' && $count == 0 && $filter['_gType']){
            return false;
        }

        //后台导出的时候，如果涉及到操作员权限判断(比如涉及拥有的仓库)，增加操作员过滤条件
        if($export_model == 'omedlyexport_mdl_ome_delivery' && $filter['ctl'] == 'admin_receipts_outer' && $filter['isSelectedAll'] == '_ALL_'){
            $filter['export_op_id'] = kernel::single('desktop_user')->get_id();
        }

        $queue_type = 'normal';
        if($count <= 1000){
            //分片任务识别这个丢进快队列处理
            $queue_type= 'quick';
        }

        //单个客户最大5个任务上限
        if(!$this->is_pass($msg)){
            echo $msg;
            exit;
        }

        $data['app'] = $app;
        $data['model'] = $model;
        $data['task_name'] = app::get($app)->model($model)->export_name.'导出-'.date('Y-m-d');
        $data['op_id'] = kernel::single('desktop_user')->get_id();
        $data['create_time'] = time();
        $data['file_name'] = '';
        $data['status'] = 'sleeping';
        $data['filter_data'] = serialize($filter);
        $data['total_count'] = 0;
        $data['finish_count'] = 0;
        $data['use_slave_db'] = 1;
        $data['export_ver']= 2;

        app::get('taoexlib')->model('ietask')->save($data);
        $task_id = $data['task_id'];

        //导出任务加队列
        //kernel::openapi_url('openapi.autotask','service')
        $push_params = array(
            'data' => array(
                'app' => $data['app'],
                'model' => $data['model'],
                'task_id' => $task_id,
                'filter_data' => $data['filter_data'],
                'queue_type' => $queue_type,
                'task_type' => 'exportsplit',
            ),
            'url' => kernel::openapi_url('openapi.autotask','service')
        );
        $flag = kernel::single('taskmgr_interface_connecter')->push($push_params);

        header("content-type:text/html; charset=utf-8");
        if($flag){
            echo "<script>top.MessageBox.success(\"导出成功\");alert(\"导出任务提交成功！请稍后到[系统->导出任务列表]查看导出结果。\");if(parent.$('export_form').getParent('.dialog'))parent.$('export_form').getParent('.dialog').retrieve('instance').close();if(parent.window.finderGroup&&parent.window.finderGroup['".$_GET['finder_id']."'])parent.window.finderGroup['".$_GET['finder_id']."'].refresh();</script>";
        }else{
            echo "<script>top.MessageBox.error(\"导出失败\");alert(\"导出任务提交失败！\");if(parent.$('export_form').getParent('.dialog'))parent.$('export_form').getParent('.dialog').retrieve('instance').close();if(parent.window.finderGroup&&parent.window.finderGroup['".$_GET['finder_id']."'])parent.window.finderGroup['".$_GET['finder_id']."'].refresh();</script>";
        }

        return true;

    }

    function export_id($data){

        return app::get('taoexlib')->model('ietask')->export_id($data['task_id']);
    }

    function clean(){
        $ietaskObj = app::get('taoexlib')->model('ietask');
        $list = $ietaskObj->getExpireIetask();
        if($list){
            foreach($list as $row){
                if(!empty($row['file_name'])){
                    //$ident_ret = kernel::single('taoexlib_storager')->parse($row['file_name']);
                    //kernel::single('taoexlib_storager')->remove($ident_ret['id']);
                    //看下本地在不在的话删除文件过期后
                    @rmdir(DATA_DIR.'/export/cache/'.$row['task_id']);
                    @unlink(DATA_DIR.'/export/file/'.$row['file_name']);
                }
                $ietaskObj->remove($row['task_id']);
            }
        }
    }

    function is_pass(&$msg){
        $ietaskObj = app::get('taoexlib')->model('ietask');
        $total = $ietaskObj->getValidCounts();
        if($total >= 5){
            $msg = "<script>top.MessageBox.success(\"导出暂停\");alert(\"现运行得导出任务不能超过五条，清稍后提交。\");if(parent.$('export_form').getParent('.dialog'))parent.$('export_form').getParent('.dialog').retrieve('instance').close();if(parent.window.finderGroup&&parent.window.finderGroup['".$_GET['finder_id']."'])parent.window.finderGroup['".$_GET['finder_id']."'].refresh();</script>";
            return false;
        }

        return true;

    }

}
