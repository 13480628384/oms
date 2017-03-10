<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class base_mdl_rpcpoll extends base_db_model{
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        if ($orderby)
            $orderby .= ", calltime DESC";
        else
            $orderby = "calltime DESC";
        $rpc_lists = parent::getList($cols,$filter,$offset,$limit,$orderby);
        if ($rpc_lists)
        {
            foreach ($rpc_lists as &$rpc_info)
            {
                if ($rpc_info['result'])
                {
                    $rpc_info['result'] = unserialize($rpc_info['result']);
                    if ($rpc_info['result'])
                        $rpc_info['result'] = "rsp:" . $rpc_info['result']['rsp'] . ", msg_id:" . $rpc_info['result']['msg_id'] . ", res:" . $rpc_info['result']['res'];
                }
            }
        }
        
        return $rpc_lists;
    }

    public function insertRpc($data,$rpc_id,$type='request')
    {  
        $cache_key = sprintf("rpcpoll-%s-%s",$type,$rpc_id);
        $cache_save = cachecore::store($cache_key, serialize($data), 10800);
        if($cache_save === false){
            $this->insert($data);
        }
    }

    public function updateRpc($data,$rpc_id,$type='request')
    {
        list($id,$calltime) = explode('-', $rpc_id);
        $cache_key = sprintf("rpcpoll-%s-%s",$type,$rpc_id);
        $rpc_res_info = cachecore::fetch($cache_key);
        if ($rpc_res_info === false) {
            $filter = array('id'=>$id,'type'=>$type);
            if ($calltime) $filter['calltime'] = $calltime;

            $this->update($data,$filter);
        } else {
            $rpc_res_info = unserialize($rpc_res_info);
            $rpc_res_info = array_merge($rpc_res_info,$data);
            cachecore::store($cache_key,serialize($rpc_res_info),10800);
        }
    }

    public function deleteRpc($rpc_id,$type='request')
    {
        list($id,$calltime) = explode('-', $rpc_id);
        $cache_key = sprintf("rpcpoll-%s-%s",$type,$rpc_id);
        $rpc_res_info = cachecore::fetch($cache_key);
        if($rpc_res_info === false){
            $filter = array('id'=>$id,'type'=>$type);
            if ($calltime) $filter['calltime'] = $calltime;

            $this->delete($filter);
        }else{
            cachecore::store($cache_key,'',1);
        }
    }

    public function getRpc($rpc_id,$type='request')
    {
        list($id,$calltime) = explode('-', $rpc_id);
        $cache_key = sprintf("rpcpoll-%s-%s",$type,$rpc_id);
        $rpc_res_info = cachecore::fetch($cache_key);
        if($rpc_res_info === false){
            $filter = array('id'=>$id,'type'=>$type);
            if ($calltime) $filter['calltime'] = $calltime;

            $tmp = $this->getList('*', $filter,0,1);
        }else{
            $rpc_res_info = unserialize($rpc_res_info);
            $rpc_res_info['params'] = unserialize($rpc_res_info['params']);
            $tmp = array(0=>$rpc_res_info);
        }

        return $tmp;
    }

    private function _use_memcache()
    {
        return true;
    }
}
