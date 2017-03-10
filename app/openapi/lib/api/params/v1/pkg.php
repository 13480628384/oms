<?php

class openapi_api_params_v1_pkg extends openapi_api_params_abstract implements openapi_api_params_interface{

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
               	'page_no'=>array('type'=>'number','require'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
       			'page_size'=>array('type'=>'number','require'=>'false','name'=>'每页数量','desc'=>'最大100'),
            ),
        );

        return $params[$method];
    }
    
    public function description($method){
        $desccription = array('getList'=>array('name'=>'查询捆绑商品信息','description'=>'获取捆绑商品信息'));
        return $desccription[$method];
    }
}