<?php
/**
 * wms rpc任务类
 *
 * @author kamisama.xia@gmail.com
 * @version 0.1
 */

class taskmgr_task_wmsrpc extends taskmgr_task_abstract {

    protected $_process_id = 'delivery_bn';

    protected $_gctime = 1800;

    protected $_timeout = 20;
}