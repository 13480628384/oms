<?php

class crm_ctl_admin_gift extends desktop_controller{

    var $workground = 'channel_center';
    

    public function __construct($app)
    {
        parent::__construct($app);
        $this->_request = kernel::single('base_component_request');
    }

    public function index(){

        $title = '赠品管理';
        
        $isbind = $this->getCrmInfo();
        
//        if($isbind == 0){
//            $title .= '&nbsp;&nbsp;<span style="color:red;">要正常使用本功能,请先绑定CRM应用</span>';
//        }

        $this->finder('crm_mdl_gift',array(
                'title'=>$title,
                'actions'=>array(             
                        array('label'=>app::get('crm')->_('新增'),'id'=>'crmgift'),                
        //array('label'=>app::get('crm')->_('test'),'target'=>'_blank','href'=>'index.php?app=crm&ctl=admin_gift&act=getProductInfo&dialog=true'),
                        array('label'=>app::get('crm')->_('删除'),'icon' => 'del.gif', 'confirm' =>'确定删除选中项？','submit'=>'index.php?app=crm&ctl=admin_gift&act=delGift',),
                ),
                'use_buildin_recycle'=>false,
                'orderBy' =>'gift_id DESC',
        ));
        $this->addGiftJs();
    }
    

    public function addGiftJs(){

        if($_REQUEST['_finder']['finder_id']){
            $finder_id = $_REQUEST['_finder']['finder_id'];
        }else{
            $finder_id = substr(md5($_SERVER['QUERY_STRING']),5,6);
        }
        

        $html = <<<EOF
        <script>
              $("crmgift").addEvent('click',function(e){
                var url='?app=desktop&act=alertpages&goto='+encodeURIComponent("index.php?app=crm&ctl=admin_gift&act=getProductInfo");
                  Ex_Loader('modedialog',function() {
                  new finderDialog(url,{params:{url:'index.php?app=crm&ctl=admin_gift&act=saveGift',name:'product_id[]'},width:1000,height:500,onCallback:function(rs){
                    if(!rs)return;
                      rs=JSON.decode(rs);
                      if(rs.result == 'succ'){
                         window.finderGroup['{$finder_id}'].refresh();
                         return ;
                      }
                  }});
                  
                });
              });

        </script>
EOF;
        echo $html;exit;
    }

    public function getCrmInfo(){

        $channelObj = app::get('channel')->model('channel');

        $filter = array('channel_type'=>'crm','filter_sql'=>'(node_id is not null and node_id !="")');

        $crmdata = $channelObj->getChannelInfo('count(channel_id) as _count',$filter);

        return $crmdata[0]['_count'];
    }
    
    #构造一个商品列表页面
    public function getProductInfo(){
        
            $base_filter['visibility'] = 'true';
            $base_filter['product_id|notin'] = explode(',',$product_id);

            $params = array(
               'title'=>'商品列表',
               'base_filter' => $base_filter,
               'use_buildin_new_dialog' => false,
                'use_buildin_set_tag'=>false,
                'use_buildin_recycle'=>false,
                'use_buildin_export'=>false,
                'use_buildin_import'=>false,
                'use_buildin_filter'=>true,
                'use_buildin_setcol'=>true,
                'use_buildin_refresh'=>true,
                'orderBy' =>'product_id DESC',
                'alertpage_finder'=>true,
                'use_view_tab' => false,
            );
            return $this->finder('ome_mdl_products',$params);
    }

    function myfinder($object_name,$params){

         header("cache-control: no-store, no-cache, must-revalidate");

        $finder = kernel::single('crm_html',$this);

        foreach($params as $k=>$v){
            $finder->$k = $v;
        }
        $app_id = substr($object_name,0,strpos($object_name,'_'));
        $app = app::get($app_id);
        $finder->app = $app;
        $finder->work($object_name);    
    }

    public function saveGift(){
        $productids = $this->_request->get_post('product_id');
    
        if(empty($productids)){
           echo json_encode(array('result'=>'fail'));exit;
        } 
       
        $giftObj = $this->app->model('gift');
    
        $productObj = app::get('ome')->model('products');

        $products = $productObj->getList('product_id,bn as gift_bn,name as gift_name,spec_info',array('product_id|in'=>$productids));
        
        $gifts = $giftObj->getList('gift_id,product_id',array('product_id|in'=>$productids));
        
        foreach($gifts as $v){
             $gift[$v['product_id']] = $v['gift_id'];
        }

        $data = array();

        foreach((array)$products as $k=>$v){
            $data[$k]['product_id'] = $v['product_id'];
            $data[$k]['gift_bn'] = $v['gift_bn'];
            $data[$k]['gift_name'] = $v['gift_name'];
            $data[$k]['spec_info'] = $v['spec_info'];
            $data[$k]['gift_id'] = $gift[$v['product_id']];
            $giftObj->save($data[$k]);
        }

        echo json_encode(array('result'=>'succ'));exit;
    }

    public function delGift(){
       
       $this->begin('index.php?app=crm&ctl=admin_gift&act=index');

       $giftObj = $this->app->model('gift');

       $isSelectedAll = $this->_request->get_post('isSelectedAll');
       
       $giftids = $this->_request->get_post('gift_id');

       if($isSelectedAll != '_ALL_' && $giftids){
           $gift_id = array('gift_id'=>$giftids);
       }elseif($giftids){
           $gift_id = array();
       }else{
           $this->end(false,$this->app->_('请选择赠品!'));
       }
       $product_id = $giftObj->getList('product_id',array('gift_id'=>$giftids));
       $product_ids = array_map('current',$product_id);
        
       #检测是否有赠品记录
       $obj_order_items = app::get('ome')->model('order_items');
       $_rs = $obj_order_items->getList('count(item_id) as count',array('product_id'=>$product_ids,'itemp_type'=>'gift','shop_product_id'=>'-1'));
       if($_rs[0]['count'] > 0){
           $this->end(false,$this->app->_('赠品有使用记录,不能删除!'));
       }
       

       if($giftObj->delete($gift_id)){
          $this->end(true, $this->app->_('删除成功'));
       }else{
          $this->end(false, $this->app->_('删除失败'));
       }
       
    }

    function edit($gift_id=0)
    {
        if($_POST){
            $this->begin('index.php?app=crm&ctl=admin_gift&act=index');
            $data = $_POST;
            $data['gift_id'] = intval($data['id']);
            $data['update_time'] = time();
            $this->app->model('gift')->save($data);
            $this->end(true, '保存成功');
        }

        ///var_dump($_GET['_finder']);
        if($gift_id>0){
            $rs = $this->app->model('gift')->dump($gift_id);
            //var_dump($rs);
            $this->pagedata['rs'] = $rs;
            $this->display('admin/gift/edit.html');
        }else{
            echo('gift_id error.');
        }
    }

}