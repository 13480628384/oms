<?php
/**
 +----------------------------------------------------------
 * 安装任务
 +----------------------------------------------------------
 * Author: ExBOY
 * Time: 2014-03-18 $
 * [Ecos!] (C)2003-2014 Shopex Inc.
 +----------------------------------------------------------
 */
class invoice_task
{
    /**
     * 安装中执行
     *
     * @return void
     * @author
     **/
    public function post_install($options)
    {
    	$sql   = "INSERT INTO `sdb_invoice_order_setting` SET sid='1', title='a:5:{i:5;s:12:\"商品明细\";i:6;s:6:\"礼品\";i:7;s:6:\"食品\";i:8;s:12:\"办公用品\";i:9;s:6:\"配件\";}',
                   tax_rate='17', tax_no='123456', bank='中国工商银行上海桂林路支行', bank_no='123456', telphone='021-12345678', 
                   address='上海市徐汇区桂林路396号2号楼', dateline='1397182687'";
        
        kernel::database()->exec($sql);
    }
}