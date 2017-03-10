<?php
class openapi_api_params_v1_stock extends openapi_api_params_abstract implements openapi_api_params_interface{

	public function checkParams($method,$params,&$sub_msg){
		if(parent::checkParams($method,$params,$sub_msg)){
			return true;
		}else{
			return false;
		}
	}
	
	public function getAppParams($method){
		$params = array(
				'getAll'=>array(
						'goods_bn'=>array('type'=>'string','required'=>'false','name'=>'商品编码'),
						'brand_name'=>array('type'=>'string','required'=>'false','name'=>'品牌'),
						'type_name'=>array('type'=>'string','required'=>'false','name'=>'类型'),
						'page_no'=>array('type'=>'number','required'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
						'page_size'=>array('type'=>'number','required'=>'false','name'=>'每页数量','desc'=>'最大100'),
				),
				'getDetailList'=>array(
						'product_bn'=>array('type'=>'string','required'=>'false','name'=>'货号'),
						'branch_bn'=>array('type'=>'string','required'=>'false','name'=>'仓库编码'),
						'page_no'=>array('type'=>'number','required'=>'false','name'=>'页码','desc'=>'默认1,第一页'),
						'page_size'=>array('type'=>'number','required'=>'false','name'=>'每页数量','desc'=>'最大100'),
				),
		);
	
		return $params[$method];
	}
	
	public function description($method){
        $description = array('getAll'=>array('name'=>'库存接口','description'=>'返回总数量带仓库明细'),
                            'getDetailList'=>array('name'=>'仓库数量接口','description'=>'返回仓库数量'),
                            'getList'=>array('name'=>'货品仓库接口','description'));
        return $description[$method];
	}
}