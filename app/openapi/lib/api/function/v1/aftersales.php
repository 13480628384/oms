<?php

class openapi_api_function_v1_aftersales extends openapi_api_function_abstract implements openapi_api_function_interface{

    public function getList($params,&$code,&$sub_msg){

        $start_time = strtotime($params['start_time']);
        $end_time = strtotime($params['end_time']);
        $page_no = intval($params['page_no']) > 0 ? intval($params['page_no']) : 1;
        $limit = (intval($params['page_size']) > 100 || intval($params['page_size']) <= 0) ? 100 : intval($params['page_size']);

        if($page_no == 1){
            $offset = 0;
        }else{
            $offset = ($page_no-1)*$limit;
        }

        $original_aftersales_data =  kernel::single('openapi_data_original_aftersales')->getList($start_time,$end_time,$offset,$limit);

        $aftersale_arr = array();
        foreach ($original_aftersales_data['lists'] as $k => $aftersale){
                $aftersale_arr[$k]['shop_code'] = $this->charFilter($aftersale['shop_bn']);
                $aftersale_arr[$k]['shop_name'] = $this->charFilter($aftersale['shop_name']);
                $aftersale_arr[$k]['order_no'] = $this->charFilter($aftersale['order_bn']);
                $aftersale_arr[$k]['aftersale_no'] = $this->charFilter($aftersale['aftersale_bn']);
                $aftersale_arr[$k]['aftersale_apply_no'] = $aftersale['return_bn'];
                $aftersale_arr[$k]['return_change_no'] = $this->charFilter($aftersale['reship_bn']);
                $aftersale_arr[$k]['refund_apply_no'] =  $aftersale['refund_apply_bn'];
                $aftersale_arr[$k]['aftersale_type'] = $aftersale['aftersale_type'];
                $aftersale_arr[$k]['pay_method'] = $aftersale['paymethod'];
                $aftersale_arr[$k]['refund_money'] = $aftersale['refundmoney'];
                $aftersale_arr[$k]['member_name'] = $this->charFilter($aftersale['member_name']);
                $aftersale_arr[$k]['member_mobile'] = $this->charFilter($aftersale['ship_mobile']);
                $aftersale_arr[$k]['check_op'] = $aftersale['check_op_name'];
                $aftersale_arr[$k]['quality_inspection_op'] = $aftersale['op_name'];
                $aftersale_arr[$k]['refund_op'] = $this->charFilter($aftersale['refund_op_name']);
                $aftersale_arr[$k]['apply_time'] = $aftersale['add_time'] ? date('Y-m-d H:i:s',$aftersale['add_time']) : '0';
                $aftersale_arr[$k]['check_time'] = $aftersale['check_time'] ? date('Y-m-d H:i:s',$aftersale['check_time']) : '0';
                $aftersale_arr[$k]['quality_inspection_time'] = $aftersale['acttime'] ? date('Y-m-d H:i:s',$aftersale['acttime']) : '0';
                $aftersale_arr[$k]['refund_time'] = $aftersale['refundtime'] ? date('Y-m-d H:i:s',$aftersale['refundtime']) : '0';
                $aftersale_arr[$k]['aftersale_time'] = $aftersale['aftersale_time'] ? date('Y-m-d H:i:s',$aftersale['aftersale_time']) : '0';
                if(isset($aftersale['aftersale_items']) && count($aftersale['aftersale_items']) > 0){
                    foreach ($aftersale['aftersale_items'] as $key => $aftersale_item){

                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['bn'] = $this->charFilter($aftersale_item['bn']);
                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['name'] = $this->charFilter($aftersale_item['product_name']);
                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['barcode'] = $aftersale_item['barcode'];                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['price'] = $aftersale_item['price'];
                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['nums'] = $aftersale_item['num'];
                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['amount'] = $aftersale_item['num']*$aftersale_item['price'];
                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['branch_name'] = $aftersale_item['branch_name'];
                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['apply_money'] = $aftersale_item['money'];
                        $aftersale_arr[$k]['aftersale_items'][$aftersale_item['item_id']]['refund_money'] = $aftersale_item['refunded'];

                    }
                }
        }

        unset($original_aftersales_data['lists']);
        $original_aftersales_data['lists'] = $aftersale_arr;

        return $original_aftersales_data;
    }

    public function add($params,&$code,&$sub_msg){
        
    }
}