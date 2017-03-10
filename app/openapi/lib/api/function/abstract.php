<?php

abstract class openapi_api_function_abstract{
    public function charFilter($str){
        return str_replace(array("\t","\r","\n",'"',"\\"),array(" "," "," ",'“',"/"),$str);
    }
}