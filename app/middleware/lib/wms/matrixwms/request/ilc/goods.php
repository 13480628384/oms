<?php
/**
* 商品
*
* @copyright shopex.cn 2014.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_ilc_goods extends middleware_wms_matrixwms_request_goods{


    
    /**
     *商品参数
     * @param  
     * @return  
     * @access  private
     * @author sunjing@shopex.cn
     */
    protected function __get_params( &$sdf,&$product_ids=array() )
    {
        $node_id = $this->node_id;
        $params = $product_ids = array();
        foreach ($sdf as $p){
            if (!is_array($p)) continue;
            $product_ids[] = $p['product_id'];
            $items[] = array(
                'name' => $p['name'],
                'product_bn' => $p['bn'],
                'barcode' => $p['barcode'],
                'item_code' => $p['bn'],
            );
        }
        $params['item_lists'] = json_encode(array('item'=>$items));
        $params['uniqid'] = self::uniqid();
        $params['line_total_count'] = count($items);
        return $params;
    }
}