<?php
class finance_shop_relation{

    /**
     * 店铺绑定
     */
    public function bind($shop_id){
        $shop = app::get('ome')->model('shop')->dump($shop_id);
        if ($shop_id && $shop['node_type'] == 'taobao') {
            kernel::single('finance_rpc_request_bill')->setShopId($shop_id)->bill_account_get();
        }
        return true;
    }
}