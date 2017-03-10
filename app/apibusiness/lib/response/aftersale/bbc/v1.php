<?php
class apibusiness_response_aftersale_bbc_v1 extends apibusiness_response_aftersale_v1
{

     static public $return_type = array(
        'ONLY_REFUND'=>'refund',
        'REFUND_GOODS'=>'return',
        'EXCHANGING_GOODS'=>'change',
       
    );
    public function add(){
        parent::add();
        $this->_aftersale_additional();
    }

    public function _aftersale_additional(){
        $return_bn = $this->_aftersaleSdf['return_bn'];
        $shop_id   = $this->_shop['shop_id'];
        $bbcreturnModel = app::get(self::_APP_NAME)->model('return_product_bbc');

        if ($this->_aftersaleSdf['return_type']){
            $data = array(
                'shop_id'=>$shop_id,
                'return_bn'=>$return_bn,
                'return_type'=>self::$return_type[$this->_aftersaleSdf['return_type']],
            );

            $bbcreturnModel->save($data);
        }
        
        
    }


}