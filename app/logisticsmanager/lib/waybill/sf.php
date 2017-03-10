<?php

class logisticsmanager_waybill_sf {

    public static $businessType = array(
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '7' => 7,
        '28' => 28,
        '37' => 37,
        '38' => 38,
    );

    public static $serviceCode = array(
        '37' => 'SFCR',
        '38' => 'SFGR',
    );

    public static $logistics = array(
        '1' => array('code'=>'1','name'=>'标准快递'),
        '2'=>array('code'=>'2','name'=>'顺丰特惠'),
        '3'=>array('code'=>'3','name'=>'电商特惠'),
        '7'=>array('code'=>'7','name'=>'电商速配'),
        '28'=>array('code'=>'28','name'=>'电商专配'),
        '37'=>array('code'=>'37','name'=>'云仓专配次日'),
        '38'=>array('code'=>'38','name'=>'云仓专配隔日'),
    );

    public static $payMethod = array(
        '1' => array('code' => '1', 'name' => '寄方式'),
        '2' => array('code' => '2', 'name' => '收方式'),
        '3' => array('code' => '3', 'name' => '第三方付'),
     );

    /**
     * 获取物流公司编码
     * @param Sring $logistics_code 物流代码
     */
    public function logistics($logistics_code) {

        if (!empty($logistics_code)) {
            return self::$logistics[$logistics_code];
        }
        return self::$logistics;
    }

    public function pay_method($method = '') {

        if (!empty($method)) {
            return self::$payMethod[$method];
        }
        return self::$payMethod;
    }

    public static function getBusinessType($type) {
        return self::$businessType[$type];
    }

    //顺丰承运商服务类型的对应oms本地的物流公司编码
    public static function getLogiCodeByCarrierService($service){
        return self::$serviceCode[$service];
    }

    //根据本地物流公司编码对应顺丰承运商服务类型
    public static function getCarrierServiceByLogiCode($code){
        $codeService = array_flip(self::$serviceCode);
        $service = $codeService[$code];
        if($service){
            return self::$logistics[$service]['name'];
        }else{
            return '';
        }
    }

    public static function getCarrierBycode($code){
        $corpList = array(
            'EMS'=>'中国邮政',
            'JD'=>'京东快递',
            'STO'=>'申通快递',
            'ZJS'=>'宅急送',
            'YUNDA'=>'韵达快递',
            'YTO'=>'圆通快递',
            'ZTO'=>'中通快递',
            'SFYX'=>'顺丰优选',
            'QF'=>'全峰快递',
            'HT'=>'汇通快递',
            'MFZS'=>'魔法自送快递',
            'ZHWL'=>'兆航物流',
            'GTO'=>'国通快递',
            'TTKDE'=>'天天快递',
            'GTO'=>'国通快递',
            'GTO'=>'国通快递',
        );
        if ($corpList[$code]){
            return $corpList[$code];
        }else{
            return '';
        }
    
    }
}