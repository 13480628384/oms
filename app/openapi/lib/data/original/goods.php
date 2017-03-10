<?php

class openapi_data_original_goods{ 
    public function getList($filter,$offset=0,$limit=40)
    {
        $goodsModel = app::get('ome')->model('goods');

        $goodscount = $goodsModel->count($filter);
        $goodslist = $goodsModel->getList('*',$filter,$offset,$limit);

        $productModel = app::get('ome')->model('products');
        $list = array();
        $formatFilter=kernel::single('openapi_format_abstract');
        foreach ((array) $goodslist as $value) {
            
            $gtype = $this->_getGoodsType($value['type_id']);
            $gbrand = $this->_getGoodsBrand($value['brand_id']);
            $goods_name= $formatFilter->charFilter($value['name']);
            $goods_bn= $value['bn'];
            $goods_type= $gtype['name'];
            $goods_brand= $gbrand['brand_name'];
            $unit= $value['unit'];
            $good = array(
                'goods_bn'    => $formatFilter->charFilter($goods_bn),
                'goods_name'  => $formatFilter->charFilter($goods_name),
                'goods_type'  => $formatFilter->charFilter($goods_type), 
                'goods_brand' => $formatFilter->charFilter($goods_brand),
                'unit'        => $formatFilter->charFilter($unit),
                'lastmodify'  => date('Y-m-d H:i:s',$value['last_modify']),
            );

            $productlist = $productModel->getList('*',array('goods_id' => $value['goods_id']));
            foreach ($productlist as $v) {
                $good['products'][] = array(
                    'product_bn'   => $formatFilter->charFilter($v['bn']),
                    'spec_info'    => $formatFilter->charFilter($v['spec_info']),
                    'store'        => $v['store'],
                    'store_freeze' => $v['store_freeze'],
                    'weight'       => $v['weight'],
                    'cost'         => $v['cost'],
                    'price'        => $v['price'],
                    'mktprice'     => $v['mktprice'],
                    'barcode'      => $formatFilter->charFilter($v['barcode']),
                );
            }

            $list[] = $good;
        }

        return array('list' => $list,'count' => (int) $goodscount);
    }

    /**
     * 获取商品类型
     *
     * @return void
     * @author 
     **/
    private function _getGoodsType($type_id)
    {
        if (!$type_id) return array();

        static $goodsType;

        if ($goodsType[$type_id]) return $goodsType[$type_id];

        $goodsTypeModel = app::get('ome')->model('goods_type');

        $goodsType[$type_id] = $goodsTypeModel->dump($type_id,'name');

        return $goodsType[$type_id];
    }

    /**
     * 获取商品品牌
     *
     * @return void
     * @author 
     **/
    private function _getGoodsBrand($brand_id)
    {
        if (!$brand_id) return array();

        static $goodsBrand;

        if ($goodsBrand[$brand_id]) return $goodsBrand[$brand_id];

        $goodsBrandModel = app::get('ome')->model('brand');

        $goodsBrand[$brand_id] = $goodsBrandModel->dump($brand_id,'brand_name');

        return $goodsBrand[$brand_id];
    }

    /**
     * 获取商品品牌
     *
     * @return void
     * @author 
     **/
     private function _getGoodsBrandByName($brand_name){
        if (!$brand_name) return array();

        $goodsBrandModel = app::get('ome')->model('brand');

        $goodsBrand = $goodsBrandModel->dump(array('brand_name'=>$brand_name),'brand_id');
        return $goodsBrand;
     }
    
    /**
     * 获取商品分类
     *
     * @return void
     * @author 
     **/
     private function _getCatTypeByName($type_name){
        if (!$type_name) return array();

        
        $goodsTypeModel = app::get('ome')->model('goods_type');

        $goodsType = $goodsTypeModel->dump(array('name'=>$type_name),'type_id');

        return $goodsType;
     }

    
    /**
     * 查询商品货号是否存在
     * @param   goods_bn
     * @return  
     * @access  private
     * @author sunjing@shopex.cn
     */
    private function _getGoodByBn($goods_bn)
    {
        $goodsModel = app::get('ome')->model('goods');
        $goods = $goodsModel->dump(array('bn'=>$goods_bn));
        return $goods;
    }
    /**
     * 查询商品条形码是否存在
     * @param   $barcode
     * @return  
     * @access  private
     * @author sunjing@shopex.cn
     */
    private function _getGoodByBarcode($barcode)
    {
        $goodsModel = app::get('ome')->model('goods');
        $goods = $goodsModel->dump(array('barcode'=>$barcode));
        return $goods;
    }
    /**
     * 通过商品id查询商品是否存在
     * @param   goods_id
     * @return  
     * @access  private
     * @author liuzecheng@shopex.cn
     */
    private function _getGoodsByGoodsId($goods_id)
    {
        $goodModel = app::get('ome')->model('goods');
        $good = $goodModel->dump(array('goods_id'=>$goods_id));
        return $good; 
    }
    /**
     * 查询货品货号是否存在
     * @param  product_bn
     * @return  
     * @access  private
     * @author sunjing@shopex.cn
     */
    private function _getProductByBn($product_bn)
    {
        $productsModel = app::get('ome')->model('products');
        $products = $productsModel->dump(array('bn'=>$product_bn));
        return $products;
    }
    /**
     * 通过货号判断商品编号和条形码是否已经存在
     * @param  product_bn,barcode,goods_bn
     * @return  
     * @access  private
     * @author liuzecheng@shopex.cn
     */
    private function _getResult($product_bn,$barcode,$goods_bn){
        $result = array('rsp'=>'succ');
        $products = $this->_getProductBybn($product_bn);
        if (!$products) {
            $result['rsp'] = 'fail';
            $result['msg'] = '货品货号不存在!';
            return $result;
        }
        $goods_id=$products['goods_id'];
        $goods = $this->_getGoodsByGoodsId($goods_id);
        if(!$goods){
            $result['rsp'] = 'fail';
            $result['msg'] = '商品不存在!';
            return $result;
        }
        $goodsModel = app::get('ome')->model('goods');
        if(!$goods_bn){
            $goodsByBn=$goodsModel->dump(array('bn'=>$goods_bn,'goods_id|noequal'=>$goods_id),'*');
            if($goodsByBn){
                $result['rsp'] = 'fail';
                $result['msg'] = '商品编码已经存在!';
                return $result;
            }
        }
        if($barcode){
            $goodsByBarcode=$goodsModel->dump(array('barcode'=>$barcode,'goods_id|noequal'=>$goods_id),'*');
            if($goodsByBarcode){
                $result['rsp'] = 'fail';
                $result['msg'] = '商品条形码已经存在!';
                return $result;
            }        
        }
        return $result;
    }

    public function add($data){
        $result = array('rsp'=>'succ');
        $oGoods = app::get('ome')->model('goods');
        $brand_name = $data['brand_name'];
        $type_name = $data['type_name'];
        $goods_name = $data['goods_name'];
        $goods_bn = $data['goods_bn'];
        $unit = $data['unit'];
        $is_serial = $data['is_serial']=='是' ? 'true' : 'false';
        $sale_price = $data['sale_price'];
        $cost_price = $data['cost_price'];
        $product_bn = $data['product_bn'];
        $barcode = $data['barcode'];
        $weight = $data['weight'];
        $rs = array();
        if ($goods_name == '' || $product_bn== '' || $barcode=='') {
            $result['rsp'] = 'fail';
            $result['msg'] = '必填字段不可为空!';
            return $result;
        }
        if ($brand_name) {
            $brand = $this->_getGoodsBrandByName($brand_name);
            if (!$brand) {
                $result['rsp'] = 'fail';
                $result['msg'] = '品牌不存在!';
                return $result;
            }
        }
        if ($type_name) {
            $goods_type = $this->_getCatTypeByName($type_name);
            if (!$goods_type) {
                $result['rsp'] = 'fail';
                $result['msg'] = '分类不存在!';
                return $result;
            }
        }
        if ($goods_bn) {
            $goods = $this->_getGoodBybn($goods_bn);
            if ($goods) {
                $result['rsp'] = 'fail';
                $result['msg'] = '商品编号已存在!';
                return $result;
                return $result;
            }
        }
        if ($product_bn) {
            $products = $this->_getProductBybn($product_bn);
            if ($products) {
                $result['rsp'] = 'fail';
                $result['msg'] = '货品货号已存在!';
                return $result;
            }
        }
        if ($barcode){
            $goods =$this -> _getGoodByBarcode($barcode);
            if ($goods) {
                $result['rsp'] = 'fail';
                $result['msg'] = '商品条形码已存在!';
                return $result;
            }
        }      
        $formatFilter=kernel::single('openapi_format_abstract');
        $sdf = array();
        $sdf['spec'] = null;
        $sdf['bn'] = $formatFilter->charFilter($goods_bn);
        $sdf['name']= $formatFilter->charFilter($goods_name);
        $sdf['type']['type_id'] = $goods_type['type_id'];
        $sdf['serial_number'] = $is_serial;
        $sdf['goods_type'] = 'normal';
        $sdf['brand']['brand_id'] = $brand['brand_id'];
        $sdf['barcode'] = $formatFilter->charFilter($barcode);
        $sdf['unit'] = $formatFilter->charFilter($unit);
        $product= array();
        $product['price']['price']['price'] = $sale_price;
        $product['price']['cost']['price'] = $cost_price;
        $product['bn'] = $formatFilter->charFilter($product_bn);
        $product['barcode'] = $formatFilter->charFilter($barcode);
        $product['unit'] = $formatFilter->charFilter($unit);
        $product['weight'] = $weight;
        $product['default'] = 1;
        $product['visibility'] = 'true';
        $sdf['product'][] = $product;
        $rs = $oGoods->saveGoods($sdf);
        if (!$rs) {
            $result['rsp'] = 'fail';
            $result['msg'] = '商品添加失败!';
        }
            return $result;
    }
    public function edit($data){
        $oGoods = app::get('ome')->model('goods');
        $brand_name = $data['brand_name'];
        $type_name = $data['type_name'];
        $goods_name = $data['goods_name'];
        $goods_bn = $data['goods_bn'];
        $unit = $data['unit'];
        $is_serial = $data['is_serial']=='是' ? 'true' : 'false';
        $sale_price = $data['sale_price'];
        $cost_price = $data['cost_price'];
        $product_bn = $data['product_bn'];
        $barcode = $data['barcode'];
        $weight = $data['weight'];        
        $rs = array();
        if ($product_bn== '') {
            $result['rsp'] = 'fail';
            $result['msg'] = '货号不可为空!';
            return $result;
        }
        $result = $this->_getResult($product_bn,$barcode,$goods_bn);
        if($result['rsp']=='fail'){
            return $result;
        }
        if ($brand_name) {  
            $brand = $this->_getGoodsBrandByname($brand_name);
            if (!$brand) {
                $result['rsp'] = 'fail';
                $result['msg'] = '品牌不存在!';
            }
        }
        if ($type_name) {
            $goods_type = $this->_getCatTypeByname($type_name);
            if (!$goods_type) {
                $result['rsp'] = 'fail';
                $result['msg'] = '分类不存在!';
            }
        }
        $formatFilter=kernel::single('openapi_format_abstract');
        $sdf = array();
        $products = $this->_getProductBybn($product_bn);
        $goods_id = $products['goods_id'];
        $goods = $this->_getGoodsByGoodsId($goods_id);
        $sdf['goods_id'] = $goods_id ? $goods_id : '';
        $sdf['category']['cat_id'] = $goods['category']['cat_id'];
        $sdf['spec'] = null;
        $sdf['bn'] = $goods_bn?$goods_bn:$goods['bn'];
        $sdf['name']= $formatFilter->charFilter($goods_name?$goods_name:$goods['name']);
        $sdf['type']['type_id'] = $goods_type['type_id']?$goods_type['type_id']:$goods['type']['type_id'];
        $sdf['serial_number'] = $is_serial;
        $sdf['picurl'] = $goods['picurl'];
        $sdf['goods_type'] = 'normal';
        $sdf['brief'] = $goods['brief'];
        $sdf['status'] = $goods['status'];
        $sdf['description'] = $goods['description'];
        $sdf['visibility'] = $goods['visibility'];
        $sdf['brand']['brand_id'] = $brand['brand_id']?$brand['brand_id']:$goods['brand']['brand_id'];
        $sdf['barcode'] = $formatFilter->charFilter($barcode?$barcode:$goods['barcode']);
        $sdf['unit'] = $formatFilter->charFilter($unit?$unit:$goods['unit']);
        $product= array();
        $product_id = $products['product_id'];
        $product['product_id'] = $product_id;
        $product['price']['price']['price'] = $sale_price?$sale_price:$products['price']['price']['price'];
        $product['price']['cost']['price'] = $cost_price?$cost_price:$products['price']['cost']['price'];
        $product['price']['mktprice']['price'] = $products['price']['mktprice']['price']?$products['price']['mktprice']['price']:'0.000';
        $product['bn'] = $product_bn;
        $product['name'] = $formatFilter->charFilter($goods_name?$goods_name:$products['name']);
        $product['barcode'] = $formatFilter->charFilter($barcode?$barcode:$products['barcode']);
        $product['unit'] = $unit?$unit:$products['unit'];
        $product['weight'] = $weight?$weight:$products['weight'];
        $product['status'] = 'true';
        $product['default'] = 1;
        $product['visibility'] = 'true';
        $sdf['product'][] = $product;
        $rs = $oGoods->saveGoods($sdf);
        if (!$rs) {
            $result['rsp'] = 'fail';
            $result['msg'] = '商品修改失败!';
        }
        return $result;
    }
}