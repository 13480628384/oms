<?php
/**
 +----------------------------------------------------------
 * 列表扩展搜索字段
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-05-04 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_finder_extend_filter_order
{
    function get_extend_colums()
    {
    	//配置缓存 
        $setting    = app::get('invoice')->getConf('invoice.order_setting');
        $setting    = $setting[0]['title'];
        
        $content_data   = array();
        foreach ($setting as $key => $val)
        {
            $content_data[$val] = $val;
        }
        unset($setting);
        
        $db['order']=array (
            'columns' => array (
                'shop_id' =>
                array (
                  'type' => 'table:shop@ome',
                  'label' => '来源店铺',
                  'width' => 75,
                  'editable' => false,
                  'in_list' => true,
                  'filtertype' => 'normal',
                  'filterdefault' => true,
                  'panel_id' => 'delivery_finder_top',
                ),
                'content' =>
                array (
                  'type' => $content_data,
                  'label' => '发票内容',
                  'width' => 75,
                  'editable' => false,
                  'in_list' => true,
                  'filtertype' => 'normal',
                  'filterdefault' => true,
                  'panel_id' => 'delivery_finder_top',
                ),
            )
        );
        return $db;
    }
}