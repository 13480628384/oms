<?php

interface openapi_api_function_interface{

    function getList($params,&$code,&$sub_msg);
    function add($params,&$code,&$sub_msg);
}