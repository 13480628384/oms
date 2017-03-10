<?php
/**
* 商品
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_ilcwms_request_goods extends middleware_wms_ilcwms_request_common{

    /**
    * 商品添加
    * @access public
    * @param Array $sdf 商品数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function goods_add(&$sdf,$sync=false){

        $params = $product_ids = array();
        foreach ($sdf as $p){
            if (!is_array($p)) continue;
            $product_ids[] = $p['product_id'];
            $items[] = array(
                'product_name' => $p['name'],
                'product_bn' => $p['bn'],
                'barcode' => $p['barcode'],
            );
        }
        $params['items'] = json_encode($items);
        $addon['addon'] = $product_ids;
        $method = 'store.wms.item.add';
        $writelog = array(
            'log_title' => '商品添加',
            'log_type' => 'store.trade.goods',
            'original_bn' => '',
            'addon' => $product_ids
        );

        return $this->request($method,$params,$writelog,$sync);
    }

    /**
    * 商品编辑
    * @access public
    * @param Array $sdf 商品数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function goods_update(&$sdf,$sync=false){
        
        $params = $product_ids = array();
        foreach ($sdf as $p){
            if (!is_array($p)) continue;
            $product_ids[] = $p['product_id'];
            $items[] = array(
                'product_name' => $p['name'],
                'product_bn' => $p['bn'],
                'barcode' => $p['barcode'],
            );
        }
        $params['items'] = json_encode($items);
        $addon['addon'] = $product_ids;
        $method = 'store.wms.item.update';
        $writelog = array(
            'log_title' => '商品编辑',
            'log_type' => 'store.trade.goods',
            'original_bn' => '',
            'addon' => $product_ids
        );

        return $this->request($method,$params,$writelog,$sync);
    }

}