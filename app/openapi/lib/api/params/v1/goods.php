<?php

class openapi_api_params_v1_goods extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg){
        if(parent::checkParams($method,$params,$sub_msg)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){
        $params = array(//类型、规格、品牌、商品名称、商品编号、计量单位、是否存在唯一码、销售价、成本价、货号、条形码、重量
            'add'=>array(
               'brand_name' => array('type'=>'string','name'=>'品牌名称'),
                'type_name'  => array('type'=>'string','name'=>'商品类型'),
                'goods_name'  => array('type'=>'string','required'=>'true','name'=>'商品名称','desc'=>'必填'),
                'goods_bn'   => array('type' => 'string','name' => '商品编号'),
                'unit'   => array('type' => 'string','name' => '计量单位'),
                'is_serial'   => array('type' => 'string','name' => '唯一码开启','desc'=>'如果开启请填是'),
                'sale_price'   => array('type' => 'string','name' => '销售价'),
                'cost_price'   => array('type' => 'string','name' => '成本价'),
                'product_bn'   => array('type' => 'string','required'=>'true','name' => '货号','desc'=>'必填'),
                'barcode'   => array('type' => 'string','required'=>'true','name' => '条形码','desc'=>'必填'),
                'weight'   => array('type' => 'string','name' => '重量'),
            ),
            'getList'=>array(
                'brand_name' => array('type'=>'string','name'=>'品牌名称'),
                'type_name'  => array('type'=>'string','name'=>'商品类型'),
                'goods_bn'   => array('type' => 'string','name' => '商品编号'),
                'start_lastmodify' => array('type' => 'date','name' => '最后更新时间开始','desc'=>'例如2012-12-08 18:50:30'),
                'end_lastmodify' => array('type' => 'date','name' => '最后更新时间结束','desc'=>'例如2012-12-08 18:50:30'),
                'page_no'=>array('type'=>'number','required'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
                'page_size'=>array('type'=>'number','required'=>'false','name'=>'每页数量','desc'=>'最大100'),            
            ),
            'edit'=>array(
               'brand_name' => array('type'=>'string','name'=>'品牌名称'),
                'type_name'  => array('type'=>'string','name'=>'商品类型'),
                'goods_name'  => array('type'=>'string','name'=>'商品名称'),
                'goods_bn'   => array('type' => 'string','name' => '商品编号'),
                'unit'   => array('type' => 'string','name' => '计量单位'),
                'is_serial'   => array('type' => 'string','name' => '唯一码开启','desc'=>'如果开启请填是'),
                'sale_price'   => array('type' => 'string','name' => '销售价'),
                'cost_price'   => array('type' => 'string','name' => '成本价'),
                'product_bn'   => array('type' => 'string','name' => '货号','desc'=>'必填'),
                'barcode'   => array('type' => 'string','name' => '条形码'),
                'weight'   => array('type' => 'string','name' => '重量'),
            ),
        );

        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'getList'=>array(
                'name'        =>'商品查询接口',
                'description' =>'实时批量获取特定条件下的商品',
            ),
            'add'=>array(
                'name'        =>'商品添加接口',
                'description' =>'添加商品',
            ),
            'edit'=>array(
                'name'        =>'商品修改接口',
                'description' =>'修改商品',
            ),
        );
        return $desccription[$method];
    }
}