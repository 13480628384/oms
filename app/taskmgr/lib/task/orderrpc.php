<?php
/**
 * order rpc任务类
 *
 * @author kamisama.xia@gmail.com
 * @version 0.1
 */

class taskmgr_task_orderrpc extends taskmgr_task_abstract {

    protected $_process_id = 'order_bn';

    protected $_gctime = 1800;

    protected $_timeout = 20;
}