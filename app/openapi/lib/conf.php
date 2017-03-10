<?php

class openapi_conf {

    static public function getMethods(){
        return array(
            'sales' => array(
                'label' => '销售单',
                'methods' => array(
                    'getList' => '销售单列表',
                    'getSalesAmount' => '销售金额',
                ),
            ),
            'aftersales' => array(
                'label' => '售后单',
                'methods' => array(
                    'getList' => '售后单列表',
                ),
            ),
            'iostock' => array(
                'label' => '出入库明细',
                'methods' => array(
                    'getList' => '出入库明细列表',
                ),
            ),
            'po' => array(
                'label' => '采购单',
                'methods' => array(
                    'add' => '新建采购单',
                    'getList'=> '采购单信息',
                ),
            ),
            'transfer' => array(
                'label' => '出入库单',
                'methods' => array(
                    'add' => '新建出入库单',
                    'getList' => '出入库单明细列表',
                ),
            ),
            'pkg' => array(
                'label' => '捆绑商品',
                'methods' => array(
                    'getList' => '捆绑商品列表',
                ),
            ),
            'stock' => array(
                'label' => '库存查询',
                'methods' => array(
                    'getAll'=>'商品库存',
                    'getDetailList'=>'仓库库存',
                )	
            ),
            //ExBOY
            'invoice' => array(
                'label' => '发票操作',
                'methods' => array(
                    'getList' => '待打印发票列表',
                    'update' => '更新发票打印状态',
                ),
            ),
            'goods' => array(
                'label' => '商品',
                'methods' => array(
                    'getList' => '商品列表',
                    'add' => '新建商品',
                    'edit' => '修改商品',
                ),
            ),
           'delivery' => array(
                'label' => '发货单',
                'methods' => array(
                    'getList' => '发货单列表',
                ),
            ),
           'purchasereturn' => array(
                'label' => '采购退货单',
                'methods' => array(
                    'getList' => '采购退货单列表',
                    'add' => '新建采购退货单',
                ),
            ),
            'appropriation' => array(
                'label' => '调拨单',
                'methods' => array(
                    'getList' => '调拨单列表',
                    'add' => '新建调拨单',
                ),
            ),
        );
    }
}