<?php

class openapi_api_params_v2_sales extends openapi_api_params_v1_sales{

    public function checkParams($method,$params,&$sub_msg){
        if(parent::checkParams($method,$params,$sub_msg)){
            return true;
        }else{
            return false;
        }
    }

}