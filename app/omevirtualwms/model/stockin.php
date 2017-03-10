<?php
class omevirtualwms_mdl_stockin extends dbeav_model{

     public function count($filter=null){
        $branch_ids = $this->app->model('allocate')->getBranchidByselfwms();
        $sqlstr = '';
        if ($branch_ids)
         {
            $sqlstr.=" AND branch_id not in (".implode(',',$branch_ids).")";
         }
         $purchase_sql = "select count(*) as _count from sdb_purchase_po where eo_status not in('3') and po_status='1' and check_status='2' and ".$this->_filter($filter).$sqlstr;

         $allocate_sql = "select count(*) as _count from sdb_taoguaniostockorder_iso where iso_status not in('3','4') and check_status='2' and type_id in('4','50','70','200','400') and ".$this->_filter($filter).' and iso_bn not in (select bn from sdb_omevirtualwms_data_status where status != \'PARTIN\' AND type=\'stockin\' and create_time >='.(time()-(WAIT_TIME*60)).')';
         $sql = sprintf('select sum(c._count) as _count from (%s UNION ALL %s) as c',$purchase_sql,$allocate_sql);

         $row = $this->db->selectrow($sql);
         return intval($row['_count']);
    }

    public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $branch_ids = $this->app->model('allocate')->getBranchidByselfwms();
        $sqlstr = '';
        if ($branch_ids)
         {
            $sqlstr.=" AND branch_id not in (".implode(',',$branch_ids).")";
         }
        $purchase_sql = "select po_bn AS bn,check_operator AS operator,purchase_time AS create_time from sdb_purchase_po where eo_status not in('3') and po_status='1' and check_status='2' and ".$this->_filter($filter).$sqlstr;

        $allocate_sql = "select iso_bn AS bn,operator,create_time from sdb_taoguaniostockorder_iso where iso_status not in('3','4') and check_status='2' and type_id in('4','50','70','200','400') and ".$this->_filter($filter).$sqlstr;

        $sql = sprintf('%s UNION ALL %s order by create_time desc',$purchase_sql,$allocate_sql);

        $rows = $this->db->selectLimit($sql,$limit,$offset);
      
        return $rows;
    }

    public function get_schema(){
        $schema = array (
            'columns' => array (
                'bn' => array (
                    'type' => 'varchar(32)',
                    'required' => true,
                    'label' => '入库单号',
                    'editable' => false,
                    'width' =>180,
                ),
                'operator' => array (
                    'type' => 'varchar(100)',
                    'label' => '操作人',
                    'editable' => false,
                    'width' =>100,
                ),
               'create_time' => array (
                    'type' => 'time',
                    'label' => '创建日期',
                    'width' =>160,
                    'editable' => false,
                ),
         
            ),
            'idColumn' => 'bn',
            'in_list' => array (
                0 => 'bn',
                1 => 'operator',
                2 => 'create_time',
               ),
            'default_in_list' => array (
                0 => 'bn',
                1 => 'operator',
                2 => 'create_time',
            ),
        );
        return $schema;
    }

}
