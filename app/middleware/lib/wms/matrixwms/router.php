<?php
/**
* 业务接口处理分发
* 
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_router{

    public static $wms_mapping = array(
        'jd_wms' => '360buy',
        'sf_wms'=>'sf',
        'flux_wms'=>'flux',
    );

    
    /**
     * 返回WMS类型
     * @param   node_type
     * @return 
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function get_wms_node_type( $node_type )
    {
        if(!isset(self::$wms_mapping[$node_type])) return $node_type;
        return self::$wms_mapping[$node_type];
    }
    /**
    * 异步接口白名单
    */
    public static $_async_whitelist = array(
        //'stockin_create',#入库单创建
        //'stockout_create',#出库单创建
        //'stockdump_create',#转储单创建
        //'goods_add',#商品添加
        //'goods_update',#商品编辑
        //'delivery_create',#发货单创建
        //'reship_create',#退货单创建
    );

    public function __construct(){
        $this->_abstract = kernel::single('middleware_wms_abstract');
    }

    /**
    * 接口请求分派
    * @param String $title 适配器接口标题
    * @param String $class 适配器接口类
    * @param String $method 适配器接口方法
    * @param Array $sdf 适配器接口参数
    * @param bool $sync 同异步类型:同步true,异步false
    * @return Array
    */
    public function request($title,$class,$method,$sdf,$sync=false){
        #异步代理
        //if($this->__async_agent($title,$class,$method,$sdf,$sync)){
            //return $this->_abstract->msgOutput('success','队列运行中');
        //}

        #是否支持同步接口
        if($sync == true && in_array($method,self::$_async_whitelist)){
            return $this->_abstract->msgOutput('fail','接口不支持同步','w403');
        }

        $_instance = kernel::single($class);
        
        #异步callback
        if($this->callback_class && $this->callback_method){
            $_instance->setUserCallback($this->callback_class,$this->callback_method,$this->callback_params);
        }

        #异步node_id
        if($this->node_id){
            $_instance->setNodeId($this->node_id);
        }

        return $_instance->$method($sdf,$sync);
    }

    /**
    * 运行队列任务
    */
    public function runQueueTask(&$cursor_id,$params){
        if(class_exists($params['class'])){
            if($_instance = kernel::single($params['class'])){
                if(method_exists($_instance,$params['method'])){
                    $rs = $_instance->$params['method']($params['sdf'],$params['sync']);
                }
            }
        }

        #请求结果回调
        if(class_exists($params['callback_class'])){
            if($_instance = kernel::single($params['callback_class'])){
                if(method_exists($_instance,$params['callback_method'])){
                    $rs = $_instance->$params['callback_method']($rs,$params['callback_params']);
                }
            }
        }
    }

    /**
    * 异步代理
    * 调用方要求异步处理，而接口为同步，则帮接口做异步代理
    * @return bool true代理成功  false代理失败
    */
    private function __async_agent($title,$class,$method,$sdf,$sync=false){
        if($sync != true && !in_array($method,self::$_async_whitelist)){
            $sdf = array(
                'class' => $class,
                'method' => $method,
                'sdf' => $sdf,
                'sync' => true,
                'callback_class' => $this->callback_class,
                'callback_method' => $this->callback_method,
                'callback_params' => $this->callback_params,
            );
            return $this->_abstract->createQueue($title,__CLASS__,'runQueueTask',$sdf);
        }
        return false;
    }

}