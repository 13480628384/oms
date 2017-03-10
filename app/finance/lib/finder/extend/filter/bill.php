<?php
class finance_finder_extend_filter_bill{
    function get_extend_colums(){
        $db['bill']=array (
            'columns' => array (
                'status' => array (
                    'type' => kernel::single('finance_bill')->get_name_by_status('',1),
                    'default' => '0',
                    'required' => true,
                    'label' => '核销状态',
                    'width' => 75,
                    'editable' => false,
                    'filtertype' => 'normal',
                    'filterdefault' => true,
                ),
                'charge_status' => array (
                    'type' => kernel::single('finance_bill')->get_name_by_charge_status('',1),
                    'default' => '0',
                    'label' => '记账状态',
                    'width' => 65,
                    'editable' => false,
                    'filtertype' => 'normal',
                    'filterdefault' => true,
                ),
                'monthly_status' => array (
                    'type' => kernel::single('finance_bill')->get_name_by_monthly_status('',1),
                    'default' => '0',
                    'label' => '月结状态',
                    'width' => 65,
                    'editable' => false,
                    'filtertype' => 'normal',
                    'filterdefault' => true,
                ),
            )
        );
        if($_GET['app'] == 'finance' && $_GET['ctl']=='bill' && $_GET['act'] == 'index'){
            unset($db['bill']['columns']['status']);
        }
        return $db;
    }
}

