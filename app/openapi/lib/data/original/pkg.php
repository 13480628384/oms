<?php

class openapi_data_original_pkg{

    public function getList($filter,$offset=0,$limit=40){
        $productModel = app::get('ome')->model('products');
        $pkgProductModel = app::get('omepkg')->model('pkg_product');
        $pkgGoodModel = app::get('omepkg')->model('pkg_goods');
        $count = $pkgGoodModel->count($filter);

        if($count >0){
            $pkgGoods = $pkgGoodModel->getList('*',$filter,$offset,$limit);

            $lists = $goods_id = $products_id = $product_list = array();
            foreach ($pkgGoods as $value) {
                $goods_id[] = $value['goods_id'];

                $lists[$value['goods_id']] = $value;
            }
            unset($pkgGoods);
            $pkgProducts = $pkgProductModel->getList('*',array('goods_id' => $goods_id));            
            foreach ($pkgProducts as $value) {
                $products_id[] = $value['product_id'];
                $value['bn']=$value['bn'];
                $value['name']=$value['name'];
                $lists[$value['goods_id']]['products'][$value['product_id']] = $value;
                $lists[$value['goods_id']]['products'][$value['product_id']]['price'] = &$product_list[$value['product_id']]['price'];
                $lists[$value['goods_id']]['products'][$value['product_id']]['weight'] = &$product_list[$value['product_id']]['weight'];
            }
            unset($pkgProducts);

            $products = $productModel->getList('product_id,price,weight',array('product_id' => $products_id));
            foreach ($products as $value) {
                $product_list[$value['product_id']]['price']  = $value['price'];
                $product_list[$value['product_id']]['weight'] = $value['weight'];
            }
            unset($products);

            return array(
                'lists' => $lists,
                'count' => $count,
            );
        }else{
            return array(
                'lists' => array(),
                'count' => 0,
            );
        }
    }
}