<?php
class wmsvirtual_delivery 
{
    public $__status_type = array('WMS_DELIVERED'=>'DELIVERY','WMS_PACKAGE'=>'DELIVERY');
    
    /**
     * 更新发货单
     * @param  
     * @return 
     * @access  public
     * @author sunjing@shopex.cn
     */
    function result($result,$node_id)
    {
        
        $method = 'wms.delivery.status_update';
        $data = $this->format_data($result);
       
        kernel::single('wmsvirtual_response')->dispatch('wms',$method,$data,$node_id);
    }

    
    /**
     *格式化数据
     * @param   
     * @return  array
     * @access  public
     * @author cyyr24@sina.cn
     */
    function format_data($result)
    {
        $delivery_bn = $result['delivery_bn'];
        $oDelivery_ext = app::get('console')->model('delivery_extension');
        $delivery_ext = $oDelivery_ext->dump(array('original_delivery_bn'=>$delivery_bn),'delivery_bn');
        $company_code = $result['company_code'];
        $wms_id = $result['wms_id'];
        $logi_code = kernel::single('wmsmgr_func')->getlogiCode($wms_id,$company_code);
        $data = array(
            'delivery_bn'=>$delivery_ext['delivery_bn'],
            'logistics'=>$logi_code,
            'logi_no'=>$result['logistics_no'],
            'status'=>$this->__status_type[$result['status_code']],
            //'volume'=>'333',
            //'weight'=>'444',
            //'remark'=>'44',
            'operate_time'=>$result['operate_time'],
            
        );
        
        return $data;

    }
} 

?>