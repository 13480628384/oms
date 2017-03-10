<?php
/**
 +----------------------------------------------------------
 * 订单发票管理
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_ctl_admin_order extends desktop_controller
{
    public function __construct($app)
    {
        parent::__construct($app);
    }
    /*------------------------------------------------------ */
    //-- 列表
    /*------------------------------------------------------ */
    function index()
    {
    	$this->title    = '订单发票列表';
        $params         = 
                array('title'=>$this->title,
                    'use_buildin_set_tag'=>true,
                    'use_buildin_filter'=>true,
                    'use_buildin_tagedit'=>true,
                    'use_buildin_export'=>true,
                    'use_buildin_import'=>false,
                    'allow_detail_popup'=>true,
                    'use_buildin_recycle'=>false,
                    'use_view_tab'=>true,
                    'finder_cols'=>'tax_rate,invoice_no,ship_tax,ship_bank,ship_bank_no,print_num,remarks,ship_area,ship_addr,ship_tel',
                );
        
        $this->finder('invoice_mdl_order', $params);
    }

    /*------------------------------------------------------ */
    //-- 分类导航
    /*------------------------------------------------------ */
    function _views()
    {
        $mdl_order      = $this->app->model('order');
        $sub_menu = array(
            0 => array('label'=>__('全部'),'filter'=>$base_filter,'optional'=>false),
            1 => array('label'=>__('普通发票'),'filter'=>array('type_id'=>'0', 'is_status'=>'0', 'is_print'=>'1'), 'optional'=>false),
            2 => array('label'=>__('专业发票'),'filter'=>array('type_id'=>'1', 'is_status'=>'0', 'is_print'=>'1'), 'optional'=>false),
            3 => array('label'=>__('已完成'),'filter'=>array('is_status'=>'1', 'is_print'=>'1'),'optional'=>false),
            4 => array('label'=>__('已作废'),'filter'=>array('is_print'=>'2'),'optional'=>false),
        );
        
        $i=0;
        foreach($sub_menu as $k => $v)
        {
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $mdl_order->viewcount($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=invoice&ctl='.$_GET['ctl'].'&act=index&view='.$i++;
        }
        return $sub_menu;
    }
    
    /*------------------------------------------------------ */
    //-- 编辑
    /*------------------------------------------------------ */
    function editor()
    {
    	header("cache-control:no-store,no-cache,must-revalidate");
        
        $id     = intval($_GET['id']);
        $data   = array();

        //
        $sql        = "SELECT a.*, 
                            b.status, b.pay_status, b.pay_status, b.ship_status, b.createtime, b.cost_item, 
                            b.is_tax, b.cost_payment, b.total_amount, b.payed, b.cost_freight, 
                            b.print_status, b.order_type, b.paytime, b.ship_area as ship_region 
                            FROM ".DB_PREFIX."invoice_order as a 
                            LEFT JOIN ".DB_PREFIX."ome_orders as b ON a.order_id=b.order_id 
                            WHERE a.id='".$id."' AND a.is_status='0'";
        $data       = kernel::database()->select($sql);
        $data       = $data[0];
        
        if(empty($data))
        {
            die('没有相关记录，无法操作！');
        }
        elseif($data['is_status'] == 1)
        {
            die('已开发票，不能进行操作。');
        }
        $data['ship_area']      = ($data['ship_area'] ? $data['ship_area'] : $data['ship_region']);
        
        
        //配置缓存
        $setting    = $this->app->getConf('invoice.order_setting');
        $data['conf_title']     = $setting[0]['title'];
        if(empty($setting))
        {
            die('请先创建发票配置；进入订单发票管理-->发票设置,点击编辑后进行保存操作！');
        }
        
        $this->pagedata['item']     = $data;
        $this->page('admin/order_editor.html');
    }
    
    /*------------------------------------------------------ */
    //-- 保存
    /*------------------------------------------------------ */
    function save()
    {
        $this->begin('');
        $filter = $data = $row = array();
        $data               = $_POST['item'];
        unset($data['is_status']);
        
        //select
        $oItem      = kernel::single("invoice_mdl_order");
        $row        = $oItem->getList('id, is_status, tax_rate', array('id'=>$data['id']), 0, 1);
        $row        = $row[0];
        if($row['is_status'] == 1)
        {
           $this->end(false, '已开发票，不能进行操作。'); 
        }
        
        //税金由打印方计算
        $data['type_id']    = intval($data['type_id']);
        $filter['id']       = $data['id'];
        
        //check
        $data['title']      = trim($data['title']);
        if(empty($data['title']))
        {
        	$this->end(false, '请填写发票抬头信息。');
        }
        
        if($data['type_id']==1)
        {
        	if(empty($data['content']) || empty($data['tax_company']) || empty($data['ship_addr']) || 
        	empty($data['ship_tel']) || empty($data['ship_addr']) || empty($data['ship_bank_no']))
        	{
        		$this->end(false, '请完整填写发票信息。');
        	}
        }
        $operator_id    = kernel::single('desktop_user')->get_id();
        $data['operator']   = $operator_id;

        //        
        $result    = $oItem->update($data, $filter);
        if($result)
        {
        	//日志
        	$log_msg   = '发票信息更新成功';
        	$opObj     = app::get('ome')->model('operation_log');
        	$opObj->write_log('invoice_edit@ome', $data['id'], $log_msg);

            $this->end(true, $log_msg);
        }else{
            $this->end(false, '发票信息更新失败');
        }
    }
    
    /*------------------------------------------------------ */
    //-- 批量更新发票表中无批次号记录
    /*------------------------------------------------------ */
    function update_batch_number_no()
    {
    	$this->title   = '批量更新打印批次号';
        $msg    = '没有相关记录';
        
        $inOrder    = $this->app->model('order');
        
        $filter     = array();
        $count      = $inOrder->count($filter);
        if(empty($count))
        {
            $this->pagedata['data']     = $data(array('msg'=>$msg));
            $this->page('admin/batch_number.html');
            exit;
        }
        
        //page
        $page           = intval($_GET['page']);
        $tpp            = 100;
        $page           = isset($page) ? max(1, intval($page)) : 1;
        $start_limit    = ($page - 1) * $tpp;
        
        //List
        $dataList      = $inOrder->getList('id, order_id, order_bn, batch_number', $filter, $start_limit, $tpp, 'createtime desc');
        $count_num     = count($dataList);

        $deliveryObj    = app::get('ome')->model('delivery');
        $mdl_queue      = app::get('ome')->model('print_queue');
        $list_i         = 0;
        foreach ($dataList as $key => $val)
        {
        	if(empty($val['batch_number']))
        	{
                //发货单批次号
                $batch_number       = '';
                $deliveryIds        = $deliveryObj->getDeliverIdByOrderId($val['order_id']);

                if($deliveryIds[0])
                {
                    $ident              = $mdl_queue->findIdent($deliveryIds[0]);
                    $batch_number       = $ident;
                }
                
                if(!empty($batch_number))
                {
                	$new_data      = array('batch_number' => $batch_number);
                	$filter        = array('order_id' => $val['order_id']);
                    $inOrder->update($new_data, $filter);
                    $list_i++;
                }
        	}
        }
        
        //日志
        if($count_num)
        {
        	$batch_count   = $page * $tpp;
        	$batch_count   = $batch_count > $count ? $count : $batch_count;
            $msg    = '批量更新批次号(共'.$count.'条，已执行'.$batch_count.'条)，请耐心等待....';
        }
        else
        {
            $msg    = '全部更新完成，本次共处理'.$count.'条记录.成功更新'.$list_i.'条记录.';
        }
        
        //
        $page     = $page + 1;
        $data     = array(
                       'page'=>$page,
                       'link'=>'/index.php?app=invoice&ctl=admin_order&act=update_batch_number_no&page='.$page,
                       'count'=>$count_num,
                       'msg'=>$msg,
                   );
        $this->pagedata['data']     = $data;
        $this->page('admin/batch_number.html');
    }
}
