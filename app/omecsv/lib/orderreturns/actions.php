<?php
class omecsv_orderreturns_actions{


    function action_modify(&$actions){
		kernel::log("action_modify = ".$actions);
		foreach($actions as $key=>$action){
			if($action['label']=="导入"){
				 $actions[$key] = array('label'=>app::get('desktop')->_('导入'),'icon'=>'upload.gif','href'=>'index.php?app=omecsv&ctl=admin_import&act=main&ctler=iostock_mdl_orderreturns&add=iostock','target'=>'dialog::{width:400,height:150,title:\''.app::get('desktop')->_('导入').'\'}');
			}
		}

    }



}
?>
