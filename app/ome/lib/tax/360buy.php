<?php
class ome_tax_360buy extends ome_tax_abstract{
    
   

    public function get_invoice_money($orders){ 
        $order_id = $orders['order_id'];
        $invoice_money = $orders['total_amount'];
        $pmt_amount = 0;
        $pmtObj = app::get('ome')->model('order_pmt');
        $pmt_detail = $pmtObj->getList("pmt_amount",array('order_id'=>$order_id,'pmt_describe'=>'41-京东券优惠'));
        foreach ($pmt_detail as $pmt){
            $pmt_amount+=$pmt['pmt_amount'];
        }
        $invoice_money = $invoice_money-$pmt_amount;
        return $invoice_money;
    
    }

    
}

?>