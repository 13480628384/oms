<?php
/**
* 交易记录定时任务脚本类
* @author dqiujing@gmail.com
* @copyright shopex.cn 2012.12.14
* @TODO：modify point
*/
class finance_cronjob_tradeScript{
    
    /**
    * 按店铺生成实时请求交易记录队列
    * @access public 
    * @param 定时任务，每小时,且必须设置了账单日
    * @return void
    */
    function trade_search_queue(){
        $run_time = 60; //每次执行脚本的时间间隔 单位 分钟
        $time = time();
        $financeObj = base_kvstore::instance('setting/finance');
        $financeObj->fetch("trade_search_queue",$last_time);

        #获取账单日
        $billObj = kernel::single('finance_monthly_report');
        $bill_status = $billObj->get_init_time();
        if ($bill_status['flag'] == 'true' && empty($last_time)){
            #设置脚本最后执行时间 = 当前时间
            $last_time = $time;
            $financeObj->store("trade_search_queue",$time);
        }
        if($bill_status['flag'] == 'true' && $time-$run_time*60-$last_time>$run_time*60){
            #设置脚本最后执行时间
            $financeObj->store("trade_search_queue",$time);
            
            #店铺信息:淘宝已授权
            $funcObj = kernel::single('finance_func');
            $shop_list = $funcObj->taobao_shop_list();
            if ($shop_list){
                $worker = 'finance_cronjob_execQueue.trade_search';
                foreach ($shop_list as $shop){
                    //店铺上一次同步时间,为空,则取当前脚本的最后执行时间
                    $financeObj->fetch("shop_trade_search_".$shop['node_id'],$shop_start_time);
                    //$shop_start_time = !empty($shop_start_time) ? $shop_start_time : $last_time;
                    if(empty($shop_start_time)){
                        // 取一个月之前的数据
                        $shop_start_time = ($time - $bill_status['time'] > 30*24*60*60) ? ($time-30*24*60*60) : $bill_status['time'];
                    }

                    //更新当前店铺的最后执行时间
                    $financeObj->store("shop_trade_search_".$shop['node_id'],$time);

                    #时间范围超过6天的处理逻辑:将此时间范围分成每隔5天进行获取
                    $day2second = 24*60*60;
                    $shop_end_time = $time;
                    $diff_day = floor(($shop_end_time - $shop_start_time)/$day2second);
                    $tmp_start_time = $sync_start_time = $sync_end_time = '';
                    $max = 1;
                    $end = false;
                    do{
                        if ($max > 99) break;
                        $tmp_start_time = $tmp_start_time ? $tmp_start_time : $shop_start_time;
                        $tmp_end_time = $tmp_start_time + 5*$day2second;
                        if ($diff_day > 6 && $shop_end_time > $tmp_end_time){
                            $sync_start_time = $tmp_start_time;
                            $sync_end_time = $tmp_end_time;
                            $tmp_start_time = $tmp_end_time;
                        }else{
                            $sync_start_time = $tmp_start_time;
                            $sync_end_time = $shop_end_time;
                            $end = true;
                        }
                        
                        #添加队列
                        $params = array(
                            'start_time' => date('Y-m-d H:i:s',$sync_start_time-5*60),#交易开始时间提前5分钟,确保不遗漏
                            'end_time' => date('Y-m-d H:i:s',$sync_end_time),
                            'node_name' => $shop['name'],
                            'node_id' => $shop['node_id'],
                        );
                        $log_title = '请求交易记录数据:'.$params['start_time'].'至'.$params['end_time'].'';
                        if(!$funcObj->addTask($log_title,$worker,$params,$type='slow')){
                            #添加日志:失败
                            $log_type = 'trade_search';
                            $logObj = kernel::single('finance_tasklog');
                            $log_id = $logObj->write_log($log_title,$log_type,$params,$status='fail',$msg='添加队列失败');
                        }

                        if ($end === true) break;

                        $max++;
                    }while(1);
                }
            }
        }
    }

     /**
    * 按店铺生成获取交易任务号队列
    * @access public 
    * @param 定时任务，每天凌晨1点,且必须设置了账单日
    * @return void
    */
    function taskid_queue(){
        $run_time = 60; //每次执行脚本的时间间隔 单位 分钟
        $financeObj = base_kvstore::instance('setting/finance');
        $time = time();

        #获取账单日
        $billObj = kernel::single('finance_monthly_report');
        $bill_status = $billObj->get_init_time();
        $financeObj->fetch("taskid_shop_init_time",$shop_init_time);
        $bill_status['flag'] = 'true';
        if ($bill_status['flag'] == 'true' && !$shop_init_time){
            #设置店铺的初始开始时间 = 当前时间
            $shop_init_time = $time;
            $financeObj->store("taskid_shop_init_time",$time);
        }

        // 每10小时请求一次
        $financeObj->fetch("tradeid_search_queue",$last_time);
        if ($bill_status['flag'] == 'true' && $time-$last_time>$run_time*60*10){
            #设置脚本最后执行时间
            $financeObj->store("tradeid_search_queue",$time);

            #店铺列表:淘宝已授权
            $funcObj = kernel::single('finance_func');
            $shop_list = $funcObj->taobao_shop_list();

            if ($shop_list){
                $worker = 'finance_cronjob_execQueue.get_taskid';
                foreach ($shop_list as $shop){
                    //店铺上一次同步时间,为空,则取店铺的初始开始时间
                    $financeObj->fetch("shop_taskid_search_".$shop['node_id'],$shop_start_time);
                    $shop_start_time = !empty($shop_start_time) ? $shop_start_time : $shop_init_time;

                    $shop_end_time = $time;
                    
                    
                    #如果店铺结束时间与开始时间相差超过3天，则只获取前3天的数据)
                    $diff_day = floor(($shop_end_time - $shop_start_time)/(24*60*60));
                    $days = date('t',$shop_end_time);
                    if ($diff_day >= $days){
                        $shop_start_time = $shop_end_time-(3*24*60*60);
                    }
                    
                    #添加队列
                    $params = array(
                        'start_time' => date('Y-m-d H:i:s',$shop_start_time-5*60),#交易开始时间提前5分钟,确保不遗漏
                        'end_time'   => date('Y-m-d H:i:s',$shop_end_time),
                        'node_name'  => $shop['name'],
                        'node_id'    => $shop['node_id'],
                    );

                    $log_title = '请求交易任务号:'.$params['start_time'].'至'.$params['end_time'];
                    if(!$funcObj->addTask($log_title,$worker,$params,$type='slow')){
                        #添加日志:失败
                        $log_type = 'get_taskid';
                        $logObj = kernel::single('finance_tasklog');
                        $log_id = $logObj->write_log($log_title,$log_type,$params,$status='fail',$msg='添加队列失败');
                    } else {

                        $financeObj->store("shop_taskid_search_".$shop['node_id'],$shop_end_time);
                    }
                }
            }
        }
    }

    /**
    * 获取交易任务号结果
    * @access public 
    * @param 定时任务，每小时,且必须设置了账单日
    * @return void
    */
    function get_taskid_result(){
        $run_time = 60; //每次执行脚本的时间间隔 单位 分钟
        $time = time();
        $financeObj = base_kvstore::instance('setting/finance');
        $financeObj->fetch("get_taskid_result",$last_time);
        
        #获取账单日
        $billObj = kernel::single('finance_monthly_report');
        $bill_status = $billObj->get_init_time();
        if($bill_status['flag'] == 'true' && time()-$last_time>$run_time*60){
            #设置脚本最后执行时间
            $financeObj->store("get_taskid_result",$time);
            
            #获取任务号
            $taskIdObj = kernel::single('finance_taskid');

            $taskid_list = $taskIdObj->taskid_list(array('createtime|sthan'=>($time-45*60)));
            if ($taskid_list){
                $apiObj = kernel::single('finance_apitrade');
                $logObj = kernel::single('finance_tasklog');
                $log_type = 'get_taskid_result';
                foreach ($taskid_list as $task){
                    $task_id     = $task['task_id'];
                    $node_id     = $task['node_id'];
                    $node_name   = $task['node_name'];
                    $taskid_time = $task['taskid_time'];
                    $start_time  = $task['start_time'];
                    $end_time    = $task['end_time'];

                    #添加日志：运行中
                    $log_title = '请求交易任务号结果:'.$task_id;
                    $log_params = array(
                        'task_id'    => $task_id,
                        'start_time' => $start_time,
                        'end_time'   => $end_time,
                        'node_id'    => $node_id,
                        'node_name'  => $node_name,
                    );
                    $log_id = $logObj->write_log($log_title,$log_type,$log_params,$status='running',$msg='正在请求任务结果数据',$node_id);

                    $rs = $apiObj->get_taskid_result($task_id,$node_id,$node_name,$start_time,$end_time);
                    if ($rs['rsp'] == 'succ'){
                        #删除日志
                        $logObj->delete($log_id);

                        #删除任务号
                        $taskIdObj->delete($task_id);
                    }else{
                        #更新日志
                        $logObj->update_log($log_id,$msg=$rs['msg'],$status='fail');
                    }

                }
            }
        }
    }
    
    /**
    * 按任务日志表生成自动重试请求队列
    * @access public 
    * @param 定时任务，每小时读取失败的任务日志
    * @return void
    */
    function autoretry(){
        $run_time = 60; //每次执行脚本的时间间隔 单位 分钟
        $time = time();
        $financeObj = base_kvstore::instance('setting/finance');
        $financeObj->fetch("autoretry_queue",$last_time);

        if($time-$run_time*60-$last_time>$run_time*60){
            #设置脚本最后执行时间
            $financeObj->store("autoretry_queue",$time);
            
            #失败任务日志
            $funcObj = kernel::single('finance_func');
            $logObj = kernel::single('finance_tasklog');
            $log_list = $logObj->getList(array('status'=>'fail','retry|sthan'=>'3'));
            if ($log_list){
                $worker = 'finance_cronjob_execQueue.autoretry';
                foreach ($log_list as $log){
                    #添加队列
                    $params = array(
                        'params' => !is_array($log['params']) ? unserialize($log['params']) : $log['params'],
                        'log_type' => $log['log_type'],
                        'log_id' => $log['log_id'],
                        'retry_nums' => $log['retry']+1,
                    );
                    if($funcObj->addTask('[自动重试]'.$log['log_title'],$worker,$params,$type='slow')){
                        #更新重试次数及状态
                        $addon['retry'] = $log['retry']+1;
                        $logObj->update_log($log['log_id'],$msg='自动重试中',$status='retring','',$addon);
                    }
                }
            }
        }
    }

    /**
     * 定时获取销售数量
     * 
     * @return void
     * @author 
     **/
    public function get_sales()
    {
        @ini_set('memory_limit', '512M'); @set_time_limit(0);

        $financeObj = base_kvstore::instance('setting/finance');

        $run_time = 60; //每次执行脚本的时间间隔 单位 分钟
        $time = time();
        $financeObj->fetch("sales_run_time",$sales_run_time);
        if($time-$run_time*60-$sales_run_time<$run_time*60){
            return false;
        }
        $financeObj->store("sales_run_time",$time);

        $financeObj->fetch('sales_read_time',$sales_read_time);
        
        # 读取最后一次执行时间 不存在取初始时间
        $init_time = app::get('finance')->getConf('finance_setting_init_time');
        if ($init_time) {
            if ($init_time['flag'] != 'true') {
                return false;
            }
            $init_time = mktime(0,0,0,$init_time['month'],$init_time['day'],$init_time['year']);

            if (!$sales_read_time['finishtime']) {
                $first_sale = app::get('ome')->model('sales')->getList('sale_time',array(),0,1,'sale_time asc');
                if ($first_sale && $first_sale[0]['sale_time'] > $init_time) {
                    $init_time = $first_sale[0]['sale_time'];
                }
                unset($first_sale);
            }
        } else {
            $init_time = time();
            return false;
        }


        $last_finishtime = $sales_read_time['finishtime'] ? $sales_read_time['finishtime'] : $init_time;

        if (!isset($sales_read_time['inittime'])) {
            $sales_read_time['inittime'] = time();
        }

        # 读取时间段,默认为5个小时
        $daytime = 5 * 3600;

        $current_finishtime = $sales_read_time['finishtime'] = $last_finishtime + $daytime;
        if ($current_finishtime > time()) {
            $current_finishtime = time();
        }

        $sales_read_time['finishtime'] = $current_finishtime;
        # 保存时间
        $financeObj->store('sales_read_time',$sales_read_time);

        $saleModel = app::get('ome')->model('sales');
        $saleItemModel = app::get('ome')->model('sales_items');
        $orderModel = app::get('ome')->model('orders');
        $filter = array(
            'sale_time|between' => array(
                0 => ($last_finishtime - 3600),
                1 => $current_finishtime,
            ),
        );
        $count = $saleModel->count($filter);
        if( $count <= 0) return false;

        $shopModel = app::get('ome')->model('shop');
        $list = $shopModel->getList('shop_id,name');
        if(empty($list)) return false;

        foreach ($list as $k => $v) {
            $shop[$v['shop_id']]['name'] = $v['name'];
        }

        $offset = 0; $limit = 9000; $sales = array(); 
        do {
            $list = $saleModel->getList('*',$filter,$offset,$limit);
            foreach ($list as $k => $v) {
                $sales[$v['sale_id']] = array(
                    'sale_time'     => $v['sale_time'],
                    'member_id'     => $v['member_id'],
                    'sale_amount'   => bcsub($v['sale_amount'], $v['cost_freight'], 3),//$v['sale_amount'], 
                    'delivery_cost' => $v['cost_freight'],
                    'sale_bn'       => $v['sale_bn'],
                    'sales_items'   => &$sales_items[$v['sale_id']],
                    'order_id'      => &$orders[$v['order_id']]['order_id'],
                    'order_bn'      => &$orders[$v['order_id']]['order_bn'],
                    'shop_id'       => &$orders[$v['order_id']]['shop_id'],
                    'shop_name'     => &$orders[$v['order_id']]['shop_name'],
                );

                $sale_id[]  = $v['sale_id'];
                $order_id[] = $v['order_id'];
            }

            $list = $orderModel->getList('order_id,order_bn,shop_id',array('order_id'=>$order_id));
            foreach ($list as $k => $v) {
                $orders[$v['order_id']]['order_id'] = $v['order_id'];
                $orders[$v['order_id']]['order_bn'] = $v['order_bn'];
                $orders[$v['order_id']]['shop_id'] = $v['shop_id'];
                $orders[$v['order_id']]['shop_name'] = $shop[$v['shop_id']]['name'];
            }

            $iOffset = 0; $iLimit = 9000;
            do {
                $list = $saleItemModel->getList('*',array('sale_id'=>$sale_id),$iOffset,$iLimit);
                if(empty($list)) break;

                foreach ($list as $k => $v) {
                    $sales_items[$v['sale_id']][] = array(
                        'bn' => $v['bn'],
                        'name' => $v['name'],
                        'nums' => $v['nums'],
                        'sales_amount' => $v['sales_amount'],
                        'shop_name' => $sales[$v['sale_id']]['shop_name'],
                        'shop_id' => $sales[$v['sale_id']]['shop_id'],
                        'order_id' => $sales[$v['sale_id']]['order_id'],
                        'order_bn' => $sales[$v['sale_id']]['order_bn'],
                    );
                }

                $iOffset += $iLimit;
            } while ( true );

            $offset += $limit;

        } while ($count > $offset);
        
        foreach ($sales as $sale_id=>$value) {
            $data = array('sales'=>$value);
            kernel::single('finance_iostocksales')->do_iostock_sales_data($data);
        }
        
    }

    /**
     * 获取费用
     *
     * @return void
     * @author 
     **/
    public function get_bills()
    {
        // 以下时间段不请求接口
        $denytime = array(
            0 => array(mktime(9,30,0,date('m'),date('d'),date('Y')),mktime(11,0,0,date('m'),date('d'),date('Y'))),
            1 => array(mktime(14,0,0,date('m'),date('d'),date('Y')),mktime(17,0,0,date('m'),date('d'),date('Y'))),
            2 => array(mktime(20,0,0,date('m'),date('d'),date('Y')),mktime(22,30,0,date('m'),date('d'),date('Y'))),
            2 => array(mktime(1,0,0,date('m'),date('d'),date('Y')),mktime(3,0,0,date('m'),date('d'),date('Y'))),
        );

        $now = time();
        foreach ($denytime as $value) {
            if ($value[0]<=$now && $now<=$value[1]) {
                return false;
            }
        }


        $run_time = 120; //每次执行脚本的时间间隔 单位 分钟
        $time = time();
        $financeObj = base_kvstore::instance('setting/finance');
        $financeObj->fetch("bills_get",$last_time);

        #获取账单日
        $billObj = kernel::single('finance_monthly_report');
        $bill_status = $billObj->get_init_time();

        $last_time = $last_time ? $last_time : 0;
        if($time-$run_time*60-$last_time>$run_time*60){
            #设置脚本最后执行时间
            $financeObj->store("bills_get",$time);
            
            #店铺信息:淘宝已授权
            $funcObj = kernel::single('finance_func');
            $shop_list = $funcObj->shop_list(array('tbbusiness_type' => 'B','node_type' => 'taobao'));
            if ($shop_list){
                $worker = 'finance_cronjob_execQueue.bills_get';
                foreach ($shop_list as $shop){
                    if (!$shop['node_id']) continue;

                    unset($last_end_time);

                    //店铺上一次同步时间,为空,则取当前脚本的最后执行时间
                    $financeObj->fetch("shop_bills_get_".$shop['node_id'],$last_end_time);
                    if(empty($last_end_time)){
                        // 取期当前时间之前一个月
                        $last_end_time = time()-30*24*60*60;
                    }
                    
                    #时间范围超过6天的处理逻辑:将此时间范围分成每隔5天进行获取
                    $day2second = 23*60*60;
                    // $last_end_time = $shop_start_time;
                    if(($last_end_time+$day2second) > $time){
                        continue;
                    }

                    $max = 1;
                    do{
                        if ($max > 99) break;

                        $next_start_time = $last_end_time;
                        $next_end_time = $last_end_time + $day2second;

                        if ($next_end_time > $time) break;
                        #添加队列
                        $params = array(
                            'start_time' => date('Y-m-d H:i:s',$next_start_time-5*60),#交易开始时间提前5分钟,确保不遗漏
                            'end_time' => date('Y-m-d H:i:s',$next_end_time),
                            'node_name' => $shop['name'],
                            'node_id' => $shop['node_id'],
                            'shop_id' => $shop['shop_id'],
                        );
                        $log_title = '请求账单数据:'.$params['start_time'].'至'.$params['end_time'].'';
                        if(!$funcObj->addTask($log_title,$worker,$params,$type='slow')){
                            #添加日志:失败
                            $log_type = 'bills_get';
                            $logObj = kernel::single('finance_tasklog');
                            $log_id = $logObj->write_log($log_title,$log_type,$params,$status='fail',$msg='添加队列失败');
                        }

                        $max++; $last_end_time = $next_end_time;
                    }while(1);

                    //更新当前店铺的最后执行时间
                    $financeObj->store("shop_bills_get_".$shop['node_id'],$last_end_time);
                }
            }
        }
    }


    /**
     * 获取虚拟账户明细数据
     *
     * @return void
     * @author 
     **/
    public function get_book_bills()
    {
        $run_time = 360; //每次执行脚本的时间间隔 单位 分钟
        $time = time();

        // 上次脚本执行的时间
        $financeObj = base_kvstore::instance('setting/finance');
        $financeObj->fetch("book_bills_get",$last_time);

        if (($time - $last_time) <= $run_time * 60) {
            return false;
        }

        // 记录本次脚本执行的时间
        $financeObj->store("book_bills_get",$time);

        $feeItemModel = app::get('finance')->model('bill_fee_item');
        $feeItemList = $feeItemModel->getList('outer_account_id');

        $funcObj = kernel::single('finance_func');
        $worker = 'finance_cronjob_execQueue.sync_book_bills_get';
        $shopList = $funcObj->shop_list(array('tbbusiness_type' => 'B','node_type' => 'taobao'));
        foreach ($shopList as $shop) {
            if (!$shop['node_id']) continue;

            unset($shop_last_endtime);

            //店铺上一次同步时间,为空,则取当前脚本的最后执行时间
            $financeObj->fetch("shop_book_bills_get_".$shop['node_id'],$shop_last_endtime);
            if (!$shop_last_endtime) {
                // 取一个月之前的数量
                $shop_last_endtime = $time - 30*24*60*60;
            }

            $day2second = 24*60*60;
            
            if(($shop_last_endtime+$day2second) > $time){
                continue;
            }

            $max = 1;
            do {
                if($max>=2) break;

                $shop_next_starttime = $shop_last_endtime;
                $shop_last_endtime  = $shop_next_starttime + $day2second;

                $params = array(
                    'start_time' => date('Y-m-d H:i:s',$shop_next_starttime-5*60),#交易开始时间提前5分钟,确保不遗漏
                    'end_time'   => date('Y-m-d H:i:s',$shop_last_endtime),
                    'node_name'  => $shop['name'],
                    'node_id'    => $shop['node_id'],
                    'shop_id'    => $shop['shop_id'],
                    'page_no'   => 1,
                    'page_size' => 50,
                    // 'account_id' => $fee_item['outer_account_id'],
                );

                foreach ($feeItemList as $fee_item) {
                    if(!$fee_item['outer_account_id']){
                        continue;
                    }
                    $params['account_id'] = $fee_item['outer_account_id'];

                    $log_title = '请求获取虚拟账户明细:'.$params['start_time'].'至'.$params['end_time'].'';
                    if(!$funcObj->addTask($log_title,$worker,$params,$type='slow')){
                        #添加日志:失败
                        $log_type = 'book_bills_get';
                        $logObj = kernel::single('finance_tasklog');
                        $log_id = $logObj->write_log($log_title,$log_type,$params,$status='fail',$msg='添加队列失败');
                    }
                }

                $max++;
            } while (1);

            $financeObj->store("shop_book_bills_get_".$shop['node_id'],$shop_last_endtime);
        }
    }
}