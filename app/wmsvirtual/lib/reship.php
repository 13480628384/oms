<?php

class wmsvirtual_reship
{
    public $__status_type = array('WMS_FINISHED'=>'FINISH');
    /**
     * 退货结果.
     * @param   
     * @return  array
     * @access  public
     * @author  sunjing@shopex.cn
     */
    function result($result,$node_id)
    {
        $data = $this->format_data($result);
        $method='wms.reship.status_update';
        
        kernel::single('wmsvirtual_response')->dispatch('wms',$method,$data,$node_id);
    }

    
    /**
     * 格式化数据
     * @param 
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function format_data($result)
    {
         
         $oReship = &app::get('ome')->model('reship');
         $oItems = &app::get('ome')->model('reship_items');
         $reship_bn = $result['out_order_code'];
         $reship = $oReship->dump(array('reship_bn'=>$reship_bn),'reship_id');
         
         $reship_id = $reship['reship_id']; 
         $items = $oItems->getlist('bn as product_bn,num as normal_num',array('reship_id'=>$reship_id));
         $items = json_encode($items);
         $data = array(
            'reship_bn'=>$reship_bn,
            'logistics'=>'',
            'logi_no'=>'',
            'warehouse'=>'33',
            'status'=>$this->__status_type[$result['status_code']],
            'remark'=>'',
            'operate_time'=>'',
            'item'=>$items,
        );
        
        return $data;
    }
} 

?>