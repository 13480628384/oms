<?php
class tgkpi_analysis_delivery extends eccommon_analysis_abstract implements eccommon_analysis_interface{
    public $report_type = 'true';
    public $type_options = array(
        'display' => 'false',
    );
    public $logs_options = array(
        '1' => array(
            'name' => '已完成发货单量',
            'flag' => array(),
            'memo' => '已完成发货单量',
            'icon' => 'money.gif',
        )
    );
    public $graph_options = array(
        'hidden' => false,
        'iframe_height' =>200,
        'show' => 'bar',
        'callback' => 'tgkpi_mdl_analysis_delivery',
    );

    public $detail_options = array(
        'hidden' => false,
        'force_ext' => true,
    );

    public function __construct(&$app){
        parent::__construct($app);
        $typeObj = app::get('omeanalysts')->model('ome_type');
        $flagList = $typeObj->get_shop();
        foreach($this->logs_options as $key=>$val){
            $this->logs_options[$key]['flag'][0] = '全部';
            foreach($flagList as $shop){
                $this->logs_options[$key]['flag'][$shop['relate_id']] = $shop['name'];
            }
        }
    }

    public function get_type(){
        $lab = '工号';
        $oPick = $this->app->model('pick');
        $data = $oPick->get_picker();
        $return = array(
            'lab' => $lab,
            'data' => $data,
        );
        return $return;
    }

    public function get_logs($time){
		$filter = array(
            'time_from' => date('Y-m-d',$time),
            'time_to' => date('Y-m-d',$time),
        );
        $storeObj = $this->app->model('ome_store');
		$outstock = $storeObj->get_outstock($filter);
		$store = $storeObj->get_store($filter);
		$store_value = $storeObj->get_value($filter);

		$result[] = array('type'=>0, 'target'=>1, 'flag'=>0, 'value'=>$outstock);
        $result[] = array('type'=>0, 'target'=>2, 'flag'=>0, 'value'=>$store['store']);
        $result[] = array('type'=>0, 'target'=>2, 'flag'=>1, 'value'=>$store['store_freeze']);
        $result[] = array('type'=>0, 'target'=>2, 'flag'=>2, 'value'=>$store['arrive_store']);
        $result[] = array('type'=>0, 'target'=>3, 'flag'=>0, 'value'=>$store_value);

        return $result;
	}

    public function ext_detail(&$detail){
        $filter = $this->_params;
        $filter['time_from'] = sprintf('%s 00:00:00',$filter['time_from']);
        $filter['time_to'] = sprintf('%s 23:59:59',$filter['time_to']);
        foreach($this->logs_options AS $target=>$option){
            $detail[$option['name']]['value'] = 0;
            $detail[$option['name']]['memo'] = $option['memo'];
            $detail[$option['name']]['icon'] = $option['icon'];
        }

        $delivery_counts = $this->app->model('pick')->get_deliveryed($filter);
        $detail['已完成发货单量']['value'] = $delivery_counts ? $delivery_counts : 0;
    }

    public function finder(){
        return array(
            'model' => 'tgkpi_mdl_analysis_delivery',
            'params' => array(
                'title'=>'发货绩效统计',
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
                'use_buildin_filter'=>false,
            ),
        );
    }

    /**
     * @description 参数设置
     * @access public
     * @param Array $params
     * @return Object $this
     */
    public function set_params($params)
    {
        $this->_params = $this->dealTime($params);
        return $this;
    }

    /**
     * @description 时间处理
     * @access public
     * @param void
     * @return void
     */
    public function dealTime($params)
    {
        if ($params['report']=='month') {     // 按月表
            // 默认为本月
            $now = time();
            $time_from = date('Y-m-01',$now);
            if ($params['time_from']) {
                $time_from = date('Y-m-01',strtotime($params['time_from']));
            }

            $time_to = date('Y-m-t',strtotime($time_from));
            if ($params['time_to']) {
                $time_to = date('Y-m-t',strtotime($params['time_to']));
            }

            $params['time_from'] = $time_from;
            $params['time_to'] = $time_to;
            $params['order_status'] = $this->analysis_config['filter']['order_status'];
        }else{                                           // 按日报
            if(isset($this->analysis_config)){
                $time_from = date("Y-m-d", time()-(date('w')?date('w')-$this->analysis_config['setting']['week']:7-$this->analysis_config['setting']['week'])*86400);
            }else{
                $time_from = date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400);
            }
            $time_to = date("Y-m-d", strtotime($time_from)+86400*7-1);

            $params['time_from'] = ($params['time_from']) ? $params['time_from'] : $time_from;
            $params['time_to'] = ($params['time_to']) ? $params['time_to'] : $time_to;
            $params['order_status'] = $this->analysis_config['filter']['order_status'];
        }

        return $params;
    }
}