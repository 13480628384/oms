<?php
class bills extends PHPUnit_Framework_TestCase
{
    function setUp() {
        //本测试用例正对于单仓库使用
        
    }

    public function testOrder(){
        /*
        $a  = kernel::single('finance_rpc_request_bill')->setShopId('35c44bc0c7aff5ae6e73b195f933810e')->bills_get('2013-09-30 23:55:00','2013-10-02 22:00:00',1,40,2);
        $a['data']['bills']['bill'][0]['bid'] *= 100
        error_log(var_export($a,true),3,DATA_DIR.'/log.log');
        exit;*/

        $model = app::get('finance')->model('bill_fee_item');
        $items = $model->getList('*');
        foreach ($items as $item){
            if($item['fee_item_id'] > 11){
                $item = array_filter($item);
                error_log(var_export(serialize($item)."\r\n",true),3,DATA_DIR.'/log.log');
            }
        }
        //error_log(var_export($items,true),3,DATA_DIR.'/log.log');
        exit;
        $financeObj = base_kvstore::instance('setting/finance');
        $financeObj->store("bills_get",strtotime('2013-10-14'));
        $financeObj->store("shop_bills_get_1039313632",strtotime('2013-10-1'));
        kernel::single('finance_cronjob_tradeScript')->get_bills();
    }

    static function strval(&$arr)
    {        
        foreach ($arr as $key => &$value) {
            if (is_array($value)) {
                self::strval($value);
            } else {
                $value = strval($value);
            }
        }
    }

    private function get_tmall_account()
    {
        define('BASE_URL','http://192.168.41.90/taoguan/branches/prerelease20130927');
        $shop_id = null;

        $shops = app::get('ome')->model('shop')->getList('shop_id,node_id',array('node_type' => 'taobao'));

        foreach ($shops as $shop) {
            if ($shop['node_id']) {
                $shop_id = $shop['shop_id'];
                break;
            }
        }

        if ($shop_id) {
            kernel::single('finance_rpc_request_bill')->setShopId($shop_id)->bill_account_get();
        }
    }

}
