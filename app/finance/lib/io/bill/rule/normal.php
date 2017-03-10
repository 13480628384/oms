<?php
/**
 * 销售账单实收导入
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_rule_normal{

    public function getParams($public = array()){
        $params = array(
            'read_line' => 2000,
            'public' => $public,
        );
        return $params;
    }

    public function getTitle(){
        $title[1] = finance_io_bill_title::getTitle('normal');
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