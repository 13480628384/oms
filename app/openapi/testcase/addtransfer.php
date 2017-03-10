<?php
class addtransfer extends PHPUnit_Framework_TestCase
{
    function setUp() {

    }

    public function testAddtransfer(){

        $url = 'http://tnf.erp.taoex.com/index.php/callback/id/8f84acc09c3483877dbdcb1c36339018-1407307907/app_id/ome';
        //$token = base_certificate::token('ome');
        $time_out = 10;

        $system = array(
            'flag' => 't1',
            'method' => 'transfer.add',
            'type' => 'json',
        );
        //jsona 07867C5F3E9F64372E59798F3F9CB577
        //json DD190C4244521E76AD7F16CE737B5CC8
        //xml C86732F85599E09D76A6BF45F18D5328
        $params = array(
            'name' => '20130523入库单API',
            'vendor' => 'sto',
            't_type' => 'E',
            'branch_bn' => 'stockhouse',
            'delivery_cost' => '10',
            'operator' => 'system',
            'memo' => '123',
            'items' => json_encode(array(
                array(
                    'bn' => 'MJB-3004-Z',
                    'name' => '几何毛巾被',
                    'nums' => '2',
                    'price' => '10',
                ),
            )),
        );

        $_POST = "res=&msg_id=53E1D083C0A80215AA487059B07938E8&err_msg=&data=%7B%22content%22%3A+%22%5Cu5904%5Cu7406%5Cu6210%5Cu529f%22%2C+%22is_success%22%3A+%22T%22%2C+%22reason_code%22%3A+null%2C+%22errorlist%22%3A+null%7D&sign=48D725CA1EFC891F36E709DFDFC2CB75&rsp=succ";

        $http = kernel::single('base_httpclient');
        $response = $http->set_timeout($time_out)->post($url,$_POST);
        if($response === HTTP_TIME_OUT){
            return false;
        }else{
            echo $response;
            
            //print_r($response);
            //$result = json_decode($response,true);print_r($result);
            //return $result;
            //$xml_data = kernel::single('taoexlib_xml')->xml2array($response);
		    //var_dump($xml_data);exit;
        }
    }

    private function gen_sign($params){
        $token = 'JclHQUADuVwOSfxVdzCObWhgkfjoRPkl';
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
