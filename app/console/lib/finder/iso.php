<?php
class console_finder_iso{

   function __construct($app)
    {
        if($_GET['io']){
            $this->column_iso_status = '入库状态';
            $this->detail_basic = '入库单详情';
            $this->detail_item = '入库单明细';
        }else{
            $this->column_iso_status = '出库状态';
            $this->detail_basic = '出库单详情';
           $this->detail_item = '出库单明细';
        }
		if($_GET['act']=='allocate_iostocklist'){
            
            unset($this->column_edit);
            
        }
        
    }
    var $detail_basic = "出库单详情";
    var $detail_item = "出库单明细";

    var $addon_cols ='iso_status,defective_status,iso_id,check_status';
    function column_iso_status($row){
        if($_GET['io']){
            $title = '入库';
        }else{
            $title = '出库';
        }
       
        $iso_status = $row[$this->col_prefix.'iso_status'];
        
        if($iso_status == '1'){
            $io_title = '未'.$title;
        }else if($iso_status == '2'){
            $io_title = '部分'.$title;
        }else if($iso_status == '3'){
            $io_title = '全部'.$title;
        }else if ($iso_status == '4'){
            $io_title = '取消'.$title;
        }
        
     	  return $io_title;
    }

    var $column_edit = '操作';
    var $column_edit_width = '100';
    function column_edit($row){
        $Oiso_items = app::get('taoguaniostockorder')->model('iso_items');
        $string = array();
        $iso_status = $row[$this->col_prefix.'iso_status'];
        $defective_status = $row[$this->col_prefix.'defective_status'];
        $iso_id = $row[$this->col_prefix.'iso_id'];
        $check_status = $row[$this->col_prefix.'check_status'];
        $io = $_GET['io'];
        $finder_id = $_GET['_finder']['finder_id'];
        $filter_data = array();
        $act = $_GET['act'];
        if($_GET['act'] == 'search_iostockorder'){
            $id = $row['iso_id'];
            $button = <<<EOF
        &nbsp;&nbsp;<a href="index.php?app=wms&ctl=admin_eo&act=printeo&p[0]=$id&t=$io" target="_bank" class="lnk">打印</a>
EOF;
            return $button;
        }
        if ($_isoST)
        foreach ($_isoST as $key=>$v){
            if (preg_match("/^_+/i",$key)) continue;
            $filter_data[$key] = $v;
        }
        $filter = urlencode(serialize($filter_data));
        
        $button = <<<EOF
            <a class="lnk" href="index.php?app=console&ctl=admin_iostockorder&act=doDefective&p[0]=$iso_id&filter=$filter&_finder[finder_id]=$finder_id&finder_id=$finder_id" target="_blank">残损确认</a> 
EOF;
        $button1 = <<<EOF
            <a class="lnk" href="index.php?app=console&ctl=admin_iostockorder&act=difference&p[0]=$iso_id&filter=$filter&_finder[finder_id]=$finder_id&finder_id=$finder_id" target="_blank">差异查看</a> 
EOF;
        $button2 = <<<EOF
            <a href="index.php?app=wms&ctl=admin_iostockorder&act=iostockorder_confirm&p[0]=$iso_id&p[1]=1&filter=$filter&find_id=$finder_id" target="_blank">入库确认</a>
            
EOF;
        $button3 = <<<EOF
            <a href="index.php?app=wms&ctl=admin_iostockorder&act=iostockorder_confirm&p[0]=$iso_id&p[1]=0&filter=$filter&find_id=$finder_id" target="_blank">出库确认</a>
           
EOF;
    $edit_button = <<<EOF
    <a href="index.php?app=console&ctl=admin_iostockorder&act=iostock_edit&p[0]=$iso_id&p[1]=$io&p[2]=$act&filter=$filter&finder_id=$finder_id" target="_blank">编辑</a>
EOF;
    $confirm_button = <<<EOF
    <a href="index.php?app=console&ctl=admin_iostockorder&act=check&p[0]=$iso_id&p[1]=$io&p[2]=$act&filter=$filter&finder_id=$finder_id" target="_blank">审核</a>
EOF;
    $cancel_button = <<<EOF
    <span class="lnk" onclick="new Dialog('index.php?app=console&ctl=admin_iostockorder&act=cancel&p[0]=$iso_id&p[1]=$io&p[2]=$act&filter=$filter&finder_id=$finder_id',{title:'取消出库',width:500,height:250})">取消</span>
EOF;
    //调拨入库 取消操作 生成入库单 打回原始仓库
    if($io == '1' && $act == 'allocate_iostock'){
        $cancel_button = <<<EOF
    <a target="_blank" href="index.php?app=console&ctl=admin_iostockorder&act=cancelIostockin&iso_id=$iso_id&finder_id=$finder_id">取消</a>
EOF;
    }

        if ($_GET['app'] == 'console'){
            if($iso_status == '3'){
                if($_GET['io'] && $defective_status=='1'){#残损确认
                    $string[]=$button;
                }
                #查看是否有差异
                $items = $Oiso_items->db->select("SELECT * from sdb_taoguaniostockorder_iso_items where iso_id=".intval($iso_id)." and (`normal_num`!=`nums` || defective_num>0)");
                if ($items){
                    $string[]=$button1;
                }
            }
            
            
            if ($check_status == '1' && $iso_status=='1'){
                //调拨入库隐藏 编辑  并且查看是否进行过调拨取消入库操作
                if($io == '1' && $act == 'allocate_iostock'){
                    //调拨入库页 获取是否已经进行过取消入库的操作
                    $taoguaniostockorder_iso_obj = app::get('taoguaniostockorder')->model('iso');
                    $iso_row = $taoguaniostockorder_iso_obj->dump(array("original_id"=>$iso_id,"type_id"=>11),"iso_bn");
                    $iso_bn_first_letter = substr($iso_row["iso_bn"],0,1);
                }else{
                    $string[]= $edit_button;
                }
                
                //在调拨入库处 已做调拨入库取消后不显示审核按钮
                if($iso_row["iso_bn"] && $iso_bn_first_letter == "X"){
                }else{
                    $string[]= $confirm_button;
                }
            }
            
            #取消
            if ($iso_status<='1'){
                $string[] = $cancel_button;
            }
        }
        
        
        if ($check_status == '2' && $_GET['app'] == 'wms' && ($iso_status < '3')){
            if($_GET['io']){
                $string[]=$button2;
            }else{
                $string[]=$button3;
            }
        }
        if($string)
        return '<span class="c-gray">'.implode('|',$string).'</span>';
    }

    function detail_basic($iso_id){
        $render = app::get('console')->render();
        $isoObj = app::get('taoguaniostockorder')->model('iso');
        //备注追加
        if($_POST){
            $iso_id = $_POST['iso_id'];
            $memo = htmlspecialchars($_POST['memo']);
            //取出原备注信息
            $oldmemo = $isoObj->dump(array('iso_id'=>$iso_id), 'memo');
            $oldmemo= $oldmemo['memo'];
            if ($oldmemo) $memo = $oldmemo.$memo.'；';
            $iso['memo'] = $memo;
            $iso['iso_id'] = $iso_id;
            $isoObj->save($iso);
        }
        
        $suObj = app::get('purchase')->model('supplier');
        $brObj = app::get('ome')->model('branch');
        $appropriation_obj = app::get('taoguanallocate')->model('appropriation');
        $appropriation_item_obj = app::get('taoguanallocate')->model('appropriation_items');
        $iso = $isoObj->dump($iso_id,'*',array('iso_items'=>array('*')));
        $oExtrabranch = app::get('ome')->model('extrabranch');
        if($iso['type_id'] == 4 || $iso['type_id'] == 40){
            $extrabranch_id = $iso['extrabranch_id'];
            
            //$extrabranch = $oExtrabranch->dump($extrabranch_id,'name');
        }else{
            $extrabranch_id = $iso['extrabranch_id'];
            
            $extrabranch = $oExtrabranch->dump($extrabranch_id,'name');
        }
        $appropriation_id = $appropriation_obj->getList('*', array('appropriation_no'=>$iso['appropriation_no']));
        $to_branch_id = $appropriation_item_obj->getList('*', array('appropriation_id' => $appropriation_id[0]['appropriation_id']));
        $to_branch = $brObj->dump($to_branch_id[0]['to_branch_id']);
        $render->pagedata['to_branch'] = $to_branch['name'];

        $total_num=0;
        if ($iso['iso_items'])
        foreach($iso['iso_items'] as $k=>$v){
            $total_num+=$v['nums'];
        }
        $su = $suObj->dump($iso['supplier_id'],'name');
        $br = $brObj->dump($iso['branch_id'], 'name');
        $iso['iso_id']   = $iso_id;
        $iso['branch_name']   = $br['name'];
        $iso['supplier_name'] = $su['name'];
        $iso['create_time'] = date("Y-m-d", $iso['create_time']);
        $iso['total_num']     = $total_num;
        $iso['memo'] = $iso['memo'];
        $iso['extrabranch_name'] = $extrabranch['name'];
        $render->pagedata['iso'] = $iso;
        $render->pagedata['amount'] = $iso['product_cost'] + $iso['iso_price'];

        if($_GET['io']){
            return $render->fetch("admin/iostock/instock_detail.html");
        }else{
            return $render->fetch("admin/iostock/outstock_detail.html");
        }
    }

    function detail_item($iso_id){
        $render = app::get('console')->render();
        $isoObj  = app::get('taoguaniostockorder')->model('iso');
        $goodsObj = app::get('ome')->model('goods');
        $productObj   = app::get('ome')->model('products');
        $iso = $isoObj->dump($iso_id,'iso_id',array('iso_items'=>array('*')));
        foreach($iso['iso_items'] as $k=>$order_item){
            $product = $productObj->dump($order_item['product_id'],'goods_id,spec_info,barcode');
            $order_item['spec_info'] = $product['spec_info'];
            $order_item['barcode'] = $product['barcode'];
            $goodsInfo = $goodsObj->getList('unit',array('goods_id'=>$product['goods_id']));
            $order_item['unit'] = isset($goodsInfo[0]['unit']) ? $goodsInfo[0]['unit'] : '';
            $iso['iso_items'][$k] = $order_item;
        }

        $render->pagedata['iso_items'] = $iso['iso_items'];
        if($_GET['io']){
            return $render->fetch("admin/iostock/instock_item.html");
        }else{
            return $render->fetch("admin/iostock/outstock_item.html");
        }
    }
}

?>