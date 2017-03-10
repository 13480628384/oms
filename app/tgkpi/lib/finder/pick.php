<?php
class tgkpi_finder_pick {
	var $column_ident = "打印批次号";
    var $column_ident_width = "120";
    var $addon_cols = "print_ident,print_ident_dly";
    function column_ident($row) {
        $identStr = '';
        if($row[$this->col_prefix.'print_ident']){
            $identStr .= $row[$this->col_prefix.'print_ident']."_".$row[$this->col_prefix.'print_ident_dly'];
        }
        return $identStr;
    }
    var $column_product_name = "商品名称";
    var $column_product_name_width = "120";
    function column_product_name($row) {
        $products = app::get('ome')->model('products');
        $product_info = $products->getList('name',array('bn'=>$row['product_bn']));
        return $product_info[0]['name'];
    }
    var $column_spec_info = "规格";
    var $column_spec_info_width = "120";
    function column_spec_info($row) {
        $products = app::get('ome')->model('products');
        $product_info = $products->getList('spec_info',array('bn'=>$row['product_bn']));
        return $product_info[0]['spec_info'];
    }
}