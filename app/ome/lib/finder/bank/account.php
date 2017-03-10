<?php

class ome_finder_bank_account
{

	var $column_edit = '操作';
	var $column_edit_width = "100";

	public function column_edit($row)
	{
		$finder_id = $_GET['_finder']['finder_id'];
		return '<a href="index.php?app=ome&ctl=admin_setting&act=add_bank_account&ba_id=' . $row['ba_id'] . '&finder_id='.$finder_id.'" target="dialog::{width:450,height:250,title:\'编辑异常类型\'}">编辑</a>';
	}

}