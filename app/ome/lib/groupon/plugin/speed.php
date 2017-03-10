<?php

/**
 * 快速导入模板
 *
 * @author shiyao744@sohu.com
 * @version 0.1b
 */
class ome_groupon_plugin_speed extends ome_groupon_plugin_abstract implements ome_groupon_plugin_interface {
	
	public $_name = '快速导入模板';
	

	/**
	 * 处理导入到原始数据
	 *
	 * @param array $data 原始数据
	 * @return Array
	 */
	public function process($data, $post) {
		
		return parent::process($data, $post);
	}
	
	public function convertToRowSdf($row, $post) {
		$row_sdf = array ();
		
		$order_bn = '';
		if ($row [0]) {
			$order_bn = str_replace ( '`', '', $row [0] );
		}
		
		$consignee_name = '';
		if ($row [1]) {
			$consignee_name = $row [1];
		}
		
		$consignee_area_province = '';
		$consignee_area_city = '';
		$consignee_area_county = '';
		$consignee_area_addr = '';
		if ($row [2]) {
			$consignee_area_province = $row [2];
		}
		
		if ($row [3]) {
			$consignee_area_city = $row [3];
		}
		
		if ($row [4]) {
			$consignee_area_county = $row [4];
		}
		
		if ($row [5]) {
			$consignee_area_addr = $row [5];
		}
		
		$consignee_mobile = '';
		if ($row [6]) {
			$consignee_mobile = $row [6];
		}
		
		$consignee_tel = '';
		if ($row [7]) {
			$consignee_tel = $row [7];
		}

        $shipping_name = '';
        if ($row [8]) {
            $shipping_name = $row [9];
        }

        $custom_mark = '';
        if ($row [9]) {
            $custom_mark = $row [10];
        }

        $createtime = '';
        if ($row [10]) {
            $createtime = strtotime($row [10]);
        }

        $product_info = '';
        if ($row [11]) {
            $product_info = $row [11];
        }

        $cost_freight = 0;
        if($row[12]){
            $cost_freight = $row[12];
        }

        $mark_text = '';
        if ($row [13]) {
            $mark_text = $row [13];
        }
//		$product_nums = '';
//		if ($row [8]) {
//			$product_nums = $row [8];
//		}
		
//		$shipping_name = '';
//		if ($row [9]) {
//			$shipping_name = $row [9];
//		}
//
//		$custom_mark = '';
//		if ($row [10]) {
//			$custom_mark = $row [10];
//		}
//
//		$createtime = '';
//		if ($row [11]) {
//			$createtime = strtotime($row [11]);
//		}
		
//		$product_bn = '';
//		if ($row [12]) {
//			$product_bn = $row [12];
//		}
		
//		$product_price = '';
//		if ($row [13]) {
//			$product_price = $row [13];
//		}
//
//		$cost_freight = 0;
//		if($row[14]){
//			$cost_freight = $row[14];
//		}
//
//		$mark_text = '';
//		if ($row [15]) {
//		    $mark_text = $row [15];
//		}
		

		$shipping_cod = false;
		$is_tax = false;
		

        // 拆分获取价格、数量、货号(匹配中英文下的冒号、分号)
        $pattner = "/\w*;\w*/";
        $pattner1 = "/\w*；\w*/";
        $tmp_prices = array();
        if (preg_match($pattner, $product_info) > 0) {
            $tmp_products = explode(';', $product_info);
            $tmp_prices = $this->formatPrice($tmp_products);

        } elseif(preg_match($pattner1, $product_info) > 0) {
            $tmp_products = explode('；', $product_info);
            $tmp_prices = $this->formatPrice($tmp_products);

        } else {
            $tmp_prices = 0;
        }

        if(empty($tmp_prices)){
            $one_arr = explode(':', $product_info);
            $one_price = explode('*', $one_arr[1]);

            $product_bn = $one_arr[0];
            $product_price = $one_price[1];
            $product_nums = $one_price[0];

            $cost_item = $product_price * $product_nums;
            $total_amount = $cost_item + $cost_freight;
        } else {
            $product_bn = $this->getProductBn($tmp_prices);
            $product_price = $this->getProductPrice($tmp_prices);
            $product_nums = $this->getProductNums($tmp_prices);

            $cost_item = $this->getSum($tmp_prices);
            $total_amount = $cost_item + $cost_freight;
        }

        // 价格计算
//		$cost_item = $product_price * $product_nums;
//		$total_amount = $cost_item + $cost_freight;

        if ($row [14]) {
            $row[14] = trim($row[14]);
            #货到付款
            if( ($row[14] == '是') || ($row[14] == 'true')||($row[14] == 'TRUE') ||($row[14] == 'yes') ||($row[14] == 'YES')){
                $shipping_cod = 'true';
            }
        }

//		if ($row [16]) {
//		    $row[16] = trim($row[16]);
//		    #货到付款
//		    if( ($row[16] == '是') || ($row[16] == 'true')||($row[16] == 'TRUE') ||($row[16] == 'yes') ||($row[16] == 'YES')){
//		        $shipping_cod = 'true';
//		    }
//		}
		$row_sdf = array(
    		'order_bn'=>trim($order_bn),
	    	'shipping'=>array(
	    		'shipping_name'=>$shipping_name,
    			'is_cod'=>$shipping_cod,
    			'cost_shipping'=>$cost_freight,    			
    	    ),
	    	'custom_mark'=>$custom_mark,
		    'mark_text'=>$mark_text,
	    	'consignee'=>array(
                'name'=>$consignee_name,
                'email'=>$consignee_email,
                'zip'=>$consignee_zip,
                'mobile'=>$consignee_mobile,
                'telephone' => $consignee_tel,
                'addr'=>$consignee_area_addr,
                'area'=>
                    array(
                     'province'=>$consignee_area_province,
                     'city'=>$consignee_area_city,
                     'county'=>$consignee_area_county,
                    ),
    		),
	    	'is_tax'=>$is_tax,
	    	'cost_item'=>$cost_item,
	    	'total_amount'=>$total_amount,
	    	'product_bn'=>$product_bn,
	    	'product_price'=>$product_price,
	    	'product_nums'=>$product_nums,
    	);
		return $row_sdf;
	}

    /**
     * get total money
     * @param $data
     * @return number
     */
    public function getSum($data){
        $sum = array();
        foreach ($data as $key => $value) {
            $nums = explode('*', $value[1]);
            $sum[] = $nums[0] * $nums[1];
        }

        return array_sum($sum);
    }

    /**
     * analysis product infomation
     * @param $products
     * @return array
     */
    public function formatPrice($products)
    {
        $tmp_price = array();
        $pattner = "/\w*:\w*/";

        foreach ($products as $key => $value) {
            if (preg_match($pattner, $value) > 0) {
                $tmp_price[] = explode(':', $value);
            } else {
                $tmp_price[] = explode('：', $value);
            }
        }
        return $tmp_price;
    }

    /**
     * @param $obj
     * @return string
     */
    public function getProductBn($obj){
        $bns = array();
        foreach ($obj as $key => $value) {
            $price_num = explode('*', $value[1]);
            $bns[] = $value[0].':'.$price_num[0].':'.$price_num[1];
        }
        return implode(';', $bns);
    }

    /**
     * @param $obj
     * @return string
     */
    public function getProductPrice($obj){
        $prices = array();
        foreach ($obj as $key => $value) {
            $vals = explode('*', $value[1]);
//            $prices[] = explode('*', $value[1])[1];
            $prices[] = $vals[1];
        }
        return implode(';', $prices);
    }

    /**
     * @param $obj
     * @return string
     */
    public function getProductNums($obj){
        $numbers = array();
        foreach ($obj as $key => $value) {
//            $numbers[] = explode('*', $value[1])[0];
            $nums = explode('*', $value[1]);
            $numbers[] = $nums[0];
        }
        return implode(';', $numbers);
    }


}