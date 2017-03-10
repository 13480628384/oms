<?php

interface openapi_api_params_interface{

    function checkParams($method,$params,&$sub_msg);
	
    function description($method);
}