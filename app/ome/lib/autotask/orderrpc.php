<?PHP
/**
 * 订单收订任务处理类
 *
 * @author kamisama.xia@gmail.com 
 * @version 0.1
 */

class ome_autotask_orderrpc
{
    public function process($params, &$msg=''){
        set_time_limit(0);

        kernel::single('base_rpc_service')->process('/api');

        exit;
    }
}