<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_application_permission extends desktop_application_prototype_xml{

    var $xml='desktop.xml';
    var $xsd='desktop_content';
    var $path = 'permissions/permission';

    function current(){
        $this->current = $this->iterator()->current();
        $this->key = $this->current['id'];
        return $this;
    }

    function row($fag,$key){
        $row = array(
            'menu_type' => $this->content_typename(),
            'app_id'=>$this->target_app->app_id,
                );
        $row['menu_title'] = $this->current['value'];
        $row['display'] =  $this->current['display'];
        $access_opct = array(
            'show' => $this->current['show'],
            'save' => $this->current['save'],
                );
        $row['addon'] = serialize($access_opct);
        $row['permission'] = $this->current['id']; 
        return $row;
    }

    
    function install(){    
        kernel::log('Installing '.$this->content_typename().' '.$this->key());
        $menus_row = $this->row($fag,$key);
        return app::get('desktop')->model('menus')->insert($menus_row);
    }
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('desktop')->model('menus')->delete(array(
            'app_id'=>$app_id,'menu_type' => $this->content_typename()));
    }
    
}
