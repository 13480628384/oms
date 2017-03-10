<?php
class tgkpi_ctl_admin_analysis extends desktop_controller{

    public function pick(){ //捡货绩效统计
        kernel::single('tgkpi_analysis_pick')->set_params($_POST)->display();
    }

    public function check(){
        kernel::single('tgkpi_analysis_check')->set_params($_POST)->display();
    }

    public function reason(){
        kernel::single('tgkpi_analysis_reason')->set_params($_POST)->display();
    }
    #发货统计
    public function delivery(){
        kernel::single('tgkpi_analysis_delivery')->set_params($_POST)->display();
    }

}