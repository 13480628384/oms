<?php
/**
 * Ò»ºÅµê
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_rule_yihaodian{

    public function getParams(){
        $params = array(
            'read_line' => 2000,
            'public' => $public,
        );
        return $params;
    }

    public function getTitle(){
        $title[1] = finance_io_bill_title::getTitle('yihaodian');
        return $title;
    }

    public function isTitle($row,$line){
        $result = true;
        $title = finance_io_bill_title::getTitle('yihaodian');
        foreach($title as $v){
            if(!in_array($v,$row)){
                $result = false;
                break;
            }
        }
        return $result;
    }

    public function isFilterLine($row,$line){
        $result = true;
        $notLine = array('1','2','3');
        if(!in_array($line,$notLine)){
            $result = false;
        }
        return $result;
    }

}
?>