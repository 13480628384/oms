<?php
class finance_analysis_bills extends finance_analysis_abstract implements eccommon_analysis_interface{
	public $detail_options = array(
        'hidden' => false,
    );
    public $graph_options = array(
        'hidden' => true,
    );
    public $type_options = array(
        'display' => 'true',
    );
	
    public function finder(){
        $filter = array();
        foreach ($this->_params as $key => $value) {
            $filter['filter'][$key] = $value;
        }

        return array(
            'model' => 'finance_mdl_analysis_bills',
            'params' => array(
                'actions'=>array(
                     array(
                        'label'  => '导出',
                        'href'   => 'index.php?app=finance&ctl=analysis_bills&act=index&action=export',
                        'target' => '{width:600,height:300,title:\'导出\'}',
                        //'id'     => 'export_id',
                        'class'=>'export',
                     ),
                ),
                'title'                 => '交易费用<script>if($$(".finder-list").getElement("tbody").get("html") == "\n" || $$(".finder-list").getElement("tbody").get("html") == "" ){$$(".export").set("href","javascript:;").set("onclick", "alert(\"没有可以生成的数据\")");}else{$$(".export").set("href",\'index.php?app=finance&ctl=analysis_bills&act=index&action=export\');}</script>',
                'use_buildin_recycle'   => false,
                'use_buildin_selectrow' => false,
                'use_buildin_filter'    => false,
            ),
        );
    }

    public function get_type()
    {
        $selfinputs  = array(
            array(
                'type' => array('1' => '按结算时间', '2' => '按业务时间'),
                'label' => '统计时间类型',
                'name' => 'time_type',
                'value' => $this->_params['time_type'],
            ),
        );

        $ui = kernel::single('base_component_ui',null,app::get('finance'));

        foreach ($selfinputs as &$input) {
            $input['html'] = $ui->input($input);
        }

        $inputs = parent::get_type();

        return array_merge((array) $inputs , (array) $selfinputs);
    }

    public function set_params($params) 
    {
        $return = parent::set_params($params);

        $this->_params['time_type'] = isset($this->_params['time_type']) ? $this->_params['time_type'] : '1';

        return $return;
    }
}