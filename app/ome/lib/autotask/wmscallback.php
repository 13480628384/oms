<?PHP
/**
 * WMS相关CALLBACK收订任务处理类
 *
 * @author kamisama.xia@gmail.com 
 * @version 0.1
 */

class ome_autotask_wmscallback
{
    public function process($params, &$error_msg=''){
        set_time_limit(0);

        kernel::single('base_rpc_service')->process($params['pathinfo']);

        exit;
    }
}