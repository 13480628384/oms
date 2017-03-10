<?php
/**
 +----------------------------------------------------------
 * 订单发票设置
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_finder_order_setting
{
	public $addon_cols = 'title,tax_rate';//调用字段
	
	/*------------------------------------------------------ */
    //-- 编辑
    /*------------------------------------------------------ */
	var $column_edit  = '编辑';
    var $column_edit_order = 5;
    var $column_edit_width = '60';
    function column_edit($row)
    {
    	return '<a href="index.php?app=invoice&ctl=admin_order_setting&act=editor&id='.$row['sid'].'">编辑</a>';
    }
    
    /*------------------------------------------------------ */
    //-- 详细列表
    /*------------------------------------------------------ */
    var $detail_edit    = '发票内容详情';
    function detail_edit($id)
    {
        $render     = app::get('invoice')->render();
        $oItem      = kernel::single("invoice_mdl_order_setting");
        $items      = $oItem->getList('*', array('sid' => $id), 0, 1);
        
        //serialize
        $str        = '';
        foreach ($items[0]['title'] as $key => $val)
        {
            $str    .= ', '.$val;
        }
        $str        = substr($str, 2);
        $items[0]['title']      = $str;
        
        $render->pagedata['item'] = $items[0];
        $render->display('admin/item_detail.html');
    }
    
    /*------------------------------------------------------ */
    //-- 发票内容序列化
    /*------------------------------------------------------ */
    var $column_titleExt        = '发票内容';
    var $column_titleExt_order  = 20;
    var $column_titleExt_width  = '300';
    function column_titleExt($row)
    {
    	$arr       = $row[$this->col_prefix . 'title'];
    	$str       = '';
    	foreach ($arr as $key => $val)
    	{
    	   $str    .= ', '.$val;
    	}
    	$str       = substr($str, 2);
    	return $str;
    }
    
    /*------------------------------------------------------ */
    //-- 税率[百分比]
    /*------------------------------------------------------ */
    var $column_tax_rate        = '税率';
    var $column_tax_rate_order  = 30;
    var $column_tax_rate_width  = '80';
    function column_tax_rate($row)
    {
        $str       = $row[$this->col_prefix . 'tax_rate'];
        if(!empty($str))
        {
            $str    .= '%';
        }
        return $str;
    }    
}