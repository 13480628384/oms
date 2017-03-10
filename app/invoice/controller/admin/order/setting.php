<?php
/**
 +----------------------------------------------------------
 * 订单发票设置
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_ctl_admin_order_setting extends desktop_controller
{
	/*------------------------------------------------------ */
    //-- 设置列表
    /*------------------------------------------------------ */
    function index()
    {
    	header("cache-control:no-store,no-cache,must-revalidate");
    	
        $oItem      = kernel::single('invoice_mdl_order_setting');
        $row        = $oItem->getList('*', '', 0, 1);
        $this->pagedata['item'] = $row[0];
		
        $this->page('admin/edit.html');
    }
    
    /*------------------------------------------------------ */
    //-- 编辑
    /*------------------------------------------------------ */
    function editor()
    {
        header("cache-control:no-store,no-cache,must-revalidate");
        
        $id     = $_GET['id'];
        $row    = array();
        if(!empty($id))
        {
            $oItem      = kernel::single('invoice_mdl_order_setting');
            $row        = $oItem->getList('*',array('sid'=>$id),0,1);
            $this->pagedata['item'] = $row[0];
        }
        
        $this->page('admin/edit.html');
    }
    
    /*------------------------------------------------------ */
    //-- 保存数据
    /*------------------------------------------------------ */
    function save()
    {
    	$this->begin('index.php?app=invoice&ctl=admin_order_setting&act=index');
    	
    	//
    	$data   = $_POST['item'];
    	unset($data['title']);
    	$data['dateline']      = time();
    	
    	//check
    	if(empty($data['bank']) || empty($data['bank_no']) || empty($data['telphone']))
    	{
    	   $this->end(false, '发票设置填写有误，请检查！');
    	}
    	
    	//
    	$oItem     = kernel::single("invoice_mdl_order_setting");
        $result    = $oItem->save($data);
        if($result)
        {
        	$this->setCache();
            $this->end(true, '新建成功');
        }else{
            $this->end(false, '新建失败');
        }
    }
    
    /*------------------------------------------------------ */
    //-- 管理发票内容
    /*------------------------------------------------------ */
    function manage()
    {
        header("cache-control:no-store,no-cache,must-revalidate");
        
        $oItem      = kernel::single('invoice_mdl_order_setting');
        $row        = $oItem->getList('sid, title', '', 0, 1);
        $row        = $row[0];

        $this->pagedata['item'] = $row;
		
        $this->page('admin/manage.html');
    }
    
    /*------------------------------------------------------ */
    //-- 增加发票内容
    /*------------------------------------------------------ */
    function add()
    {
        $this->begin();

        $title      = trim($_POST['title']);
        
        $oItem      = kernel::single('invoice_mdl_order_setting');
        $row        = $oItem->getList('sid, title', '',0,1);
        $row        = $row[0];

        if(!empty($row['title']))
        {
            array_push($row['title'], $title);
        }
        else
        {
            $row['title'][]      = $title;
        }
        
        $result    = $oItem->save($row);
        if($result)
        {
            $this->setCache();
            $this->end(true, '新建成功');
        }
        else
        {
            $this->end(false, '新建失败');
        }
    }
    
    /*------------------------------------------------------ */
    //-- 删除发票内容
    /*------------------------------------------------------ */
    function del()
    {
        $this->begin('index.php?app=invoice&ctl=admin_order_setting&act=manage');
        
        $key        = intval($_GET['key']);
        
        if(empty($_GET['key']) || !is_numeric($_GET['key']))
        {
            $this->end(false, '无效操作，请检查');
        }

        //
        $data       = array();
        $oItem      = kernel::single('invoice_mdl_order_setting');
        $row        = $oItem->getList('sid, title', '', 0, 1);
        $row        = $row[0];
       // $row['title']   = unserialize($row['title']);
        
        unset($row['title'][$key]);

        $result    = $oItem->save($row);
        if($result)
        {
            $this->setCache();
            $this->end(true, '删除成功');
        }else{
            $this->end(false, '删除失败');
        }
    }
    
    /*------------------------------------------------------ */
    //-- ajax删除发票内容
    /*------------------------------------------------------ */
    function remove()
    {
        $key        = intval($_POST['key']);
        $data       = array('res'=>'succ', 'msg'=>'');
        
        if(empty($_POST['key']) && $_POST['key']!='0')
        {
            $data       = array('res'=>'fail','msg'=>'无效操作，请检查');
            echo json_encode($data);exit;
        }

        //
        $oItem      = kernel::single('invoice_mdl_order_setting');
        $row        = $oItem->getList('sid, title', '', 0, 1);
        $row        = $row[0];
        
       // $row['title']   = unserialize($row['title']);        
        unset($row['title'][$key]);
        $result         = $oItem->save($row);

        if(!$result){
            $data       = array('res'=>'fail','msg'=>'删除不成功');
            echo json_encode($data);exit;
        }
        
        $this->setCache();
        echo json_encode($data);
    }
    
    /*------------------------------------------------------ */
    //-- 发票设置[生成缓存]
    /*------------------------------------------------------ */
    function setCache($data=null)
    {
        if(empty($data))
        {
            $data   = array();
            $oItem  = $this->app->model('order_setting');
            $data   = $oItem->getList('*');
        }
        
        $this->app->setConf('invoice.order_setting', $data);
    }
}