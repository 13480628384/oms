﻿新增：
1)开放接口增加直接出入接口创建的接口，支持分销入库和分销出库类型，增加直接出入库、调拨单获取列表的接口 DDGL-309 wjj
2)增加商品类型，用以维护基础物料的相关属属性信息 jira 251 jt
3)仓储中心导航栏目下增加_销售物料店铺冻结列表 jira DDGL-308 wb
4)增加本地赠品促销模块 jt
5)BBC二期售后业务实现 sj
6)顺丰次日配、隔日配快递、仓储新产品对接 sj
7)新增前端销售平台有量对接(发货、库存回写) lzc
 
优化：
1)优化_基础物料数据表物料规格字段_修改为varchar(255)长度 jira: DDGL-310 wb
2)创建订单加入事务_防止批量导入订单时保存订单数据失败_但货品预占及店铺冻结数量增加 jira:DDGL-261 wb
3)归档代码优化添加事务机制，防止执行异常时的数据错乱 DDGL-307 sj
4)销售物料店铺冻结sql语句查询优化 jira: DDGL-311 wb
5)唯品会接口机制调整支持唯品会上自定义外部商家编码 DDGL-312 wjj
6)优化基础物料或销售物料时限制编码和条码只能是由数字英文下划线组成的限制 DDGL-314 wjj
7)淘宝处方药订单，未审核状态，不接受 合并来自云企ERP wkz
8)调整SKU360推送发货单电话为空时未知改为空。因对方验证通不过 sj
9)调整科捷发票金额取订单总额 sj
10)调整分销商品的拉取的请求数量为20，因为接口限制 wb
11)电子面单回写EMS和京东等方法合并去掉多余代码 sj
12)发货单回传weight传空时转化为浮点型 sj
13)处理分派后对话框不自动关闭 jira 287  jt
14)模拟发货单列表状态过滤只取ready progress sj
15)销售单兼容归档订单搜索 sj

Bug：
1)修复供应计划-出入库计划-入库单新建的入库类型下拉框“调拨入库取消”不能作为选择项 DDGL-256 wjj
2)修复第三方仓储的采购退货单出现在自建仓储的采购退货中的问题 DDGL-275 xyj
3)修复火狐45版本，选择三级地区mainland中的text信息取不到的问题 DDGL-286 xyj
4)修复校验绩效已发货统计由于条件不一致导致的统计问题 wkz
5)修复调拨出库单审核时看不到调拨入库的仓库的问题  jira 288 wjj
6)修复到付款订单，开票金额应取应收款，目前取的是订单总额 jira:DDGL-263 sj
7)修复当商品货号添加特殊字符+号时，提示信息没有正常显示的问题 DDGL-276 xyj
8)修复采购退货后，冻结库存被清空 DDGL-303 sj
9)修复天猫供销平台订单总是被自动撤销发货单 DDGL-294 sj