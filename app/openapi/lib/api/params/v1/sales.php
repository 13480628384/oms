<?php

class openapi_api_params_v1_sales extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg){
        if(parent::checkParams($method,$params,$sub_msg)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){

        $params = array(
            'getList'=>array(
            	'start_time'=>array('type'=>'date','required'=>'true','name'=>'开始时间(销售单创建时间),例如2012-12-08 18:50:30'),
            	'end_time'=>array('type'=>'date','required'=>'true','name'=>'结束时间(同上)'),
                'page_no'=>array('type'=>'number','required'=>'false','name'=>'页码，默认1 第一页'),
                'page_size'=>array('type'=>'number','required'=>'false','name'=>'每页数量，最大100'),
                'shop_bn'=>array('type'=>'string','name'=>'店铺编码','desc'=>'多个店铺编码之间，用#分隔'),
            ),
            'getSalesAmount'=>array(
                'start_time'=>array('type'=>'date','required'=>'true','name'=>'开始时间','desc'=>'(销售单创建时间),例如2012-12-08 18:50:30'),
                'end_time'=>array('type'=>'date','required'=>'true','name'=>'结束时间','desc'=>'(销售单创建时间),例如2012-12-08 18:50:30'),
                'page_no'=>array('type'=>'number','required'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
                'page_size'=>array('type'=>'number','required'=>'false','name'=>'每页数量','desc'=>'最大100'),
                'shop_bn'=>array('type'=>'string','name'=>'店铺编码','desc'=>'多个店铺编码之间，用#分隔'),
            ),                
        );

        return $params[$method];
    }
    
    public function description($method){
        $desccription = array(
                'getList'=>array('name'=>'查询销售单信息(根据销售单创建时间)','description'=>'批量获取一个时间段内的销售单信息数据'),
                'getSalesAmount'=>array('name'=>'查询销售单信息(根据销售单创建时间)','description'=>'获取一段时间段内的销售单总额数据')
                );
        return $desccription[$method];
    }
}