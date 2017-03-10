<?php
/**
 * @author ykm 2015-12-22
 * @describe 配置字段与方法（单表）的映射关系
 */

class logisticsmanager_print_mapping {

    private $fieldMapping = array(
        'ship_name'   => array('delivery.ship_name'),
        'ship_area_0' => array('delivery.ship_province'),
        'ship_area_1' => array('delivery.ship_city'),
        'ship_area_2' => array('delivery.ship_district'),
        'ship_addr'   => array('delivery.ship_addr'),
        'ship_addr_mark'  => array('delivery.ship_addr', 'delivery.bracket_memo'),
        'ship_detailaddr' => array('delivery.ship_province', 'delivery.ship_city', 'delivery.ship_district', 'delivery.ship_addr'),
        'ship_detailaddr_mark' => array('delivery.ship_province', 'delivery.ship_city', 'delivery.ship_district', 'delivery.ship_addr', 'delivery.bracket_memo'),
        'delivery_bn' => array('delivery.delivery_bn'),
        'logi_no' => array('delivery.logi_no'),
        'ship_tel'    => array('delivery.ship_tel'),
        'ship_mobile' => array('delivery.ship_mobile'),
        'ship_zip'    => array('delivery.ship_zip'),
        'dly_name'    => array('shop.default_sender'),
        'dly_area_0'  => array('shop.area_0'),
        'dly_area_1'  => array('shop.area_1'),
        'dly_area_2'  => array('shop.area_2'),
        'dly_address' => array('shop.addr'),
        'dly_detailaddr' => array('shop.area_all','shop.addr'),
        'dly_tel'     => array('shop.tel'),
        'dly_mobile'  => array('shop.mobile'),
        'dly_zip'     => array('shop.zip'),
        'date_y'      => array('date.Y'),
        'date_m'      => array('date.m'),
        'date_d'      => array('date.d'),
        'date_ymd'    => array('date.Y-m-d'),
        'date_h'      => array('date.H'),
        'date_i'      => array('date.i'),
        'date_s'      => array('date.s'),
        'order_bn'    => array('order.order_bn'),
        'order_count' => array('deliveryItems.order_count'),
        'delyvery_memo' => array('delivery.memo'),
        'delivery_order_amount' => array('order.delivery_order_amount'),
        'delivery_order_amount_d' => array('order.delivery_order_amount_d'),
        'delivery_receivable' => array('order.delivery_receivable'),
        'delivery_receivable_d' => array('order.delivery_receivable_d'),
        'order_memo'  => array('order.order_memo'),
        'order_custom' => array('order.order_custom'),
        'shop_name'   => array('shop.name'),
        'bn_spec_num_n' => array('deliveryItems.bn', '  ', 'deliveryItems.spec_info', " x ", 'deliveryItems.number', ','),
        'bn_spec_num_y' => array('deliveryItems.bn', '  ', 'deliveryItems.spec_info', " x ", 'deliveryItems.number', "\r\n"),
        'goodsbn_spec_num_n' => array('deliveryItems.goodsbn', '  ', 'deliveryItems.addon', " x ", 'deliveryItems.number', ','),
        'goodsbn_spec_num_y' => array('deliveryItems.goodsbn', '  ', 'deliveryItems.addon', " x ", 'deliveryItems.number', "\n"),
        'member_uname' => array('member.uname'),
        'bn_amount_n' => array('deliveryItems.bn', ' x ', 'deliveryItems.number',  ' ', 'deliveryItems.lastField_count'),
        'name_amount_n' => array('deliveryItems.name', 'deliveryItems.addon', ' x ', 'deliveryItems.number', ' ', 'deliveryItems.lastField_count'),
        'bn_name_amount_n' => array('deliveryItems.bn', ' ：', 'deliveryItems.name', 'deliveryItems.addon', ' x ', 'deliveryItems.number', ' ', 'deliveryItems.lastField_count'),
        'bn_amount' => array("货号：", 'deliveryItems.bn', " 数量：", 'deliveryItems.number', "\n"),
        'name_amount' => array("货品名：", 'deliveryItems.name', 'deliveryItems.addon', " 数量：", 'deliveryItems.number', "\n"),
        'bn_name_amount' => array("货号：", 'deliveryItems.bn', " 货品名：", 'deliveryItems.name', 'deliveryItems.addon', " 数量：", 'deliveryItems.number', "\n"),
        'bn_amount_pos' => array('deliveryItems.bn', " x ", 'deliveryItems.number', ' - ', 'deliveryItems.pos', "\n"),
        'name_amount_pos' => array('deliveryItems.name', 'deliveryItems.addon', " x ", 'deliveryItems.number', ' - ', 'deliveryItems.pos', "\n"),
        'bn_name_amount_pos' => array('deliveryItems.bn', " ：", 'deliveryItems.name', 'deliveryItems.addon', " x ", 'deliveryItems.number', ' - ', 'deliveryItems.pos', "\n"),
        'print_no' => array('queueItems.print_no'),
        'name_spec_amount' => array('deliveryItems.name', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', "  "),
        'bn_name_spec_amount' => array('deliveryItems.bn', "：", 'deliveryItems.name', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', ','),
        'bn_name_spec_amount_y' => array('deliveryItems.bn', "：", 'deliveryItems.name', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', "\r\n"),
        'new_bn_name_amount' => array('deliveryItems.name', " x ", 'deliveryItems.number', ','),
        'new_bn_name_amount_n' => array('deliveryItems.name', " x ", 'deliveryItems.number', "\n"),
        'bn_spec_num'=>array('deliveryItems.bn', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', ','),
        'productbn_spec_num_n'=>array('deliveryItems.bn', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', "\n"),
        'total_product_weight_g'=>array('deliveryItems.total_product_weight_g'),
        'total_product_weight_kg'=>array('deliveryItems.total_product_weight_kg'),
        'productname_spec_num_n'=>array('deliveryItems.name', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', "\n"),
        'productname_spec_num'=>array('deliveryItems.name', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', ','),
        'bn_productname_spec_num_pos_n'=>array('deliveryItems.bn', "：", 'deliveryItems.name', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', '-', 'deliveryItems.pos', "\n"),
        'bn_productname_spec_num_pos'=>array('deliveryItems.bn', "：", 'deliveryItems.name', "  ", 'deliveryItems.addon', " x ", 'deliveryItems.number', '-', 'deliveryItems.pos', ','),
        'goods_bn'=>array('deliveryItems.goodsbn', "\n"),
        'pkgbn_num_n'=>array('order.pkgbn_num', "\n"),
        'pkgbn_num' => array('order.pkgbn_num', ','),
        'pkgname_bn_spec_num_n' => array('order.pkgname_bn_spec_num', "\n"),
        'pkg_productname_num_n'=>array('order.pkg_productname_num', "\n"),
        'pkg_productname_num' =>array('order.pkg_productname_num', ','),
        'pkg_productname_bn_num_n'=>array('order.pkg_productname_bn_num', "\n"),
        'pkg_productname_bn_num' => array('order.pkg_productname_bn_num', ','),
        'normal_good_n' => array('order.normal_good', "\n"),
        'normal_good' => array('order.normal_good', ','),
        'normal_productname_bn_num_n' => array('order.normal_productname_bn_num', "\n"),
        'normal_productname_bn_num' => array('order.normal_productname_bn_num', ','),
        'sfcity_code'=>array('delivery.sfcity_code'),
        'mailno_position' => array('waybill.position'),
        'mailno_position_no' => array('waybill.position_no'),
        'package_wdjc' => array('waybill.package_wdjc'),
        'package_number' => array('delivery.package_number'),
        'batch_logi_no' => array('delivery.batch_logi_no'),
        'cloud_stack_position' => array('shop.cloud_stack_position'),
    );
    //不在单据打印项中，模板渲染需使用
    private $fieldMappingExtend = array(
        'seller_id' => array('channel.seller_id'),
        'mailno_barcode' => array('waybill.mailno_barcode'),
        'package_wd' => array('waybill.package_wd'),
        'mailno_qrcode' => array('waybill.qrcode'),
        'print_config' => array('waybill.print_config'),
        'cp_code' => array('corp.type'),
    );
    //自定义字段 需要优先截去的字段，请放置在更上面
    private $fieldMappingDefined = array(
        'goods_bn2' => array('deliveryItems.goodsbn', "  "),
        'goods_bn' => array('deliveryItems.goodsbn', "  "),
        'new_bn_name' => array('deliveryItems.name', "  "),
        'bn' => array('deliveryItems.bn', "  "),
        'pos' => array('deliveryItems.pos', "  "),
        'name' => array('deliveryItems.name', "  "),
        'spec' => array('deliveryItems.spec_info', "  "),
        'amount' => array('deliveryItems.number', "  "),
        'n' => array("\n", '  ')
    );

    public function getAllField() {
        return array_merge(array_keys($this->fieldMapping),array_keys($this->fieldMappingExtend),array(implode('_', array_keys($this->fieldMappingDefined))));
    }

    public function getFieldMethod($arrField) {
        $ret = array();
        foreach($arrField as $val) {
            if($this->fieldMapping[$val]) {
                $this->fieldMappingMethod($this->fieldMapping[$val], $ret);
            } elseif ($this->fieldMappingExtend[$val]) {
                $this->fieldMappingMethod($this->fieldMappingExtend[$val], $ret);
            } else {
                $definedField = $this->isAutoDefined($val);
                if($definedField) {
                    foreach($definedField as $tmp) {
                        $this->fieldMappingMethod($this->fieldMappingDefined[$tmp], $ret);
                    }
                }
            }
        }
        return $ret;
    }

    public function getFieldData($arrField, $oriRowData) {
        $arrRet = array();
        foreach($arrField as $val) {
            if($this->fieldMapping[$val]) {
                $arrRet[$val] = $this->fieldMappingData($this->fieldMapping[$val], $oriRowData);
            } elseif ($this->fieldMappingExtend[$val]) {
                $arrRet[$val] = $this->fieldMappingData($this->fieldMappingExtend[$val], $oriRowData);
            } else {
                $definedField = $this->isAutoDefined($val);
                if($definedField) {
                    $arrRet[$val] = $this->autoDefinedData($definedField, $oriRowData);
                } else {
                    $arrRet[$val] = isset($oriRowData[$val]) ? $oriRowData[$val] : $val;
                }
            }
        }
        return $arrRet;
    }

    private function fieldMappingMethod($fieldArr, &$ret) {
        foreach($fieldArr as $val) {
            if(strpos($val, '.')) {
                $arr = explode('.', $val);
                if(!in_array($arr[1], $ret[$arr[0]])) {
                    $ret[$arr[0]][] = $arr[1];
                }
            }
        }
    }

    private function isAutoDefined($val) {
        static $definedField = array();
        if(isset($definedField[$val])) {
            return $definedField[$val];
        }
        $oldVal = $val;
        $definedKey = array_keys($this->fieldMappingDefined);
        $hasField = array();
        foreach($definedKey as $key) {
            $index = strpos($val, $key);
            if($index !== false) {
                $hasField[] = $key;
                $val = str_replace($key, '', $val);
            }
        }
        $val = str_replace('_', '', $val);
        if(empty($val)) {
            $definedField[$oldVal] = $hasField;
        } else {
            $definedField[$oldVal] = false;
        }
        return $definedField[$oldVal];
    }

    private function fieldMappingData($mapping, $oriRowData) {
        $ret = '';
        foreach($mapping as $k => $val) {
            if(is_array($oriRowData[$val])) {
                $index = array_keys($oriRowData[$val]);
                return $this->fieldMappingDataArr($mapping, $oriRowData, $index);
            } elseif(isset($oriRowData[$val])) {
                $ret .= $oriRowData[$val];
            } else {
                $ret .= $val;
            }
        }
        return $ret;
    }

    private function fieldMappingDataArr($mapping, $oriRowData, $arrIndex) {
        $ret = $end = '';
        foreach($arrIndex as $index) {
            foreach ($mapping as $k => $val) {
                if(isset($oriRowData[$val])) {
                    if(is_array($oriRowData[$val])) {
                        $ret .= $oriRowData[$val][$index];
                    } elseif(strpos($val, 'lastField_')) {
                        $end = $oriRowData[$val];
                    } else {
                        $ret .= $oriRowData[$val];
                    }
                } else {
                    $ret .= $val;
                }
            }
        }
        $last = end($mapping);
        if(!$oriRowData[$last]) {
            $ret = trim($ret, $last);
        } elseif($end) {
            $ret .= $end;
        }
        return $ret;
    }

    private function autoDefinedData($arrField, $oriRowData) {
        $mapping = array();
        $arrIndex = array();
        foreach($arrField as $field) {
            foreach($this->fieldMappingDefined[$field] as $value) {
                if($oriRowData[$value] && empty($arrIndex)) {
                    $arrIndex = array_keys($oriRowData[$value]);
                }
                array_push($mapping, $value);
            }
        }
        return $this->fieldMappingDataArr($mapping, $oriRowData, $arrIndex);
    }
}