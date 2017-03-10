<?php
/**
 * 账单导入逻辑处理基类
 * @copyright shopex.cn
 * @author chenjun 594553417@qq.com
 * @version ocs
 */
class finance_io_bill_process{

    private $importFiletype = '';
    private $importProcessObject = '';

    public function type($importFiletype = 'normal'){
        $this->importFiletype = $importFiletype;

        if (!class_exists('finance_io_bill_process_'.$importFiletype)) {
            return false;
        }

        $this->importProcessObject = kernel::single('finance_io_bill_process_'.$importFiletype);
        return $this;
    }

    public function structure_import_data(&$mdl,$row,&$format_row=array(),&$result){
        $this->importProcessObject->structure_import_data($mdl,$row,$format_row,$result);
    }

    public function checking_import_data(&$mdl,$row,&$result){
        $this->importProcessObject->checking_import_data($mdl,$row,$result);
    }

    public function finish_import_data(&$mdl,$row,&$result){
        $this->importProcessObject->finish_import_data($mdl,$row,$result);
    }

    /**
     * 读取到的数据格式化
     *
     * @param Object $mdl MODEL层对象
     * @param Array $row 读取一行
     * @return void
     * @author 
     **/
    public function getSDf(&$mdl,$row,&$mark)
    {
        return $this->importProcessObject->getSDf($mdl,$row,$mark);
    }

}
?>