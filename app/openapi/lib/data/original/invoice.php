<?php
/**
 +----------------------------------------------------------
 * Api接口[数据处理]
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class openapi_data_original_invoice
{
    /*------------------------------------------------------ */
    //-- 待打印发票列表
    /*------------------------------------------------------ */
    public function getList($filter, $offset=0, $limit=100)
    {
    	$result        = array('code'=>'0', 'message'=>'error');
        if(empty($filter['start_time']) || empty($filter['end_time']))
        {
            return $result;
        }
        
        //发票配置
        $setting        = app::get('invoice')->getConf('invoice.order_setting');

        //sql
        $where  = '';
        
        $where      .= " AND a.create_time>='".$filter['start_time']."'";
        $where      .= " AND a.create_time<='".$filter['end_time']."'";
 
        if(!empty($filter['type_id']) || $filter['type_id'] === 0)
        {
            $where  .= " AND a.type_id='".$filter['type_id']."'";
        }
        if(!empty($filter['is_print']))
        {
            $where  .= " AND a.is_print='".$filter['is_print']."'";
        }
        if(!empty($filter['batch_no']))
        {
            $where  .= " AND a.batch_number like '".$filter['batch_no']."%'";//批次号模糊匹配
        }
        $where      .= " AND a.is_status='".$filter['is_status']."'";
        $where      .= " AND b.process_status='splited'";//订单确认状态
        $where      = substr($where, 5);
        
        $sql        = "SELECT a.order_id, a.order_bn, a.is_status, a.type_id, a.amount, a.cost_tax, a.title, a.content, a.remarks, a.batch_number, 
                            a.delivery_id, a.tax_company, a.ship_area, a.ship_addr, a.ship_tel, a.ship_tax, a.ship_bank, a.ship_bank_no, 
                            b.createtime, b.ship_status, b.total_amount 
                            FROM ".DB_PREFIX."invoice_order as a 
                            LEFT JOIN ".DB_PREFIX."ome_orders as b ON a.order_id=b.order_id 
                            WHERE ".$where." LIMIT ".$offset.", ".$limit;
        $data       = kernel::database()->select($sql);
        $oOrder     = app::get('ome')->model('orders');
        $oDelivery  = app::get('ome')->model('print_queue_items');//批次号表
        $formatFilter=kernel::single('openapi_format_abstract');
        $item_list  = array();
        
        //format
        foreach ($data as $key => $val)
        {
        	//关联所有商品明细
        	if($val['content'] == '商品明细')
        	{
                $item_list    = $oOrder->order_objects($val['order_id']);
                foreach ($item_list as $key_j => $val_j)
                {
                    $val['product']      = array('obj_id'=>$val_j['obj_id'], 'obj_alias'=>$val_j['obj_alias'], 'name'=>$formatFilter->charFilter($val_j['name']), 'price'=>$val_j['price'], 
                                                 'pmt_price'=>$val_j['pmt_price'], 'sale_price'=>$val_j['sale_price'], 'quantity'=>$val_j['quantity']);
                    //明细关联商品
                    foreach ($val_j['order_items'] as $key_k => $val_k)
                    {
                        $val['product']['order_items'][$key_k]   = array('item_id'=>$val_k['item_id'], 'name'=>$formatFilter->charFilter($val_k['name']), 'price'=>$val_k['price'], 'pmt_price'=>$val_k['pmt_price'],
                                                    'sale_price'=>$val_k['sale_price'], 'nums'=>$val_k['nums'], 'addon'=>unserialize($val_k['addon']));
                    }
                }
        	}
        	
            //地区
            $areaArr        = array();
	        $areaArr           = explode(':', $val['ship_area']);
	        $areaArr           = $areaArr[1];
	        $val['ship_area']  = str_replace('/', ' ', $areaArr);
	        $val['ship_addr']  = $val['ship_area'] . $val['ship_addr'];
	        
	        //完整批次号[第4段]
	        if($val['batch_number'] && $val['delivery_id'])
	        {
	            $delivery_arr  = $oDelivery->getList('ident_dly', array('delivery_id' => $val['delivery_id'], 'ident' => $val['batch_number']), 0, 1);
	            $val['batch_number']   = $val['batch_number'].'_'.$delivery_arr[0]['ident_dly'];
	        }
	        
	        unset($val['ship_area']);
	        $data[$key]           = $val;
        }
        
        unset($setting[0]['sid']);
        unset($setting[0]['title']);
        unset($setting[0]['dateline']);       
        $result     = array('code'=>'100', 'message'=>'success', 'corp'=>$setting[0], 'list'=>$data);
        return $result;
    }
    
    /*------------------------------------------------------ */
    //-- 更新订单发票的打印状态
    /*------------------------------------------------------ */
    public function update($data, $method)
    {
    	$Invoice       = app::get('invoice')->model('order');
    	$opObj         = app::get('ome')->model('operation_log');
    	
    	$filter        = $row = array();
    	$log_msg       = '';
    	$count         = 0;
    	foreach ($data as $key => $val)
    	{
    		$filter           = array('order_id'=>$val['order_id'], 'is_print'=>'1');
    		
    		//chk是否有打印历史
    		$val['print_num']     = 1;

    		$row          = $Invoice->getList('order_id, is_status, print_num', $filter, 0, 1);
    		if($row[0]['is_status'])
    		{
    			$val['is_status']    = 1;
    			$val['print_num']    = intval($row[0]['print_num']) + 1;
    			
    			unset($val['invoice_no']);//不再更新_发票号
    		}
    		elseif($row[0]['order_id'])
    		{
                $val['dateline']    = time();
    		}
    		else
    		{
    		  $val['is_status']    = 0;//无效打印
    		}
    		
    		if(!empty($val['invoice_no']) && $val['is_status'] == 1)
    		{
    			$log_msg   = $method.'发票打印成功';
    		}
    		else
    		{
    			$log_msg   = $method.'发票打印失败';
    		}
    		
    		//update
            $Invoice->update($val, $filter);
    		
    		//log
            $opObj->write_log('invoice_print@ome', $val['order_id'], $log_msg);
            
            $count++;
    	}
    	
    	return array(
                'msg' => 'success',
                'message' => '更新订单发票的打印状态完成.',
    	        'method' => $method,
    	        'count' => $count,
            );
    }
}