<?php
/**
 * 店铺冻结明细查询列表
 *
 * @access public
 * @author wangbiao<wangbiao@shopex.cn>
 * @version $Id: shopfreezelog.php 2016-01-18 13:00
 */
class console_ctl_admin_shopfreezelog extends desktop_controller
{
    public function index()
    {
        $this->title = '店铺冻结明细查询';
        
        $params = array(
            'title' => $this->title,
            'finder_cols' => '*',
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>false,
            'orderBy' => 'product_id DESC, log_id DESC',
        );
        
        $this->finder('console_mdl_shopfreezelog', $params);
    }
}
