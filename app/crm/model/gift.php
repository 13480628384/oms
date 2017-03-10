<?php
class crm_mdl_gift extends dbeav_model{
    
    public function filter($filter = array()){
        return parent::filter($filter);
    }

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        return parent::getList($cols, $filter, $offset, $limit, $orderby);
    }

    public function count($filter = array()){
        return parent::count($filter);
    }

    public function save($filter = array()){
        return parent::save($filter);
    }

}