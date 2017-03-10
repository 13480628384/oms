<?php
/**
 * ¾©¶«¾ÜÊÕ
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_rule_jingdong_jushou{

    public function getParams(){
        $params = array(
            'read_line' => 2000,
            'public' => $public,
        );
        return $params;
    }

    public function getTitle(){
        $title[1] = finance_io_bill_title::getTitle('jingdong_jushou');
        return $title;
    }

    public function isTitle($row,$line){
        $result = true;
        $title = finance_io_bill_title::getTitle('jingdong_jushou');
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
        $notLine = array('1');
        if(!in_array($line,$notLine)){
            $result = false;
        }
        return $result;
    }
}
?>