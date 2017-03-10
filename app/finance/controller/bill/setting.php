<?php
class finance_ctl_bill_setting extends desktop_controller{


    public function index(){
        //显示数据不取缓存数据，取表中的数据
    	// $feeitemObj = app::get('finance')->model('bill_fee_item');
    	// $fee_item_tmp = $feeitemObj->getList('fee_item_id,fee_type_id,fee_item,inlay',array('delete'=>'false'));
     //    $fee_item = array();
     //    foreach($fee_item_tmp as $v){
     //        $fee_type = app::get('finance')->model('bill_fee_type')->getList('fee_type',array('fee_type_id'=>$v['fee_type_id']));
     //        $fee_item[$v['fee_type_id']]['name'] = $fee_type[0]['fee_type'];
     //        $fee_item[$v['fee_type_id']]['item'][$v['fee_item_id']]['inlay'] = $v['inlay'];
     //        $fee_item[$v['fee_type_id']]['item'][$v['fee_item_id']]['name']= $v['fee_item'];
     //        $fee_item[$v['fee_type_id']]['item'][$v['fee_item_id']]['fee_item_id']= $v['fee_item_id'];
     //    }

     //    $this->pagedata['fee_item'] = $fee_item;
     //    $this->page('bill/feemap.html');
        $params = array(
            'use_buildin_recycle' => false,
            'base_filter' => array(
                'fee_type_id|noequal' => '9',
            ),
        );

        $this->finder('finance_mdl_bill_fee_type',$params);
    }

    public function additem($fee_type_id = ''){
        $this->pagedata['fee_type_id'] = $fee_type_id;
        $this->page('bill/feeitem_add.html');
    }

    public function do_additem(){
        if($_POST){
            $this->begin();
            $res =array('status'=>'succ');
            $type_id = $_POST['fee_type_id'];
            $fee_item = $_POST['name'];
            if(empty($fee_item)){
                $res=array('status'=>'fail','msg'=>'请输入费用项名称');
                $this->end(false,$res['msg'],'',$rs);
                return;
            }
            $rs = kernel::single('finance_bill')->is_exist_item_by_table($fee_item);
            if($rs == 'false'){
                $res=array('status'=>'fail','msg'=>'费用项已存在');
                $this->end(false,$res['msg'],'',$res);
                return;
            }
            $data = kernel::single('finance_bill')->add_fee_item($type_id,$fee_item);
            if(!$data){
                $res=array('status'=>'fail','msg'=>'保存不成功');
                $this->end(false,$res['msg'],'',$res);
                return;
            }
            $res =array('status'=>'succ','msg'=>'保存成功');
            $this->end(true,$res['msg'],'',$res);
        }
    }

    //删除自定义的费用项
    public function do_delitem($fee_item_id = ''){
        $this->begin();
        $res =array('status'=>'succ');
        $data['fee_item_id']= $fee_item_id ? $fee_item_id : $_POST['fee_item_id'];
        $data['delete'] = 'true';
        $feeitemObj = app::get('finance')->model('bill_fee_item');
        $rs = $feeitemObj->save($data);
        if(!$rs){
            $res =array('status'=>'fail','msg'=>'删除失败');
            $this->end(false,$res['msg'],'',$res);
        }
        $this->end(true,'','',$res);
    }
}