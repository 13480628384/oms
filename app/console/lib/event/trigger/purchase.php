<?php
class console_event_trigger_purchase extends console_event_trigger_stockinabstract{

    
    protected $_io_type = 'PURCHASE';

    /**
    * 入库数据
    */
    function getStockInParam($param){
        
        $Opo = app::get('purchase')->model('po');
        $po_id = $param['po_id'];
        $aRow = $Opo->dump($po_id, '*', array('po_items' => array('name,price,num,bn,product_id')));
        $branch_id = $aRow['branch_id'];
        $branch_detail = $this->getBranchByid($branch_id);
        $supplier_id = $aRow['supplier_id'];
        $purchase = array(
            'io_type'         => 'PURCHASE',
            'io_bn'           => $aRow['po_bn'],
            'branch_bn'       => $branch_detail['branch_bn'],
            'storage_code'    => $branch_detail['storage_code'],
            'create_time'     => $aRow['purchase_time'],
            'arrive_time'     => $aRow['arrive_time'],
            'total_goods_fee' => $aRow['amount'],
            'branch_id'       => $branch_id,
            'supplier_id'     => $supplier_id,
            'receiver_name'   => $branch_detail['uname'],
            'receiver_address' => $branch_detail['address'],
            'receiver_phone' => $branch_detail['phone'],
            'receiver_mobile' => $branch_detail['mobile'],
            'receiver_zip' => $branch_detail['zip'],
        );
        
        $area = $branch_detail['area'];
        if ($area) {
            kernel::single('eccommon_regions')->split_area($area);
            $purchase['receiver_state']    = $area[0];
            $purchase['receiver_city']     = $area[1];
            $purchase['receiver_district'] = $area[2];
        }


        $memo = $aRow['memo'];
        if ($memo){
            $memo = unserialize($memo);
            if($memo){
                $memo = array_pop($memo);
                $purchase['memo'] = $memo['op_content'];
            }
            
        }
        $item = array();
        foreach($aRow['po_items'] as $po_items){
            $item[] = array(
                'num'  =>$po_items['num'],
                'bn'  =>$po_items['bn'],
                'name'  =>$po_items['name'],
                'price' => $po_items['price'],
            
            );
        }
        $purchase['items'] = $item;
       
        return $purchase;
    }

    protected function update_out_bn($io_bn,$out_iso_bn)
    {
        $oPo = app::get('purchase')->model('po');
        $data = array(
            'out_iso_bn'=>$out_iso_bn
        );
       
        $result = $oPo->update($data,array('po_bn'=>$io_bn));
       
    }
}