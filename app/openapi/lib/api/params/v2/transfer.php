<?php

class openapi_api_params_v2_transfer extends openapi_api_params_v1_transfer{

    public function checkParams($method,$params,&$sub_msg){
        if(parent::checkParams($method,$params,$sub_msg)){
            return true;
        }else{
            return false;
        }
    }

}