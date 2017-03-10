<?php

class openapi_data_original_stock{

    /**
     * @param Array $filter 过滤条件
     * @param Int $offset 下标
     * @param Int $limit 限制条数
     * 
     * @return void
     * @author 
     **/
    public function getBnBranchStore($filter,$offset,$limit = 100)
    {
        $where = array(1);

        if ($filter['brand_name']) {
            $brandModel = app::get('ome')->model('brand');
            $brand = $brandModel->dump(array('brand_name' => $filter['brand_name']),'brand_id');

            // $f['brand_id'] = $brand['brand_id'];
            $where[] = 'g.brand_id=' . $brand['brand_id'];
        }

        if ($filter['type_name']) {
            $goodsTypeModel = app::get('ome')->model('goods_type');
            $goods_type = $goodsTypeModel->dump(array('name' => $filter['type_name']),'type_id');

            // $f['type_id'] = $goods_type['type_id'];
            $where[] = 'g.type_id=' . $goods_type['type_id'];
        }

        if ($filter['goods_bn']) {
            $goodsModel = app::get('ome')->model('goods');
            $goods = $goodsModel->getlist('goods_id',array('bn' => $filter['goods_bn']));

            $goods_id[] = 0;
            if ($goods) {
                foreach ($goods as $good ) {
                    $goods_id[] = $good['goods_id'];
                }
                
            }
            // $f = array('goods_id' => $good['goods_id']);
            $where[] = 'p.goods_id in('.implode(',', $goods_id).')';
            
        }

        $productsModel = app::get('ome')->model('products');
        if ($filter['product_bn']) {
            // $f = array('bn' => $filter['product_bn']);
            $where = array('p.bn="' . $filter['product_bn'] . '"');
        }

        $sql = 'SELECT count(1) as _c FROM sdb_ome_products AS p LEFT JOIN sdb_ome_goods AS g ON(p.goods_id=g.goods_id) WHERE ' . implode(' AND ', $where);

        $count = $productsModel->db->selectrow($sql);

        $sql = 'SELECT p.product_id,p.bn,p.name,p.store,p.store_freeze,p.spec_info FROM sdb_ome_products AS p LEFT JOIN sdb_ome_goods AS g ON(p.goods_id=g.goods_id) WHERE ' . implode(' AND ', $where) . ' LIMIT ' . $offset . ',' . $limit;

        $productList = $productsModel->db->select($sql);
        $formatFilter=kernel::single('openapi_format_abstract');
        $branchProductModel = app::get('ome')->model('branch_product');
        $data = array();
        if ($productList) {
            $productIdArr = array();
            foreach ($productList as $product) {
                $productIdArr[] = $product['product_id'];
                $product['bn']= $formatFilter->charFilter($product['bn']);
                $product['name']= $formatFilter->charFilter($product['name']);
                $data[$product['product_id']] = $product;
                $data[$product['product_id']]['branchstore'] = array();
            }
            $branchProductList = $branchProductModel->getList('*',array('product_id' => $productIdArr));
            $i = '1';
            foreach ((array) $branchProductList as $bp) {
                $branch = $this->getBranch($bp['branch_id']);

                $data[$bp['product_id']]['branchstore'][$i] = array(
                        'branch_bn'    => $formatFilter->charFilter($branch['branch_bn']),
                        'branch_name'  => $formatFilter->charFilter($branch['name']),
                        'store'        => $bp['store'],
                        'store_freeze' => $bp['store_freeze'],
                        'arrive_store' => $bp['arrive_store'],
                );

                $i++;
            }

            unset($branchProductList);
        }

        unset($productList);

        return array('lists' => $data, 'count' => (int) $count['_c']);
    }

    private function getBranch($branch_id)
    {

        static $branchList;

        if ($branchList[$branch_id]) return $branchList[$branch_id];

        $branchModel = app::get('ome')->model('branch');

        $branchList[$branch_id] = $branchModel->dump($branch_id,'branch_bn,name');

        return $branchList[$branch_id];
    }


    public function getDetailList($filter,$offset,$limit = 100)
    {
        $where = array();

        $productsModel = app::get('ome')->model('products');
        if ($filter['product_bn']) {
            $product = $productsModel->dump(array('bn' => $filter['product_bn']),'product_id');

            $where['product_id'] = $product['product_id'];
        }

        if ($filter['branch_name'] || $filter['branch_bn']) {
            $branchModel = app::get('ome')->model('branch');

            if(!empty($filter['branch_name'])){
                $branchFilter = array_unique( array('branch_bn' => $filter['branch_bn'],'name' => $filter['branch_name']) );
            }else{
                $branchFilter = array('branch_bn' => $filter['branch_bn']);
            }

            $branch = $branchModel->dump($branchFilter,'branch_id');

            $where['branch_id'] = $branch['branch_id'];
        }

        $branchProductModel = app::get('ome')->model('branch_product');

        $count = $branchProductModel->count($where);

        $branchProductList = $branchProductModel->getList('*',$where,$offset,$limit);

        $data = array();
        $formatFilter=kernel::single('openapi_format_abstract');
        if ($branchProductList) {
            $productIdArr = array();
            $i = '1';
            foreach ($branchProductList as $key => $bp) {
                $product_info = $productsModel->dump($bp['product_id'],'bn,name,spec_info');
                $productIdArr[] = $bp['product_id'];

                
                $data[$i]['store']            = $bp['store'];
                $data[$i]['store_freeze']     = $bp['store_freeze'];
                $data[$i]['store_in_transit'] = $bp['arrive_store'];
                $data[$i]['product_bn']   = $formatFilter->charFilter($product_info['bn']);
                $data[$i]['product_name'] = $formatFilter->charFilter($product_info['name']);
                $data[$i]['product_spec'] = $formatFilter->charFilter($product_info['spec_info']);

                $branch = $this->getBranch($bp['branch_id']);
                $data[$i]['branch_bn']   = $formatFilter->charFilter($branch['branch_bn']);
                $data[$i]['branch_name'] = $formatFilter->charFilter($branch['name']);

                $i++;
            }

            /*$productList = $productsModel->getList('*',array('product_id' => $productIdArr));
            foreach ((array) $productList as $p) {
                $productMap['product_bn']   = $p['bn'];
                $productMap['product_name'] = $p['name'];
                $productMap['product_spec'] = $p['spec_info'];
            } */
        }

        return array('lists' => $data,'count' => (int) $count);
        
    }
    
    
}