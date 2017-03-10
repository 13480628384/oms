<?php

class openapi_api_params_v1_transfer extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg){
        if(parent::checkParams($method,$params,$sub_msg)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){
        $params = array(
            'add'=>array(
            	'name'=>array('type'=>'string','required'=>'true','name'=>'入库单名称','desc'=>'必填'),
            	'vendor'=>array('type'=>'string','required'=>'false','name'=>'供应商'),
                'branch_bn'=>array('type'=>'string','required'=>'true','name'=>'仓库编号','desc'=>'必填'),
                'delivery_cost'=>array('type'=>'number','required'=>'false','name'=>'出入库费用'),
                'memo'=>array('type'=>'string','required'=>'false','name'=>'备注'),
                'operator'=>array('type'=>'string','required'=>'false','name'=>'经办人'),
                't_type'=>array('type'=>'string','required'=>'true','name'=>'出入库类型','desc'=>'必填
                                                                                                E – 直接入库
                                                                                                A – 直接出库
                                                                                                G – 赠品入库
                                                                                                F – 赠品出库
                                                                                                K – 样品入库
                                                                                                J – 样品出库
                                                                                                Y – 分销入库
                                                                                                Z – 分销出库'),
                'items'=>array('type'=>'string','required'=>'true','name'=>'明细','desc'=>'必填   格式为：bn:test1,name:测试1,price:10,nums:1;bn:test2,name:测试2,price:20,nums:2'),
            ),
            'getList'=>array(
            	'original_bn'=>array('type'=>'string','required'=>'false','name'=>'原始单据号'),
            	'supplier_bn'=>array('type'=>'string','required'=>'false','name'=>'供应商编号'),
                'branch_bn'=>array('type'=>'string','required'=>'false','name'=>'仓库编号'),
                't_type'=>array('type'=>'string','required'=>'false','name'=>'出入库类型','desc'=>'
                                                                                                E – 直接入库
                                                                                                A – 直接出库
                                                                                                G – 赠品入库
                                                                                                F – 赠品出库
                                                                                                K – 样品入库
                                                                                                J – 样品出库
                                                                                                Y – 分销入库
                                                                                                Z – 分销出库'),
                'start_time'=>array('type'=>'date','required'=>'true','name'=>'开始时间','desc'=>'例如2012-12-08 18:50:30'),
                'end_time'=>array('type'=>'date','required'=>'true','name'=>'结束时间','desc'=>'例如2012-12-08 18:50:30'),
                'page_no'=>array('type'=>'number','required'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
                'page_size'=>array('type'=>'number','required'=>'false','name'=>'每页数量','desc'=>'最大100'),
            ),
        );

        return $params[$method];
    }
    
    public function description($method){
        $desccription = array(
                'add'=>array('name'=>'创建出入单','description'=>'创建一个直接出入库的指令'),
                'getList'=>array('name'=>'出入单明细','description'=>'出入单的出入库明细列表'),
        );
        return $desccription[$method];
    }
}