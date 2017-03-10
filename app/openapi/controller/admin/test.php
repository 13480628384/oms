<?php
class openapi_ctl_admin_test extends desktop_controller{
	
	public function test(){
		$conf = openapi_conf::getMethods();
		$paramsObj = new openapi_api_params_v1_stock;
		$methods = array_keys($conf);
        
		foreach($methods as $v){
			$methods = get_class_methods('openapi_api_function_v1_'.$v);
			$paramsObj = kernel::single('openapi_api_params_v1_'.$v);
			foreach ($methods as $method){
				if( $paramsObj->getAppParams($method) ){
					$list[] = $v.'.'.$method;
				}
			}
		}
		
		$this->pagedata['list'] = $list;
		$this->page('admin/test/test.html');
	}
	
	public function ajaxResult(){
		if(!$_POST['apiName']) return;
		$info = explode('.', $_POST['apiName']);
		$class = $info[0];
		$function = $info[1];
		
		$obj = kernel::single('openapi_api_params_v1_'.$class);
		$list = $obj->getAppParams($function);
		$description = $obj->description($function);
		

		$this->pagedata['list'] = $list;
		$this->pagedata['post'] = $_POST;
		$this->pagedata['description'] = $description;
		$this->display('admin/test/apiForm.html');
	}
	
	public function result(){
		$poParams = kernel::single('openapi_api_params_v1_po');
		$getList = $poParams->getAppParams('getList');
		$url = 'http://'.$_SERVER['SERVER_NAME'].'/'.$_SERVER['SCRIPT_NAME'].'/openapi/rpc/service/';
		$token = $_POST['token'];
		$method = $_POST['method1'];
		unset($_POST['token']);
		unset($_POST['method1']);
		
		$params = $_POST;
		$params['ver'] = 1;
		$params['method'] = $method;
		
		$params['type'] = $_POST['data_format'];
		$params['charset'] = 'utf-8';
		
		//$params['page_no'] = 1;
		//$params['page_size'] = 100;
		if($params['items']){
			$items = explode(";", $params['items']);
			foreach ($items as $item){
			    if($item){
			        $tempa=explode(",", $item);
			        foreach ($tempa as $tempb){
			            $tempc=explode(":", $tempb);
			            $tempd[$tempc[0]]=$tempc[1];
			        }
			        $tempe[]=$tempd;
			    }
			}
			$params['items']=  json_encode($tempe);
		}
		$sign = $this->gen_sign($params ,$token);
		$params['sign'] = $sign;
        $http = kernel::single('base_httpclient');
		$response = $http->set_timeout($time_out)->post($url,$params ,$headers);
		echo $response;
	}
	
	private function gen_sign($params,$token){
	
		if(!$token){
			return false;
		}
		return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
	}
	
	private function assemble($params)
	{
		if(!is_array($params))  return null;
		ksort($params, SORT_STRING);
		$sign = '';
		foreach($params AS $key=>$val){
			if(is_null($val))   continue;
			if(is_bool($val))   $val = ($val) ? 1 : 0;
			$sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
		}
		return $sign;
	}
}