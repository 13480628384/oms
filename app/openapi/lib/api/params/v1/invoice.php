<?php
/**
 +----------------------------------------------------------
 * Api接口[参数判断]
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class openapi_api_params_v1_invoice extends openapi_api_params_abstract implements openapi_api_params_interface
{
    public function checkParams($method,$params,&$sub_msg)
    {
        if(parent::checkParams($method,$params,$sub_msg))
        {
            return true;
        }else{
            return false;
        }
    }
    
    /*------------------------------------------------------ */
    //-- Parameter
    /*------------------------------------------------------ */
    public function getAppParams($method)
    {
        $params = array(
            'import'=>array(
                'start_time'=>array('type'=>'date','required'=>'true','name'=>'开始时间,例如2012-12-08 18:50:30'),//开始时间
                'end_time'=>array('type'=>'date','required'=>'true','name'=>'结束时间(同上)'),//结束时间
            ),
			'getList'=>array(
				'start_time'=>array('type'=>'date','required'=>'true','name'=>'开始时间','desc'=>'例如2012-12-08 18:50:30'),
				'end_time'=>array('type'=>'date','required'=>'true','name'=>'结束时间','desc'=>'例如2012-12-08 18:50:30'),
                'status'=>array('type'=>'string','required'=>'false','name'=>'开票状态','desc'=>'是否已开票（1为是，0为否）默认为0'),//是否已开票（1为是，0为否）默认为0
			),
			'update'=>array(
			     'act' => array('type'=>'string', 'required'=>'false','name'=>'操作方式','desc'=>'(get或者post)'),
			),
        );
        
        return $params[$method];
    }

    /*------------------------------------------------------ */
    //-- Parameter
    /*------------------------------------------------------ */
    public function description($method)
    {
        $desccription       = array(
                                'getList'=>array('name'=>'获取需打印发票的订单', 'description'=>'第三方获取需开发票的订单'),
                                'update'=>array('name'=>'更新打印发票的信息', 'description'=>'第三方回传成功打印的发票信息'),
                            );
        return $desccription[$method];
    }
}