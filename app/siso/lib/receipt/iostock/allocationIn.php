<?php
class siso_receipt_iostock_allocationIn extends siso_receipt_iostock_abstract implements siso_receipt_iostock_interface{

     /**
     *
     * 出入库类型id
     * @var int
     */
    protected $_typeId = 4;

    /**
     *
     * 出库/入库动作
     * @var int
     */
    protected $_io_type = 1;
    /**
     *
     * 根据调拨入库组织出入库单明细内容
     * @param int $iso_id
     * 
     */
    function get_io_data($data){
        $iso_id = $data['iso_id'];
        $isoObj = app::get('taoguaniostockorder')->model('iso');
        $itemsObj = app::get('taoguaniostockorder')->model('iso_items');
        $iostock_data = array();
        $iso_detail = $isoObj->dump($iso_id,'*');
        if ($data['items']){
            $iso_items_detail = $data['items'];;
        }else{
            $iso_items_detail = $itemsObj->getList('*', array('iso_id'=>$iso_id), 0, -1);
        }
        $operator       = kernel::single('desktop_user')->get_name();
        $operator = $operator=='' ? 'system' : $operator;
        $create_time = $data['operate_time'] == '' ? $iso_detail['create_time']: $data['operate_time'];
        if ($iso_items_detail){
            foreach ($iso_items_detail as $k=>$v){
                $iostock_data[] = array(
                    'branch_id' => $data['branch_id']!='' ? $data['branch_id']:$iso_detail['branch_id'],
                    'original_bn' => $data['original_bn']!='' ? $data['original_bn'] :$iso_detail['iso_bn'],
                    'original_id' => $data['original_id']!='' ? $data['original_id'] : $iso_id,
                    'original_item_id' => $v['iso_items_id'],
                    'supplier_id' => $iso_detail['supplier_id'],
                    'supplier_name' => $iso_detail['supplier_name'],
                    'bn' => $v['bn'],
                    'iostock_price' => $v['price']!='' ? $v['price']: '0',
                    'nums' => $v['nums'],
                    'cost_tax' => $iso_detail['cost_tax'],
                    'oper' => $iso_detail['oper']== '' ? $data['operator'] : $iso_detail['oper'],
                    'create_time' => $create_time,
                    'operator' => $operator,
                   'memo'=>$create_time,
  
                );
            }
        }
        

        return $iostock_data;
    }
}