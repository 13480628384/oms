<?php
/**
 * 店铺冻结增减明细日志Lib类_只用于测试
 * 
 * @author wangbiao@shopex.cn
 * @version 1.0
 */
class ome_shop_freeze_log
{
    var $freeze_type    = array();
    
    function __construct()
    {
        #店铺冻结类型
        $this->freeze_log    = array(
                1 => array('title'=>'创建订单', 'io'=>1),
                2 => array('title'=>'订单更新(新增)', 'io'=>1),
                3 => array('title'=>'订单更新(释放)', 'io'=>0),
                4 => array('title'=>'订单暂停', 'io'=>1),
                5 => array('title'=>'订单编辑(新增)', 'io'=>1),
                6 => array('title'=>'订单编辑(释放)', 'io'=>0),
                7 => array('title'=>'CRM赠品', 'io'=>1),
                8 => array('title'=>'订单优惠赠品', 'io'=>1),
                9 => array('title'=>'失败订单', 'io'=>1),
                10 => array('title'=>'取消订单', 'io'=>0),
                11 => array('title'=>'余单撤消', 'io'=>0),
                
                21 => array('title'=>'新建发货单', 'io'=>0),
                22 => array('title'=>'撤销发货单', 'io'=>1),
                23 => array('title'=>'打回发货单', 'io'=>1),
                24 => array('title'=>'取消发货单', 'io'=>1),
        );
    }
    
    /**
     *
     * 店铺冻结明细日志
     * 
     * @param String $shop_id
     * @param Int $product_id
     * @param Int $num    释放冻结为负数
     * @param tinyint $type_id    类型
     * @param string $memo    备注
     * @return Boolean
     */
    public function changeLog($shop_id, $product_id, $num, $type_id, $memo='')
    {
        $shopFreezeLogObj    = app::get('ome')->model('shop_freeze_log');
        
        #查询冻结信息
        $freezeInfo    = $shopFreezeLogObj->getList('balance_nums', array('shop_id'=>$shop_id, 'product_id'=>$product_id), 0, 1, 'create_time DESC');
        
        #初始化明细日志
        if(empty($freezeInfo))
        {
            $omeShopFreezeObj    = app::get('ome')->model('shop_freeze');
            $shopBnFreezeInfo    = $omeShopFreezeObj->dump(array('product_id'=>$product_id, 'shop_id'=>$shop_id), 'shop_freeze');
            $balance_nums        = $shopBnFreezeInfo['shop_freeze'];
            
            #保存
            $memo        = '[初始化]' . $memo;
            $operator    = kernel::single('desktop_user')->get_name();
            $save_data    = array(
                    'shop_id' => $shop_id,
                    'product_id' => $product_id,
                    'type_id' => $type_id,
                    'nums' => $num,
                    'balance_nums' => $balance_nums,
                    'operator' => $operator,
                    'create_time' => time(),
                    'memo' => $memo,
            );
            $shopFreezeLogObj->save($save_data);
            
            return true;//初始化后,直接返回
        }
        
        $balance_nums    = $freezeInfo[0]['balance_nums'];
        if(empty($balance_nums))
        {
            $balance_nums    = 0;
        }
        
        #店铺冻结类型
        $io    = $this->getFreezeTypeIo($type_id);
        if($io)
        {
            #增加
            $balance_nums    = $balance_nums + $num;
        }
        else 
        {
            #释放
            if($balance_nums < $num)
            {
                $balance_nums    = 0;
            }
            else 
            {
                $balance_nums    = $balance_nums - $num;
            }
        }
        
        #保存
        $operator    = kernel::single('desktop_user')->get_name();
        $save_data    = array(
            'shop_id' => $shop_id,
            'product_id' => $product_id,
            'type_id' => $type_id,
            'nums' => $num,
            'balance_nums' => $balance_nums,
            'operator' => $operator,
            'create_time' => time(),
            'memo' => $memo,
        );
        
        $shopFreezeLogObj->save($save_data);
        
        return true;
    }
    
    /**
     * 获取店铺冻结类型
     * 
     * @param tinyint $type_id
     * @return number
     */
    function getFreezeTypeIo($type_id)
    {
        if(isset($this->freeze_log[$type_id]))
        {
            return $this->freeze_log[$type_id]['io'];
        }
        else
        {
            return 1;
        }
    }
    function getFreezeTypeTitle($type_id)
    {
        return $this->freeze_log[$type_id]['title'];
    }
    
    function getFreezeTypeSign($type_id)
    {
        if(isset($this->freeze_log[$type_id]))
        {
            return ($this->freeze_log[$type_id]['io'] == '1' ? '+' : '-');
        }
        else
        {
            return '';
        }
    }
    
    /**
     * 获取店铺冻结系统配置
     *
     * @return Boolean
     */
    function get_product_shop_freeze_config()
    {
        $is_shop_freeze_log    = app::get('ome')->getConf('ome.product.shop_freeze');
        
        return $is_shop_freeze_log == '1' ? true : false;
    }
}
