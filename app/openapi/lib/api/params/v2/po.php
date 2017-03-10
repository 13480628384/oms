<?php

class openapi_api_params_v2_po extends openapi_api_params_v1_po{

    public function checkParams($method,$params,&$sub_msg){
        if(parent::checkParams($method,$params,$sub_msg)){
            return true;
        }else{
            return false;
        }
    }

}