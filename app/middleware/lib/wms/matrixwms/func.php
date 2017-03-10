<?php
/**
* 函数库
*/
class middleware_wms_matrixwms_func{
    
    /**
    * 获取适配器信息
    * @access public
    * @return Array
    */
    public function getAdapter(){
        $content = include APP_DIR.'/middleware/lib/wms/matrixwms/config.php';
        return $content['adapter'];
    }

}