<?php
class wmsvirtual_wms 
{
    
    
    /**
     * 返回仓库列表
     * 
     * @return  array
     * @access  public
     * @author sunjing@shopex.cn
     */
    function get_branch_list()
    {
        $branchObj = &app::get('ome')->model('branch');
        $channelObj = &app::get('channel')->model('channel');
        $node_type = array('jd_wms');
        $wms_id = $channelObj->get_wmd_idBynodetype($node_type);

        $branchs = $branchObj->getList('branch_id',array('wms_id'=>$wms_id),0,-1);
        
         $branch_ids = array();
         foreach ($branchs as $branch ) {
             $branch_ids[] = $branch['branch_id'];
         }
         return $branch_ids;
    }
    
} 

?>