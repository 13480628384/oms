<?php
/**
 * @copyright shopex.cn
 * @author Kris.wang 406048119@qq.com
 * @version ocs
 */

class finance_mdl_verification extends dbeav_model{
    function modifier_type($type){
        return kernel::single('finance_verification')->get_name_by_type($type);
    }

    function _filter($filter,$tableAlias=null,$baseWhere=null){
        if(isset($filter['search_bill_bn'])){
            $veritemObj = app::get('finance')->model('verification_items');
            $rs = $veritemObj->getList('log_id',array('bill_bn'=>$filter['search_bill_bn']));
            foreach($rs as $v){
                $ids[] = $v['log_id'];
            }
            $log_id = implode(',', $ids);
            $where .= ' and log_id in ('.$log_id.")";
            unset($filter['search_bill_bn']);
        }
        $return = parent::_filter($filter,$tableAlias,$baseWhere).$where;
        return $return;
    }

    public function searchOptions(){
        return array(
            'log_bn'=>$this->app->_('核销流水号'),
            'search_bill_bn'=>$this->app->_('单据编号'),
        );
    }
}