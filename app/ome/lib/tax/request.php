<?php
class ome_tax_request
{
    /**
     * 平台类型  shop
     *
     * @var string
     **/
    private $__shop_type = '';

 

    /**
     * 设置初始化
     *
     * @return void
     * @author 
     **/
    public function set($shop_type)
    {
        $this->__shop_type = $shop_type;
        return $this;
    }

    /**
     * 
     *
     * @
     * @author 
     **/
    public function __call($method,$args)
    {   
       
        try{
                $class_name = sprintf('ome_tax_%s',$this->__shop_type);
                if (class_exists($class_name)) {};
            } catch (Exception $e) {

                $class_name = 'ome_tax_default';
            }
   
        return call_user_func_array(array($class_name,$method), $args);
    }
}