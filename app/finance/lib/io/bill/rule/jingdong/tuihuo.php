<?php
/**
 * ¾©¶«ÍË»õ
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_rule_jingdong_tuihuo{

    public function getParams(){
        $params = array(
            'read_line' => 2000,
            'public' => $public,
        );
        return $params;
    }

    public function getTitle(){
        $title[1] = finance_io_bill_title::getTitle('jingdong_tuihuo');
        return $title;
    }

    public function isTitle($row,$line){
        $result = true;
        $title = finance_io_bill_title::getTitle('jingdong_tuihuo');
        foreach($title as $v){
            if(!in_array($v,$row)){
                $result = false;
                break;
            }
        }
        return $result;
    }

    public function isFilterLine($row,$line){
        return false;
    }
}
?>