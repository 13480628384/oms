<?php
class ome_ctl_admin_setting extends desktop_controller{
    var $name = "基本设置";
    var $workground = "setting_tools";

    private $tabs = array(
        'order' => '订单配置',
        'purchase' => '仓储采购',
        'preprocess' => '预处理配置',
        'other' => '其他配置',
    );

    private function _comp_setting($arr1,$arr2){
        if($arr1["order"] == $arr2["order"])return 0;return $arr1["order"] > $arr2["order"] ? 1 : -1;
    }
    public function index(){
        $opObj    = app::get('ome')->model('operation_log');#配置修改日志
        
        //配置信息保存
        if($_POST['set']){
            $settins = $_POST['set'];
            $this->begin();
            if($settins['ome.product.serial.merge']=='true' && !empty($settins['ome.product.serial.separate'])){
                $settins['ome.product.serial.separate'] = trim($settins['ome.product.serial.separate']);
                if(strlen($settins['ome.product.serial.separate'])>1){
                    $this->end(false,'分隔符只允许是一个字符');
                }
                if(preg_match("/([a-zA-Z]{1}|[0-9]{1})/i", $settins['ome.product.serial.separate'])){
                    $this->end(false,'分隔符不允许是字母或数字');
                }
                $productObj = $this->app->model("products");
                $filter['barcode|has'] = $settins['ome.product.serial.separate'];
                $checkInfo = $productObj->dump($filter,'product_id,barcode');
                if($checkInfo['product_id']>0){
                    $this->end(false,'现有商品条形码中存在此分隔符');
                }
            }else{
                unset($settins['ome.product.serial.separate']);
            }

           if(!isset($settins['ome.combine.addressconf']['ship_address']) && !isset($settins['ome.combine.addressconf']['mobile'])) {
                $this->end(false,'相同地址判定中,收货地址和手机至少选择一个!');
            }
            
            //自动审单配置 ExBOY
            $old_is_auto_combine    = $this->app->getConf('ome.order.is_auto_combine');
            $now_is_auto_combine    = $settins['ome.order.is_auto_combine'];
            
            if($old_is_auto_combine != $now_is_auto_combine)
            {
                if($now_is_auto_combine == 'true')
                {
                    $log_msg   = '开启自动审单';
                }
                else
                {
                    $log_msg   = '关闭自动审单';
                }
                $opObj->write_log('order_split@ome', 0, $log_msg);
            }
            
            //复审配置
            if( !isset($settins['ome.order.retrial']['product'])){
                $settins['ome.order.retrial']['product'] = 0;
            }
            if( !isset($settins['ome.order.retrial']['order'])){
                $settins['ome.order.retrial']['order'] = 0;
            }
            if( !isset($settins['ome.order.retrial']['delivery'])){
                $settins['ome.order.retrial']['delivery'] = 0;
            }
            
            if( !isset($settins['ome.order.cost_multiple']['flag'])){
                $settins['ome.order.cost_multiple']['flag'] = 0;
            }
            if( !isset($settins['ome.order.sales_multiple']['flag'])){
                $settins['ome.order.sales_multiple']['flag'] = 0;
            }
            
            #订单拆单配置 ExBOY
            if($settins['ome.order.split'] == '1')
            {
                if($settins['ome.order.split_model'] == '1')
                {
                    if($settins['ome.order.split_class'] == '1')
                    {
                        $settins['ome.order.split_type']     = '1';
                    }
                    elseif($settins['ome.order.split_class'] == '2')
                    {
                        $settins['ome.order.split_type']     = '2';
                    }
                    $settins['ome.order.split_send']    = '';
                }
                elseif($settins['ome.order.split_model'] == '2')
                {
                    if($settins['ome.order.split_send'] == '1')
                    {
                        $settins['ome.order.split_type']     = '1';
                    }
                    elseif($settins['ome.order.split_send'] == '2')
                    {
                        $settins['ome.order.split_type']     = '2';
                    }
                    $settins['ome.order.split_class']    = '';
                }
                
                #设置是否有效
                if(empty($settins['ome.order.split_model']) || empty($settins['ome.order.split_type']))
                {
                    $this->end(false, '拆单配置Tab中未设置拆单方式或回写方式');
                }
                
                #配置日志
                $log_msg   = '开启拆单功能;';
                $log_msg   .= ($settins['ome.order.split_model'] == '1' ? '按子订单方式进行拆分订单' : '按sku方式拆分订单');
                $log_msg   .= '-';
                $log_msg   .= ($settins['ome.order.split_type'] == '1' ? '回写第一张' : '回写最后一张');
                
                $opObj->write_log('order_split@ome', 0, $log_msg);
            }
            else
            {
                #关闭拆单功能_配置日志
                if($this->app->getConf('ome.order.split') == '1')
                {
                    $log_msg   = '关闭拆单功能';
                    $opObj->write_log('order_split@ome', 0, $log_msg);
                }
                
                #注销拆单配置
                $settins['ome.order.split']          = '';
                $settins['ome.order.split_model']    = '';
                $settins['ome.order.split_type']     = '';
                $settins['ome.order.split_class']    = '';
                $settins['ome.order.split_send']     = '';
            }
            
            #关闭生成店铺冻结日志明细功能_清空数据表 ExBOY
            if($this->app->getConf('ome.product.shop_freeze') == '1' && $settins['ome.product.shop_freeze'] != '1')
            {
                $shopFreezeLogObj    = app::get('ome')->model('shop_freeze_log');
                $shopFreezeLogObj->db->exec("DELETE FROM sdb_ome_shop_freeze_log WHERE 1=1");
            }
            
            foreach($settins as $set=>$value){
                $curSet = $this->app->getConf($set);
                if($curSet!=$settins[$set]){
                    $curSet = $settins[$set];
                    $this->app->setConf($set,$settins[$set]);
                }
            }

            if(!isset($settins['ome.combine.addressconf']['ship_address'])){
                $settins['ome.combine.addressconf']['ship_address'] = 1;
            }

            if( !isset($settins['ome.combine.addressconf']['mobile'])){
                $settins['ome.combine.addressconf']['mobile'] = 1;
            }
            if($settins['ome.delivery.weight'] == 'on'){
               $this->app->setConf('ome.delivery.check_delivery','off');#称重开启后，关闭校验完即发货功能
             }

            //如果提交的内容值有变化才更新
            // foreach($settins as $set=>$value){
            //     $curSet = app::get('ome')->getConf($set);
            //     if($curSet!=$settins[$set]){
            //         $curSet = $settins[$set];
            //         app::get('ome')->setConf($set,$settins[$set]);
            //     }
            // }

            //库存成本保存
            // if($settins['ome.delivery.weight'] == 'off'){
            //     $this->app->setConf('ome.delivery.logi','0');
            // }
            if($_POST['extends_set']){
                foreach(kernel::servicelist('system_setting') as $k=>$obj){
                    if(method_exists($obj,'save')){
                       if($obj->save($_POST['extends_set'],$msg) === false) $this->end(false,$msg);
                    }
                }
            }

            //扩展配置信息保存
            foreach(kernel::servicelist('system_setting') as $k=>$obj){
                if(method_exists($obj,'saveConf')){
                    $obj->saveConf($settins);
                }
            }

            $this->end(true,'保存成功');
        }

        // 系统配置显示
        //$settingTabs = array(
        //    array('name' => '订单配置', 'file_name' => 'admin/system/setting/tab_order.html', 'app' => 'ome'),
        //    array('name' => '仓储采购', 'file_name' => 'admin/system/setting/tab_storage.html', 'app' => 'ome'),
        //    array('name' => '发货校验', 'file_name' => 'admin/system/setting/tab_delivery.html', 'app' => 'ome'),
        //    array('name' => '预处理配置', 'file_name' => 'admin/system/setting/tab_preprocess.html', 'app' => 'ome'),
        //    array('name' => '订单复审设置', 'file_name' => 'admin/system/setting/tab_retrial.html', 'app'=>'ome', 'order' => 30),
        //    array('name' => '其他配置', 'file_name' => 'admin/system/setting/tab_other.html', 'app'=>'ome'),
        //);
        $settingTabs = array();
        $setData = array();
        // $setView = array();

        // 读取所有可配置项
        $setting_info = array();

        //其他的配置暂时不动，直接赋值，后面细分到具体app
        // $show_tabs = $this->tabs;

        $servicelist = kernel::servicelist('system_setting');
        
        //配置信息的加载
        foreach($servicelist as $k=>$obj){

            //顶部tab页
            // if(isset($obj->tab_key) && isset($obj->tab_name)){
            //     $show_tabs = array_merge($show_tabs,array($obj->tab_key=>$obj->tab_name));
            // }

            //具体配置参数
            if(method_exists($obj,'all_settings')){
                $setting_info = array_merge($setting_info,$obj->all_settings());
            }

            if (method_exists($obj, 'get_setting_tab')) {
                $settingTabs = array_merge($settingTabs, $obj->get_setting_tab());
            }

            if (method_exists($obj,'get_pagedata')) {
                $obj->get_pagedata($this);
            }

            if (method_exists($obj,'get_setting_data')) {
                $setData = array_merge($setData,$obj->get_setting_data());
            }
        }

        uasort($settingTabs,array($this,'_comp_setting'));

        // 获取配置项值
        // foreach($setting_info as $set){
        //     $key = str_replace('.','_',$set);
        //     $setData[$key] = app::get('ome')->getConf($set);
        // }
        //因为老数据的问题，扩展的信息赋值放在全局赋值后面
        // foreach($servicelist as $k=>$obj){
        //     if(method_exists($obj, 'getView')){
        //         $setView[] = $obj->getView();
        //     }
        // }
        // if($_GET['pos']){
        //     $this->pagedata['display_pos'] = $_GET['pos'];
        // }
        #快递单与称重的顺序标示
        // if(!isset($setData['ome_delivery_logi'])){
        //     $setData['ome_delivery_logi'] = '0';
        // }
        
        // if($_GET['pos']){
        //     $this->pagedata['display_pos'] = $_GET['pos'];
        // }
        #快递单与称重的顺序标示
        // if(!isset($setData['ome_delivery_logi'])){
        //     $setData['ome_delivery_logi'] = '0';
        // }
        #逐单校验后即发货,默认是关闭的
        if(!isset($setData['ome_delivery_check_delivery'])){
            $setData['ome_delivery_check_delivery'] = 'off';
        }
        #称重开启，校验完即发货功能,默认是关闭的
        if($settins['ome.delivery.weight'] == 'on'){
            $setData['ome_delivery_check_delivery'] = 'off';
        }
        #华强宝默认是开启的
        if(!isset($setData['ome_delivery_hqepay'])){
            $setData['ome_delivery_hqepay'] = 'true';
        }

        $this->pagedata['settingTabs'] = $settingTabs;
        $this->pagedata['setData'] = $setData;
        $this->pagedata['branchCount'] = count(app::get('ome')->model('branch')->Get_branchlist());
        // $this->pagedata['setView']=$setView;
        $this->pagedata['show_tabs'] = $show_tabs;
        
        #[拆单]未处理的订单[部分拆分、部分发货] ExBOY
        if($setData['ome_order_split'] == '1')
        {
            $fields     = "order_id, order_bn, shop_id, shop_type, process_status, ship_status, total_amount, last_modified";
            $where      = " WHERE (process_status='splitting' || ship_status='2') AND `status`='active' ";
            $order_num  = kernel::database()->select("SELECT count(*) as num FROM ".DB_PREFIX."ome_orders ".$where);
            $order_list = kernel::database()->select("SELECT ".$fields." FROM ".DB_PREFIX."ome_orders ".$where." ORDER BY order_id DESC LIMIT 5");
            
            #关联发货单_数量
            if(!empty($order_list))
            {
                //确认状态、发货状态
                $ship_array     = array (0 => '未发货', 1 => '已发货', 2 => '部分发货', 3 => '部分退货', 4 => '已退货');
                $process_array  = array('unconfirmed' => '未确认','confirmed' => '已确认','splitting' => '部分拆分',
                                        'splited' => '已拆分完', 'cancel' => '取消', 'remain_cancel' =>'余单撤销');
                
                //店铺
                $shop_list  = array();
                $oShop      = app::get('ome')->model('shop');
                $data_shop  = $oShop->getList('shop_id, name', null, 0, -1);
                foreach ($data_shop as $key => $val)
                {
                    $sel_shop_id    = $val['shop_id'];
                    $shop_list[$sel_shop_id]    = $val['name'];
                }
                
                $data_dly   = array();
                foreach ($order_list as $key => $val)
                {
                    $sel_order_id   = $val['order_id'];
                    $sql    = "SELECT dord.delivery_id, d.status FROM ".DB_PREFIX."ome_delivery_order AS dord
                                LEFT JOIN ".DB_PREFIX."ome_delivery AS d ON (dord.delivery_id=d.delivery_id)
                                WHERE dord.order_id=".$sel_order_id." AND (d.parent_id=0 OR d.is_bind='true') AND d.disabled='false'
                                AND d.status NOT IN('failed','cancel','back','return_back')";
                    $data_dly   = kernel::database()->select($sql);
                    
                    $order_list[$key]['dly_count']  = count($data_dly);
                    $order_list[$key]['delivery']   = $data_dly;
                    $order_list[$key]['dly_succ']   = 0;
                    
                    foreach ($data_dly as $key_j => $val_j)
                    {
                        if($val_j['status'] == 'succ')
                        {
                            $order_list[$key]['dly_succ']++;//已发货数量
                        }
                    }
                    
                    $sel_shop_id    = $val['shop_id'];
                    $ship_status    = $val['ship_status'];
                    $process_status = $val['process_status'];
                    $order_list[$key]['shop_name']       = $shop_list[$sel_shop_id];
                    $order_list[$key]['ship_status']     = $ship_array[$ship_status];
                    $order_list[$key]['process_status']  = $process_array[$process_status];
                }
            }
            
            $this->pagedata['order_num']    = $order_num[0]['num'];
            $this->pagedata['order_list']   = $order_list;
        }
        
        $this->page("admin/system/setting_index_all.html");
    }

    function app_list(){
        $rows = kernel::database()->select('select app_id,app_name from sdb_base_apps where status = "active"');
        $app_list = array();
        foreach($rows as $v){
           $app_list[] = $v['app_id'];
        }
        return $app_list;
    }
     /*
     * 订单异常类型设置
     */
    function abnormal(){
        $this->finder('ome_mdl_abnormal_type',array(
            'title'=>'订单异常类型设置',
            'actions'=>array(
                            array(
                                'label'=>'添加',
                                'href'=>'index.php?app=ome&ctl=admin_setting&act=addabnormal',
                                 'target' => 'dialog::{width:450,height:150,title:\'新建异常类型\'}'
                            ),
                        ),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
         ));
    }
    /*
    * 添加订单异常类型
    */
    function addabnormal(){
        $oAbnormal = $this->app->model("abnormal_type");
        if($_POST){
            $this->begin('index.php?app=ome&ctl=admin_setting&act=abnormal');
            $oAbnormal->save($_POST['type']);
            $this->end(true, app::get('base')->_('保存成功'),3);
        }
        $this->pagedata['title'] = '添加订单异常类型';
        $this->page("admin/system/abnormal.html");
    }
    /*
    * 编辑订单异常类型
    */
    function editabnormal($type_id){
        $oAbnormal = $this->app->model("abnormal_type");
        $this->pagedata['abnormal']=$oAbnormal->dump($type_id);
        $this->pagedata['title'] = '编辑订单异常类型';
        $this->page("admin/system/abnormal.html");
    }
     /*
     * 售后问题类型设置
     */
    function product_problem(){//return_product_problem
        $this->finder('ome_mdl_return_product_problem',array(
            'title'=>'售后问题类型设置',
            'actions'=>array(
                            array(
                                'label'=>'添加',
                                'href'=>'index.php?app=ome&ctl=admin_setting&act=addproblem',
                                'target' => 'dialog::{width:450,height:150,title:\'新建售后问题类型\'}',
                            ),
                        ),
            'use_buildin_filter'=>true,
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
         ));
    }

    /*
     * 添加售后问题
     */
    function addproblem(){
        $oProblem = $this->app->model("return_product_problem");
        if($_POST){
            $this->begin('index.php?app=ome&ctl=admin_setting&act=product_problem');
            $oProblem->save($_POST);
            $this->end(true, app::get('base')->_('添加成功'),3);
        }
        $this->pagedata['disabled_type'] = array('true'=>'是','false'=>'否');
        $this->pagedata['problem']['disabled'] = 'false';
        $this->page("admin/system/product_problem.html");
    }
    /*
     * 编辑售后问题
     */
    function editproblem($problem_id){
        $oProblem = $this->app->model("return_product_problem");
        $problem = $oProblem->dump($problem_id);
        $this->pagedata['problem'] = $problem;
        $this->pagedata['disabled_type'] = array('true'=>'是','false'=>'否');
        $this->page("admin/system/product_problem.html");
    }

    /**
     * 收款账号管理
     */
    function set_collection_account()
    {
        $this->finder('ome_mdl_bank_account', array(
            'title' => '收款账号管理',
            'actions'=>array(
                array(
                    'label'=>'添加',
                    'href'=>'index.php?app=ome&ctl=admin_setting&act=add_bank_account',
                    'target' => 'dialog::{width:450,height:250,title:\'新建银行账户\'}',
                ),
            ),
            'use_buildin_set_tag' => false,
            'use_buildin_filter' => true,
            'use_buildin_new_dialog' => false,
            'use_buildin_tagedit' => true,
            'use_buildin_export' => false,
            'use_buildin_import' => false,
            'use_buildin_recycle'=> true,
        ));
    }

    /**
     * 收款账号新增
     */
    function add_bank_account()
    {
        $bank_account = '';
        if(isset($_GET['ba_id'])){
            $ba_id = $_GET['ba_id'];
            $bank_account_obj = kernel::single('ome_mdl_bank_account');
            $bank_account = $bank_account_obj->getList('*', array('ba_id'=>$ba_id), 0, 1);
        }
        $this->pagedata['item'] = $bank_account[0];
        $this->page('admin/system/bank_account.html');
    }

    public function do_add_bank_account()
    {
        $bank_acount = $this->app->model('bank_account');
        if($_POST){
            if($_POST['item']['ba_id'] != ''){
                // 修改
                $has_exists = $bank_acount->dump(array('ba_id' => $_POST['item']['ba_id']), '*');
                $this->begin('index.php?app=ome&ctl=admin_setting&act=set_collection_account');

                if($has_exists['account'] == $_POST['item']['account']){
                    $bank_acount->update($_POST['item'], array('ba_id' => $_POST['item']['ba_id']));
                    $this->end(true, app::get('base')->_('编辑成功'));
                } else {
                    $banks = $bank_acount->dump(array('account' => $_POST['item']['account']), '*');
                    if(!isset($banks['ba_id'])){
                        $bank_acount->update($_POST['item'], array('ba_id' => $_POST['item']['ba_id']));
                        $this->end(true, app::get('base')->_('编辑成功'));
                    } else {
                        $this->end(false, app::get('base')->_('账号重复'));
                    }
                }

            } else {
                // 添加
                $has_exists = $bank_acount->dump(array('account' => $_POST['item']['account']), '*');

                $this->begin('index.php?app=ome&ctl=admin_setting&act=set_collection_account');
                if($has_exists){
                    $this->end(false, app::get('base')->_('该账号已经存在，请勿重复添加'), 3);
                } else {
                    $bank_acount->save($_POST['item']);
                    $this->end(true, app::get('base')->_('添加成功'));
                }

            }
        }
    }

}
?>
