<?php

class openapi_api_function_v2_aftersales extends openapi_api_function_v1_aftersales{

    public function getList($params,&$code,&$sub_msg){
        return parent::getList($params,$code,$sub_msg);
    }
}