<?php
/**
 +----------------------------------------------------------
 * 发票操作日志类
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_mdl_order extends dbeav_model
{
    //是否有导出配置
     var $has_export_cnf = true;
	 public $defaultOrder = array('id', 'DESC');
	 
    /*------------------------------------------------------ */
    //-- 用户信息
    /*------------------------------------------------------ */
    function getUserName($uid)
    {
    	$uid           = intval($uid);
    	$userData      = array();
    	$filter        = array('user_id'=>$uid);
    	$rows          = app::get('desktop')->model('users')->getList('user_id, name', $filter);
    	
    	return $rows[0];
    }
    
    /*------------------------------------------------------ */
    //-- 获取列表数据[自定义]
    /*------------------------------------------------------ */
    public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        //Where
        $where        = '';
        $key_array    = array('order_id', 'order_bn', 'type_id', 'cost_tax', 'is_status', 
                        'is_print', 'invoice_no', 'content', 'batch_number', 'operator');
        
		if(!empty($filter['id']))
        {
			if(is_array($filter['id']))
			{
				$where   .= " AND a.id in(" . implode(',', $filter['id']) .")";
			}
			else
			{
        		$where   .= " AND a.id='" . $filter['id'] ."'";
			}
        }
		
		foreach ($filter as $key => $val)
        {
            if(in_array($key, $key_array))
            {
                $where   .= " AND a.". $key . "='" . $val ."'";
            }
        }
        
        if(!empty($filter['amount']))
        {
        	$filter['amount']      = floatval($filter['amount']);
            if($filter['_amount_search'] == 'than')
            {
               $where .= " AND a.amount > '".$filter['amount']."'"; 
            }
            elseif($filter['_amount_search'] == 'lthan')
            {
                $where .= " AND a.amount < '".$filter['amount']."'"; 
            }
            elseif($filter['_amount_search'] == 'nequal')
            {
                $where .= " AND a.amount = '".$filter['amount']."'"; 
            }
            elseif($filter['_amount_search'] == 'sthan')
            {
                $where .= " AND a.amount <= '".$filter['amount']."'"; 
            }
            elseif($filter['_amount_search'] == 'bthan')
            {
                $where .= " AND a.amount >= '".$filter['amount']."'"; 
            }
            elseif($filter['_amount_search'] == 'between' && $filter['amount_from'] && $filter['amount_to'])
            {
                $where .= " AND a.amount >= '".$filter['amount_from']."' AND a.amount <= '".$filter['amount_to']."'";
            }
        }
              
        if(!empty($filter['title']))
        {
        	$where .= " AND a.title like('%".$filter['title']."%')";
        }
        
        if(!empty($filter['create_time']))
            {
                $create_time_hour     = $filter['_DTIME_']['H']['create_time'];
                $create_time_minute   = $filter['_DTIME_']['M']['create_time'];             
                $create_time_start    = strtotime($filter['create_time'].' '.$create_time_hour.':'.$create_time_minute.':00');
                if($filter['_create_time_search']=='nequal')
                {
                    $where .= " AND a.create_time='".$create_time_start."'";
                }
                elseif($filter['_create_time_search']=='than')
                {
                    $where .= " AND a.create_time>'".$create_time_start."'";
                }
                elseif($filter['_create_time_search']=='lthan')
                {
                    $where .= " AND a.create_time<'".$create_time_start."'";
                }
                elseif($filter['_create_time_search']=='between' && $filter['create_time_from'] && $filter['create_time_to'])
                {
                    $from_hour            = $filter['_DTIME_']['H']['create_time_from'];
                    $from_minute          = $filter['_DTIME_']['H']['create_time_from'];
                    $create_time_from     = $filter['create_time_from'];
                    $create_time_from     = strtotime($create_time_from.' '.$from_hour.':'.$from_minute.':00');
                    
                    $to_hour            = $filter['_DTIME_']['H']['create_time_to'];
                    $to_minute          = $filter['_DTIME_']['H']['create_time_to'];
                    $create_time_to     = $filter['create_time_to'];
                    $create_time_to     = strtotime($create_time_to.' '.$to_hour.':'.$to_minute.':00');
                    
                    $where  .= " AND (a.create_time>='".$create_time_from."' AND a.create_time<='".$create_time_to."')";
                }
        }
        if(!empty($filter['dateline']))
            {
                $create_time_hour     = $filter['_DTIME_']['H']['dateline'];
                $create_time_minute   = $filter['_DTIME_']['M']['dateline'];             
                $create_time_start    = strtotime($filter['dateline'].' '.$create_time_hour.':'.$create_time_minute.':00');
                if($filter['_dateline_search']=='nequal')
                {
                    $where .= " AND a.dateline='".$create_time_start."'";
                }
                elseif($filter['_dateline_search']=='than')
                {
                    $where .= " AND a.dateline>'".$create_time_start."'";
                }
                elseif($filter['_dateline_search']=='lthan')
                {
                    $where .= " AND a.dateline<'".$create_time_start."'";
                }
                elseif($filter['_dateline_search']=='between' && $filter['dateline_from'] && $filter['dateline_to'])
                {
                    $from_hour            = $filter['_DTIME_']['H']['dateline_from'];
                    $from_minute          = $filter['_DTIME_']['H']['dateline_from'];
                    $create_time_from     = $filter['dateline_from'];
                    $create_time_from     = strtotime($create_time_from.' '.$from_hour.':'.$from_minute.':00');
                    
                    $to_hour            = $filter['_DTIME_']['H']['dateline_to'];
                    $to_minute          = $filter['_DTIME_']['H']['dateline_to'];
                    $create_time_to     = $filter['dateline_to'];
                    $create_time_to     = strtotime($create_time_to.' '.$to_hour.':'.$to_minute.':00');
                    
                    $where  .= " AND (a.dateline>='".$create_time_from."' AND a.dateline<='".$create_time_to."')";
                }
        }
        
        //orders
        if(!empty($filter['shop_id']))
        {
            $where  .= " AND b.shop_id='".$filter['shop_id']."'";
        }
        if($filter['is_status']=='0' && $filter['is_print']=='1')
        {
            $where  .= " AND b.process_status='splited'";
        }

        $where        = substr($where, 5);
        if(!empty($where))
        {
            $where    = " WHERE ".$where;
        }
        
        //Sql
        $sql      = "SELECT a.*, b.process_status FROM ". DB_PREFIX ."invoice_order as a 
                      LEFT JOIN ". DB_PREFIX ."ome_orders as b ON a.order_id=b.order_id 
                      ". $where ." ORDER BY a.create_time DESC";

        $rows     = $this->db->selectLimit($sql, $limit, $offset);
        $this->tidy_data($rows, $cols);
        
        return $rows;
    }
    
    /*------------------------------------------------------ */
    //-- 获取总数[自定义]
    /*------------------------------------------------------ */
    public function count($filter=null)
    {
    	//Where
        $where        = '';
        $key_array    = array('order_id', 'order_bn', 'type_id', 'cost_tax', 'is_status', 
                              'is_print', 'invoice_no', 'content', 'batch_number', 'operator');
        
        foreach ($filter as $key => $val)
        {
            if(in_array($key, $key_array))
            {
                $where   .= " AND a.". $key . "='" . $val ."'";
            }
        }
        
        if(!empty($filter['create_time']))
        {
            $create_time_hour     = $filter['_DTIME_']['H']['create_time'];
            $create_time_minute   = $filter['_DTIME_']['M']['create_time'];
            $create_time_start    = strtotime($filter['create_time'].' '.$create_time_hour.':'.$create_time_minute.':00');
            if($filter['_create_time_search']=='nequal')
            {
                $where .= " AND a.create_time='".$create_time_start."'";
            }
            elseif($filter['_create_time_search']=='than')
            {
                $where .= " AND a.create_time>'".$create_time_start."'";
            }
            elseif($filter['_create_time_search']=='lthan')
            {
                $where .= " AND a.create_time<'".$create_time_start."'";
            }
            elseif($filter['_create_time_search']=='between' && $filter['create_time_from'] && $filter['create_time_to'])
            {
                $from_hour            = $filter['_DTIME_']['H']['create_time_from'];
                $from_minute          = $filter['_DTIME_']['H']['create_time_from'];
                $create_time_from     = $filter['create_time_from'];
                $create_time_from     = strtotime($create_time_from.' '.$from_hour.':'.$from_minute.':00');
        
                $to_hour            = $filter['_DTIME_']['H']['create_time_to'];
                $to_minute          = $filter['_DTIME_']['H']['create_time_to'];
                $create_time_to     = $filter['create_time_to'];
                $create_time_to     = strtotime($create_time_to.' '.$to_hour.':'.$to_minute.':00');
        
                $where  .= " AND (a.create_time>='".$create_time_from."' AND a.create_time<='".$create_time_to."')";
            }
        }
        
        if(!empty($filter['dateline']))
        {
            $create_time_hour     = $filter['_DTIME_']['H']['dateline'];
            $create_time_minute   = $filter['_DTIME_']['M']['dateline'];
            $create_time_start    = strtotime($filter['dateline'].' '.$create_time_hour.':'.$create_time_minute.':00');
            if($filter['_dateline_search']=='nequal')
            {
                $where .= " AND a.dateline='".$create_time_start."'";
            }
            elseif($filter['_dateline_search']=='than')
            {
                $where .= " AND a.dateline>'".$create_time_start."'";
            }
            elseif($filter['_dateline_search']=='lthan')
            {
                $where .= " AND a.dateline<'".$create_time_start."'";
            }
            elseif($filter['_dateline_search']=='between' && $filter['dateline_from'] && $filter['dateline_to'])
            {
                $from_hour            = $filter['_DTIME_']['H']['dateline_from'];
                $from_minute          = $filter['_DTIME_']['H']['dateline_from'];
                $create_time_from     = $filter['dateline_from'];
                $create_time_from     = strtotime($create_time_from.' '.$from_hour.':'.$from_minute.':00');
        
                $to_hour            = $filter['_DTIME_']['H']['dateline_to'];
                $to_minute          = $filter['_DTIME_']['H']['dateline_to'];
                $create_time_to     = $filter['dateline_to'];
                $create_time_to     = strtotime($create_time_to.' '.$to_hour.':'.$to_minute.':00');
        
                $where  .= " AND (a.dateline>='".$create_time_from."' AND a.dateline<='".$create_time_to."')";
            }
        }
        
        //orders
        if(!empty($filter['shop_id']))
        {
            $where  .= " AND b.shop_id='".$filter['shop_id']."'";
        }
        if($filter['is_status']=='0' && $filter['is_print']=='1')
        {
            $where  .= " AND b.process_status='splited'";
        }
        
        if(!empty($filter['amount']))
        {
            $filter['amount']      = floatval($filter['amount']);
            if($filter['_amount_search'] == 'than')
            {
                $where .= " AND a.amount > '".$filter['amount']."'";
            }
            elseif($filter['_amount_search'] == 'lthan')
            {
                $where .= " AND a.amount < '".$filter['amount']."'";
            }
            elseif($filter['_amount_search'] == 'nequal')
            {
                $where .= " AND a.amount = '".$filter['amount']."'";
            }
            elseif($filter['_amount_search'] == 'sthan')
            {
                $where .= " AND a.amount <= '".$filter['amount']."'";
            }
            elseif($filter['_amount_search'] == 'bthan')
            {
                $where .= " AND a.amount >= '".$filter['amount']."'";
            }
            elseif($filter['_amount_search'] == 'between' && $filter['amount_from'] && $filter['amount_to'])
            {
                $where .= " AND a.amount >= '".$filter['amount_from']."' AND a.amount <= '".$filter['amount_to']."'";
            }
        }
        
        $where        = substr($where, 5);
        if(!empty($where))
        {
            $where    = " WHERE ".$where;
        }
        
        //Sql
        $sql      = "SELECT count(*) as num FROM ". DB_PREFIX ."invoice_order as a 
                      LEFT JOIN ". DB_PREFIX ."ome_orders as b ON a.order_id=b.order_id 
                      ". $where;
        
        $row      = $this->db->select($sql);
        return $row[0]['num'];
    }
    
    /*------------------------------------------------------ */
    //-- 更新批次号
    /*------------------------------------------------------ */
    function update_batch_number($allItems, $idents)
    {
    	if(empty($allItems) || empty($idents['idents']))
    	{
    	   return false;
    	}
    	
    	$inOrder       = app::get('invoice')->model('order');
    	$batch_number  = join(',', $idents['idents']);//批次号前三段,例：1-40409-0176
    	
    	$new_data      = array();
    	foreach ($allItems as $key => $volist)
    	{
    	   foreach ($volist['delivery_order'] as $key_j => $val)
    	   {
    	   	   //更新批次号+发货单号
    	   	   $new_data   = array('batch_number' => $batch_number, 'delivery_id'=>$val['delivery_id']);
    	   	   $inOrder->update($new_data, array('order_id'=>$val['order_id']));
    	   }
    	}
    	
    	return true;
    }
    
    /*------------------------------------------------------ */
    //-- 更新开票订单
    /*------------------------------------------------------ */
    function update_order($data)
    {
    	if(empty($data['order_id']))
    	{
    	   return false;
    	}
        
    	$opObj     = app::get('ome')->model('operation_log');
    	$inOrder   = app::get('invoice')->model('order');
    	$row       = $inOrder->getList('id, order_id, is_status', array('order_id'=>$data['order_id']), 0, 1);
    	$row       = $row[0];
    	
    	//新增
    	if(empty($row) && $data['is_tax'] == 'true')
    	{
    		
    		$oItem         = app::get('ome')->model('orders');
    		$rs_order      = $oItem->getList('order_id, order_bn, total_amount, tax_company, ship_name, ship_area, ship_addr, ship_tel', 
    		                     array('order_id' => $data['order_id']), 0, 1);
    		$rs_order      = $rs_order[0];

    		$setting       = app::get('app')->getConf('invoice.order_setting');
	        $tax_rate      = $setting[0]['tax_rate'];
	        $tax_rate      = $tax_rate ? $tax_rate : 17;//税率,默认17%

	        $rs_order['tax_company']    = strip_tags($rs_order['tax_company']);
			$tax_company	= (trim($rs_order['tax_company']) ? $rs_order['tax_company'] : $rs_order['ship_name']);
			
	        $data       = array(
	                        'order_id'=>$rs_order['order_id'],
	                        'order_bn'=>$rs_order['order_bn'],
	                        'amount'=>$rs_order['total_amount'],//订单总额
	                        'type_id'=>0,//发票类型
							'is_print' => 1,//默认打印
	                        'title'=>$tax_company,//发票抬头
	                        'content'=>'商品明细',//发票内容
	                        'remarks'=>'',//发票备注
	                        'tax_company'=>$rs_order['ship_name'],//客户名称,默认调用收货人
	                        'ship_area'=>$rs_order['ship_area'],//客户收货地区
	                        'ship_addr'=>$rs_order['ship_addr'],//客户地址
	                        'ship_tel'=>$rs_order['ship_tel'],//客户电话
	                        'create_time' => time(),
	                    );
	            
	        //insert
	        $result       = $inOrder->save($data);
	        
	        //日志
	        $msg          = '变更订单发票状态为开票时，插入发票订单信息.';
	        $opObj->write_log('invoice_create@ome', $rs_order['order_id'], $msg);
	        
    	}//更新
    	elseif(!empty($row) && $row['is_status'] == 0 && $data['is_tax'] == 'true')
    	{
            $data['tax_title']    = trim(strip_tags($data['tax_title']));
            if(empty($data['tax_title']))
            {
            	$oItem         = app::get('ome')->model('orders');
            	$rs_order      = $oItem->getList('ship_name', array('order_id' => $data['order_id']), 0, 1);
            	$data['tax_title']  = $rs_order[0]['ship_name'];
            }
            
    		$new_data   = array('title' => $data['tax_title']);
            $filter     = array('id' => $row['id']);
    		$result     = $inOrder->update($new_data, $filter);
            
            //日志
            $log_msg   = '变更订单发票状态后，更新发票信息';
            $opObj->write_log('invoice_edit@ome', $row['id'], $log_msg);
            
    	}//删除
        elseif(!empty($row) && $row['is_status'] == 0 && $data['is_tax'] == 'false')
        {
            $result     = $inOrder->delete(array('id'=>$row['id']));
            
            //日志
            $log_msg   = '变更订单发票状态为不开票时，删除发票记录';
            $opObj->write_log('invoice_delete@ome', $row['id'], $log_msg);
        }
    }
    
    /*------------------------------------------------------ */
    //-- 删除发票订单记录
    /*------------------------------------------------------ */
    function delete_order($order_id)
    {
        if(empty($order_id))
        {
           return false;
        }
        
        $opObj      = app::get('ome')->model('operation_log');
        $inOrder    = app::get('invoice')->model('order');
        $result     = $inOrder->delete(array('order_id' => $order_id));
        
        //日志
        $log_msg   = '订单取消，删除发票记录';
        $opObj->write_log('invoice_delete@ome', $order_id, $log_msg);
    }
    
    /*------------------------------------------------------ */
    //-- 有新订单，自动插入发票订单表
    /*------------------------------------------------------ */
    function insert_order($rs_order)
    {
        $inOrder        = app::get('invoice')->model('order');
        $row            = $inOrder->getList('order_id', array('order_id'=>$rs_order['order_id']), 0, 1);
        
        if(!empty($row))// || ($rs_order['is_tax'] == 'true')
        {
            return false;
        }
        
        $setting       = app::get('app')->getConf('invoice.order_setting');
        $tax_rate      = $setting[0]['tax_rate'];
        $tax_rate      = $tax_rate ? $tax_rate : 17;//税率,默认17%
        
        $rs_order['tax_company']    = strip_tags($rs_order['tax_company']);
		$tax_company	= (trim($rs_order['tax_company']) ? $rs_order['tax_company'] : $rs_order['ship_name']);
		
        $data       = array(
                        'order_id'=>$rs_order['order_id'],
                        'order_bn'=>$rs_order['order_bn'],
                        'amount'=>$rs_order['total_amount'],//订单总额

                        'type_id'=>0,//发票类型
						'is_print' => 1,//默认打印
                        'title'=>$tax_company,//发票抬头
                        'content'=>'商品明细',//发票内容
                        'remarks'=>'',//发票备注
                        'tax_company'=>$rs_order['ship_name'],//客户名称,默认调用收货人
                        'ship_area'=>$rs_order['ship_area'],//客户收货地区
                        'ship_addr'=>$rs_order['ship_addr'],//客户地址
                        'ship_tel'=>$rs_order['ship_tel'],//客户电话
                        'create_time' => time(),
                    );
            
        //insert
        $result       = $inOrder->save($data);
        if($result)
        {
            $msg          = '成功，新订单自动插入发票订单信息.';
        }
        else
        {
            $msg          = '失败，有新订单自动插入发票订单信息.';
        }

        $opObj     = app::get('ome')->model('operation_log');
        $opObj->write_log('invoice_create@ome', $rs_order['order_id'], $msg);
        
        return true;
    }
    /**
     * 导出数据格式
     * @param unknown_type $filter
     * @param unknown_type $ioType
     */
    function io_title($filter = null, $ioType = 'csv' )
    {
        switch( $ioType ) {
            case 'csv':
            default:
                $this->oSchema['csv'][$filter] = array(
                    '*:订单号' => 'order_bn',
                    '*:订单确认状态' => 'process_status',
                    '*:来源店铺' => 'shop_name',
                    '*:发票类型' => 'type_id',
                    '*:开票金额' => 'amount',
                    '*:税金' => 'cost_tax',
                    '*:税率' => 'tax_rate',
                    '*:发票抬头' => 'title',
                    '*:开票状态' => 'is_status',
                    '*:发票号' => 'invoice_no',
                    '*:发票内容' => 'content',
                    '*:批次号' => 'batch_number',
                    '*:发票备注' => 'remarks',
                    '*:打印次数' => 'print_num',
                    '*:创建日期' => 'create_time',
                    '*:开票时间' => 'dateline',
                    '*:客户名称' => 'tax_company',
                    '*:收货地区' => 'ship_area',
                    '*:客户地址' => 'ship_addr',
                    '*:客户电话' => 'ship_tel',
                    '*:客户税号' => 'ship_tax',
                    '*:客户开户银行' => 'ship_bank',
                    '*:客户银行账号' => 'ship_bank_no',
                );
        }
        $this->ioTitle[$ioType][$filter]        = array_keys( $this->oSchema[$ioType][$filter] );
        return $this->ioTitle[$ioType][$filter];
    }
    /**
     * 导出数据(方法名固定，由系统调用)
     * @param Array $data 导出的数据
     * @param Array $filter 过滤器
     * @param Int $offset 当前记录位置
     * @param Int $exportType 导出类型
     */
    function fgetlist_csv( &$data,$filter,$offset,$exportType = 1 )
    {
        set_time_limit(0);
        @ini_set('memory_limit','128M');
        if ($offset > $max_offset) {
            return false;
        }
        
        if( !$data['title'] ){
            $title = array();
            foreach( $this->io_title('order') as $k => $v ){
                $title[]    = $v;
            }
            $data['title']  = '"' . implode('","', $title) . '"';
        }

        $limit     = 100;
        if( !$list = $this->getlist('*', $filter, $offset * $limit, $limit) )return false;
        
        //字段属性
        $order_Obj      = app::get('invoice')->model('order');
        $columns        = $order_Obj->schema;
        $type_id        = $columns['columns']['type_id']['type'];
        $is_status      = $columns['columns']['is_status']['type'];
        
        $Oorders    = app::get('ome')->model('orders');
        $col_list   = $Oorders->schema;
        $process_status = $col_list['columns']['process_status']['type'];
        $orderExtendObj = app::get('ome')->model('order_extend');
        //
        $db        = kernel::database();
        foreach($list as $key => $row)
        {
            #来源店铺           
            $sql_shop  = "SELECT b.name FROM ".DB_PREFIX."ome_orders as a 
                   LEFT JOIN ".DB_PREFIX."ome_shop as b ON a.shop_id=b.shop_id 
                   WHERE a.order_id='".$row['order_id']."'";
            $shop_name  = $db->select($sql_shop);
            $row['shop_name']   = $shop_name[0]['name'];
            $orderextend = $orderExtendObj->dump(array('order_id'=>$row['order_id']),'receivable');
            unset($row['id'], $row['order_id'], $row['is_print'], $row['delivery_id'], $row['operator'], $row['print_time']);
            
            $rowVal = array();
            $rowVal['*:订单号']        = "\t".$row['order_bn'];
            $rowVal['*:订单确认状态']   = $process_status[$row['process_status']];
            $rowVal['*:来源店铺']       = $row['shop_name'];
            $rowVal['*:发票类型']       = $type_id[$row['type_id']];
            $rowVal['*:开票金额']       =$orderextend['receivable']>0 ? $orderextend['receivable'] : $row['amount'];
            
            $rowVal['*:税金']     = $row['cost_tax'];
            $rowVal['*:税率']     = $row['tax_rate'];
            $rowVal['*:发票抬头'] = $row['title'];
            $rowVal['*:开票状态'] = $is_status[$row['is_status']];
            $rowVal['*:发票号']   = $row['invoice_no'];
            
            $rowVal['*:发票内容']   = $row['content'];
            $rowVal['*:批次号']     = $row['batch_number'];
            $rowVal['*:发票备注']   = $row['remarks'];            
            $rowVal['*:打印次数']   = $row['print_num'];
            $rowVal['*:创建日期']   = ($row['create_time'] ? date('Y-m-d H:i', $row['create_time']) : '');
            
            $rowVal['*:开票时间']   = ($row['dateline'] ? date('Y-m-d H:i', $row['dateline']) : '');
            $rowVal['*:客户名称']   = $row['tax_company'];
            $rowVal['*:收货地区']   = $row['ship_area'];
            $rowVal['*:客户地址']   = $row['ship_addr'];            
            $rowVal['*:客户电话']   = $row['ship_tel'];
            
            $rowVal['*:客户税号']       = $row['ship_tax'];
            $rowVal['*:客户开户银行']   = $row['ship_bank'];
            $rowVal['*:客户银行账号']   = $row['ship_bank_no'];
            
            $data['content'][] = '"' . implode( '","', $rowVal ) . '"';
        }
        
        return true;
    }
    /**
     * 输出导出数据
     * @param Array $data 数据
     * @param Int $exportType 输出类型
     */
    function export_csv($data, $exportType = 1 )
    {
        if(!$this->is_queue_export)
        {
            foreach ($data['content'] as $key => $value)
            {
                $data['content'][$key] = $value;
            }
        }

        $output     = array();
        $output[]   = $this->charset->utf2local($data['title']."\n".implode("\n",(array)$data['content']));

        if ($this->is_queue_export == true)
        {
            return implode("\n",$output);
        } else {
            echo implode("\n",$output);
        }
    }
    public function disabled_export_cols(&$cols){
        unset($cols['column_edit']);
    }
}