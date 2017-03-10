<?php

class openapi_api_function_v1_pkg extends openapi_api_function_abstract implements openapi_api_function_interface{

    public function getList($params,&$code,&$sub_msg){
        $filter = array();

        if ($params['pkg_bn']) {
            $filter['pkg_bn'] = explode(',', $params['pkg_bn']);
        }

        $page_no = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $limit = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 40 : intval($params['page_size']);

        if($page_no == 1){
            $offset = 0;
        }else{
            $offset = ($page_no-1)*$limit;
        }

        $original_sales_data = kernel::single('openapi_data_original_pkg')->getList($filter,$offset,$limit);

        $lists = array();
        foreach ($original_sales_data['lists'] as $goods_id => $pkg){
            $lists[$goods_id]['pkg_bn']   = $this->charFilter($pkg['pkg_bn']);
            $lists[$goods_id]['pkg_name'] = $this->charFilter($pkg['name']);
            $lists[$goods_id]['weight']   = $pkg['weight'];
            foreach ($pkg['products'] as $product_id => $product){
                $lists[$goods_id]['products'][$product_id]['bn']     = $this->charFilter($product['bn']);
                $lists[$goods_id]['products'][$product_id]['name']   = $this->charFilter($product['name']);
                $lists[$goods_id]['products'][$product_id]['nums']   = $product['pkgnum'];
                $lists[$goods_id]['products'][$product_id]['price']  = $product['price'];
                $lists[$goods_id]['products'][$product_id]['weight'] = $product['weight'];
            }
        }

        unset($original_sales_data['lists']);
        $original_sales_data['lists'] = $lists;

        return $original_sales_data;
    }

    public function add($params,&$code,&$sub_msg){}
}