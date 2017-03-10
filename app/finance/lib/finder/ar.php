<?php
class finance_finder_ar{

    var $addon_cols = "addon";
    function detail_edit($ar_id){
        $aritemObj = app::get('finance')->model('ar_items');
        $items = $aritemObj->getList('*',array('ar_id'=>$ar_id));
        $render = app::get('finance')->render();
        $render->pagedata['items'] = $items;
        $render->pagedata['finder_id'] = $_GET['_finder']['finder_id'];
        return $render->fetch('ar/detail.html');
    }

    var $column_sale_money = '商品成交金额';
    var $column_sale_money_width = "65";
    var $column_sale_money_order = 8;
    public function column_sale_money($row){
        $addon = unserialize($row[$this->col_prefix.'addon']);
        return "￥".number_format($addon['sale_money'],2);
    }

    var $column_fee_money = '运费收入';
    var $column_fee_money_width = "65";
    var $column_fee_money_order = 9;
    public function column_fee_money($row){
        $addon = unserialize($row[$this->col_prefix.'addon']);
        return "￥".number_format($addon['fee_money'],2);
    }

    var $column_delete = "删除";
    var $column_delete_width = "65";
    var $column_delete_order = 17;
    function column_delete($row){
        $ar_id = $row['ar_id'];
        $render = app::get('finance')->render();
        $render->pagedata['ar_id'] = $ar_id;
        $render->pagedata['finder_id'] = $_GET['_finder']['finder_id'];
        if($row['charge_status'] == 0){
            return $render->fetch('ar/do_cancel.html');
        }
    }
}
?>