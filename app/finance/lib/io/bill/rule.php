<?php
/**
 * 账单导入规则处理基类
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_rule{

    private $importFiletype = '';
    private $importRuleObject = '';

    public function type($importFiletype = 'normal'){
        $this->importFiletype = $importFiletype;
        $this->importRuleObject = kernel::single('finance_io_bill_rule_'.$importFiletype);
        return $this;
    }

    public function getParams(){
        return $this->importRuleObject->getParams();
    }

    public function getTitle(){
        return $this->importRuleObject->getTitle();
    }

    public function isTitle($row,$line){
        return $this->importRuleObject->isTitle($row,$line);
    }

    public function isFilterLine($row,$line){
        return $this->importRuleObject->isFilterLine($row,$line);
    }
}
?>