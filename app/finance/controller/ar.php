<?php
class finance_ctl_ar extends desktop_controller{
	var $name = "销售应收单";

    public function index(){
        $is_export = kernel::single('desktop_user')->has_permission('finance_export');#增加销售应收单导出权限
        # $view_filter = kernel::single('omeio_public_func')->view_filter($this,'finance_mdl_ar');
        $this->finder('finance_mdl_ar',array(
                'title'=>app::get('finance')->_('销售应收单'),
                'actions'=>array(
                    array(
                        'label' => '批量审核记账',
                        'submit' => 'index.php?app=finance&ctl=ar&act=do_charge&finder_id='.$_GET['finder_id'],
                    ),
                    array(
                        'label' => '导入',
                        'href' => 'index.php?app=omecsv&ctl=admin_import&act=main&add=finance&ctler=finance_mdl_ar&filter[checkTime]=after',
                        'target' => "dialog::{width:400,height:170,title:'队列导入'}",
                    ),
                    array(
                        'label' => '下载模版',
                        'href' => 'index.php?app=omecsv&ctl=admin_export&act=main&add=finance&ctler=finance_mdl_ar&filter[template]=1',
                        'target' => "dialog::{width:400,height:170,title:'下载模版'}",
                    ),
                ),
                'use_buildin_export'=>$is_export,
                'use_buildin_recycle'=>false,
                'use_view_tab'=>true,
                'use_buildin_selectrow'=>true,
                'use_buildin_filter'=>true,
                'finder_cols'=>'ar_bn,channel_name,trade_time,member,type,order_bn,column_sale_money,column_fee_money,money,status,confirm_money,unconfirm_money,charge_status,charge_time,monthly_status,column_delete',
            ));
    }

    function _views(){
        $arObj = $this->app->model('ar');
        $sub_menu = array(
            0 => array('label'=>app::get('base')->_('全部'),'filter'=>'','addon'=>$arObj->count(),'optional'=>false),
            1 => array('label'=>app::get('base')->_('未记账'),'filter'=>array('charge_status'=>0),'addon'=>$arObj->count(array('charge_status'=>0)),'optional'=>false),
            2 => array('label'=>app::get('base')->_('待核销'),'filter'=>array('status|noequal'=>2,'charge_status'=>1),'addon'=>$arObj->count(array('status|noequal'=>2,'charge_status'=>1)),'optional'=>false),
            3 => array('label'=>app::get('base')->_('已核销'),'filter'=>array('status'=>2),'addon'=>$arObj->count(array('status'=>2)),'optional'=>false),
        );
        return $sub_menu;
    }

    //批量记账
    public function do_charge(){
        $this->begin('javascript:finderGroup["'.$_GET['finder_id'].'"].refresh();');
        $arObj = app::get('finance')->model('ar');
        if($_POST['isSelectedAll'] == '_ALL_'){
            $where = $arObj->_filter($_POST);
            $sql = 'select ar_id from sdb_finance_ar where '.$where;
            $rs = kernel::database()->select($sql);
            $ids = array();
            foreach($rs as $v){
                $ids['ar_id'][] =$v['ar_id'];
            }
        }else{
            $ids = $_POST;
        }
        $res = kernel::single('finance_ar')->do_charge($ids);
        if($res['status'] == 'fail'){
            $this->end(false,$res['msg'],'javascript:finderGroup["'.$_GET['finder_id'].'"].refresh();');
        }
        $this->end(true,'操作成功！','javascript:finderGroup["'.$_GET['finder_id'].'"].refresh();');
    }

    public function do_cancel(){
        $id = $_GET['id'];
        $data = array('res'=>'succ','msg'=>'');
        $arObj = app::get('finance')->model('ar');
        $rs = $arObj->delete(array('ar_id'=>$id));
        if(!$rs){
            $data = array('res'=>'fail','msg'=>'作废不成功');
            echo json_decode($data);exit;
        }
        kernel::single('finance_ar_verification')->change_verification_flag($id);
        echo json_encode($data);
    }

    public function importTemplate_act($filter = array(),$params = array()){
        $this->pagedata['checkTime'] = $params['checkTime'];
        return $this->fetch('ar/io/import_filetype.html');
    }

    public function exportTemplate_act($filter = ''){
        return $this->fetch('ar/export.html');
    }
}