<?php
class ome_ctl_admin_member extends desktop_controller{
    var $name = "会员";
    var $workground = "invoice_center";

    function index(){
        $this->finder('ome_mdl_members',array(
            'title' => '会员管理',
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>true,
            'actions' => array(
                    array(
                        'label' => '添加会员',
                        'href' => 'index.php?app=ome&ctl=admin_member&act=addMember',
                        'target' => "_blank",
                    ),
            ),
       ));
    }
    
    function addMember(){                                                                                                                         
        $this->display("admin/member/add_member.html");
    }

    function editMember($member_id){
        $objMember = $this->app->model('members');
        $member = $objMember->dump(array('member_id'=>$member_id));
        $this->pagedata['member'] = $member;
        $this->pagedata['flag'] = md5($member['member_id']);
        $this->display("admin/member/add_member.html");
    }
    
    function doAddMember(){
        $post = $_POST;
        $sameName = array('uname'=>$post['account']['uname']);
        $mObj = $this->app->model("members");
        if($post['member_id']) {
            if(md5($post['member_id']) != $post['flag']) {
                echo json_encode(array('succ'=>'false', 'msg'=>'编辑失败，请重新点击编辑'));
                exit();
            }
            unset($post['flag']);
            if(!$mObj->dump(array('member_id'=>$post['member_id']))) {
                echo json_encode(array('succ'=>'false', 'msg'=>'编辑失败，没有这个用户'));
                exit();
            }
            $sameName['member_id|noequal'] = $post['member_id'];
        }
        $member = $mObj->dump($sameName,'member_id');
        if ($member){
            echo json_encode(array('succ'=>'false', 'msg'=>'操作会员失败，可能用户名重复'));
            exit;
        }
        $mem = $post;
        if ($mObj->save($mem)){
            $data = $mObj->getList('member_id,uname,area,mobile,email,sex',array('member_id'=>$mem['member_id']),0,-1);
            if ($data)
            foreach ($data as $k => $v){
                $data[$k]['sex'] = ($v['sex']=='male') ? '男' : '女';
            }
            echo json_encode($data);
        }else{
            echo json_encode(array('succ'=>'false', 'msg'=>'操作失败'));
        }
    }
    
    
}
?>
