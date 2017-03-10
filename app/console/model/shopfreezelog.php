<?php
/**
 * 店铺冻结明细日志model类
 *
 * @access public
 * @author wangbiao<wangbiao@shopex.cn>
 * @version $Id: shopfreezelog.php 2016-01-18 13:00
 */
class console_mdl_shopfreezelog extends dbeav_model
{
    public function table_name($real = false){
        if($real){
            $table_name = 'sdb_ome_shop_freeze_log';
        }else{
            $table_name = 'shop_freeze_log';
        }
        
        return $table_name;
    }
    
    public function get_schema()
    {
        return app::get('ome')->model('shop_freeze_log')->get_schema();
    }
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        if($orderby)
        {
            $orderby    = ' ORDER BY ' . $orderby;
        }
        
        $sql    = "SELECT a.*, b.bn FROM ". $this->table_name(true) ." AS a LEFT JOIN sdb_ome_products AS b ON a.product_id=b.product_id 
                   WHERE ". $this->_filter($filter) . $orderby;
        $rows = $this->db->selectLimit($sql, $limit, $offset);
        
        #格式化数据
        $shopObj    = app::get('ome')->model('shop');
        $shopFreezeLogLib    = kernel::single('ome_shop_freeze_log');
        
        if(!empty($rows))
        {
            foreach ($rows as $key => $val)
            {
                #货号
                $rows[$key]['product_id']    = $val['bn'];
                
                #店铺名称
                $shopInfo   = $shopObj->dump(array('shop_id'=>$val['shop_id']), 'name');
                $rows[$key]['shop_id']    = $shopInfo['name'];
                
                #冻结类型
                $rows[$key]['type_id']    = $shopFreezeLogLib->getFreezeTypeTitle($val['type_id']);
                
                #冻结数据
                $rows[$key]['nums']    = $shopFreezeLogLib->getFreezeTypeSign($val['type_id']) . $val['nums'];
            }
        }
        
        return $rows;
    }
    
    public function _filter($filter, $tableAlias=null, $baseWhere=null)
    {
        $where    = array();
        
        if(isset($filter['product_id']) && $filter['product_id'])
        {
            $where[] = "b.bn='". $filter['product_id'] ."'";
            unset($filter['product_id']);
        }
        
        $filter    = parent::_filter($filter, 'a', $baseWhere) . ($where ? " AND " .implode($where, ' AND ') : '');
        
        return $filter;
    }
    
    public function count($filter = null)
    {
        $sql    = "SELECT count(*) as _count FROM ". $this->table_name(true) ." AS a LEFT JOIN sdb_ome_products AS b ON a.product_id=b.product_id 
                   WHERE ". $this->_filter($filter);
        $rows   = $this->db->select($sql);
        
        return intval($rows[0]['_count']);
    }
    
    /**
     * 货号格式化
     * 
    function modifier_product_id($product_id)
    {
        $productObj    = app::get('ome')->model('products');
        $productInfo   = $productObj->dump(array('product_id'=>$product_id), 'bn');
        
        return $productInfo['bn'];
    }
    */
    
    /**
     * 店铺名称格式化
     * 
    function modifier_shop_id($shop_id)
    {
        $shopObj    = app::get('ome')->model('shop');
        $shopInfo   = $shopObj->dump(array('shop_id'=>$shop_id), 'name');
        
        return $shopInfo['name'];
    }
    */
}
