<?php
class ome_view_helper{

    function __construct(&$app){
        $this->app = $app;
    }
    
    public function function_desktop_header($params, &$smarty){
        return $smarty->fetch('admin/include/header.tpl',$this->app->app_id);
    }

    public function function_desktop_footer($params, &$smarty){
        return $smarty->fetch('admin/include/footer.tpl',$this->app->app_id);
    }

}
