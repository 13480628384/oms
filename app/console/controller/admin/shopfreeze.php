<?php
/**
 * 店铺冻结列表
 *
 * @access public
 * @author wangbiao<wangbiao@shopex.cn>
 * @version $Id: shopfreeze.php 2016-01-18 13:00
 */
class console_ctl_admin_shopfreeze extends desktop_controller
{
    public function index()
    {
        $this->title = '店铺冻结明细查询';
        
        $params = array(
            'title' => $this->title,
            'finder_cols' => '*',
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>false,
            'orderBy' => 'last_modified DESC',
        );
        
        $this->finder('console_mdl_shopfreeze', $params);
    }
    
    public function show_shopfreeze_list()
    {
        $product_id    = intval($_GET['product_id']);
        $shop_id       = $_GET['shop_id'];
        
        if(empty($product_id) || empty($shop_id))
        {
            header("content-type:text/html; charset=utf-8");
            echo "<script>alert('无效操作，请检查');window.close();</script>";
            exit;
        }
        
        $omeShopFreezeObj    = app::get('ome')->model('shop_freeze');
        $statusObj           = kernel::single('ome_order_status');
        $shopObj             = app::get('ome')->model('shop');
        
        #店铺信息
        $shop_info    = $shopObj->dump(array('shop_id'=>$shop_id), 'name');
        
        #货品信息
        $oProduct     = app::get('ome')->model('products');
        $product_info = $oProduct->dump(array('product_id'=>$product_id), 'bn');
        $shop_info['product_bn']    = $product_info['bn'];
        
        $this->pagedata['shop_info'] = $shop_info;
        
        #订单信息
        $order_list  = array();
        $shop_freeze_num    = 0;
        $sql		= "SELECT o.order_id, o.order_bn, o.process_status, o.ship_status, o.confirm, o.shop_id, o.createtime, 
                       i.item_id, i.product_id, i.bn, i.nums, i.sendnum 
			           FROM sdb_ome_order_items AS i LEFT JOIN sdb_ome_orders AS o ON i.order_id=o.order_id 
	                   WHERE o.process_status in ('unconfirmed', 'is_retrial', 'is_declare', 'splitting') AND o.ship_status in('0', '2') 
			           AND o.status='active' AND i.`delete`= 'false' AND o.shop_id='". $shop_id ."' AND i.product_id=".$product_id;
        
        $dataList	= $omeShopFreezeObj->db->select($sql);
        
        foreach($dataList as $k=>$v)
        {
            $order_id    = $v['order_id'];
            
            $v['status']        = $statusObj->ship_status($v['ship_status']);
            $v['createtime']    = date("Y-m-d H:i:s",$v['createtime']);
            $v['pay_status']    = $statusObj->pay_status($v['pay_status']);
            $v['process_title'] = $statusObj->process_status($v['process_status']);
            $v['shop_name']     = $shop_info['name'];
            
            #防订单号重复
            $v['nums']       = $v['nums'] + intval($order_list[$order_id]['nums']);
            $v['sendnum']    = $v['sendnum'] + intval($order_list[$order_id]['sendnum']);
            
            #部分拆分订单_计算已审核的数量
            if($v['process_status'] == 'splitting')
            {
                $dly_sql	= "SELECT SUM(number) AS num FROM `sdb_ome_delivery_items_detail` AS did 
					           JOIN `sdb_ome_delivery` AS d ON d.delivery_id=did.delivery_id 
	                           WHERE did.order_item_id='". $v['item_id'] ."' AND did.product_id='". $product_id ."' 
	                           AND d.status NOT IN('back', 'cancel', 'return_back') AND d.is_bind='false'";
                $deliveryNum	 = $omeShopFreezeObj->db->selectrow($dly_sql);
                $v['dly_num']    = intval($deliveryNum['num']) + intval($order_list[$order_id]['dly_num']);
            }
            
            $order_list[$order_id]    = $v;
        }
        $this->pagedata['rows']      = $order_list;
        
        #手工重新计算货品对应的店铺冻结数量
        $shopFreezeLib    = kernel::single('ome_shop_freeze');
        $shop_freeze_num  = $shopFreezeLib->update_product_shop_freeze($shop_id, $product_id);
        $this->pagedata['shop_freeze_num']    = $shop_freeze_num;
        
        $this->singlepage('admin/stock/shop_freeze_list.html');
    }
}
