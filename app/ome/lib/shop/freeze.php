<?php
/**
 * 货品product对应店铺冻结Lib类
 * 
 * @author wangbiao@shopex.cn
 * @version 1.0
 */
class ome_shop_freeze
{
    function __construct()
    {
        $this->_omeShopFreezeObj    = app::get('ome')->model('shop_freeze');
        $this->last_modified        = time();
    }

    /**
     *
     * 增加货品的店铺冻结
     * 
     * @param String $shop_id
     * @param Int $product_id
     * @param Int $num
     * @return Boolean
     */
    public function freeze($shop_id, $product_id, $num)
    {
        #如果该货品没有店铺冻结记录_新增一条
        $shopBnFreezeInfo    = $this->_omeShopFreezeObj->getList('product_id', array('product_id'=>$product_id, 'shop_id'=>$shop_id), 0, 1);
        if(empty($shopBnFreezeInfo))
        {
            $tmpData    = array('product_id'=>$product_id, 'shop_id'=>$shop_id, 'shop_freeze'=>0);
            $this->_omeShopFreezeObj->insert($tmpData);
            unset($tmpData);
        }
        
        $shopFreeze    = "shop_freeze=IFNULL(shop_freeze,0)+" . $num;
        
        $sql = "UPDATE sdb_ome_shop_freeze SET ". $shopFreeze .", last_modified=". $this->last_modified ." 
                WHERE product_id=". $product_id ." AND shop_id = '". $shop_id ."'";
        return $this->_omeShopFreezeObj->db->exec($sql);
    }

    /**
     *
     * 释放货品的店铺冻结
     * 
     * @param String $shop_id
     * @param Int $product_id
     * @param int $num
     * @return Boolean
     */
    public function unfreeze($shop_id, $product_id, $num)
    {
        $shopFreeze    = " shop_freeze=IF((CAST(shop_freeze AS SIGNED)-". $num .")>0,shop_freeze-$num,0)";
        
        $sql = "UPDATE sdb_ome_shop_freeze SET ".$shopFreeze.", last_modified=". $this->last_modified ." 
                WHERE product_id=". $product_id ." AND shop_id = '". $shop_id ."'";
        return $this->_omeShopFreezeObj->db->exec($sql);
    }
    
    /**
     *
     * 指定更新单个货品对应的店铺冻结
     *
     * @param String $shop_id
     * @param Int $product_id
     * @return Boolean
     */
    public function update_product_shop_freeze($shop_id, $product_id)
    {
        #获取店铺冻结系统配置
        $shopFreezeLogLib      = kernel::single('ome_shop_freeze_log');
        $is_shop_freeze_log    = $shopFreezeLogLib->get_product_shop_freeze_config();
        
        #第一：先初始化货品对应店铺的冻结数量
        $sql_update     = "UPDATE sdb_ome_shop_freeze SET shop_freeze=0 WHERE shop_id='". $shop_id ."' AND product_id='". $product_id ."'";
        $this->_omeShopFreezeObj->db->exec($sql_update);
        
        #设置店铺冻结数量
        $sql		= "SELECT o.order_id, o.order_bn, o.process_status, o.confirm, i.item_id, i.bn, i.nums, i.sendnum
			           FROM sdb_ome_order_items AS i LEFT JOIN sdb_ome_orders AS o ON i.order_id=o.order_id
	                   WHERE o.process_status in ('unconfirmed', 'is_retrial', 'is_declare', 'splitting') AND o.ship_status in('0', '2')
			           AND o.status='active' AND i.`delete`= 'false' AND o.shop_id='". $shop_id ."' AND i.product_id=".$product_id;
        $dataList	= $this->_omeShopFreezeObj->db->select($sql);
        
        if(empty($dataList))
        {
            return 0;
        }
        
        $count_num    = 0;
        foreach ($dataList as $key => $val)
        {
            #订单部分拆分_但未确认状态
            if($val['confirm'] != 'N' && $val['process_status'] != 'splitting')
            {
                continue;//跳过
            }
            
            #剩余未生成发货单的货品数量
            $num	= 0;
            if($val['process_status'] == 'splitting')
            {
                #部分拆分时_直接减去发货单上货品数量
                $dly_sql	= "SELECT SUM(number) AS num FROM `sdb_ome_delivery_items_detail` AS did
					           JOIN `sdb_ome_delivery` AS d ON d.delivery_id=did.delivery_id
	                           WHERE did.order_item_id='". $val['item_id'] ."' AND did.product_id='". $product_id ."'
	                           AND d.status NOT IN('back', 'cancel', 'return_back') AND d.is_bind='false'";
                $deliveryNum	= $this->_omeShopFreezeObj->db->selectrow($dly_sql);
                $num			= $val['nums'] - intval($deliveryNum['num']);
            }
            elseif($val['nums'] > $val['sendnum'])
            {
                $num	= $val['nums'] - $val['sendnum'];
            }
            
            #添加到店铺冻结
            if($num)
            {
                $count_num    += $num;
                
                #[增加]货品店铺冻结 ExBOY
                $this->freeze($shop_id, $product_id, $num);
            }
        }
        
        #最后_更新店铺冻结明细日志
        if($is_shop_freeze_log)
        {
            $sql    = "SELECT balance_nums FROM sdb_ome_shop_freeze_log WHERE shop_id='". $shop_id ."' AND product_id='". $product_id ."' ORDER BY create_time DESC";
            $row    = $this->_omeShopFreezeObj->db->selectrow($sql);
            
            $balance_nums    = intval($row['balance_nums']);
            $diff_num        = $count_num - $balance_nums;
            
            if($diff_num > 0)
            {
                $shopFreezeLogLib->changeLog($shop_id, $product_id, $diff_num, 5, '点击重新计算店铺冻结数量');
            }
            elseif($diff_num < 0)
            {
                $shopFreezeLogLib->changeLog($shop_id, $product_id, abs($diff_num), 6, '点击重新计算店铺冻结数量');
            }
        }
        
        return $count_num;
    }
}
