<?php
/**
 +----------------------------------------------------------
 * 订单发票列表
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_finder_order
{
    public $addon_cols = 'id,is_status,type_id';//调用字段
    
    /*------------------------------------------------------ */
    //-- 编辑
    /*------------------------------------------------------ */
    var $column_edit  = '编辑';
    var $column_edit_order = 5;
    var $column_edit_width = '50';
    function column_edit($row)
    {
        if($row['is_print'] == 2)
        {
            return '已作废';
        }
        elseif($row['is_status'] == 1)
        {
            return '已打印';
        }
        else
        {
           return '<a href="index.php?app=invoice&ctl=admin_order&act=editor&id='.$row['id'].'&p[0]='.$row['id'].'&finder_id='.$_GET['_finder']['finder_id'].'" 
        target="dialog::{width:800,height:630,title:\'编辑发票信息\'}">编辑</a>';
        }
    }
    
    /*------------------------------------------------------ */
    //-- 详细列表
    /*------------------------------------------------------ */
    var $detail_invoice    = '发票详情';
    function detail_invoice($id)
    {
        $render     = app::get('invoice')->render();
        $result     = array();
        
        //invoice_order
        $oItem      = app::get('invoice')->model('order');
        $items      = $oItem->getList('*', array('id' => $id), 0, 1);
        
        //操作人
        $operatorArr                = $oItem->getUserName($items[0]['operator']);
        $items[0]['operator_name']  = $operatorArr['name'];
 
        //type
        $columns         = $oItem->schema;
        $type_arr        = $columns['columns']['type_id']['type'];
        $status_arr      = $columns['columns']['is_status']['type'];
        
        $type_id         = $items[0]['type_id'];
        $is_status       = $items[0]['is_status'];
        $items[0]['type_id']            = $type_arr[$type_id];
        $items[0]['is_status']          = $status_arr[$is_status];
        
        //地区
        $areaArr        = explode(':', $items[0]['ship_area']);
        $areaArr        = $areaArr[1];
        $items[0]['ship_area']  = str_replace('/', ' ', $areaArr);
        
        //完整批次号[第4段]
        if($items[0]['batch_number'] && $items[0]['delivery_id'])
        {
            $oDelivery      = app::get('ome')->model('print_queue_items');
            $delivery_arr   = $oDelivery->getList('ident_dly', array('delivery_id' => $items[0]['delivery_id'], 
                            'ident' => $items[0]['batch_number']), 0, 1);
            
            $items[0]['dly']    = '_'.$delivery_arr[0]['ident_dly'];
        }
        
        $render->pagedata['item']       = $items[0];
        
        //ome_orders
        $order_id   = $items[0]['order_id'];
        $orderObj   = app::get('ome')->model('orders');
        
        //type
        $columns        = $orderObj->schema;
        $pay_status     = $columns['columns']['pay_status']['type'];
        $ship_status    = $columns['columns']['ship_status']['type'];

        $orders     = $orderObj->getList('status, pay_status, pay_status, ship_status, 
                        createtime, cost_item, is_tax, cost_payment, total_amount, payed, 
                        mark_text, custom_mark, print_status, order_type, paytime', 
                        array('order_id'=>$order_id), 0, 1);
        
        $pay_val        = $orders[0]['pay_status'];
        $ship_val       = $orders[0]['ship_status'];
        
        $orders[0]['print_status']  = ($orders[0]['print_status'] ? '已打印' : '未打印');
        $orders[0]['pay_status']    = $pay_status[$pay_val];
        $orders[0]['ship_status']   = $ship_status[$ship_val];
        
        $orders[0]['custom_mark']   = unserialize($orders[0]['custom_mark']);
        $orders[0]['mark_text']     = unserialize($orders[0]['mark_text']);
        $orderExtendObj = app::get('ome')->model('order_extend');
        $orderextend = $orderExtendObj->dump(array('order_id'=>$order_id),'receivable');
        $receivable = $orderextend['receivable'];
        //物流信息
        $deliveryObj        = app::get('ome')->model('delivery');
        $deliveryIds        = $deliveryObj->getDeliverIdByOrderId($order_id);

        if($deliveryIds[0])
        {
            $dly        = $deliveryObj->dump($deliveryIds[0]);
            $render->pagedata['dlyArr']   = $dly;
        }
        $render->pagedata['receivable']   = $receivable;
        $render->pagedata['orders']   = $orders[0];
        return $render->fetch('admin/order_detail.html');
    }
    
    /*------------------------------------------------------ */
    //-- 发票打印记录
    /*------------------------------------------------------ */
    var $detail_print = '发票打印记录';
    function detail_print($id)
    {
        $render     = app::get('invoice')->render();
        
        //invoice_order
        $oItem      = app::get('invoice')->model('order');
        $items      = $oItem->getList('order_id', array('id' => $id), 0, 1);
        $order_id   = $items[0]['order_id'];

        //log
        $logObj     = app::get('ome')->model('operation_log');
        $goodslog   = $logObj->read_log(array('obj_id'=>$order_id, 'obj_type'=>'order@invoice', 'operation'=>'invoice_print@ome'), 0, -1);
        foreach($goodslog as $k=>$v)
        {
            $goodslog[$k]['operate_time'] = date('Y-m-d H:i:s',$v['operate_time']);
        }
        
        $render->pagedata['datalist'] = $goodslog;
        return $render->fetch('admin/detail_print.html');
    }
    
    /*------------------------------------------------------ */
    //-- 发票编辑日志
    /*------------------------------------------------------ */
    var $detail_history = '发票编辑日志';
    function detail_history($id)
    {
        $render     = app::get('invoice')->render();

        $logObj     = app::get('ome')->model('operation_log');
        $goodslog   = $logObj->read_log(array('obj_id'=>$id, 'obj_type'=>'order@invoice', 'operation'=>'invoice_edit@ome'), 0, -1);
        foreach($goodslog as $k=>$v)
        {
            $goodslog[$k]['operate_time'] = date('Y-m-d H:i:s',$v['operate_time']);
        }
        
        $render->pagedata['datalist'] = $goodslog;
        return $render->fetch('admin/detail_log.html');
    }
    
    /*------------------------------------------------------ */
    //-- 发票操作日志
    /*------------------------------------------------------ */
    var $detail_operate_logs = '发票操作日志';
    function detail_operate_logs($id)
    {
        $render     = app::get('invoice')->render();
        
        //invoice_order
        $oItem      = app::get('invoice')->model('order');
        $items      = $oItem->getList('order_id', array('id' => $id), 0, 1);
        $order_id   = $items[0]['order_id'];

        $logObj     = app::get('ome')->model('operation_log');
        $goodslog   = $logObj->read_log(array('obj_id'=>$order_id, 'obj_type'=>'order@invoice', 'operation'=>'invoice_create@ome'), 0, -1);
        foreach($goodslog as $k=>$v)
        {
            $goodslog[$k]['operate_time'] = date('Y-m-d H:i:s',$v['operate_time']);
        }
        
        $render->pagedata['datalist'] = $goodslog;
        return $render->fetch('admin/detail_log.html');
    }
    
    /*------------------------------------------------------ */
    //-- 显示行样式
    /*------------------------------------------------------ */
    function row_style($row)
    {
        $style = '';
        if($row[$this->col_prefix . 'is_status'] == '1')
        {
           $style .= ' highlight-row ';
        }
        if($row[$this->col_prefix . 'type_id'] == '1')
        {
           $style .= ' list-even ';
        }
        
        return $style;
    }
    
    /*------------------------------------------------------ */
    //-- 格式化字段
    /*------------------------------------------------------ */
    #确认状态
    var $column_process_status = '确认状态';
    var $column_process_status_width = '80';
    var $column_process_status_order = 54;
    function column_process_status($row)
    {
        $order_Obj     = app::get('ome')->model('orders');
        
        //process_status
        $columns        = $order_Obj->schema;
        $process_array  = $columns['columns']['process_status']['type'];
        
        $orderRs       = $order_Obj->getList('order_id, process_status', array('order_bn'=>$row['order_bn']), 0, 1);

        $str       = ($orderRs[0]['process_status'] == 'splited' ? '<span style="font-weight:bold;color:#ff0000;">'
                       .$process_array[$orderRs[0]['process_status']].'</span>' : $process_array[$orderRs[0]['process_status']]);

        return $str;
    }
    #来源店铺
    var $column_source_shop = '来源店铺';
    var $column_source_shop_width = '100';
    var $column_source_shop_order = 55;
    function column_source_shop($row)
    {
    	$sql       = "SELECT b.name FROM ".DB_PREFIX."ome_orders as a 
    	           LEFT JOIN ".DB_PREFIX."ome_shop as b ON a.shop_id=b.shop_id 
    	           WHERE a.order_id='".$row['order_id']."'";
    	$data      = kernel::database()->select($sql);

        return $data[0]['name'];
    }
}