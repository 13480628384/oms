<?php
class openapi_api_params_v1_purchasereturn extends openapi_api_params_abstract implements openapi_api_params_interface{

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
                'rp_bn'=>array('type'=>'string','require'=>'false','name'=>'退货单编号'),
                'supplier'=>array('type'=>'string','require'=>'false','name'=>'供应商名称'),
                'returned_time'=>array('type'=>'string','require'=>'false','name'=>'退货日期','desc'=>'例如2014-12-08 18:50:30'),
                'branch'=>array('type'=>'string','require'=>'false','name'=>'仓库'),

                'return_status'=>array('type'=>'string','require'=>'false','name'=>'退货单状态','desc'=>'1 默认2 退货完成 3 出库拒绝'),
                'page_no'=>array('type'=>'number','require'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
                'page_size'=>array('type'=>'number','require'=>'false','name'=>'每页最大数量','desc'=>'最大100'),
            ),
            'add'=>array(
                'name'=>array('type'=>'string','require'=>'true','name'=>'退货单名称','desc'=>'必填'),
                'supplier_bn'=>array('type'=>'string','require'=>'true','name'=>'供应商编码','desc'=>'必填'),
                'branch_bn'=>array('type'=>'string','require'=>'true','name'=>'退货仓库编码','desc'=>'必填'),
                'operator'=>array('type'=>'string','require'=>'true','name'=>'经办人','desc'=>'必填'),
                'delivery_cost'=>array('type'=>'string','require'=>'false','name'=>'物流费用'),
                'logi_no'=>array('type'=>'number','require'=>'true','name'=>'物流单号'),
                'emergency'=>array('type'=>'string','require'=>'false','name'=>'是否为特别退货单','desc'=>'如果是请填是，默认为否'),
                'memo'=>array('type'=>'string','require'=>'false','name'=>'备注'),
                'items'=>array('type'=>'string','required'=>'true','name'=>'明细','desc'=>'必填   格式为：bn:test1,price:10,nums:1;bn:test2,price:20,nums:2'),                
            )
        );
    
        return $params[$method];
    }
    
    public function description($method){
        $description = array(
                            'getList'=>array('name'=>'采购退货单接口','description'=>'获取指定条件下的采购退货单列表'),
                            'add'=>array('name'=>'采购退货单接口','description'=>'新建采购退货单'));
        return $description[$method];
    }
}