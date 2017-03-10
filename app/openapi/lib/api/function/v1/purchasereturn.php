<?php

class openapi_api_function_v1_purchasereturn extends openapi_api_function_abstract implements openapi_api_function_interface{
    
    public function add($params,&$code,&$sub_msg){
        $data = kernel::single('openapi_data_original_purchasereturn')->add($params);
        return $data;
    }

    public function getList ($params,&$code,&$sub_msg)
    {
    	$page_no = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
    	$limit = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);
    	
    	if($page_no == 1){
            $offset = 0;
        }else{
            $offset = ($page_no-1)*$limit;
        }

        unset($params['page_no']);
    	unset($params['page_size']);
    	$result = kernel::single('openapi_data_original_purchasereturn')->getList ($params,$offset,$limit);
        foreach ($result as $return ) {
            $return['po_time'] = date('Y-m-d H:i:s',$return['po_time']);
        }
    	// todo定义返回结构
    
    
    	return $result;
    }
    
 
}