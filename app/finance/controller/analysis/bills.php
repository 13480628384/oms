<?php
/**
* 费用统计，由API获取平台数据
*
* @category finance
* @package finance/constroller/ananlysis
* @author chenping<chenping@shopex.cn>
* @version $Id: bills.php 2013-10-11 17:23Z
*/
class finance_ctl_analysis_bills extends desktop_controller
{
    public function index()
    {
        foreach ($_POST as $k => $v) {
            if (!is_array($v) && $v !== false)
                $_POST[$k] = trim($v);
            if ($_POST[$k] === '') {
                unset($_POST[$k]);
            }
        }

        $this->getAnalysisObject()->set_params($_POST)->display();
    }

    private function getAnalysisObject(){
        
        if($_GET['view'] == 1){
            $obj = kernel::single('finance_analysis_bookbills');
        } elseif($_GET['view'] == 0){
            $obj = kernel::single('finance_analysis_bills');
        }
        return $obj;
    }

    function _views(){
        //$_GET['view_from'] = 1;
        $views = array(
            0 => array(
                'label' => '交易费用',
                'url' => '',
                'optional' => true,
                'addon' => 'tabshow',
            ),
            1 => array(
                'label' => '运营费用',
                'url' => '',
                'optional' => true,
                'addon' => 'tabshow',
            ),
        );
        
        return $views;
    }
}