<?php

class openapi_api_params_v2_pkg extends openapi_api_params_v1_pkg{

    public function checkParams($method,$params,&$sub_msg){
        if(parent::checkParams($method,$params,$sub_msg)){
            return true;
        }else{
            return false;
        }
    }

}