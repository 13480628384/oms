<?php
class ome_tax_default extends ome_tax_abstract{
    
   

    public function get_invoice_money($orders){ 
        $invoice_money = $orders['total_amount'];
        return $invoice_money;
    
    }

    
}

?>