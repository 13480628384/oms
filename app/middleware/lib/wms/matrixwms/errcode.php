<?php
/**
* 错误代码类
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_errcode{

    private $_errcode = array(
        'w03020' => array('comment'=>'接口方法不存在','wms_errcode'=>'w402'),
    );

    /**
    * 转换wms的通用错误编码
    *
    * @access public
    * @param String $err_code 错误码
    * @return mixed
    */
    public function getWmsErrCode($err_code=NULL){
        if ($wmsErrcode = $this->_errcode[$err_code]['wms_errcode']){
            return $wmsErrcode;
        }else{
            return $err_code;
        }
    }

}