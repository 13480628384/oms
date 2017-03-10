<?php
/**
 +----------------------------------------------------------
 * Api接口[返回数据xml,json]
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class openapi_api_function_v1_invoice extends openapi_api_function_abstract implements openapi_api_function_interface
{
	/*------------------------------------------------------ */
    //-- 待打印发票列表
    /*------------------------------------------------------ */
    public function getList($params, &$code, &$sub_msg)
    {
        $start_time     = strtotime($params['start_time']);
        $end_time       = strtotime($params['end_time']);
		
        if($start_time <=0 || $end_time <= 0)
        {
            $result        = array('code'=>'0', 'message'=>'Please check the time range');
            return $result;
        }        
        $start_time     = date('Y-m-d', $start_time).' 00:00:00';
        $end_time       = date('Y-m-d', $end_time).' 23:59:59';
        $start_time     = strtotime($start_time);
        $end_time       = strtotime($end_time);
		
        $type_id        = intval($params['type_id'])==2 || intval($params['type_id']) == 1 || $params['type_id'] === '0' ? intval($params['type_id']) : '';
        $batch_no       = trim($params['batch_no']);
        $is_status      = intval($params['status'])==1 ? 1 : 0;
        $page_no        = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $page_size      = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);

        $offset         = ($page_no - 1) * $page_size;
        
        //filter
        $filter         = array(
                            'start_time' => $start_time,//开始日期
                            'end_time' => $end_time,
                            'is_print' => 1,//是否打印
                            'type_id' => $type_id,//普通|专业发票
                            'batch_no' => $batch_no,//订单发货批次号
                            'is_status' => $is_status,//已开票
                            'page_no' => $page_no,
                            'page_size' => $page_size,
                          );
        $result       = kernel::single('openapi_data_original_invoice')->getList($filter, $offset, $page_size);

    	return $result;
    }
    
    /*------------------------------------------------------ */
    //-- 更新订单发票的打印状态
    /*------------------------------------------------------ */
    public function update($params, &$code, &$sub_msg)
    {
    	$data              = array();
    	$params['act']     = strtolower($params['act']);
    	if($params['act'] == 'get')
    	{
    		$data[0]['order_id']     = intval($params['order_id']);
    		$data[0]['invoice_no']   = trim($params['invoice_no']);
    		$data[0]['is_status']    = intval($params['is_status']);
    		$data[0]['print_time']   = strtotime($params['print_time']);
    		//$data[0]['amount']       = floatval($params['amount']);    		
    		$data[0]['tax_rate']     = intval($params['tax_rate']);
    		$data[0]['cost_tax']     = floatval($params['cost_tax']);
    	}
    	elseif($params['act'] == 'post')
    	{
    		$data = json_decode($_POST['datalist'], true);
    		foreach ($data as $key => $val)
    		{
    			$val['order_id']     = intval($val['order_id']);
    			$val['invoice_no']   = trim($val['invoice_no']);
                $val['is_status']    = intval($val['is_status']);
                $val['print_time']   = strtotime($val['print_time']);
                //$val['amount']       = floatval($val['amount']);
                $val['tax_rate']     = intval($val['tax_rate']);
                $val['cost_tax']     = floatval($val['cost_tax']);

    			$data[$key]      = $val;
    		}
    	}
    	
        $result     = kernel::single('openapi_data_original_invoice')->update($data, $params['act']);

        return $result;
    }
    
    /*------------------------------------------------------ */
    //-- add必须存在
    /*------------------------------------------------------ */
    public function add($params,&$code,&$sub_msg){ }
}