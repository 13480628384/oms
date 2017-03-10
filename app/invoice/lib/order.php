<?php
/**
 +----------------------------------------------------------
 * 创建订单后执行的操作[后置执行]
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-04-30 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_order
{
	//OME创建订单后，自动执行此操作
	function create_order_after($sdf)
	{
        if($sdf['is_tax'] == 'true' || $sdf['is_tax'] == '1')
        {
            $telphone   = ($sdf['consignee']['mobile'] ? $sdf['consignee']['mobile'] : $sdf['consignee']['telephone']);
            $sdf['tax_title']   = (trim($sdf['tax_title']) ? $sdf['tax_title'] : $sdf['consignee']['name']);
            
            $data       = array(
                            'order_id' => $sdf['order_id'],
                            'order_bn' => $sdf['order_bn'],
                            'total_amount' => $sdf['total_amount'],//订单总额
                            'tax_company' => $sdf['tax_title'],//发票抬头
            
                            'ship_name' => $sdf['consignee']['name'],//客户名称,默认调用收货人
                            'ship_area' => $sdf['consignee']['area'],//客户收货地区
                            'ship_addr' => $sdf['consignee']['addr'],//客户地址
                            'ship_tel' => $telphone,//客户电话
                        );

            $inOrder    = app::get('invoice')->model('order');
            $result     = $inOrder->insert_order($data);
        }
	}
}