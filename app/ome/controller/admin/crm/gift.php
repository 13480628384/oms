<?php
/**
 * User: jintao
 * Date: 2016/1/28
 * Time: 11:31
 */
class ome_ctl_admin_crm_gift extends desktop_controller
{
    public function rule($act){
        $this->finder('ome_mdl_gift_rule',array(
            'title'=>'促销规则列表',
            'actions'=>array(
                array(
                    'label'=>'添加促销规则',
                    'href'=>'index.php?app=ome&ctl=admin_crm_gift&act=addAndEdit&p[0]=add&shop_id='.$_GET['shop_id'].'&view='.$view,
                    //'target'=>'dialog::{width:700,height:380,title:\'添加促销规则\'}'
                ),
            ),
            //'base_filter'=>$base_filter,
            'orderBy' => 'status DESC,priority DESC,id DESC',
            'use_buildin_recycle' => false,
        ));
    }

    /**
     * 新增页面显示
     */
    public function addAndEdit(){
        $shopObj =  app::get('ome')->model('shop');
        $shop_id = $_GET['shop_id'];
        $id = intval($_GET['id']);

//        $shops_name = $shopObj->getList('shop_id,name',array('node_id|noequal'=>''));
        $shops_name = $shopObj->getList('shop_id,name');
        $shops = $shops_name;

        $rule = array(
            'start_time' => strtotime(date('Y-m-d')),
            'status' => 1,
            'shop_id' => $shop_id,
            'time_type' => 'pay_time',
            'lv_id' => 0,
            'filter_arr' => array(
                'order_amount' => array(
                    'type'=>0
                ),
                'buy_goods' => array(
                    'type'=>0
                ),
            ),
        );

        $rs = app::get('eccommon')->model('regions')->getList('local_name',array('region_grade'=>1,'region_id|sthan'=>3320));
        $provinces = $rs;

        $rule_obj = $this->app->model('gift_rule');

        //修改规则信息
        $goods_name = '';
        if($id>0){
            $rule = $rule_obj->dump($id);
            $rule['filter_arr'] = json_decode($rule['filter_arr'], true);

            $goods_bn = $rule['filter_arr']['buy_goods']['goods_bn'];
            if( ! is_array($goods_bn)){
                $goods_bn = array($goods_bn,'','','','','','','','','');
            }
            $rule['filter_arr']['buy_goods']['goods_bn'] = $goods_bn;

            if($rule['shop_ids']){
                $rule['shop_ids'] = explode(',', $rule['shop_ids']);
            }else{
                $rule['shop_ids'] = array($rule['shop_id']);
            }

            foreach($shops as $ks => &$vs){
                if(in_array($vs['shop_id'], $rule['shop_ids'])){
                    $vs['checked'] = 'true';
                } else {
                    $vs['checked'] = 'false';
                }
            }


            if(isset($rule['filter_arr']['province'])){
                foreach($provinces as $keys => &$vals){
                    if(in_array($vals['local_name'], $rule['filter_arr']['province'])){
                        $vals['checked'] = 'true';
                    } else{
                        $vals['checked'] = 'false';
                    }
                }
            }

        }else{
            $rule['filter_arr']['buy_goods']['goods_bn'] = array('','','','','','','','','','');
        }

        //已经设定的赠品组合
        $gifts = array();
        if($rule['gift_ids']){
            $gift_ids = explode(',', $rule['gift_ids']);
            $gift_num = explode(',', $rule['gift_num']);

            foreach($gift_ids as $k=>$v){
                $gift_id_num[$v] = $gift_num[$k];
            }

            $gifts = app::get('crm')->model('gift')->getList('*,"checked" as checked',array('gift_id'=>$gift_ids,'gift_num|bthan'=>1));
            foreach($gifts as $k=>$v){
                $gifts[$k]['gift_name'] = mb_substr($v['gift_name'],0,22,'utf-8');
                $gifts[$k]['num'] = $gift_id_num[$v['gift_id']];
            }
        }else{
            //$gifts = $this->app->model('shop_gift')->getList('*',array(),0,5);
        }



        $rule['start_time_hour'] = 0;
        if($rule['start_time']){
            $rule['start_time_hour'] = (int)date('H', $rule['start_time']);
        }

        $rule['end_time_hour'] = 0;
        if($rule['end_time']){
            $rule['end_time_hour'] = (int)date('H', $rule['end_time']);
        }
//echo '<pre>';print_r($shops);exit;
        $this->pagedata['conf_hours'] = array_merge(array('00','01','02','03','04','05','06','07','08','09'),range(10,23));
        $this->pagedata['provinces'] = $provinces;
        $this->pagedata['goods_name'] = $goods_name;
        $this->pagedata['shops'] = $shops;
        $this->pagedata['gifts'] = $gifts;
        $this->pagedata['rule'] = $rule;
        $this->pagedata['view'] = $_GET['view'];
        $this->pagedata['beigin_time'] = date("Y-m-d",time());
        $this->pagedata['end_time'] = date('Y-m-d',strtotime('+15 days'));
        $this->page('admin/gift/rule_edit.html');
    }

    public function logs(){
        $actions = array();
        $base_filter = array();
        $this->finder('ome_mdl_gift_logs',array(
            'title'=>'赠品发送记录',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'orderBy' => 'id DESC',
            'use_buildin_recycle' => false,
            //'use_buildin_filter' => true,
            'use_view_tab' => true,
        ));
    }

    public function setgift(){
        if(isset($_POST['set_type'])){
            $url = 'index.php?app=ome&ctl=admin_crm_gift&act=setgift';
            $this->begin($url);

            $set_type = $_POST['set_type'];
            $arr = array(
                'set_type' => $set_type=='include' ? 'include' : 'exclude',
                'op_user' => kernel::single('desktop_user')->get_name(),
                'create_time' => time(),
            );
            $this->app->model('gift_set_logs')->insert($arr);

            $this->end(true,'保存成功');
        }

        //以最后一次设定的模式为准
        //默认为叠加 include
        $set_type = 'include';
        $rs = $this->app->model('gift_set_logs')->getList('set_type', '', 0, 1, 'id DESC');
        if($rs){
            $set_type = $rs[0]['set_type'];
        }
        $this->pagedata['set_type'] = $set_type;

        $extra_view = array('ome'=>'admin/gift/set.html');

        $gift_on_off = $this->app->getConf('gift.on_off.cfg');
        $gift_error_ways = $this->app->getConf('gift.error.ways');

        $this->pagedata['gift_on_off'] = $gift_on_off;
        $this->pagedata['gift_error_ways'] = $gift_error_ways;

        $actions = array();
        $base_filter = array();
        $this->finder('ome_mdl_gift_set_logs',array(
            'title'=>'赠品设置',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'orderBy' => 'id DESC',
            'use_buildin_recycle' => false,
            'use_buildin_filter' => false,
            'use_view_tab' => false,
            'top_extra_view' => $extra_view,
            'use_buildin_setcol' => false,
            'use_buildin_refresh' => false,
        ));
    }


    /**
     * 规则保存
     */
    public function save_rule(){

        $this->begin('index.php?app=ome&ctl=admin_crm_gift&act=rule');
        $gift_rule_obj = app::get('ome')->model('gift_rule');

        $data = $_POST;
        $data['filter_arr'] = $_POST['filter_arr'];
        $data['gift_ids'] = $_POST['gift_id'];
        $data['gift_num'] = $_POST['gift_num'];
        $data['shop_ids'] = $_POST['shop_ids'];
        $data['start_time'] = strtotime($_POST['start_time'].' '.$_POST['start_time_hour'].':00:00');
        $data['end_time'] = strtotime($_POST['end_time'].' '.$_POST['end_time_hour'].':00:00');
        $data['modified_time'] = time();

        if(is_array($data['shop_ids']) && count($data['shop_ids'])>10){
            $this->end(false, '最多只能选择十个店铺');
        }

        $data['shop_id'] = $data['shop_ids'][0];
        $data['shop_ids'] = implode(',', $data['shop_ids']);

        if($data['filter_arr']['buy_goods']['goods_bn']){
            foreach($data['filter_arr']['buy_goods']['goods_bn'] as $v){
                $v = strtoupper($v);
            }
        }
        $data['filter_arr'] = json_encode($data['filter_arr']);

        if(!$data['id']) $data['create_time'] = time();

        //清理gift_num
        foreach($data['gift_num'] as $k=>$v){
            if(!in_array($k, $data['gift_ids'])){
                unset($data['gift_num'][$k]);
            }
        }

        $data['gift_ids'] = implode(',', $data['gift_ids']);
        $data['gift_num'] = implode(',', $data['gift_num']);

        if($data['id']){
            // 数据快照
            $obj_service = kernel::servicelist('data.snapshot');
            foreach($obj_service as $obj){
                if(method_exists($obj, 'gift_rule_change')){
                    $sdf = $gift_rule_obj->dump($data['id'], '*');
                    $sdf = array(
                        'title' => '赠品规则发生变动',
                        'content' => json_encode($sdf),
                        'task_id' => $data['id'],
                    );
                    $obj->gift_rule_change($sdf);
                }
            }
        }

        if($gift_rule_obj->save($data)){
            $this->end(true, '添加成功');
        } else {
            $this->end(false, '添加失败');
        }

    }

    public function priority($id=0){
        if($_POST){
            $this->begin("index.php?app=ome&ctl=admin_crm_gift&act=rule");
            $shopGiftObj = app::get('ome')->model('gift_rule');
            $data = $_POST;
            $data['priority'] = intval($_POST['priority']);
            $data['modified_time'] = time();
            if($shopGiftObj->save($data)){
                $this->end(true,'添加成功');
            }else{
                $this->end(false,'添加失败');
            }
        }

        //修改规则信息
        if($id>0){
            $rule = $this->app->model('gift_rule')->dump($id);

            $rule['start_time'] = date("Y-m-d", $rule['start_time']);
            $rule['end_time'] = date("Y-m-d", $rule['end_time']);
        }

        $this->pagedata['rule'] = $rule;
        $this->pagedata['view'] = $_GET['view'];
        $this->display('admin/gift/priority.html');
    }


    function ajax_set_derail(){
        $derail = $_POST['on_or_off'];
        $rst = $this->app->setConf('gift.on_off.cfg', $derail);
        echo json_encode($rst);
    }

    function ajax_set_dealway(){
        $ways = $_POST['dealways'];
        $rst = $this->app->setConf('gift.error.ways', $ways);
        echo json_encode($rst);
    }

}
