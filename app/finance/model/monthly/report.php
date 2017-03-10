<?php
/**
 * @copyright shopex.cn
 * @author hanbingshu sanow@126.com
 * @version ocs
 */

class finance_mdl_monthly_report extends dbeav_model{
    
    function  get_schema()
    {
        $schema = parent::get_schema();
        #echo "<pre>";print_r($schema);exit;
        $schema['columns']['should_in']['label'] = '本期应收合计';
        $schema['columns']['should_in']['type'] = 'money';
        $schema['columns']['should_in']['orderby'] = false;
        $schema['columns']['should_in']['order'] = 50;
        $schema['columns']['actual_in']['label'] = '本期实收合计';
        $schema['columns']['actual_in']['type'] = 'money';
        $schema['columns']['actual_in']['orderby'] = false;
        $schema['columns']['actual_in']['order'] = 60;
        $schema['columns']['sum_money']['label'] = '本期费用合计';
        $schema['columns']['sum_money']['type'] = 'money';
        $schema['columns']['sum_money']['orderby'] = false;
        $schema['columns']['sum_money']['order'] = 70;
        $schema['default_in_list'][] = 'should_in';
        $schema['in_list'][] = 'should_in';
        $schema['default_in_list'][] = 'actual_in';
        $schema['in_list'][] = 'actual_in';
        $schema['default_in_list'][] = 'sum_money';
        $schema['in_list'][] = 'sum_money';
        return $schema;
    }

    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $aTmp = parent::getList('*', $filter, $offset, $limit, $orderType);
        $aData = array();
        foreach($aTmp as $kdata=>$vdata){
            $should_in_data = $this->db->selectrow("select sum(money) as money from sdb_finance_ar where trade_time>=".intval($vdata['begin_time'])." and trade_time<".intval($vdata['end_time']));
            $actual_in_data = $this->db->selectrow("select sum(money) as money from sdb_finance_bill where fee_type_id=1 and trade_time>=".intval($vdata['begin_time'])." and trade_time<".intval($vdata['end_time']));
            $sum_money_data = $this->db->selectrow("select sum(money) as money from sdb_finance_bill where fee_type_id!=1 and trade_time>=".intval($vdata['begin_time'])." and trade_time<".intval($vdata['end_time']));
            $arr = $vdata;
            $arr['should_in'] = $should_in_data['money'];
            $arr['actual_in'] = $actual_in_data['money'];
            $arr['sum_money'] = $sum_money_data['money'];
            $aData[] = $arr;
        }
        return $aData;
    }

    function modifier_status($row){
        $status = array('未启用','未结账','已结账');
        return $status[$row];
    }
}
