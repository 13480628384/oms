<?php
/**
* 开放平台发起调用类
* 
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_request extends middleware_wms_requestInterface{

    public $goods_add_limit = 20;#商品分页数
    public $goods_update_limit = 20;

    public function __construct(){
        $this->_router = kernel::single('middleware_wms_matrixwms_router');
    }

    /**
    * 设置用户异步回调
    */
    public function setUserCallback($callback_class,$callback_method,$callback_params=null){
        $this->_router->callback_class = $callback_class;
        $this->_router->callback_method = $callback_method;
        $this->_router->callback_params = $callback_params;
    }

    /**
    * 设置节点号信息
    */
    public function setNodeId($node_id=''){
        $this->node_id = $node_id;
        $this->_router->node_id = $this->node_id;
    }

    /**
    * 获取节点类型
    */
    public function getNode_type($node_id){
        $channel_adapter = app::get('channel')->model('channel');
        $detail = $channel_adapter->getList('node_type',array('node_id'=>$node_id),0,1);
        $node_type = kernel::single('middleware_wms_matrixwms_router')->get_wms_node_type( $detail[0]['node_type'] );
        return $node_type;
    }
    /**
    * 入库单创建
    */
    public function stockin_create($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_stockin';
        
        $node_type = $this->getNode_type($this->node_id);

        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_stockin',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }

        return $this->_router->request('入库单创建',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 入库单取消
    */
    public function stockin_cancel($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_stockin';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_stockin', $node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
       
        return $this->_router->request('入库单取消',$class,__FUNCTION__,$sdf,$sync);
    }

    
    /**
     *入库单查询
     * @param  
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function stockin_search($sdf=array(),$sync=false)
    {
        $class = 'middleware_wms_matrixwms_request_stockin';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_stockin',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
       
        return $this->_router->request('入库单查询',$class,__FUNCTION__,$sdf,$sync);
    }
    /**
    * 出库单创建
    */
    public function stockout_create($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_stockout';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_stockout',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        return $this->_router->request('出库单创建',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 出库单取消
    */
    public function stockout_cancel($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_stockout';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_stockout',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        return $this->_router->request('出库单取消',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 出库查询
    */
    function stockout_search($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_stockout';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_stockout',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }    

        return $this->_router->request('出库单查询',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 转储单创建
    */
    public function stockdump_create($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_stockdump';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_stockdump',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        return $this->_router->request('转储单创建',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 转储单取消
    */
    public function stockdump_cancel($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_stockdump';
        $node_type = $this->getNode_type($this->node_id);
        if ($node_type) {
            $plat_class =  sprintf('middleware_wms_matrixwms_request_%s_stockdump',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        return $this->_router->request('转储单取消',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 发货单创建
    */
    public function delivery_create($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_delivery';
        $node_type = $this->getNode_type($this->node_id);
        if ($node_type) {
            $plat_class =  sprintf('middleware_wms_matrixwms_request_%s_delivery',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        return $this->_router->request('发货单创建',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 发货单取消
    */
    public function delivery_cancel($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_delivery';
        $node_type = $this->getNode_type($this->node_id);
        if ($node_type) {
            $plat_class =  sprintf('middleware_wms_matrixwms_request_%s_delivery',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        
        return $this->_router->request('发货单取消',$class,__FUNCTION__,$sdf,$sync);
    }

    
    /**
     * 发货单查询
     * @param   type    $varname    description
     * @return  type    description
     * @access  public
     * @author cyyr24@sina.cn
     */
    function delivery_search($sdf=array(),$sync=false)
    {
        $class = 'middleware_wms_matrixwms_request_delivery';
        $node_type = $this->getNode_type($this->node_id);
        if ($node_type) {
            $plat_class =  sprintf('middleware_wms_matrixwms_request_%s_delivery',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        
        return $this->_router->request('发货单查询',$class,__FUNCTION__,$sdf,$sync);
    }
    /**
    * 退货单创建
    */
    public function reship_create($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_reship';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_reship',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //www
            }
        }
        
        return $this->_router->request('退货单创建',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 退货单取消
    */
    public function reship_cancel($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_reship';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_reship',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        return $this->_router->request('退货单取消',$class,__FUNCTION__,$sdf,$sync);
    }

    public function reship_search($sdf=array(),$sync=false)
    {
        $class = 'middleware_wms_matrixwms_request_reship';
        $node_type = $this->getNode_type($this->node_id);
        if ($node_type) {
            $plat_class =  sprintf('middleware_wms_matrixwms_request_%s_reship',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        

        return $this->_router->request('退货单查询',$class,__FUNCTION__,$sdf,$sync);
    }
    /**
    * 商品添加
    */
    public function goods_add($sdf=array(),$sync=false){

        $node_type = $this->getNode_type($this->node_id);
        $class = 'middleware_wms_matrixwms_request_goods';
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_goods',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        return $this->_router->request('商品添加',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
    * 商品编辑
    */
    public function goods_update($sdf=array(),$sync=false){
        $class = 'middleware_wms_matrixwms_request_goods';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_goods',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        
        
        return $this->_router->request('商品编辑',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
     * 获取仓库列表
     * @param   
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function get_warehouse_list($sdf=array(),$sync=false)
    {
        $class = 'middleware_wms_matrixwms_request_branch';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_branch',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        

        return $this->_router->request('获取仓库列表',$class,__FUNCTION__,$sdf,$sync);
    }

    /**
     * 获取物流公司
     * @param   
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function get_logistics_list( $sdf=array(),$sync=false)
    {
        $class = 'middleware_wms_matrixwms_request_branch';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_branch',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        

        return $this->_router->request('获取物流公司',$class,__FUNCTION__,$sdf,$sync);
    }

    
    /**
     * 供应商添加
     * @param   array
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function supplier_create($sdf=array(),$sync=false)
    {
        $class = 'middleware_wms_matrixwms_request_supplier';
        $node_type = $this->getNode_type($this->node_id);
        if ( $node_type ) {
            $plat_class = sprintf('middleware_wms_matrixwms_request_%s_supplier',$node_type);
            try{
                if(class_exists($plat_class)){
                    $class = $plat_class;
                }
            }catch(Exception $e){
                //
            }
        }
        

        return $this->_router->request('添加供应商',$class,__FUNCTION__,$sdf,$sync);
    }

}