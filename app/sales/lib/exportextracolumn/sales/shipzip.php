<?php
/**
 * 销售单导出扩展收货地址字段
 * @author xiayuanjun@shopex.cn
 * @version 1.0
 */
class sales_exportextracolumn_sales_shipzip extends sales_exportextracolumn_abstract implements sales_exportextracolumn_interface{

    protected $__pkey = 'order_id';

    protected $__extra_column = 'column_ship_zip';

    /**
     *
     * 获取订单相关的优惠方案
     * @param $ids
     * @return array $tmp_array关联数据数组
     */
    public function associatedData($ids){
        //根据订单ids获取相应的优惠方案
        $orderObj = app::get('ome')->model('orders');
        $shipzip_lists = $orderObj->db->select('select ship_zip,'.$this->__pkey.' from  sdb_ome_orders where order_id in ('.implode(',',$ids).')');

        $tmp_array= array();
        foreach($shipzip_lists as $k=>$row){
            $tmp_array[$row[$this->__pkey]] = $row['ship_zip'];
        }
        return $tmp_array;
    }

}