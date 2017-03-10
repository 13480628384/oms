<?php
class wmsvirtual_ctl_reship extends desktop_controller{

    
    function index()
    {
        $base_filter['is_check'] = '1';
        $branch_ids = kernel::single('wmsvirtual_wms')->get_branch_list();
         if ($branch_ids){
            $base_filter['branch_id'] = $branch_ids;
        }else{
            $base_filter['branch_id'] = 'false';
        }
        $actions[] = array('label' => '获取退货结果',
                            'submit' => 'index.php?app=wmsvirtual&ctl=reship&act=batch_sync', 
                           'target' => 'refresh');
        $params = array(
            'use_buildin_recycle'=>false,
            'base_filter' => $base_filter,
            'actions'=>$actions,
            'title'=>'退货单',
        );
        
        $this->finder('ome_mdl_reship', $params);
    }

    /**
     * 发送至第三方
     * @
     * @
     * @access  public
     * @author sunjing@shopex.cn
     */
    function batch_sync()
    {
        $this->begin('');
        kernel::database()->exec('commit');
        $oReship = app::get('ome')->model('reship');
        $ids = $_POST['reship_id'];
        if (!empty($ids)) {
            foreach ($ids as  $id) {
                $reship = $oReship->dump($id, 'branch_id,out_iso_bn,reship_bn');
                $branch_id = $reship['branch_id'];
                $wms_id = kernel::single('ome_branch')->getWmsIdById($branch_id );
                $data = array(
                    'out_order_code' =>$reship['out_iso_bn'],
                    'stockout_bn'=>$reship['reship_bn'],
                );
                //$result = kernel::single('middleware_wms_request', $wms_id)->reship_search($data, true);
                $result = kernel::single('erpapi_router_request')->set('wms',$wms_id)->reship_search($data);
             
                if ($result['rsp'] == 'success') {
                    $node_id = app::get('channel')->model('channel')->get_node_idBywms($wms_id);
                    $rpc_data = json_decode($result['data'],true);
                    $rpc_data['wms_id'] = $wms_id;
                    kernel::single('wmsvirtual_reship')->result($result['data'],$node_id);
                }
                
            }
        }
        $this->end(true, '命令已经被成功发送！！');
    }

    
    /**
     * Short description.
     * @param   type    $varname    description
     * @return  type    description
     * @access  public
     * @author cyyr24@sina.cn
     */
    function test()
    {
        $wms_id =16;
        $result = array (
  'rsp' => 'success',
  'msg' => NULL,
  'msg_code' => '',
  'data' => 
  array (
    'status_code' => 'WMS_FINISHED',
    'wms_order_code' => 'JCO322399',
    'out_order_code' => '201406051013000686',
  ),
  'msg_id' => '53901608C0A84B760378092389B0F49B',
  'err_msg' => NULL,
  'err_code' => '',
);
                if ($result['rsp'] == 'success') {
                    $node_id = app::get('channel')->model('channel')->get_node_idBywms($wms_id);
                    kernel::single('wmsvirtual_reship')->result($result['data'],$node_id);
                }
    }
}
