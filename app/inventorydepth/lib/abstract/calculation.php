<?php
/**
 * 库存计算抽象类
 *
 * @author chenping<chenping@shopex.cn>
 */

abstract class inventorydepth_abstract_calculation {
    
    public $shop_branch          = array();
    
    public static $shopStock     = array();
    
    public static $shopFreeze    = array();
    
    public static $actualStock   = array();
    
    public static $releaseStock  = array();
    
    public static $globalsFreeze = array();

    public $recal_shop_freeze = true;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = kernel::database();
    }

    public function init()
    {
        self::$shopStock     = 
        self::$shopFreeze    = 
        self::$actualStock   = 
        self::$releaseStock  = 
        self::$globalsFreeze = array();
    }

    /**
     * 设置店铺库存
     *
     * @param String $shop_product_bn 货品编号
     * @param String $shop_bn 店铺编号 
     * @return void
     * @author 
     **/
    public function set_shop_stock($shop_product_bn,$shop_bn,$shop_stock)
    {
        $sha1Str = $shop_bn.'-'.$shop_product_bn;
        $sha1 = sha1($sha1Str);
        self::$shopStock[$sha1] = $shop_stock;
    }

    /**
     * 店铺库存
     *
     * @param String $shop_product_bn 货品编号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     *
     * @return Int 
     */
    public function get_shop_stock($shop_product_bn,$shop_bn,$shop_id)
    {
        # 读内存值
        $sha1Str = $shop_bn.'-'.$shop_product_bn;
        $sha1 = sha1($sha1Str);
        if(isset(self::$shopStock[$sha1]) || $this->recal_shop_stock === false) return (int)self::$shopStock[$sha1];

        $batch = kernel::single('inventorydepth_stock_products')->batch;
        if ($batch) {
            $this->recal_shop_stock = false;
            $list = $this->app->model('shop_adjustment')->getList('shop_stock,shop_product_bn,shop_bn',array('shop_product_bn'=>array_keys(inventorydepth_stock_products::$products)));
            if ($list) {
                foreach ($list as $l) {
                    $tmpsha1 = sha1($l['shop_bn'].'-'.$l['shop_product_bn']);
                    self::$shopStock[$tmpsha1] = $l['shop_stock'];
                }
            }
        } else {
            # 货品转CRC32
            $skusLib = kernel::single('inventorydepth_shop_skus');
            $shop_product_bn_crc32 = $skusLib->crc32($shop_product_bn);
            $shop_bn_crc32 = $skusLib->crc32($shop_bn);

            $list = $this->app->model('shop_adjustment')
                ->select()->columns('shop_stock,shop_product_bn,shop_bn')
                ->where('shop_product_bn_crc32=?',$shop_product_bn_crc32)
                ->where('shop_bn_crc32=?',$shop_bn_crc32)
                ->instance()->fetch_all();

            $shop_stock = 0;
            foreach ($list as $key => $value) {
                if ($value['shop_product_bn'] == $shop_product_bn && $value['shop_bn'] == $shop_bn) {
                    $shop_stock = $value['shop_stock'];
                    break;
                }
            }
            self::$shopStock[$sha1] = (int)$shop_stock > 0 ? $shop_stock : 0;
        }

        return (int)self::$shopStock[$sha1];
    }

    /**
     *  店铺预占；根据订单来计算
     *
     * @param String $shop_product_bn 货品编号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     */
    public function get_shop_freeze($shop_product_bn,$shop_bn,$shop_id)
    {
        $sha1Str = $shop_id.'-'.strtolower($shop_product_bn);
        $sha1 = sha1($sha1Str);
        if(isset(self::$shopFreeze[$sha1]) || $this->recal_shop_freeze === false || $this->recal_product_bn[$shop_product_bn] === false) return (int)self::$shopFreeze[$sha1];
        
        # 根据订单计算店铺预占(未发货订单) 该店铺下的商品ID
        $batch = kernel::single('inventorydepth_stock_products')->batch;

        // 以product_id做为搜索条件
        $product_ids = array();
        if ($batch) {
            foreach ((array) inventorydepth_stock_products::$products as $key=>$value) {
                $product_ids[] = $value['product_id'];
            }
        } else {
            $product = app::get('ome')->model('products')->dump(array('bn'=>$shop_product_bn),'product_id');
            $product_ids[] = $product['product_id'];
        }
        
        $product_ids = array_filter($product_ids);
        if ( !$product_ids ) {
            return 0;
        }

        #获取货品对应的店铺冻结
        $productObj          = app::get('ome')->model('products');
        $omeShopFreezeObj    = app::get('ome')->model('shop_freeze');
        
        $productList    = array();
        $product_info    = $productObj->getList('product_id, bn', array('product_id'=>$product_ids));
        foreach ($product_info as $key => $val)
        {
            $productList[$val['product_id']]    = $val['bn'];
        }
        
        #批量标识
        if ($batch)
        {
            $this->recal_shop_freeze = false;
        }
        else 
        {
            $this->recal_product_bn[$shop_product_bn] = false;
        }
        
        $shopBnFreezeInfo    = $omeShopFreezeObj->getList('product_id, shop_id, shop_freeze', array('product_id'=>$product_ids));
        if($shopBnFreezeInfo)
        {
            foreach ($shopBnFreezeInfo as $key => $item)
            {
                $item['bn']    = $productList[$item['product_id']];
                $tmpsha1       = sha1($item['shop_id']. '-' .strtolower($item['bn']));
                
                self::$shopFreeze[$tmpsha1]    = $item['shop_freeze'];
            }
        }
        unset($product_info, $productList, $shopBnFreezeInfo);
        
        return (int)self::$shopFreeze[$sha1];
    }

    public function set_shop_freeze($shop_product_bn,$shop_bn,$shop_id,$shop_freeze) 
    {
        $sha1Str = $shop_id.'-'.$shop_product_bn;
        $sha1 = sha1($sha1Str);
        self::$shopFreeze[$sha1] = $shop_freeze;
    }

    /**
     * 可售库存 = 仓库库存 - 冻结库存 - 预占 
     *
     * @param String $shop_product_bn 货品编号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID  
     */
    public function get_actual_stock($shop_product_bn,$shop_bn,$shop_id)
    {
        $branches = kernel::single('inventorydepth_shop')->getBranchByshop($shop_bn);

        if (!$branches) {
            kernel::log("没有传入货号{$shop_product_bn}或者店铺未绑定仓库");
            return false;
        }

        $sha1Str = $shop_bn.'-'.$shop_product_bn;
        $sha1 = sha1($sha1Str);
        if(isset(self::$actualStock[$sha1])) return (int)self::$actualStock[$sha1];

        $stockProductsLib = kernel::single('inventorydepth_stock_products');
        $store_sum = $store_freeze_sum = 0;
        foreach ($branches as $branch_bn) {
            $branch_product = $stockProductsLib->fetch_branch_products($branch_bn,$shop_product_bn);
            if ($branch_product) {
                $store_sum += $branch_product['store'];
                $store_freeze_sum += $branch_product['store_freeze'];
            }
        }

        $actual_stock = $store_sum - $this->get_globals_freeze($shop_product_bn,$shop_bn,$shop_id)-$store_freeze_sum;

        //$actual_stock(可售库存)减去配额库存
        if(app::get('drm')->is_installed()) {
            $dataObj = kernel::single('drm_inventory_router','data');
            $authorize_store = 0;
            $authorize_store = $dataObj->get_authorize_store($shop_product_bn,$branches,$shop_bn);
            $actual_stock = $actual_stock - $authorize_store;
        }

        self::$actualStock[$sha1] = (int)$actual_stock > 0 ? $actual_stock : 0;

        return (int)self::$actualStock[$sha1];
    }

    /**
     * 设置发布库存
     *
     *
     * @param String $shop_product_bn 货品编号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     * @return void
     * @author 
     **/
    public function set_release_stock($shop_product_bn,$shop_bn,$release_stock)
    {
        $sha1Str = $shop_bn.'-'.$shop_product_bn;
        $sha1 = sha1($sha1Str);
        self::$releaseStock[$sha1] = $release_stock;
    }

    /**
     * 发布库存
     *
     * @param String $shop_product_bn 货品编号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID  
     */
    public function get_release_stock($shop_product_bn,$shop_bn,$shop_id)
    {
        $sha1Str = $shop_bn.'-'.$shop_product_bn;
        $sha1 = sha1($sha1Str);
        if(isset(self::$releaseStock[$sha1]) || $this->recal_release_stock === false) return (int)self::$releaseStock[$sha1];

        $batch = kernel::single('inventorydepth_stock_products')->batch;
        if ($batch) {
            $this->recal_release_stock = false;
            $list = $this->app->model('shop_adjustment')->getList('release_stock,shop_product_bn,shop_bn',array('shop_product_bn'=>array_keys(inventorydepth_stock_products::$products) ));
            if ($list) {
                foreach ($list as $l) {
                    $tmpsha1 = sha1($l['shop_bn'].'-'.$l['shop_product_bn']);
                    self::$releaseStock[$tmpsha1] = $l['release_stock'];
                }
            }
        } else {
            $skusLib = kernel::single('inventorydepth_shop_skus');
            $shop_product_bn_crc32 = $skusLib->crc32($shop_product_bn);
            $shop_bn_crc32 = $skusLib->crc32($shop_bn);
            $list = $this->app->model('shop_adjustment')
                ->select()->columns('release_stock,shop_product_bn,shop_bn')
                ->where('shop_product_bn_crc32=?',$shop_product_bn_crc32)
                ->where('shop_bn_crc32=?',$shop_bn_crc32)
                ->instance()->fetch_all();

            $release_stock = 0;
            foreach ($list as $key => $value) {
                if ($value['shop_product_bn'] == $shop_product_bn && $value['shop_bn'] == $shop_bn) {
                    $release_stock = $value['release_stock'];
                    break;
                }
            }
            self::$releaseStock[$sha1] = (int)$release_stock > 0 ? $release_stock : 0;
        }
        return (int)self::$releaseStock[$sha1];
    }

    /**
     * 全局预占
     *
     * @param String $shop_product_bn 货品编号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID  
     */
    public function get_globals_freeze($shop_product_bn,$shop_bn,$shop_id)
    {
        $shop_branches = kernel::single('inventorydepth_shop')->getBranchByshop();
        $branches      = kernel::single('inventorydepth_shop')->getBranchByshop($shop_bn);
        if (empty($branches) || empty($shop_branches)) {
            kernel::log("没有传入货号{$product_bn}或者店铺未绑定仓库");
            return false;
        }

        $sha1Str = $shop_bn.'-'.$shop_product_bn;
        $sha1 = sha1($sha1Str);
        if(isset(self::$globalsFreeze[$sha1])) return (int)self::$globalsFreeze[$sha1];

        # 获取这些仓所对应的所有店铺
        if (!$this->shopList[$shop_bn]) {
            $shopes = array();
            foreach ($branches as $branch_bn) {
                foreach ($shop_branches as $shop => $branch) {
                    if (in_array($branch_bn, $branch)) {
                        $shopes[] = $shop;
                    }
                }
            }
            $s = $this->app->model('shop')->getList('shop_id,shop_bn',array('shop_bn'=>$shopes));
            $this->shopList[$shop_bn] = $s;
        }
        # 根据订单计算店铺预占(未发货订单) 该店铺下的商品ID
        $globals_freeze = 0;
        foreach ($this->shopList[$shop_bn] as $key=>$value) {
            $globals_freeze += $this->get_shop_freeze($shop_product_bn,$value['shop_bn'],$value['shop_id']);
        }
        self::$globalsFreeze[$sha1] = (int)$globals_freeze > 0 ? $globals_freeze : 0;

        return (int)self::$globalsFreeze[$sha1];
    }


    /**
     * 设置商品的库存
     *
     * @param Array $shop_product_bn 货品编号集合
     * @param String $shop_bn 店铺编号
     * @param Int $goods_shop_stock 商品库存
     * @return void
     * @author 
     **/
    public function set_goods_shop_stock($shop_product_bn,$shop_bn,$goods_shop_stock)
    {
        $sha1Str = $shop_bn.'-'.implode('-', $shop_product_bn);
        $sha1 = sha1($sha1Str);
        self::$shopStock[$sha1] = $goods_shop_stock;
    }

    /**
     * 获取店铺商品库存
     *
     * @param Array $shop_product_bn 货品编号集合
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     */
    public function get_goods_shop_stock($shop_product_bn,$shop_bn,$shop_id){

        $sha1Str = $shop_bn.'-'.implode('-', $shop_product_bn);
        $sha1 = sha1($sha1Str);
        if(isset(self::$shopStock[$sha1])) return (int)self::$shopStock[$sha1];

        $shop_stock = 0;
        foreach ($shop_product_bn as $pbn) {
            $shop_stock += $this->get_shop_stock($pbn,$shop_bn,$shop_id);
        }

        self::$shopStock[$sha1] = (int)$shop_stock > 0 ? $shop_stock : 0;

        return (int)self::$shopStock[$sha1];
    }

    /**
     * @description 获取店铺商品预占
     * @access public
     * @param Array $shop_product_bn 货品编号集合
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     */
    public function get_goods_shop_freeze($shop_product_bn,$shop_bn,$shop_id){

        $sha1Str = $shop_bn.'-'.implode('-', $shop_product_bn);
        $sha1 = sha1($sha1Str);
        if(isset(self::$shopFreeze[$sha1])) return (int)self::$shopFreeze[$sha1];

        # 根据订单计算店铺预占(未发货订单) 该店铺下的商品ID
        $shop_freeze = 0;
        foreach ($shop_product_bn as $pbn) {
            $shop_freeze += $this->get_shop_freeze($pbn,$shop_bn,$shop_id);
        }
        self::$shopFreeze[$sha1] = (int)$shop_freeze > 0 ? $shop_freeze : 0;

        return (int)self::$shopFreeze[$sha1];
    }

    /**
     * @description 获取商品可用库存
     * @access public
     * @param Array $shop_product_bn 货品编号集合
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     */
    public function get_goods_actual_stock($shop_product_bn,$shop_bn,$shop_id)
    {
        $branches = kernel::single('inventorydepth_shop')->getBranchByshop($shop_bn);    
        if (!$branches) {
            kernel::log("商品所在店铺未绑定仓库");
            return false;
        }

        $sha1Str = $shop_bn.'-'.implode('-', $shop_product_bn);
        $sha1 = sha1($sha1Str);
        if(isset(self::$actualStock[$sha1])) return (int)self::$actualStock[$sha1];

        # 获取商品所有货品
        $actual_stock = 0;
        foreach ($shop_product_bn as $pbn) {
            $actual_stock += $this->get_actual_stock($pbn,$shop_bn,$shop_id);
        }
        self::$actualStock[$sha1] = (int)$actual_stock > 0 ? $actual_stock : 0;

        return (int)self::$actualStock[$sha1];
    }


    /**
     * 设置发布库存
     *
     * @param Array $shop_product_bn 货品编号集合
     * @param String $shop_bn 店铺编号
     * @param Int release_stock 发布库存
     * @return void
     * @author 
     **/
    public function set_goods_release_stock($shop_product_bn,$shop_bn,$release_stock)
    {
        $sha1Str = $shop_bn.'-'.implode('-', $shop_product_bn);
        $sha1 = sha1($sha1Str);
        self::$releaseStock[$sha1] = $release_stock;
    }

    /**
     * @description 获取商品发布库存
     * @access public
     * @param Array $shop_product_bn 货品编号集合
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     */
    public function get_goods_release_stock($shop_product_bn,$shop_bn,$shop_id)
    {
        $sha1Str = $shop_bn.'-'.implode('-', $shop_product_bn);
        $sha1 = sha1($sha1Str);
        if(isset(self::$releaseStock[$sha1])) return (int)self::$releaseStock[$sha1];

        $release_stock = 0;
        foreach ($shop_product_bn as $pbn) {
            $release_stock += $this->get_release_stock($pbn,$shop_bn,$shop_id);
        }

        self::$releaseStock[$sha1] = (int)$release_stock > 0 ? $release_stock : 0;

        return (int)self::$releaseStock[$sha1];
    }

    /**
     * @description 获取商品全局预占
     * @access public
     * @param Array $shop_product_bn 货品编号集合
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     */
    public function get_goods_globals_freeze($shop_product_bn,$shop_bn,$shop_id)
    {   

        $shop_branches = kernel::single('inventorydepth_shop')->getBranchByshop();
        $branches = kernel::single('inventorydepth_shop')->getBranchByshop($shop_bn);
        if (empty($branches) || empty($shop_branches)) {
            kernel::log("没有传入货号{$product_bn}或者店铺未绑定仓库");
            return false;
        }

        $sha1Str = $shop_bn.'-'.implode('-', $shop_product_bn);
        $sha1 = sha1($sha1Str);
        if(isset(self::$globalsFreeze[$sha1])) return (int)self::$globalsFreeze[$sha1];

        $globals_freeze = 0;
        foreach ($shop_product_bn as $pbn) {
             $globals_freeze += $this->get_globals_freeze($pbn,$shop_bn,$shop_id);
        }

        self::$globalsFreeze[$sha1] = (int)$globals_freeze > 0 ? $globals_freeze : 0;

        return (int)self::$globalsFreeze[$sha1];
    }

    /**
     * @description 捆绑商品店铺库存
     * @access public
     * @param String $shop_product_bn 捆绑货号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     * @return void
     */
    public function get_pkg_shop_stock($shop_product_bn,$shop_bn,$shop_id) 
    {
        # 读内存值
        $sha1Str = $shop_bn.'-'.$shop_product_bn.'-pkg';
        $sha1 = sha1($sha1Str);
        if(isset(self::$shopStock[$sha1])) return (int)self::$shopStock[$sha1];

        # 货品转CRC32
        $shop_stock = $this->get_shop_stock($shop_product_bn,$shop_bn,$shop_id);

        self::$shopStock[$sha1] = (int)$shop_stock > 0 ? $shop_stock : 0;

        return (int)self::$shopStock[$sha1];
    }

    public function set_pkg_shop_stock($shop_product_bn,$shop_bn,$shop_stock)
    {
        $sha1Str = $shop_bn.'-'.$shop_product_bn.'-pkg';
        $sha1 = sha1($sha1Str);
        self::$shopStock[$sha1] = $shop_stock;
    }

    /**
     * @description 捆绑商品店铺预占
     * @access public
     * @param String $shop_product_bn 捆绑货号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     * @return void
     */
    public function get_pkg_shop_freeze($shop_product_bn,$shop_bn,$shop_id) 
    {
        $sha1Str = $shop_id.'-'.strtolower($shop_product_bn).'-pkg';
        $sha1 = sha1($sha1Str);
        if(isset(self::$shopFreeze[$sha1]) || $this->recal_pkg_shop_freeze===false) return (int)self::$shopFreeze[$sha1];
        
        $batch = kernel::single('inventorydepth_stock_pkg')->batch;
        if ($batch) {
            $this->recal_pkg_shop_freeze = false;
            $sql = 'SELECT sum(obj.quantity) as _s, obj.bn, ord.shop_id 
                    FROM '.DB_PREFIX.'ome_order_objects AS obj 
                    LEFT JOIN '.DB_PREFIX.'ome_orders AS ord ON (obj.order_id=ord.order_id) 
                    WHERE ord.process_status IN(\'unconfirmed\',\'is_retrial\',\'is_declare\') AND ord.ship_status in(\'0\') AND ord.status=\'active\' AND obj.obj_type=\'pkg\' GROUP BY ord.shop_id,obj.bn';
            $list = $this->db->select($sql);
            
            $split_order_list    = array();
            if ($list) {
                foreach ($list as $key=>$value)
                {
                    $tmpsha1 = sha1($value['shop_id'].'-'.strtolower($value['bn']).'-pkg');
                    
                    #单独获取[部分拆分]订单的店铺预占数量 ExBOY
                    $splitOrderFreezeNum    = $this->get_split_order_shop_freeze($value['shop_id'], $value['bn']);
                    $pkg_shop_freeze        = intval($value['_s']) + $splitOrderFreezeNum;
                    
                    #防止中文货号
                    $md5_bn    = sha1($value['bn']);
                    $split_order_list[$value['shop_id']][$md5_bn]    = true;
                    
                    self::$shopFreeze[$tmpsha1] = $pkg_shop_freeze;
                }
            }
            
            #防止店铺只有一个订单并且是部分拆分状态 ExBOY
            $sql = 'SELECT obj.bn, ord.shop_id 
                    FROM '.DB_PREFIX.'ome_order_objects AS obj 
                    LEFT JOIN '.DB_PREFIX.'ome_orders AS ord ON (obj.order_id=ord.order_id) 
                    WHERE ord.process_status IN(\'splitting\') AND ord.ship_status in(\'0\', \'2\', \'3\') 
                    AND ord.status=\'active\' AND obj.obj_type=\'pkg\' GROUP BY ord.shop_id,obj.bn';
            $list = $this->db->select($sql);
            if ($list)
            {
                foreach ($list as $key=>$value)
                {
                    $md5_bn    = sha1($value['bn']);
                    if($split_order_list[$value['shop_id']][$md5_bn])
                    {
                        continue;
                    }
                    
                    $tmpsha1 = sha1($value['shop_id'].'-'.strtolower($value['bn']).'-pkg');
                    
                    #[部分拆分]捆绑商品的店铺预占数量 ExBOY
                    $splitOrderFreezeNum    = $this->get_split_order_shop_freeze($value['shop_id'], $value['bn']);
                    
                    self::$shopFreeze[$tmpsha1]    = $splitOrderFreezeNum;
                }
                unset($split_order_list, $splitOrderFreezeNum);
            }
            
        } else {
            # 根据订单计算店铺预占(未发货订单) 该店铺下的商品ID
            $sql = 'SELECT sum(obj.quantity) as _s 
                    FROM '.DB_PREFIX.'ome_order_objects AS obj 
                    LEFT JOIN '.DB_PREFIX.'ome_orders AS ord ON (obj.order_id=ord.order_id) 
                    WHERE ord.process_status IN(\'unconfirmed\',\'is_retrial\',\'is_declare\') AND ord.ship_status in(\'0\') AND ord.status=\'active\' AND ord.shop_id=\''.$shop_id.'\' AND obj.bn=\''.$shop_product_bn.'\' AND obj.obj_type=\'pkg\'';

            $shop_freeze = $this->db->selectrow($sql);
            
            #单独获取[部分拆分]订单的店铺预占数量 ExBOY
            $splitOrderFreezeNum    = $this->get_split_order_shop_freeze($shop_id, $shop_product_bn);
            $pkg_shop_freeze    = intval($shop_freeze['_s']) + $splitOrderFreezeNum;
            
            self::$shopFreeze[$sha1] = $pkg_shop_freeze;
            
            unset($splitOrderFreezeNum, $pkg_shop_freeze);
        }
        
        return (int)self::$shopFreeze[$sha1];
    }

    /**
     * @description 捆绑商品可售库存
     * @access public
     * @param String $shop_product_bn 捆绑货号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     * @return void
     */
    public function get_pkg_actual_stock($shop_product_bn,$shop_bn,$shop_id) 
    {

        $sha1Str = $shop_bn.'-'.$shop_product_bn.'-pkg';
        $sha1 = sha1($sha1Str);
        if(isset(self::$actualStock[$sha1])) return (int)self::$actualStock[$sha1];

        $pkg = kernel::single('inventorydepth_stock_pkg')->fetch_pkg($shop_product_bn);
        if(!$pkg || !$pkg['products'] || !is_array($pkg['products'])) return false;

        $stockList = array();
        foreach ($pkg['products'] as $product) {
            $stock = $this->get_actual_stock(trim($product['bn']),$shop_bn,$shop_id);

            if ($stock === false) return false;

            $stockList[$product['bn']] = (int)$stock/$product['pkgnum'];
        }

        sort($stockList); $actual_stock = array_shift($stockList);

        self::$actualStock[$sha1] = (int)$actual_stock > 0 ? $actual_stock : 0;

        return (int)self::$actualStock[$sha1];
    }

    /**
     * @description 捆绑商品发布库存
     * @access public
     * @param String $shop_product_bn 捆绑货号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     * @return void
     */
    public function get_pkg_release_stock($shop_product_bn,$shop_bn,$shop_id) 
    {
        $sha1Str = $shop_bn.'-'.$shop_product_bn.'-pkg';
        $sha1 = sha1($sha1Str);
        if(isset(self::$releaseStock[$sha1])) return (int)self::$releaseStock[$sha1];

        $release_stock = $this->get_release_stock($shop_product_bn,$shop_bn,$shop_id);

        self::$releaseStock[$sha1] = (int)$release_stock > 0 ? $release_stock : 0;

        return (int)self::$releaseStock[$sha1];
    }

    public function set_pkg_release_stock($shop_product_bn,$shop_bn,$release_stock)
    {
        $sha1Str = $shop_bn.'-'.$shop_product_bn.'-pkg';
        $sha1 = sha1($sha1Str);
        self::$releaseStock[$sha1] = $release_stock;
    }

    /**
     * @description 捆绑商品全局预占
     * @access public
     * @param String $shop_product_bn 捆绑货号
     * @param String $shop_bn 店铺编号
     * @param String $shop_id 店铺ID
     * @return void
     */
    public function get_pkg_globals_freeze($shop_product_bn,$shop_bn,$shop_id) 
    {
        $shop_branches = kernel::single('inventorydepth_shop')->getBranchByshop();
        $branches      = kernel::single('inventorydepth_shop')->getBranchByshop($shop_bn);
        if (empty($branches) || empty($shop_branches)) {
            kernel::log("没有传入货号{$product_bn}或者店铺未绑定仓库");
            return false;
        }

        $sha1Str = $shop_bn.'-'.$shop_product_bn.'-pkg';
        $sha1 = sha1($sha1Str);
        if(isset(self::$globalsFreeze[$sha1])) return (int)self::$globalsFreeze[$sha1];

        # 获取这些仓所对应的所有店铺
        $shop_id = $this->shopList[$shop_bn];
        if (!$shop_id) {
            $shopes = array();
            foreach ($branches as $branch_bn) {
                foreach ($shop_branches as $shop => $branch) {
                    if (in_array($branch_bn, $branch)) {
                        $shopes[] = $shop;
                    }
                }
            }
            $s = $this->app->model('shop')->getList('shop_id,shop_bn',array('shop_bn'=>$shopes));
            $shop_id = array_map('current',$s);
            $this->shopList[$shop_bn] = $shop_id;
        }

        $globals_freeze = 0;
        foreach ($this->shopList[$shop_bn] as $key=>$value) {
            $globals_freeze += $this->get_pkg_shop_freeze($shop_product_bn,$value['shop_bn'],$value['shop_id']);
        }
        self::$globalsFreeze[$sha1] = (int)$globals_freeze > 0 ? $globals_freeze : 0;

        return (int)self::$globalsFreeze[$sha1];
    }

    /**
     * @description
     * @access public
     * @param void
     * @return void
     */
    public function set_recal_shop_freeze($recal_shop_freeze) 
    {
        $this->recal_shop_freeze = $recal_shop_freeze;
        if ($recal_shop_freeze == true) {
            self::$shopFreeze = array();
        }
    }

    /**
     * 获取部分拆分订单上捆绑商品对应店铺冻结数量
     * 发货状态：未发货、部分发货、部分退货
     *
     * @param $order_id
     * @param $obj_bn
     *
     * @return intval
     * @author wangbiao@shopex.cn
     */
    function get_split_order_shop_freeze($shop_id, $obj_bn)
    {
       $sql    = "SELECT ord.order_id, obj.obj_id, obj.quantity FROM ". DB_PREFIX ."ome_order_objects AS obj 
                  LEFT JOIN ". DB_PREFIX ."ome_orders AS ord ON obj.order_id=ord.order_id 
                  WHERE ord.process_status IN('splitting') AND ord.ship_status IN('0', '2', '3') AND ord.status='active' 
                  AND ord.shop_id='". $shop_id ."' AND obj.bn='". $obj_bn ."' AND obj.obj_type='pkg'";
       $objInfo = $this->db->select($sql);
       
       if(empty($objInfo))
       {
           return 0;
       }
       
       //防止订单上有多个重复相同商品货号
       $freeze_num    = 0;
       foreach ($objInfo as $key => $val)
       {
           #获取item订单上其中一条详细信息
           $sql_item    = "SELECT item_id, product_id, nums, `delete` FROM ". DB_PREFIX ."ome_order_items 
                           WHERE order_id=". $val['order_id'] ." AND obj_id=". $val['obj_id'];
           $itemIfno    = $this->db->selectrow($sql_item);
           
           if(empty($itemIfno))
           {
               $freeze_num    += $val['quantity'];//如是没明细的无效订单_增加预占_防止多回写库存
           }
           elseif($itemIfno['delete'] == 'true')
           {
               continue;//部分拆分时_Obj层是删除状态则跳过
           }
           
           #获取货品已拆分发货单上的数量
           $dly_sql    = "SELECT SUM(number) AS num FROM `sdb_ome_delivery_items_detail` AS did 
                         JOIN `sdb_ome_delivery` AS d ON d.delivery_id=did.delivery_id 
                         WHERE did.order_item_id='". $itemIfno['item_id'] ."' AND did.product_id='". $itemIfno['product_id'] ."' 
                         AND d.status NOT IN('back', 'cancel', 'return_back') AND d.is_bind='false'";
           $deliveryNum	= $this->db->selectrow($dly_sql);
           
           #剩余未拆分数量
           $diff_num    = $itemIfno['nums'] - intval($deliveryNum['num']);
           $obj_freeze_num    = intval($val['quantity'] / $itemIfno['nums'] * $diff_num);
           
           $freeze_num    += $obj_freeze_num;
       }
       
       return $freeze_num;
    }
}
