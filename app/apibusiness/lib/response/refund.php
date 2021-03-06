<?php
/**
* 退款单路由
*
* @category apibusiness
* @package apibusiness/response/
* @author chenping<chenping@shopex.cn>
* @version $Id: refund.php 2013-3-12 17:23Z
*/
class apibusiness_response_refund
{
    private $_respservice = null;

    const _APP_NAME = 'ome'; 

    public function setRespservice($respservice)
    {
        $this->_respservice = $respservice;

        return  $this;
    }


    /**
     * 退款单调整方法跳转
     *
     * @param String $method 调用方法
     * @param Array $sdf 接收参数
     * @return void
     * @author 
     **/
    public function dispatch($method,$sdf)
    {
        $data = array('tid'=>$sdf['order_bn'],'refund_id'=>$sdf['refund_bn'],'retry'=>'false');

        if (!base_rpc_service::$node_id) {
            $this->_respservice->send_user_error('no node id',$data);
        }

        $shopModel = app::get(self::_APP_NAME)->model('shop');
        $shop = $shopModel->dump(array('node_id'=>base_rpc_service::$node_id));
        if (!$shop) {
            $this->_respservice->send_user_error('shop is not exist',$data);
        }

        // 以前端店铺为主，后端店铺为次
        $shop['node_version'] = $sdf['node_version'] ? $sdf['node_version'] : $shop['api_version'];
        if (version_compare($shop['node_version'], $shop['api_version'],'>')) {
            $shopModel->update(array('api_version'=>$shop['node_version']),array('shop_id'=>$shop['shop_id']));
        }

        $class_name = $this->getClassName($sdf,$shop,$tgver);
        
        $obj = kernel::single($class_name,$sdf);
        if (!$obj instanceof apibusiness_response_refund_abstract) {
            $this->_respservice->send_user_error("Class `{$class_name}` is not instance of `apibusiness_response_refund_abstract`",$data);
        }
        if (!method_exists($obj, $method)) {
            $this->_respservice->send_user_error("method `{$method}` is not exist",$data);
        }

        $obj->setRespservice($this->_respservice)
            ->setTgVer($tgver)
            ->setShop($shop)
            ->$method();

        $logModel = app::get(self::_APP_NAME)->model('api_log');
        $log_id = $logModel->gen_id();
        $logModel->write_log($log_id,
                             $obj->_apiLog['title'],
                             get_class($this), 
                             $method, 
                             '', 
                             '', 
                             'response', 
                             'success', 
                             implode('<hr/>',$obj->_apiLog['info']),
                             '',
                             '',
                             $sdf['order_bn']);
        return $data;
    }

    public function getClassName($sdf,$shop,&$tgver)
    {
        $data = array('tid'=>$sdf['order_bn'],'refund_id'=>$sdf['refund_bn'],'retry'=>'false');

        # 获取版本
        $tgver = kernel::single('apibusiness_router_mapping')->getVersion($shop['node_type'],$shop['node_version']);
        $dirname = $shop['shop_type'];
        if ($dirname == 'taobao') {
            if (strtoupper($shop['tbbusiness_type']) == 'B') {
                $dirname = 'tmall';
            }
        }
        if (isset(apibusiness_router_mapping::$shopex[$shop['shop_type']])) {
            $dirname = 'shopex_'.apibusiness_router_mapping::$shopex[$shop['shop_type']];  
        } elseif (isset(apibusiness_router_mapping::$party[$shop['shop_type']])){
            $dirname = apibusiness_router_mapping::$party[$shop['shop_type']];
        }
        
        do {
            # 如果版本号小于0，直接报错
            
            if ($tgver<=0) {
                $this->_respservice->send_user_error('no version matched',$data); exit;
            }

            $class_name = sprintf('apibusiness_response_refund_%s_v%s',$dirname,$tgver);
            
            try{
                # 版本文件存在跳出循环
                if (class_exists($class_name)) break;
            } catch (Exception $e) {
                // do nothing
                
            }

            # 当前版本文件不存在，取低版本的
            $tgver--;

        } while (true);

        return $class_name;
    }
}