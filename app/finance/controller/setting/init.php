<?php
class finance_ctl_setting_init extends desktop_controller{
    var $name = "期初初始化";

    public function index(){
        #账单起始年月日
        $tyear = date('Y');
        for($y=$tyear-5;$y<=$tyear+5;$y++){
             $year[$y] = $y.'年';
        }
        for($m=1;$m<=12;$m++){
             $month[$m] = $m.'月';
        }
        for($d=1;$d<=28;$d++){
             $day[$d] = $d.'日';
        }
        $init_time = app::get('finance')->getConf('finance_setting_init_time');
        //$init_time = array();app::get('finance')->setConf('finance_setting_init_time',$init_time);
        
        #判断是否可更改初始化时间
        $finance_ar_mdl = app::get('finance')->model('ar');
        $finance_bill_mdl = app::get('finance')->model('bill');
        $filter['charge_status'] = 0;
        if($finance_ar_mdl->count($filter) > 0 || $finance_bill_mdl->count($filter) > 0) $isSaveInitTime = 'false';
        else $isSaveInitTime = 'true';

        $this->pagedata['year'] = $year;
        $this->pagedata['month'] = $month;
        $this->pagedata['day'] = $day;
        $this->pagedata['init_time'] = $init_time;
        $this->pagedata['isSaveInitTime'] = $isSaveInitTime;
        $this->pagedata['isInit'] = $init_time['flag'];

        $this->pagedata['canImport'] = in_array($_SERVER['SERVER_NAME'],array('mmfs.erp.shopexdrp.cn','jylmall.erp.shopexdrp.cn')) ? 'true' : 'false';

        $this->page('setting/init.html');
    }

    public function save_init_time(){
        if($_POST){
            $this->begin('index.php#app=finance&ctl=setting_init&act=index');
            $init_time = $_POST['init_time'];
            $init_time['flag'] = 'false';
            app::get('finance')->setConf('finance_setting_init_time',$init_time);
            $this->end(true);
        }
    }

    public function save_init(){
        if($_POST){
            $this->begin('index.php#app=finance&ctl=setting_init&act=index');
            $isInit = $_POST['isInit'];
            if($isInit == 'true'){
                $rs = kernel::single('finance_monthly_report')->set_init_charge();
                if($rs == 'true'){
                    $init_time = app::get('finance')->getConf('finance_setting_init_time');
                    $init_time['flag'] = 'true';
                    app::get('finance')->setConf('finance_setting_init_time',$init_time);
                    $this->end(true);
                }
            }
            $this->end(false);
        }
    }

    public function exportTemplate_act($filter = ''){
        return $this->fetch('setting/export.html');
    }


}