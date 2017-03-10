<?php

class openapi_api_params_v1_iostock extends openapi_api_params_abstract implements openapi_api_params_interface{

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
                'branch_bn' => array('type'=>'string','name'=>'仓库编号'),
                'start_time'=>array('type'=>'date','required'=>'true','name'=>'开始时间','desc'=>'例如2012-12-08 18:50:30'),
                'end_time'=>array('type'=>'date','required'=>'true','name'=>'结束时间','desc'=>'例如2012-12-08 18:50:30'),
                'page_no'=>array('type'=>'number','required'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
                'page_size'=>array('type'=>'number','required'=>'false','name'=>'每页数量','desc'=>'最大100'),
            ),
        );

        return $params[$method];
    }
    
    public function description($method){
        $desccription = array('getList'=>array('name'=>'查询出入库明细','description'=>'根据出入库时间来查询该时间段内的出入库明细'));
        return $desccription[$method];
    }
}