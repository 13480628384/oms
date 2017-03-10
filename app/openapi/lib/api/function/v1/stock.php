<?php

class openapi_api_function_v1_stock extends openapi_api_function_abstract implements openapi_api_function_interface{

    public function getList($params,&$code,&$sub_msg){
    }
    
    /**
     * 获取该货品所有仓库下的库存
     *
     **/
    public function getAll($params,&$code,&$sub_msg){
        
        
        $filter = array();
        $filter['goods_bn']   = $params['goods_bn'];
        $filter['brand_name'] = $params['brand_name'];
        $filter['type_name']  = $params['type_name'];
        
        $page_no   = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $page_size = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);

        $offset = ($page_no-1) * $page_size;

        $data = kernel::single('openapi_data_original_stock')->getBnBranchStore($filter,$offset,$page_size);

        foreach ($data['lists'] as $k => $d) {
            unset($data['lists'][$k]['product_id']);
        }

        return $data;
    }
    
    public function getDetailList($params,&$code,&$sub_msg){
        $filter = array();
        $filter['product_bn'] = $params['product_bn'];
        $filter['branch_bn']  = $params['branch_bn'];

        $page_no = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $page_size = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);

        $offset = ($page_no-1) * $page_size;


        $data = kernel::single('openapi_data_original_stock')->getDetailList($filter,$offset,$page_size);

        return $data;
    }

    public function add($params,&$code,&$sub_msg){
    }
    
    
}