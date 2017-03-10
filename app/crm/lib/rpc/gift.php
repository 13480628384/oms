<?php
class crm_rpc_gift extends crm_rpc_request{

    //获取crm赠品规则
    /*
    shop_id        店铺ID
    order_bn       订单号
    buyer_nick     买家昵称
    receiver_name  收货人姓名
    mobile         联系电话（手机）
    */
    public function getGiftRule($data){
       $res = array('result'=>'fail','data'=>array(),'msg'=>'获取失败');

       $api_name = 'store.gift.rule.get';
       
       $Oshop = app::get('ome')->model('shop');
       $shopdata = $Oshop->getRow(array('shop_id'=>$data['shop_id']),'node_id,name');

//       $params = array(
//           'buyer_nick'=>$data['buyer_nick'],
//           'receiver_name'=>$data['receiver_name'],
//           'mobile'=>$data['mobile'],
//           'unique_node'=>$shopdata['node_id'],
//       );

       $params = array(
           'buyer_nick' => $data['buyer_nick'],
           'receiver_name'=> $data['receiver_name'],
           'mobile' => $data['mobile'],
           'tel' => $data['tel'],
           'shop_id' => $data['shop_id'],
           'unique_node' => $shopdata['node_id'],
           'shop_name' => $shopdata['name'],
           'order_bn' => $data['order_bn'],
           'province' => $data['province'],
           'city' => $data['city'],
           'district' => $data['district'],
           'total_amount' => $data['total_amount'],
           'payed' => $data['payed'],
           'createtime' => $data['createtime'],
           'pay_time' => $data['pay_time'],
           'is_cod' => $data['is_cod'],
           'items' => is_array($data['items']) ? json_encode($data['items']) : $data['items'],
           'addon' => '',
           'is_send_gift' => $data['is_send_gift'],#强制重新请求的标示
       );
       $write_log = array(
           'class'=>__CLASS__,
           'method'=>__METHOD__,
           'order_bn'=>$data['order_bn'],
       );

       $result = $this->call('store.gift.rule.get','获取CRM赠品规则',$params,10,$write_log);

       return $result;
    }

    #发送CRM赠品日志
    public function getGiftRuleLog($data) {
        $res = array('result'=>'fail','data'=>array(),'msg'=>'发送失败');
        $api_name = 'store.gift.rule.get';
        $Oshop = app::get('ome')->model('shop');
        $shopdata = $Oshop->getRow(array('shop_id'=>$data['shop_id']),'node_id,name');
        $addon = array(
            'func' => 'log',
            'params' => $data['addon'],
        );
        $params = array(
            'buyer_nick' => $data['buyer_nick'],
            'receiver_name' => $data['receiver_name'],
            'mobile' => $data['mobile'],
            'tel' => $data['tel'],
            'shop_id' => $data['shop_id'],
            'unique_node' => $shopdata['node_id'],
            'shop_name' => $shopdata['name'],
            'order_bn' => $data['order_bn'],
            'province' => $data['province'],
            'city' => $data['city'],
            'district' => $data['district'],
            'total_amount' => $data['total_amount'],
            'payed' => $data['payed'],
            'is_cod' => $data['is_cod'],
            'items' => '',
            'addon' => json_encode($addon),
        );
        $write_log = array(
            'class' => __CLASS__,
            'method' => __METHOD__,
            'order_bn' => $data['order_bn'],
        );
        $result = $this->call('store.gift.rule.get', '发送CRM赠品日志', $params, 10, $write_log);
        return $result;
    }

    /**
     * 返回数据格式化
     * @param $data
     * @return array
     */
    function getFormatRst($data){
        $res = array('result'=>'fail','msg'=>'','data'=>array());
        $rst = $this->getLocalGiftRule($data);

        if(isset($rst['err'])){
            $res['msg'] = $rst['msg'];
            return $res;
        } else {
            $res['result'] = 'succ';
            $res['msg'] = $rst['err_msg'];
            $res['data'] = $rst;
            return $res;
        }

    }

    /*
     * 格式化参数
     */
    private function formatParams($data){
        $res = array('result'=>'fail','data'=>array(),'msg'=>'获取失败');

        $Oshop = app::get('ome')->model('shop');
        $shopdata = $Oshop->getRow(array('shop_id'=>$data['shop_id']),'node_id,name');

        $params = array(
            'buyer_nick' => $data['buyer_nick'],
            'receiver_name'=> $data['receiver_name'],
            'mobile' => $data['mobile'],
            'tel' => $data['tel'],
            'shop_id' => $data['shop_id'],
//            'unique_node' => $data['shop_id'],
            'unique_node' => $shopdata['node_id'],
            'shop_name' => $shopdata['name'],
            'order_bn' => $data['order_bn'],
            'province' => $data['province'],
            'city' => $data['city'],
            'district' => $data['district'],
            'total_amount' => $data['total_amount'],
            'payed' => $data['payed'],
            'createtime' => $data['createtime'],
            'pay_time' => $data['pay_time'],
            'is_cod' => $data['is_cod'],
            'items' => is_array($data['items']) ? json_encode($data['items']) : $data['items'],
            'addon' => '',
            'is_send_gift' => $data['is_send_gift'],#强制重新请求的标示
        );

        return $params;
    }

    /**
     * 获取erp的赠品规则
     * @param $data
     * @return array
     */
    public function getLocalGiftRule($data){
        $sdf = $this->formatParams($data);
        $responseObj = kernel::single('base_rpc_service');

        $log_mdl = app::get('ome')->model('api_log');
        $logTitle = 'ERP赠品接口['.$sdf['order_bn'].']';
        $logInfo = '订单赠品接口：<BR>';
        $logInfo .= '请求参数 $sdf 信息：' . var_export($sdf, true) . '<BR>';
        $reason = '';


        $pay_time = floatval($sdf['pay_time']);
        $createtime = floatval($sdf['createtime']);

        $shop_name = $sdf['shop_name'];
        $addon = $sdf['addon'];
        $buyer_nick = $sdf['buyer_nick'];
        $receiver_name = $sdf['receiver_name'];
        $mobile = $sdf['mobile'];
        $tel = $sdf['tel'];
        $order_bn = $sdf['order_bn'];
        $payed = floatval($sdf['payed']);
        $province = $sdf['province'];
        $unique_node = $sdf['unique_node'];
        $is_cod = intval($sdf['is_cod']);
        $order_items = json_decode($sdf['items'], true);
//        $lv_id = 0;
        $total_paid = 0;
        $is_send_gift = intval($sdf['is_send_gift']); //强制重新发送赠品标志位

        if($is_cod==1 || $payed==0){
            $logInfo .= '不处理货到付款和未付款订单';
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '',
                'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
            // $responseObj->send_user_error(app::get('base')->_('x001'), '不处理货到付款和未付款订单');
            return array('err' => 1, 'msg' => '不处理货到付款和未付款订单');
        }

        if(!$buyer_nick && !$receiver_name){
            $logInfo .= '买家帐号或收货人不能同时为空';
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '',
                'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
//            $responseObj->send_user_error(app::get('base')->_('x001'), '买家帐号或收货人不能同时为空');
            return array('err' => 1, 'msg' => '买家帐号或收货人不能同时为空');
        }

        //查询赠品日志，已经送过的订单号不送第二次，除非  is_send_gift =1
        if($is_send_gift == 0){
            $rs = app::get('ome')->model('gift_logs')->dump(array('order_bn'=>$order_bn), 'id');
            if($rs){
                $logInfo .= '订单号：'.$order_bn.' 不能重复赠送';
                $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
//                $responseObj->send_user_error(app::get('base')->_('x001'), '订单号'.$order_bn.'不能重复赠送');
                return array('err' => 1, 'msg' => '订单号'.$order_bn.'不能重复赠送');
            }
        }

        $shopObj = app::get('ome')->model('shop');
        $shop = $shopObj->dump(array('node_id'=>$sdf['unique_node']),'shop_id,node_type');
//        $shop = $shopObj->dump(array('shop_id'=>$sdf['unique_node']),'shop_id,node_type');
        $shop_id = $shop['shop_id'];
        $total_paid = $payed;

        // 查询是否存在有效规则
        $time = time();
        $sql = "select * from sdb_ome_gift_rule where status = '1' order by priority DESC,id DESC";
        $data = $shopObj->db->select($sql);
        if($data){

            $sql = 'select set_type from sdb_ome_gift_set_logs order by id DESC';
            $rs_set_type = $shopObj->db->selectRow($sql);
            if($rs_set_type){
                $set_type = $rs_set_type['set_type'];
            } else {
                $set_type = 'exclude';
            }

            $gift_bns = array();//需要发送到erp的赠品列表
            $gift_ids = '0';
            $gift_num = '0';
            $gift_send_log = array();//记录赠品发送日志

            // 检测是否符合赠送条件
            foreach($data as $rule){
                // 互斥排他模式下， 退出循环
                if($gift_ids && $gift_ids != '0' && $set_type=='exclude'){
                    break;
                }

                // 检测时间有效期
                if($rule['time_type']=='createtime'){
                    if($createtime>$rule['end_time'] or $createtime<$rule['start_time']) continue;
                }elseif($rule['time_type']=='pay_time'){
                    if($pay_time>$rule['end_time'] or $pay_time<$rule['start_time']) continue;
                }else{
                    if($time>$rule['end_time'] or $time<$rule['start_time']) continue;
                }

                // 赠品判断条件
                $rule['filter_arr'] = json_decode($rule['filter_arr'], true);

                if($rule['shop_ids']){
                    $rule['shop_ids'] = explode(',', $rule['shop_ids']);
                }elseif($rule['shop_id']){
                    $rule['shop_ids'] = array($rule['shop_id']);
                }

                if(!$rule['gift_ids']){
                    $reason = '没有设定赠品';
                    continue;
                }elseif($rule['shop_ids'] && !in_array($shop_id, $rule['shop_ids'])){
                    $reason = '不符合指定店铺';
                    continue;
                }

                if($rule['filter_arr']['province']){
                    if( ! $province or ! in_array($province, $rule['filter_arr']['province'])){
                        $reason = '不符合指定收货区域';
                        continue;
                    }
                }

                if($rule['filter_arr']['order_amount']['type']==1){
                    if($rule['filter_arr']['order_amount']['sign']=='bthan'){
                        if($payed<$rule['filter_arr']['order_amount']['max_num']){
                            $reason = '不满足最低付款';
                            continue;
                        }
                    }else{
                        if($payed<$rule['filter_arr']['order_amount']['min_num'] or $payed>$rule['filter_arr']['order_amount']['max_num']){
                            $reason = '不满足付款区间';
                            continue;
                        }
                    }
                }

                if($rule['filter_arr']['order_amount']['type']==2){
                    if($rule['filter_arr']['order_amount']['sign']=='bthan'){
                        if($total_paid<$rule['filter_arr']['order_amount']['max_num']){
                            continue;//累计不满足最低付款
                        }
                    }else{
                        if($total_paid<$rule['filter_arr']['order_amount']['min_num'] or $total_paid>$rule['filter_arr']['order_amount']['max_num']){
                            continue;//累计不满足付款区间
                        }
                    }
                }

                //限量赠送
                if($rule['filter_arr']['buy_goods']['limit_type']==1){
                    //判断已经送出的订单数
                    $sql = "select count(distinct order_bn) as total_orders from sdb_ome_gift_logs where gift_rule_id=".$rule['id']." ";
                    $rs_temp = $shopObj->db->selectRow($sql);
                    if($rs_temp) {
                        if($rs_temp['total_orders'] >= $rule['filter_arr']['buy_goods']['limit_orders']){
                            $reason = '超过送出数量限制';
                            continue;
                        }
                    }
                }

                $has_buy = false;
                $item_nums = $this->get_buy_goods_num($rule, $order_items, $has_buy);
                if($rule['filter_arr']['buy_goods']['count_type'] == 'paid'){
                    $item_nums = $payed;
                }

                if($has_buy == false){
                    $reason = '不符合指定商品购买条件';
                    continue;
                }

                //计算赠品数量
                if($item_nums>0 && $rule['filter_arr']['buy_goods']['num_rule']=='auto'){
                    $ratio = intval($item_nums/$rule['filter_arr']['buy_goods']['per_num']);
                    $suite = $rule['filter_arr']['buy_goods']['send_suite']*$ratio;
                    $suite = min($suite, $rule['filter_arr']['buy_goods']['max_send_suite']);
                    if($suite >= 1){
                        //数量倍数
                        $temp_arr = explode(',', $rule['gift_num']);
                        foreach($temp_arr as $k=>$v){
                            $temp_arr[$k] = $v * $suite;
                        }
                        $temp_arr = implode(',', $temp_arr);
                    }elseif($suite == 0){
                        //数量不符合要求
                        $reason = '不符合商品数量购买条件';
                        continue;
                    }

                    $gift_ids .= ','.$rule['gift_ids'];
                    $gift_num .= ','.$temp_arr;

                    $gift_send_log[] = array(
                        'gift_rule_id' => $rule['id'],
                        'gift_ids' => explode(',', $rule['gift_ids']),
                        'gift_num' => explode(',', $temp_arr)
                    );
                    continue;
                }elseif($item_nums>0){
                    if($rule['filter_arr']['buy_goods']['rules_sign']=='nequal'){
                        if($item_nums!=$rule['filter_arr']['buy_goods']['min_num']){
                            $reason = '不等于指定数量';
                            continue;
                        }
                    }elseif($rule['filter_arr']['buy_goods']['rules_sign']=='between'){
                        if($item_nums<$rule['filter_arr']['buy_goods']['min_num'] or $item_nums>=$rule['filter_arr']['buy_goods']['max_num']){
                            $reason = '不在数量范围内';
                            continue;
                        }
                    }else{
                        if($item_nums<$rule['filter_arr']['buy_goods']['min_num']){
                            $reason = '小于指定数量';
                            continue;
                        }
                    }
                    $gift_ids .= ','.$rule['gift_ids'];
                    $gift_num .= ','.$rule['gift_num'];

                    $gift_send_log[] = array(
                        'gift_rule_id' => $rule['id'],
                        'gift_ids' => explode(',', $rule['gift_ids']),
                        'gift_num' => explode(',', $rule['gift_num'])
                    );
                    continue;
                }
            }

            //如果符合条件，添加赠送日志
            if($gift_ids && $gift_ids != '0'){
                $gift_bns = array();
                $gifts = array();

                //获取每个赠品id对应的数量
                $gift_id_arr = explode(',', $gift_ids);
                $gift_num_arr = explode(',', $gift_num);
                foreach($gift_id_arr as $k=>$v){
                    if(!isset($gift_id_num[$v])) $gift_id_num[$v] = 0;

                    if(intval($gift_num_arr[$k])==0){
                        $gift_id_num[$v] += 1;
                    }else{
                        $gift_id_num[$v] += intval($gift_num_arr[$k]);
                    }
                }

                $err_msg = '';
                $rs = app::get('crm')->model('gift')->getList('gift_id,gift_bn,gift_name,gift_num',array('gift_id'=>$gift_id_arr));
                foreach($rs as $v){
                    if($v['gift_num'] <= 0) {
                        $err_msg .= $v['gift_bn'].'库存不足;';
                        $reason = '赠品库存不足';
                        continue;
                    }

                    $rs_gifts[$v['gift_id']] = $v;
                    $gift_num = $gift_id_num[$v['gift_id']];

                    $gift_bns[] = $v['gift_bn'];
                    $gifts[$v['gift_bn']] = $gift_num;

                    //扣减库存
                    $sql = "update sdb_crm_gift set gift_num=gift_num-".$gift_num.",send_num=send_num+".$gift_num." where gift_id=".$v['gift_id'];
                    $shopObj->db->exec($sql);
                }

                //返回erp的发货数据
                $return = array(
//                    'm_level'=>$lv_id,
                    'order_bn'=>$order_bn,
                    'gifts'=>$gifts,
                    'gift_bn'=>implode(',', $gift_bns)
                );
                if($err_msg){
                    $return['err_msg'] = $err_msg;
                }

                //记录赠品发送日志
                $create_time = time();
                $m_gift_logs = app::get('ome')->model('gift_logs');
                foreach($gift_send_log as $v){
                    foreach($v['gift_ids'] as $kk=>$vv){
                        //跳过库存为 0 的赠品
                        if(!isset($rs_gifts[$vv])) {
                            $reason = '赠品库存不足';
                            continue;
                        }
                        // 跳过已经赠送完了的商品
                        if( ($rs_gifts[$vv]['gift_num'] - $rs_gifts[$vv]['send_num']) <= 0 ){
                            $reason = $vv. ' 赠品已送完';
                            continue;
                        }

                        $md5_key = md5($order_bn.$rs_gifts[$vv]['gift_bn'].$v['gift_rule_id'].$create_time);
                        $log_arr = array(
                            'order_source'=>$shop_name,
                            'order_bn'=>$order_bn,
                            'buyer_account'=>$buyer_nick,
                            'shop_id'=>$shop_id,
                            'paid_amount'=>$payed,
                            'gift_num'=>$v['gift_num'][$kk],
                            'gift_rule_id'=>$v['gift_rule_id'],
                            'gift_bn'=>$rs_gifts[$vv]['gift_bn'],
                            'gift_name'=>$rs_gifts[$vv]['gift_name'],
                            'create_time'=>$create_time,
                            'md5_key'=>$md5_key,
                            'status'=>0,
                        );
                        $q = $m_gift_logs->save($log_arr);
                        if ( ! $q){
                            $sql = "update sdb_ome_gift_logs set gift_num=gift_num+".$v['gift_num'][$kk]." where md5_key='$md5_key' ";
                            $m_gift_logs->db->exec($sql);
                        }
                    }
                }

                if(!$gift_send_log){
                    $logInfo .= '赠品为空'.'<br/>'.$reason;
                    $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
                    return array();
                }

                $logInfo .= '返回参数：' . var_export($return, true) . '<BR>';
                $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo, array('task_id'=>$sdf['order_bn']));
                return $return;
            }

            $logInfo .= '赠品为空'.'<br/>'.$reason;
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
            return array();

        } else {
//            $responseObj->send_user_error(app::get('base')->_('x004'), '不存在有效的促销规则，赠品获取失败');
            return array('err' => 1, 'msg' => '不存在有效的促销规则，赠品获取失败');
        }

    }

    /**
     * @param $rule
     * @param $order_items
     * @param $has_buy
     * @return int|mixed
     */
    public function get_buy_goods_num($rule, $order_items, &$has_buy){
        $item_nums = 0;
        $item_num_arr = array();
        $item_bns = array();
        $buy_goods_bns = $rule['filter_arr']['buy_goods']['goods_bn'];
        $count_type = $rule['filter_arr']['buy_goods']['count_type'];

//        if(!is_array($buy_goods_bns)){
//            $buy_goods_bns = array(strtoupper($buy_goods_bns));
//        }
        if(!is_array($buy_goods_bns)){
            $buy_goods_bns = array($buy_goods_bns);
        }

        // 清理空数据
        $buy_goods_bns = $this->clear_value($buy_goods_bns);

        foreach($order_items as $item){
//            $item['bn'] = strtoupper($item['bn']);

            if($rule['filter_arr']['buy_goods']['type'] == 1){
                if($rule['filter_arr']['buy_goods']['buy_type'] == 'all'){
                    //购买了全部指定商品
                    if( ! in_array($item['bn'], $item_bns)) $item_bns[] = $item['bn'];
                    if(in_array($item['bn'], $buy_goods_bns)){
                        $item_num_arr[$item['bn']] = intval($item_num_arr[$item['bn']]) + $item['nums'];
                        unset($buy_goods_bns[array_search($item['bn'], $buy_goods_bns)]);
                    }
                }elseif($rule['filter_arr']['buy_goods']['buy_type'] == 'none'){
                    //排除购买的指定商品
                    if( ! in_array($item['bn'], $buy_goods_bns)){
                        $item_nums += $item['nums'];
                        if( ! in_array($item['bn'], $item_bns)) $item_bns[] = $item['bn'];
                        $has_buy = true;
                    }
                }else{
                    //}else($rule['filter_arr']['buy_goods']['buy_type'] == 'any'){
                    //购买了任意一个指定商品
                    if(in_array($item['bn'], $buy_goods_bns)){
                        $item_nums += $item['nums'];
                        if( ! in_array($item['bn'], $item_bns)) $item_bns[] = $item['bn'];
                        $has_buy = true;
                    }
                }
            } else {
                $item_nums += $item['nums'];
                $item_num_arr[$item['bn']] = intval($item_num_arr[$item['bn']]) + $item['nums'];
                $has_buy = true;
            }
        }

        //购买了全部指定商品，数量以最少的为准
        if($rule['filter_arr']['buy_goods']['type']==1){
            if($rule['filter_arr']['buy_goods']['buy_type'] == 'all'){
                if( ! $buy_goods_bns){
                    $item_nums = min($item_num_arr);
                    $has_buy = true;
                }
            }
        }

        if($count_type == 'sku' && $has_buy === true){
            $item_nums = count($item_bns);
        }

        return $item_nums;

    }

    //删除数组里的空元素
    public function clear_value($arr)
    {
        foreach($arr as $k=>$v){
            if(is_array($v)){
                $arr[$k] = $this->clear_value($v);
            }else{
                //检测邮政编码格式
                if($k==='zip' && !preg_match("/^(\d){5,6}$/", $v)){
                    unset($arr[$k]);
                }

                if( ! $v) unset($arr[$k]);
            }
        }
        return $arr;
    }

}

