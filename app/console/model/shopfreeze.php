<?php
/**
 * 店铺冻结列表model类
 *
 * @access public
 * @author wangbiao<wangbiao@shopex.cn>
 * @version $Id: shopfreeze.php 2016-01-18 13:00
 */
class console_mdl_shopfreeze extends dbeav_model
{
    public function table_name($real = false){
        if($real){
            $table_name = 'sdb_ome_shop_freeze';
        }else{
            $table_name = 'shop_freeze';
        }
        
        return $table_name;
    }
    
    public function get_schema()
    {
        return app::get('ome')->model('shop_freeze')->get_schema();
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
        
        if(!empty($rows))
        {
            foreach ($rows as $key => $val)
            {
                #店铺冻结数量
                $link_url    = 'index.php?app=console&ctl=admin_shopfreeze&act=show_shopfreeze_list&product_id='. $val['product_id'] .'&shop_id='. $val['shop_id'];
                $rows[$key]['shop_freeze']    = '<a href="'. $link_url .'" target="_blank" title="点击重新计算店铺冻结数量.">'. $val['shop_freeze'] .'</a>';
                
                #货号
                $rows[$key]['product_id']    = $val['bn'];
                
                #店铺名称
                $shopInfo   = $shopObj->dump(array('shop_id'=>$val['shop_id']), 'name');
                $rows[$key]['shop_id']    = $shopInfo['name'];
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
}
