<?php

class openapi_api_params_v1_aftersales extends openapi_api_params_abstract implements openapi_api_params_interface{

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
            	'start_time'=>array('type'=>'date','required'=>'true','name'=>'开始时间','desc'=>'(售后单创建时间),例如2012-12-08 18:50:30'),
            	'end_time'=>array('type'=>'date','required'=>'true','name'=>'结束时间','desc'=>'(售后单创建时间),例如2012-12-08 18:50:30'),
            ),
        );

        return $params[$method];
    }

    public function description($method){
        $desccription = array('getList'=>array('name'=>'查询售后单信息(根据售后单创建时间)','description'=>'批量获取一个时间段内的售后单信息数据'));
        return $desccription[$method];
    }
}