<?php

class openapi_api_function_v1_goods extends openapi_api_function_abstract implements openapi_api_function_interface{

    public function getList($params,&$code,&$sub_msg)
    {
        $params = array_filter($params);
        $filter = array();

        if (isset($params['brand_name'])) {
            $brandModel = app::get('ome')->model('brand');
            $brand = $brandModel->dump(array('brand_name' => $params['brand_name']));

            if (!$brand) return array('list' => array(),'count' => '0');

            $filter['brand_id'] = $brand['brand_id'];
        }

        if (isset($params['type_name'])) {
            $goodsTypeModel = app::get('ome')->model('goods_type');
            $gtype = $goodsTypeModel->dump(array('name' => $params['type_name']));

            if (!$gtype) return array('list' => array(), 'count' => '0');

            $filter['type_id'] = $gtype['type_id'];
        }

        if (isset($params['goods_bn'])) {
            $filter['bn'] = $params['goods_bn'];
        }

        if (isset($params['start_lastmodify'])) $params['start_lastmodify'] = strtotime($params['start_lastmodify']);
        if (isset($params['end_lastmodify'])) $params['end_lastmodify'] = strtotime($params['end_lastmodify']);

        if (isset($params['start_lastmodify']) && isset($params['end_lastmodify'])) {
            $filter['last_modify|between'] = array($params['start_lastmodify'],$params['end_lastmodify']);
        } elseif (isset($params['start_lastmodify']) && !isset($params['end_lastmodify'])) {
            $filter['last_modify|bthan'] = $params['start_lastmodify'];
        } elseif (!isset($params['start_lastmodify']) && isset($params['end_lastmodify'])) {
            $filter['last_modify|sthan'] = $params['end_lastmodify'];
        }

        $page_no = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $limit   = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);

        $data = kernel::single('openapi_data_original_goods')->getList($filter,($page_no-1)*$limit,$limit);
          return $data;
    }

    public function add($params,&$code,&$sub_msg){
        
        $rs = kernel::single('openapi_data_original_goods')->add($params);

        return $rs;

        
    }
    public function edit($params,&$code,&$sub_msg){       
        $rs = kernel::single('openapi_data_original_goods')->edit($params);
        return $rs;        
    }
}