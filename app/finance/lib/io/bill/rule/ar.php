<?php
/**
 * 销售账单应收导入
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_rule_ar{

    public function getParams(){
        $params = array(
            'read_line' => 2000,
            'relation' => array(
                'mfkey' => array(
                    1 => array('mkey'=>'*:业务流水号','fkey'=>'*:业务流水号'),
                    2 => array('mkey'=>'*:业务流水号','fkey'=>'*:业务流水号'),
                ),
            ),
            'public' => $public,
        );
        return $params;
    }

    public function getTitle(){
        $title = finance_io_bill_title::getTitle('ar');
        return $title;
    }

    public function isTitle($row,$line){
        return $sp = strpos(implode(',',$row),'*:')===false?false:true;
    }

    public function isFilterLine($row,$line){
        return false;
    }
}
?>