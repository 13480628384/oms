<?php
class ome_event_receive_delivery extends ome_event_response{

    /**
     * 发货单对象
     */
    private $__dlyObj = null;

    /**
     * 日志对象
     */
    private $__operationLogObj = null;

    /**
     * 当前处理的发货单源数据
     */
    private $__currDlyInfo = array();

    /**
     * 当前处理的发货单单号
     */
    private $__currDlyBn = array();

    /**
     * 当前处理的发货单ID
     */
    private $__currDlyId = array();

    /**
     * 当前发货单是否是合并发货单
     */
    private $__isBind = false;

    /**
     * 是否是第三方仓储回传，根据回传参数判断的
     */
    private $__isThirdParty = false;

    /**
     * 当前传入的参数
     */
    private $__inputParams = array();

    /**
     * 格式化后的参数
     */
    private $__formatParams = false;

    /**
     *
     * 发货通知单处理总入口
     * @param array $data
     */
    public function update($data){
        //参数检查
        if(!isset($data['status'])){
            return $this->send_error('必要参数缺失', $msg_code, $data);
        }
       
        $type = $data['status'];
        unset($data['status']);
        switch ($type){
            case 'delivery':
                return $this->consign($data);
                break;
            case 'print':
                return $this->setPrint($data);
                break;
            case 'check':
                return $this->setCheck($data);
                break;
            case 'cancel':
                return $this->rebackDly($data);
                break;
            case 'update':
                return $this->updateDetail($data);
                break;
            default:
                return $this->send_succ('未知的发货单操作通知行为', $msg_code, $data);
                break;
        }
    }

    /**
     *
     * 初始化核心所需的加载类
     * @param void
     */
    private function _instanceObj(){
        $this->__dlyObj = app::get('ome')->model('delivery');
        $this->__operationLogObj = app::get('ome')->model('operation_log');
    }

    /**
     *
     * 初始化发货单信息
     * @param array $params 传入参数
     * @param string $msg 错误信息
     */
    private function _initDlyInfo($params, &$msg){
        if(!isset($params['delivery_bn']) || empty($params['delivery_bn'])){
            $msg = '发货单通知单编号参数没有定义!';
            return false;
        }

        $deliveryInfo = $this->__dlyObj->dump(array('delivery_bn'=>$params['delivery_bn']),'*',array('delivery_items'=>array('*'),'delivery_order'=>array('*')));
        if(!isset($deliveryInfo['delivery_id'])){
            $msg = '发货单通知单编号不存在!';
            return false;
        }

        //接口传入参数
        $this->__inputParams = $params;

        //当前处理的发货单原始数据
        $this->__currDlyInfo = $deliveryInfo;

        //当前处理的发货单ID
        $this->__currDlyId = $deliveryInfo['delivery_id'];

        //当前处理的发货单单号
        $this->__currDlyBn = $deliveryInfo['delivery_bn'];

        //识别当前发货单是否是合并的
        $this->__isBind = ($deliveryInfo['is_bind'] == 'true') ? true : false;

        unset($deliveryInfo);
        return true;
    }

    /**
     *
     * 检查发货单相关状态
     * @param string $msg 错误信息
     */
    private function _checkStatus(&$msg){
         //判断是否发货单已取消如果已取消不更新
        if (in_array($this->__currDlyInfo['status'],array('cancel','back','return_back'))) {
            foreach ($this->__currDlyInfo['delivery_order'] as $dlyOrder){
                $this->__operationLogObj->write_log('order_modify@ome',$dlyOrder['order_id'],'第三方仓库回写:已发货状态,因发货单目前状态为已取消或打回,不更新');
            }

            $msg = '发货单状态为已取消不更新发货状态!';
            return false;
        }

        //如果传入有物流公司和运单号，检查运单号是否被其他发货单占用
        if(isset($this->__inputParams['logi_no'])){
            // 验证运单号是否存在
            if ($this->__dlyObj->dump(array('logi_no'=>$this->__inputParams['logi_no'],'delivery_id|noequal'=>$this->__currDlyInfo['delivery_id']))) {
                $msg = '运单号重复!';
                return false;
            }
        }

        return true;
    }

    /**
     *
     * 格式化相应参数
     * @param void
     */
    private function _convertSdf(){

        $order_fundObj = kernel::single('ome_func');
        $this->__formatParams['delivery_time']      = isset($this->__inputParams['delivery_time']) ? $order_fundObj->date2time($this->__inputParams['delivery_time']) : time();
        $this->__formatParams['weight']                = isset($this->__inputParams['weight']) ? floatval($this->__inputParams['weight']) : 0.00;
        $this->__formatParams['delivery_cost_actual'] = isset($this->__inputParams['delivery_cost_actual']) ? $this->__inputParams['delivery_cost_actual'] : 0.00;

        //第三方回写发货要更新物流相关信息
        if(isset($this->__inputParams['logi_id']) && isset($this->__inputParams['logi_no'])){
            $dlyCorpObj = app::get('ome')->model('dly_corp');
            $dlyInfo = $dlyCorpObj->dump(array('type'=>$this->__inputParams['logi_id']),'corp_id,name');

            //物流公司是否发生变化，不变化已原来的为准
            $erpdlyInfo = $dlyCorpObj->dump(array('corp_id'=>$this->__currDlyInfo['logi_id'],'type'=>$this->__inputParams['logi_id']),'corp_id,name');
            if (!$erpdlyInfo){
                $this->__formatParams['logi_id'] = empty($dlyInfo['corp_id']) ? '' : $dlyInfo['corp_id'];
                $this->__formatParams['logi_name'] = empty($dlyInfo['name']) ? '' : $dlyInfo['name'];
            }

            $this->__formatParams['logi_no'] = $this->__inputParams['logi_no'];
            $this->__isThirdParty = true;
        }

        return true;
    }

    /**
     *
     * 发货通知单发货事件处理
     * @param array $data
     */
    public function consign($data){
        
        //初始化类的对象
        $this->_instanceObj();

        //初始化当前处理发货单的数据
        if(!$this->_initDlyInfo($data, $msg)){
            return $this->send_error($msg, $msg_code, $data);
        }

        //该检查项单拉出来，如果是已发货的返回成功的响应标记，伊藤忠会出现成功发货还重复请求的问题
        if ($this->__currDlyInfo['status'] == 'succ' ) {
            return $this->send_succ('发货成功');
        }

        //检查当前发货单对应状态是否可以操作
        if(!$this->_checkStatus($msg)){
            return $this->send_error($msg, $msg_code, $this->__inputParams);
        }

        //组织参数
        $this->_convertSdf();

        /*发货处理核心流程 Begin*/
        //加入事务防并发
        kernel::database()->exec('begin');

        //具体发货处理
        if(!$this->_consign($msg)){
            kernel::database()->exec('rollback');
            return $this->send_error($msg, $msg_code, $this->__inputParams);
        }

        //生成出入库明细及销售单
        if(!$this->_consign_siso($msg)){
            kernel::database()->exec('rollback');
            return $this->send_error($msg, $msg_code, $this->__inputParams);
        }

        // 事务提交
        kernel::database()->exec('commit');
        /*发货处理核心流程 End*/
        
        //发货后续处理
        $this->_after_consign();
        
        return $this->send_succ('发货成功');
    }

    /**
     * 发货详细处理流程
     *
     * @param string $msg 错误信息
     * @return void
     **/
    private function _consign(&$msg){
        //加载所用到的类
        #[拆单]配置
        $orderSplitLib    = kernel::single('ome_order_split');
        $split_seting     = $orderSplitLib->get_delivery_seting();
        $split_type     = intval($split_seting['split_type']);
        
        $orderObj = app::get('ome')->model('orders');
        $productObj = app::get('ome')->model('products');
        $branch_productObj = app::get('ome')->model('branch_product');
        #[拆单]发货单状态回写记录表 ExBOY
        $delivery_sync     = app::get('ome')->model('delivery_sync');

        //是否合并发货单
        if($this->__isBind){
            //主发货单更新信息，如果是第三方仓储的并且物流公司有变化，则赋值更新
            if($this->__isThirdParty == true){
                if ($this->__formatParams['logi_id']) {
                    $maindly['logi_id'] = $this->__formatParams['logi_id'];
                }
                if ($this->__formatParams['logi_name']) {
                    $maindly['logi_name'] = $this->__formatParams['logi_name'];
                }
                
                $maindly['logi_no'] = $this->__formatParams['logi_no'];
            }

            //子发货单相应处理
            $ids = $this->__dlyObj->getItemsByParentId($this->__currDlyId,'array');
            foreach ($ids as $item){
                $delivery = $this->__dlyObj->dump($item,'delivery_id,type,is_cod',array('delivery_items'=>array('*'),'delivery_order'=>array('*')));

                $de = $delivery['delivery_order'];
                $or = array_shift($de);
                $ord_id = $or['order_id'];
                if ($delivery['type'] == 'normal'){//如果不为售后生成的发货单，才进行订单发货数量的更新
                    $this->__dlyObj->consignOrderItem($delivery);
                }

                $childly['delivery_id'] = $delivery['delivery_id'];
                $childly['process']     = 'true';
                $childly['status'] = 'succ';
                $childly['delivery_time'] = $this->__formatParams['delivery_time'];
                $this->__dlyObj->save($childly);//更新子发货单发货状态为已发货

                #[拆单]判断订单是否全部发完货时,并过滤合并发货单中的父发货单  ExBOY
                $get_dly_process    = $orderSplitLib->get_delivery_process($delivery['delivery_id'], 'false', $this->__currDlyId);
                if(empty($get_dly_process['delivery']) && empty($get_dly_process['order_items'])){
                    $orderdata['archive'] = 1;//订单归档
                }else{
                    $orderdata['archive'] = 0;//有未发货的发货单或未拆单完成
                }

                $item_num = $this->__dlyObj->countOrderSendNumber($ord_id);
                if ($item_num == 0){//已发货
                    if ($delivery['is_cod'] == 'false') {
                        $orderdata['status'] ='finish';
                    }
                    $orderdata['ship_status'] = '1';
                    $affect_order = $orderObj->update($orderdata, array('order_id'=>$ord_id));//更新订单发货状态
                }else {//部分发货
                    $orderdata['ship_status'] = '2';
                    $affect_order = $orderObj->update($orderdata, array('order_id'=>$ord_id));//更新订单发货状态
                }

                if (!is_numeric($affect_order) || $affect_order <= 0) {
                    $msg = '订单状态更新失败!';
                    return false;
                }
                
                #[拆单]新增_发货单状态回写记录  ExBOY
                $dly_data       = array();
                $frst_info      = $orderObj->dump(array('order_id'=>$ord_id), 'shop_id, shop_type, order_bn');
                
                if(!empty($split_seting))
                {
                    $dly_data['order_id']       = $ord_id;
                    $dly_data['order_bn']       = $frst_info['order_bn'];
                    $dly_data['delivery_id']    = $this->__currDlyId;
                    $dly_data['delivery_bn']    = $this->__currDlyBn;
                    $dly_data['logi_no']        = isset($maindly['logi_no']) ? $maindly['logi_no'] : $this->__currDlyInfo['logi_no'];
                    $dly_data['logi_id']        = isset($maindly['logi_id']) ? $maindly['logi_id'] : $this->__currDlyInfo['logi_id'];
                    $dly_data['branch_id']      = $this->__currDlyInfo['branch_id'];
                    $dly_data['status']         = $childly['status'];//发货状态
                    $dly_data['shop_id']        = $this->__currDlyInfo['shop_id'];
                    $dly_data['delivery_time']  = $this->__formatParams['delivery_time'];
                    $dly_data['dateline']       = $this->__formatParams['delivery_time'];
                    $dly_data['split_model']    = intval($split_seting['split_model']);//拆单方式
                    $dly_data['split_type']     = intval($split_seting['split_type']);//回写方式
                    
                    $delivery_sync->save($dly_data);
                }
                
                unset($delivery,$childly,$orderdata);
            }

            $GLOBALS['frst_delivery_bn'] = $this->__currDlyBn;
            if ($this->__currDlyInfo['type'] == 'normal'){//如果不为售后生成的发货单，才进行货品发货的冻结释放 fix by danny 2012-4-26
                //扣减库存
                $stock = array();
                foreach ($this->__currDlyInfo['delivery_items'] as $dly_item){ //循环大发货单的items数据
                    $product_id = $dly_item['product_id'];
                    $branch_id = $this->__currDlyInfo['branch_id'];
                    $num = $dly_item['number'];//需要扣减的数量

                    //仓储冻结释放
                    $rs = $branch_productObj->unfreez($branch_id,$product_id,$num);
                    if ($rs == false) {
                        $msg = '仓储冻结释放更新失败!';
                        return false;
                    }

                    //货品冻结释放
                    $rs = $productObj->chg_product_store_freeze($product_id,$num,"-");
                    if ($rs == false) {
                        $msg = '货品冻结释放更新失败!';
                        return false;
                    }

                    //记录仓库货品出库情况，用于安全库存计算，暂时屏蔽，安全库存计算需重新整理 xiayuanjun 2016-3-24
                    //$this->__dlyObj->createStockChangeLog($branch_id,$num,$product_id);
                }
            }

            // 更新主发货单 
            $maindly['delivery_id']          = $this->__currDlyId;
            $maindly['process']              = 'true';
            $maindly['status']               = 'succ';
            $maindly['weight']               = $this->__formatParams['weight'];
            $maindly['delivery_time']        = $this->__formatParams['delivery_time'];
            $maindly['delivery_cost_actual'] = $this->__formatParams['delivery_cost_actual'];

            //打印状态
            $maindly['expre_status'] = 'true';
            $maindly['deliv_status'] = 'true';
            $maindly['stock_status'] = 'true';

            $affect_row = $this->__dlyObj->update($maindly,array('delivery_id' => $maindly['delivery_id'],'process' => 'false'));
            if (!is_numeric($affect_row) || $affect_row <= 0) {
                $msg = '发货单发货状态更新失败!';
                return false;
            }

            #[拆单]日志增加_发货单号 ExBOY
            $this->__operationLogObj->write_log('delivery_process@ome', $this->__currDlyId, '发货单发货完成,（发货单号：'.$this->__currDlyBn.'）','',$opinfo);
        }else{
            $de = $this->__currDlyInfo['delivery_order'];
            $or = array_shift($de);
            $ord_id = $or['order_id'];
            if ($this->__currDlyInfo['type'] == 'normal'){//如果不为售后生成的发货单，才进行订单发货数量的更新
                $this->__dlyObj->consignOrderItem($this->__currDlyInfo);
            }

            //如果是第三方仓储的并且物流公司有变化，则赋值更新
            if($this->__isThirdParty == true){
                if ($this->__formatParams['logi_id']) {
                    $singledly['logi_id'] = $this->__formatParams['logi_id'];
                }
                if ($this->__formatParams['logi_name']) {
                    $singledly['logi_name'] = $this->__formatParams['logi_name'];
                }
                
                $singledly['logi_no'] = $this->__formatParams['logi_no'];
            }

            // 更新主发货单
            $singledly['delivery_id']          = $this->__currDlyId;
            $singledly['process']              = 'true';
            $singledly['status']               = 'succ';
            $singledly['weight']               = $this->__formatParams['weight'];
            $singledly['delivery_time']        = $this->__formatParams['delivery_time'];
            $singledly['delivery_cost_actual'] = $this->__formatParams['delivery_cost_actual'];

            //打印状态
            $singledly['expre_status'] = 'true';
            $singledly['deliv_status'] = 'true';
            $singledly['stock_status'] = 'true';

            $affect_row = $this->__dlyObj->update($singledly,array('delivery_id' => $singledly['delivery_id'],'process' => 'false'));
            if (!is_numeric($affect_row) || $affect_row <= 0) {
                $msg = '发货单发货状态更新失败!';
                return false;
            }

            #[拆单]判断订单是否全部发完货 ExBOY
            $get_dly_process    = $orderSplitLib->get_delivery_process($this->__currDlyId, 'false');
            if(empty($get_dly_process['delivery']) && empty($get_dly_process['order_items']))
            {
                $orderdata['archive'] = 1;//订单归档
            }
            else
            {
                $orderdata['archive'] = 0;//有未发货的发货单或未拆单完成
            }

            $item_num = $this->__dlyObj->countOrderSendNumber($ord_id);
            if ($item_num == 0){//已发货
                $orderdata['ship_status'] = '1';
                if ($this->__currDlyInfo['is_cod'] == 'false') {
                    $orderdata['status'] ='finish';
                }
                
                $affect_order = $orderObj->update($orderdata, array('order_id'=>$ord_id));//更新订单发货状态
            }else {//部分发货
                $orderdata['ship_status'] = '2';
                $affect_order = $orderObj->update($orderdata, array('order_id'=>$ord_id));//更新订单发货状态
            }

            if (!is_numeric($affect_order) || $affect_order <= 0) {
                $msg = '订单状态更新失败!';
                return false;
            }

            //danny_freeze_stock_log
            $frst_info = $orderObj->dump(array('order_id'=>$ord_id),'shop_id,shop_type,order_bn');
            $GLOBALS['frst_shop_id'] = $frst_info['shop_id'];
            $GLOBALS['frst_shop_type'] = $frst_info['shop_type'];
            $GLOBALS['frst_order_bn'] = $frst_info['order_bn'];
            $GLOBALS['frst_delivery_bn'] = $this->__currDlyBn;
            if ($this->__currDlyInfo['type'] == 'normal'){//如果不为售后生成的发货单，才进行货品发货的冻结释放 fix by danny 2012-4-26
                //扣减库存
                $stock = array();
                foreach ($this->__currDlyInfo['delivery_items'] as $dly_item){ //循环发货单的items数据
                    $product_id = $dly_item['product_id'];
                    $branch_id = $this->__currDlyInfo['branch_id'];
                    $num = $dly_item['number'];//需要扣减的数量

                    //仓储冻结释放
                    $rs = $branch_productObj->unfreez($branch_id,$product_id,$num);
                    if ($rs == false) {
                        $msg = '仓储冻结释放更新失败!';
                        return false;
                    }

                    //货品冻结释放
                    $rs = $productObj->chg_product_store_freeze($product_id,$num,"-");
                    if ($rs == false) {
                        $msg = '货品冻结释放更新失败!';
                        return false;
                    }

                    //记录仓库货品出库情况，用于安全库存计算，暂时屏蔽，安全库存计算需重新整理 xiayuanjun 2016-3-24
                    //$this->__dlyObj->createStockChangeLog($branch_id,$num,$product_id);
                }
            }

            #[拆单]新增_发货单状态回写记录  ExBOY
            if(!empty($split_seting))
            {
                $dly_data       = array();
                $dly_data['order_id']       = $ord_id;
                $dly_data['order_bn']       = $frst_info['order_bn'];
                $dly_data['delivery_id']    = $this->__currDlyId;
                $dly_data['delivery_bn']    = $this->__currDlyBn;
                $dly_data['logi_no']        = isset($singledly['logi_no']) ? $singledly['logi_no'] : $this->__currDlyInfo['logi_no'];
                $dly_data['logi_id']        = isset($singledly['logi_id']) ? $singledly['logi_id'] : $this->__currDlyInfo['logi_id'];
                $dly_data['branch_id']      = $this->__currDlyInfo['branch_id'];
                $dly_data['status']         = $singledly['status'];//发货状态
                $dly_data['shop_id']        = $this->__currDlyInfo['shop_id'];
                $dly_data['delivery_time']  = $this->__formatParams['delivery_time'];
                $dly_data['dateline']       = $this->__formatParams['delivery_time'];
                $dly_data['split_model']    = intval($split_seting['split_model']);//拆单方式
                $dly_data['split_type']     = intval($split_seting['split_type']);//回写方式
                
                $delivery_sync->save($dly_data);
            }
            
            #[拆单]日志增加_发货单号 ExBOY
            $this->__operationLogObj->write_log('delivery_process@ome', $this->__currDlyId, '发货单发货完成,（发货单号：'.$this->__currDlyBn.'）','',$opinfo);
        }

        return true;
    }

    /**
     * 发货完成后生成出入库明细及销售单
     *
     * @param string $msg 错误信息
     * @return void
     **/
    private function _consign_siso(&$msg){

        $soldIoLib = kernel::single('siso_receipt_iostock_sold');
        $soldSalesLib = kernel::single('siso_receipt_sales_sold');

        $save_iostock = $soldIoLib->create(array('delivery_id'=>$this->__currDlyId), $iostock_data, $msg);
        if($save_iostock){
            $save_sales = $soldSalesLib->create(array('delivery_id'=>$this->__currDlyId,'iostock'=>$iostock_data), $msg);
            if(!$save_sales){
                $msg = '销售单生成失败!';
                return false;
            }
        }else{
            $msg = '出入库明细生成失败!';
            return false;
        }

        return true;
    }

    /**
     * 发货以后的后续逻辑处理
     *
     * @return void
     **/
    private function _after_consign(){

        //电子面单回传
        $this->_after_consign_logisticsmanager();

        //京东仓储扩展信息的保存
        $this->_after_consign_extend();

        //状态回写
        $this->_after_consign_shop();

        //短信
        $this->_after_consign_sms();

        //绩效
        $this->_after_consign_tgkpi();

        //华强宝
        $this->_after_consign_hqepay();

        //淘宝全链路
        $this->_after_consign_tmc();
    }

    /**
     * 电子面单回传
     *
     * @return void
     * @author 
     **/
    private function _after_consign_logisticsmanager()
    {
        //对EMS直联电子面单作处理（以及京东360buy）(京东先回写运单号）
        if (app::get('logisticsmanager')->is_installed()) {
            $channel_type = $this->__dlyObj->getChannelType($this->__currDlyInfo['logi_id']);
            if ($channel_type  && (in_array($channel_type,array('360buy','taobao','ems'))) && class_exists('logisticsmanager_service_' . $channel_type)) {
                $channelTypeObj = kernel::single('logisticsmanager_service_' . $channel_type);
                $channelTypeObj->delivery($this->__currDlyId);
            }
        }
        return true;
    }

    /**
     * 京东仓储扩展信息的保存
     *
     * @return void
     * @author 
     **/
    private function _after_consign_extend()
    {
        //确认有没外部发货单号如果有更新，目前只针对坑爹的京东仓储
        if ($this->__inputParams['out_delivery_bn']) {
            $oDelivery_ext = app::get('console')->model('delivery_extension');
            $delivery_ext = $oDelivery_ext->dump(array('delivery_bn'=>$this->__currDlyBn),'original_delivery_bn');
            if (!$delivery_ext) {
                $ext_data = array(
                    'delivery_bn'=>$this->__currDlyBn,
                    'original_delivery_bn'=>$this->__inputParams['out_delivery_bn'],
                );
                $oDelivery_ext->save($ext_data);
            }
        }
        return true;
    }

    /**
     * 状态回写
     *
     * @return void
     * @author 
     **/
    private function _after_consign_shop()
    {
        //调用发货相关api，比如订单的发货状态，库存的回写，发货单的回写
        $this->__dlyObj->call_delivery_api($this->__currDlyId, false);
        return true;
    }

    /**
     * 短信
     *
     * @return void
     * @author 
     **/
    private function _after_consign_sms()
    {
        if(defined('APP_TOKEN')&&defined('APP_SOURCE')){
            kernel::single('taoexlib_delivery_sms')->deliverySendMessage($this->__currDlyId);
        }
        return true;
    }

    /**
     * 绩效
     *
     * @return void
     * @author 
     **/
    private function _after_consign_tgkpi()
    {
        if (!app::get('tgkpi')->is_installed()) return;

        $sql = 'select delivery_id from sdb_tgkpi_pick WHERE delivery_id ='.$this->__currDlyId;
        $row = $this->__dlyObj->db->selectrow($sql);
        if($row){
            $opInfo = kernel::single('ome_func')->getDesktopUser();
            $sql = sprintf('UPDATE `sdb_tgkpi_pick` SET `pick_status`="deliveryed",`op_name`="%s" WHERE delivery_id="%s"',$opInfo['op_name'],$this->__currDlyId);
            kernel::database()->exec($sql);
        }
        return true;
    }

    /**
     * 华强宝
     *
     * @return void
     * @author 
     **/
    private function _after_consign_hqepay()
    {
        #检测是否开启华强宝物流
        $is_hqepay_on =  app::get('ome')->getConf('ome.delivery.hqepay');
        if($is_hqepay_on == 'false'){
            return true;
        }
        #订阅物流信息
        kernel::single('ome_service_delivery')->get_hqepay_logistics($this->__currDlyId);
        return true;
    }

    /**
     * 淘宝全链路
     *
     * @return void
     * @author 
     **/
    private function _after_consign_tmc(){
        $this->__dlyObj->sendMessageProduce($this->__currDlyId, array(10, 11, 12));#淘宝全链路 已打包，已称重，已出库
        return true;
    }

    /**
     *
     * 更新接收发货单已打印
     * @param array $data
     */
    public function setPrint($data){
        if(!isset($data['delivery_bn']) || empty($data['delivery_bn'])){
            return $this->send_error('发货单通知单编号参数没有定义', $msg_code, $data);
        }else{
            $delivery_bn = $data['delivery_bn'];
        }

        $dlyObj  = app::get('ome')->model('delivery');
        $deliveryInfo = $dlyObj->dump(array('delivery_bn'=>$data['delivery_bn']),'*');

        //检查该物流单号是否存在
        if(!isset($deliveryInfo['delivery_id'])){
            return $this->send_error('发货单通知单编号不存在', $msg_code, $data);
        }

        // 发货单状态判断
        if (in_array($deliveryInfo['status'], array('succ','back','stop','cancel','failed','timeout','return_back'))) {
            return $this->send_error('发货单状态异常:'.$deliveryInfo['status'],$msg_code,$data);
        }
        
        //更新发货单打印状态
        if(!isset($data['stock_status'])) $data['stock_status'] = 'true';
        if(!isset($data['deliv_status'])) $data['deliv_status'] = 'true';
        if(!isset($data['expre_status'])) $data['expre_status'] = 'true';

        $dlyObj->update(array('print_status'=>1,'stock_status'=>$data['stock_status'],'deliv_status'=>$data['deliv_status'],'expre_status'=>$data['expre_status'],'status'=>'progress'),array('delivery_id'=>$deliveryInfo['delivery_id']));

        //更新订单打印状态
        $dlyObj->updateOrderPrintFinish($deliveryInfo['delivery_id']);

        //请求前端发货单进行更新
        foreach (kernel::servicelist('service.delivery') as $object => $instance) {
            if (method_exists($instance, 'update_status')) {
                $instance->update_status($deliveryInfo['delivery_id'], 'progress');
            }
        }

        return $this->send_succ();
    }

    /**
     *
     * 更新接收发货单已校验
     * @param array $data
     */
    public function setCheck($data){
        if(!isset($data['delivery_bn']) || empty($data['delivery_bn'])){
            return $this->send_error('发货单通知单编号参数没有定义', $msg_code, $data);
        }else{
            $delivery_bn = $data['delivery_bn'];
        }

        $dlyObj  = app::get('ome')->model('delivery');
        $dlyItemObj = app::get('ome')->model('delivery_items');
        $deliveryInfo = $dlyObj->dump(array('delivery_bn'=>$data['delivery_bn']),'*');

        //检查该物流单号是否存在
        if(!isset($deliveryInfo['delivery_id'])){
            return $this->send_error('发货单通知单编号不存在', $msg_code, $data);
        }

        //判断是否已经校验过、单据是否取消或暂停

        if ($dlyItemObj->verifyItemsByDeliveryId($deliveryInfo['delivery_id'])){
            $delivery['delivery_id'] = $deliveryInfo['delivery_id'];
            $delivery['verify'] = 'true';

            if (!$dlyObj->save($delivery)){
                return false;
            }

            if($deliveryInfo['is_bind'] == 'true'){
                $ids = $dlyObj->getItemsByParentId($delivery['delivery_id'], 'array');
                foreach ($ids as $i){
                    $dlyItemObj->verifyItemsByDeliveryId($i);
                }
            }
            #淘宝全链路 已捡货，已验货
            $dlyObj->sendMessageProduce($delivery['delivery_id'], array(8, 9));
        }
        return $this->send_succ();
    }

    /**
     *
     * 更新接收发货单信息变更
     * @param array $data
     */
    public function updateDetail($data){
        if(!isset($data['delivery_bn']) || empty($data['delivery_bn'])){
            return $this->send_error('发货单通知单编号参数没有定义', $msg_code, $data);
        }else{
            $delivery_bn = $data['delivery_bn'];
        }

        $dlyObj  = app::get('ome')->model('delivery');
        $dlyItemObj = app::get('ome')->model('delivery_items');
        $deliveryInfo = $dlyObj->dump(array('delivery_bn'=>$data['delivery_bn']),'*');

        //检查该物流单号是否存在
        if(!isset($deliveryInfo['delivery_id'])){
            return $this->send_error('发货单通知单编号不存在', $msg_code, $data);
        }

        //判断单据是否取消或暂停

        //保存发货单变更信息
        $dlyObj->updateDelivery($data, array('delivery_id' => $deliveryInfo['delivery_id']));

        //根据动作类型记录相关日志
        if($data['action']){
            $opObj = app::get('ome')->model('operation_log');
            switch ($data['action']){
                case 'updateDetail':
                    $opObj->write_log('delivery_modify@ome', $deliveryInfo['delivery_id'], '修改发货单详情');
                    break;
                case 'addLogiNo':
                    $opObj->write_log('delivery_logi_no@ome', $deliveryInfo['delivery_id'], '录入快递单号:'.$data['logi_no']);
                    break;
            }
        }

        return $this->send_succ();
    }

    /**
     *
     * 撤销发货单
     * @param array $data
     */
    public function rebackDly($data){
        if(!isset($data['delivery_bn']) || empty($data['delivery_bn'])){
            return $this->send_error('发货单通知单编号参数没有定义', $msg_code, $data);
        }else{
            $delivery_bn = $data['delivery_bn'];
        }

        $dlyObj  = app::get('ome')->model('delivery');
        $opObj = app::get('ome')->model('operation_log');
        $dlyOrdObj = app::get('ome')->model('delivery_order');
        $delivery_itemsObj = app::get('ome')->model('delivery_items');
        $branch_productObj = app::get('ome')->model('branch_product');
        $orderObj = app::get('ome')->model('orders');
        $combineObj = new omeauto_auto_combine();
        $dispatchObj = app::get('omeauto')->model('autodispatch');

        $dlyInfo = $dlyObj->dump(array('delivery_bn'=>$delivery_bn),'*');
        if(!$dlyInfo){
            return $this->send_error('找不到该发货单单号', $msg_code, $data);
        }

        //检查发货单状态
        if($dlyInfo['status'] == 'back'){
            return $this->send_error('该发货单已经被打回，无法继续操作', $msg_code, $data);
        }
        if($dlyInfo['delivery_logi_number'] > 0){
            return $this->send_error('该发货单已部分发货，无法继续操作', $msg_code, $data);
        }
        if($dlyInfo['pause'] == 'true'){
            return $this->send_error('该发货单已暂停，无法继续操作', $msg_code, $data);
        }
        if($dlyInfo['process'] == 'true'){
            return $this->send_error('该发货单已经发货，无法继续操作', $msg_code, $data);
        }

        //检查订单状态


        $tmp['memo']        = $data['memo'];
        $tmp['status']      = 'back';
        $tmp['logi_no'] = null;
        $tmp['delivery_id'] = $dlyInfo['delivery_id'];
        $dlyObj->save($tmp);
        
        $shopFreezeLib    = kernel::single('ome_shop_freeze');
        $shopFreezeLogLib    = kernel::single('ome_shop_freeze_log');
        $shop_id    = $dlyInfo['shop_id'];
        
        #获取店铺冻结系统配置
        $is_shop_freeze_log    = $shopFreezeLogLib->get_product_shop_freeze_config();
        
        //增加branch_product释放冻结库存
        $branch_id = $dlyObj->getList('branch_id',array('delivery_id'=>$dlyInfo['delivery_id']),0,-1);
        $product_ids = $delivery_itemsObj->getList('product_id,number',array('delivery_id'=>$dlyInfo['delivery_id']),0,-1);
        foreach($product_ids as $key=>$v){
            $branch_productObj->unfreez($branch_id['0']['branch_id'],$v['product_id'],$v['number']);
            
            #[增加]货品店铺冻结 ExBOY
            $shopFreezeLib->freeze($shop_id, $v['product_id'], $v['number']);
            
            #店铺冻结增减明细日志
            if($is_shop_freeze_log)
            {
                $shopFreezeLogLib->changeLog($shop_id, $v['product_id'], $v['number'], 22, '撤消发货单号:'. $delivery_bn .',增加货品店铺冻结');
        }
            
        }
        $serialFilter['delivery_id'][] = $dlyInfo['delivery_id'];
        $this->rebackSerial($serialFilter);
        $delivery_bn = $dlyObj->dump(array('delivery_id'=>$dlyInfo['delivery_id']),'delivery_bn,logi_no');
        $logi_info = $delivery_bn['logi_no'] ? ',物流单号'.$delivery_bn['logi_no'] : '';
        
        $opObj->write_log('delivery_back@ome', $dlyInfo['delivery_id'], '发货单打回'.$logi_info);
        //释放主单库存
        if($dlyInfo['is_bind'] == 'true'){
            $ids = $dlyObj->getItemsByParentId($dlyInfo['delivery_id'],'array');
            foreach ($ids as $i){
               $tmpdly = array(
                    'delivery_id' => $i,
                    'status' => 'cancel',
                    'logi_id' => NULL,
                    'logi_name' => '',
                    'logi_no' => NULL,
                );
                $dlyObj->save($tmpdly);
                $opObj->write_log('delivery_back@ome', $i,'发货单打回');
                $dlyObj->updateOrderPrintFinish($i, 1);
               
            }

        }
        //单个发货单的对应订单号
        $order_ids = $dlyObj->getOrderIdByDeliveryId($dlyInfo['delivery_id']);
        foreach ($order_ids as $order_id ) {
            $orderInfo = $orderObj->dump($order_id, 'order_id,order_bn,order_combine_idx,order_combine_hash,pay_status,ship_status');
            $pay_status = $orderInfo['pay_status'];
            $memo = '';
            if ($pay_status == '5' && $orderInfo['ship_status'] == '0'){
                $memo.= '全额退款订单取消';
                app::get('ome')->model('refund_apply')->check_iscancel($order_id);
            }else{
                $params[] = array(
                    'idx' => $orderInfo['idx'],
                    'hash' => $orderInfo['hash'],
                    'orders' => array (
                        0 => $orderInfo['order_id'],
                    ),
                );
                //开始处理订单分派
                $result = $combineObj->dispatch($params);
                if($result && $result['did'] && $result['did']>0){
                    $opData = $dispatchObj->dump($result['did'],'group_id,op_id');
                }else{
                    $dispatchData = $dispatchObj->getList('group_id,op_id',array('defaulted'=>'true'));
                    if($dispatchData && is_array($dispatchData[0])){
                        $opData = $dispatchData[0];
                    }else{
                        $opData = array('group_id'=>'0','op_id'=>'0');
                    }
                }
                //修改订单确认状态
                $opData['confirm'] = 'N';
                $opData['process_status'] = 'unconfirmed';
                $opData['pause'] = 'true';

                #[拆单]判断有部分拆分的有效发货单存在(确认状态为splitting) ExBOY
                if ($dlyObj->validDeiveryByOrderId($order_id))
                {
                    $opData['process_status']   = 'splitting';
                    $opData['pause']            = 'false';//因部分拆分后订单"基本信息"Tab没有操作按扭
                    unset($opData['group_id'], $opData['op_id']);//部分拆分不重新分派
                }
                $memo.=",订单重新分派";
                $orderObj->update($opData,array('order_id'=>$order_id));
            }
            $opObj->write_log('order_back@ome', $order_id, '发货单'.$dlyInfo['delivery_bn'].$logi_info.'打回+'.'备注:'.$tmp['memo'].$memo);
        }
            
        $dlyObj->updateOrderPrintFinish($dlyInfo['delivery_id'], 1);
            
        
        return $this->send_succ();
    }

    private function rebackSerial($filter){
        $serialObj = app::get('ome')->model('product_serial');
        $serialLogObj = app::get('ome')->model('product_serial_log');
        if($filter['delivery_id'] && count($filter['delivery_id'])>0){
            $logFilter['act_type'] = 0;
            $logFilter['bill_type'] = 0;
            $logFilter['bill_no'] = $filter['delivery_id'];
            $serialLogs = $serialLogObj->getList('item_id',$logFilter);
            foreach($serialLogs as $key=>$val){
                $itemIds[] = $val['item_id'];
            }
            if(count($itemIds)>0 && $serialObj->update(array('status'=>0),array('item_id'=>$itemIds,'status'=>1))){
                return true;
            }
        }
        return false;
    }

}