<?php

class openapi_api_function_v1_sales extends openapi_api_function_abstract implements openapi_api_function_interface{

    public function getList($params,&$code,&$sub_msg){

        $start_time = strtotime($params['start_time']);
        $end_time = strtotime($params['end_time']);
        $page_no = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $limit = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);
        $filter['shop_bn'] = $params['shop_bn'];
        if($page_no == 1){
            $offset = 0;
        }else{
            $offset = ($page_no-1)*$limit;
        }

        $original_sales_data = kernel::single('openapi_data_original_sales')->getList($filter,$start_time,$end_time,$offset,$limit);

        $sale_arr = array();
        foreach ($original_sales_data['lists'] as $k => $sale){
                $sales_arr[$k]['shop_code'] = $this->charFilter($sale['shop_bn']);
                $sales_arr[$k]['shop_name'] = $this->charFilter($sale['shop_name']);
                $sales_arr[$k]['order_no'] = $this->charFilter($sale['order_bn']);
                $sales_arr[$k]['member_name'] = $this->charFilter($sale['member_name']);
                $sales_arr[$k]['sale_no'] = $this->charFilter($sale['sale_bn']);
                $sales_arr[$k]['pay_method'] = $this->charFilter($sale['payment']);
                $sales_arr[$k]['sale_time'] = date('Y-m-d H:i:s',$sale['sale_time']);
                $sales_arr[$k]['order_create_time'] = date('Y-m-d H:i:s',$sale['order_create_time']);
                $sales_arr[$k]['pay_time'] = date('Y-m-d H:i:s',$sale['paytime']);
                $sales_arr[$k]['ship_time'] = date('Y-m-d H:i:s',$sale['ship_time']);
                $sales_arr[$k]['order_check_op'] = $this->charFilter($sale['order_check_name']);
                $sales_arr[$k]['order_check_time'] = date('Y-m-d H:i:s',$sale['order_check_time']);
                $sales_arr[$k]['goods_amount'] = $sale['total_amount'];
                $sales_arr[$k]['freight_amount'] = $sale['cost_freight'];
                $sales_arr[$k]['additional_amount'] = $sale['additional_costs'];
                $sales_arr[$k]['has_tax'] = $sale['is_tax'] == 'false' ? '否' : '是';
                $sales_arr[$k]['pmt_amount'] = $sale['discount'];
                $sales_arr[$k]['sale_amount'] = $sale['sale_amount'];
                $sales_arr[$k]['logi_name'] = $this->charFilter($sale['logi_name']);
                $sales_arr[$k]['logi_no'] = $this->charFilter($sale['logi_no']);
                $sales_arr[$k]['branch_name'] = $this->charFilter($sale['branch_name']);
                $sales_arr[$k]['branch_bn'] = $this->charFilter($sale['branch_bn']);
                $sales_arr[$k]['delivery_no'] = $this->charFilter($sale['delivery_bn']);
                $sales_arr[$k]['consignee'] = $this->charFilter($sale['ship_name']);
                $sales_arr[$k]['consignee_area'] = $sale['ship_area'];
                $sales_arr[$k]['consignee_addr'] = $this->charFilter($sale['ship_addr']);
                $sales_arr[$k]['consignee_zip'] = $this->charFilter($sale['ship_zip']);
                $sales_arr[$k]['consignee_tel'] = $this->charFilter($sale['ship_tel']);
                $sales_arr[$k]['consignee_mobile'] = $this->charFilter($sale['ship_mobile']);
                $sales_arr[$k]['consignee_email'] = $this->charFilter($sale['ship_email']);
                //delivery_cost_actual,order_memo
                $sales_arr[$k]['weight'] = $sale['weight'];
                $sales_arr[$k]['delivery_cost_actual'] = $sale['delivery_cost_actual'];
                $sales_arr[$k]['order_memo'] = $sale['order_memo'];
                $sales_arr[$k]['tax_title'] = $sale['tax_title'];
                foreach ($sale['sale_items'] as $key => $sale_item){
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['bn'] = $sale_item['bn'];
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['name'] = $this->charFilter($sale_item['name']);
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['spec_name'] = $this->charFilter($sale_item['spec_name']);
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['barcode'] = $sale_item['barcode'];                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['price'] = $sale_item['price'];
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['nums'] = $sale_item['nums'];
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['pmt_price'] = $sale_item['pmt_price'];
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['sale_price'] = $sale_item['sale_price'];
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['apportion_pmt'] = $sale_item['apportion_pmt'];
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['sales_amount'] = $sale_item['sales_amount'];
                    $sales_arr[$k]['sale_items'][$sale_item['item_id']]['item_type'] = $this->charFilter($sale_item['item_type']);
                    
                }
        }

        unset($original_sales_data['lists']);
        $original_sales_data['lists'] = $sales_arr;

        return $original_sales_data;
    }

    public function add($params,&$code,&$sub_msg){
        
    }
    public function getSalesAmount($params,&$code,&$sub_msg){
        $start_time = strtotime($params['start_time']);
        $end_time = strtotime($params['end_time']);
        $page_no = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $limit = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);
        if($page_no == 1){
            $offset = 0;
        }else{
            $offset = ($page_no-1)*$limit;
        }
        
        $shop_bn = array();
        if($params['shop_bn']){
            $all_shop_bn = explode('#',$params['shop_bn']);
            foreach($all_shop_bn as $v){
                if(trim($v)){
                    $shop_bn[] = trim($v);
                }
            }
        }
        $original_sales_data = kernel::single('openapi_data_original_sales')->SalesAmount($start_time,$end_time,$offset,$limit, $shop_bn);
        return $original_sales_data;
    }
}