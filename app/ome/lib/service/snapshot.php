<?php
/**
 * User: jintao
 * Date: 2016/2/1
 * Time: 15:53
 */
class ome_service_snapshot
{


    public function gift_rule_change($sdf)
    {
        $snapshot_mdl = app::get('ome')->model('snapshot');

        $data = array(
            'task_id' => $sdf['task_id'],
            'type'    => 1,
            'title'   => $sdf['title'],
            'content' => mb_substr($sdf['content'],0, 1000, 'utf-8'),
            'op_user' => kernel::single('desktop_user')->get_name(),
            'create_time' => date('Y-m-d H:i:s'),
        );

        $snapshot_mdl->insert($data);
        return true;
    }

}