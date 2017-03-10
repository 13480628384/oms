<?php
/**
 * oms callback任务类
 *
 * @author kamisama.xia@gmail.com
 * @version 0.1
 */

class taskmgr_task_omecallback extends taskmgr_task_abstract {

    protected $_process_id = 'rpc_id';

    protected $_gctime = 1800;

    protected $_timeout = 20;
}