<?php
/**
* beibei(贝贝网)接口请求实现
* 贝贝网，发货回写
*/
class apibusiness_request_v1_beibei extends apibusiness_request_partyabstract{

    public function __construct()
    {
        parent::__construct();

        $this->_caller->set_matrix_v('2.0');
    }

    #获取发货参数
    protected function getDeliveryParam($delivery){

        $param = array(
            'tid'               => $delivery['order']['order_bn'],
            'company_code'      => $delivery['dly_corp']['type'],
            'logistics_no'      => $delivery['logi_no'] ? $delivery['logi_no'] : '',
        );
        return $param;
    }

}
