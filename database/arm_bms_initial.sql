/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : arm_bms

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-02-13 20:56:26
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `bms_system_assigned_group`
-- ----------------------------
DROP TABLE IF EXISTS `bms_system_assigned_group`;
CREATE TABLE `bms_system_assigned_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_group` int(11) NOT NULL,
  `revision` int(4) NOT NULL DEFAULT '1',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bms_system_assigned_group
-- ----------------------------
INSERT INTO `bms_system_assigned_group` VALUES ('1', '1', '1', '1', '0', '0');
INSERT INTO `bms_system_assigned_group` VALUES ('2', '2', '2', '1', '1455798431', '1');
INSERT INTO `bms_system_assigned_group` VALUES ('3', '16', '8', '1', '1460517103', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('4', '5', '2', '1', '1460870674', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('5', '18', '3', '1', '1460870692', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('6', '21', '4', '1', '1461057648', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('7', '22', '5', '1', '1461057659', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('8', '26', '6', '1', '1461057668', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('9', '23', '5', '1', '1461057678', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('10', '42', '7', '1', '1461057690', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('11', '41', '7', '1', '1463195426', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('12', '15', '9', '1', '1464057817', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('13', '73', '7', '1', '1464061308', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('14', '74', '7', '1', '1464061321', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('15', '75', '7', '1', '1464061336', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('16', '25', '7', '1', '1464062795', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('17', '27', '7', '1', '1464062812', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('18', '29', '7', '1', '1464062832', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('19', '30', '7', '1', '1464062852', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('20', '37', '7', '1', '1464062936', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('21', '40', '7', '1', '1464062956', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('22', '44', '7', '1', '1464062985', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('23', '49', '7', '1', '1464063003', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('24', '76', '7', '1', '1464065836', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('25', '31', '7', '1', '1464080105', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('26', '33', '7', '1', '1464080130', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('27', '34', '7', '2', '1464080148', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('28', '51', '7', '2', '1464080250', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('29', '32', '7', '1', '1464152427', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('30', '24', '6', '1', '1464158194', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('31', '51', '7', '1', '1464159367', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('32', '28', '7', '1', '1464159397', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('33', '46', '6', '1', '1464159465', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('34', '36', '7', '1', '1464159495', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('35', '38', '7', '1', '1464159516', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('36', '45', '7', '1', '1464159545', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('37', '47', '7', '1', '1464159568', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('38', '77', '5', '2', '1464402109', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('39', '78', '6', '1', '1464423669', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('40', '79', '6', '1', '1464423679', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('41', '80', '7', '1', '1464423697', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('42', '81', '6', '1', '1465358305', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('43', '4', '10', '1', '1465961279', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('44', '82', '7', '1', '1466495929', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('45', '83', '7', '1', '1466495944', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('46', '84', '7', '1', '1466496018', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('47', '85', '6', '2', '1468289697', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('48', '86', '6', '1', '1468297678', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('49', '88', '5', '1', '1468996946', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('50', '89', '6', '1', '1469065642', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('51', '91', '7', '1', '1471317848', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('52', '92', '11', '1', '1471857277', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('53', '93', '11', '1', '1471857292', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('54', '62', '12', '1', '1472281412', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('55', '94', '7', '1', '1473064853', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('56', '95', '7', '1', '1473348219', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('57', '97', '6', '1', '1474259132', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('58', '96', '7', '1', '1474259142', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('59', '98', '11', '1', '1475291800', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('60', '99', '13', '1', '1477109591', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('61', '103', '7', '1', '1477816588', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('62', '104', '5', '1', '1478401990', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('63', '3', '6', '1', '1484971056', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('64', '108', '7', '1', '1485940954', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('65', '109', '7', '1', '1486457635', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('66', '110', '7', '1', '1486457651', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('67', '111', '7', '1', '1486457665', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('68', '112', '7', '1', '1486457676', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('69', '113', '7', '1', '1486457686', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('70', '114', '7', '1', '1486457697', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('71', '115', '7', '1', '1486457706', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('72', '116', '7', '1', '1486457729', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('73', '106', '8', '1', '1486457748', '2');
INSERT INTO `bms_system_assigned_group` VALUES ('74', '118', '12', '1', '1486805342', '2');

-- ----------------------------
-- Table structure for `bms_system_history`
-- ----------------------------
DROP TABLE IF EXISTS `bms_system_history`;
CREATE TABLE `bms_system_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(255) DEFAULT NULL,
  `table_id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `data` varchar(255) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `action` varchar(20) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bms_system_history
-- ----------------------------

-- ----------------------------
-- Table structure for `bms_system_history_hack`
-- ----------------------------
DROP TABLE IF EXISTS `bms_system_history_hack`;
CREATE TABLE `bms_system_history_hack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `controller` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT 'Active',
  `action_id` int(11) DEFAULT '99',
  `other_info` text,
  `date_created` int(11) DEFAULT '0',
  `date_created_string` varchar(255) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bms_system_history_hack
-- ----------------------------

-- ----------------------------
-- Table structure for `bms_system_site_offline`
-- ----------------------------
DROP TABLE IF EXISTS `bms_system_site_offline`;
CREATE TABLE `bms_system_site_offline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bms_system_site_offline
-- ----------------------------

-- ----------------------------
-- Table structure for `bms_system_task`
-- ----------------------------
DROP TABLE IF EXISTS `bms_system_task`;
CREATE TABLE `bms_system_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'TASK',
  `parent` int(11) NOT NULL DEFAULT '0',
  `controller` varchar(500) NOT NULL,
  `ordering` smallint(6) NOT NULL DEFAULT '9999',
  `icon` varchar(255) NOT NULL DEFAULT 'menu.png',
  `status` varchar(11) NOT NULL DEFAULT 'Active',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  `date_updated` int(11) DEFAULT NULL,
  `user_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bms_system_task
-- ----------------------------
INSERT INTO `bms_system_task` VALUES ('1', 'Settings', 'MODULE', '0', '', '1', 'menu.png', 'Active', '1455625924', '1', '1472629569', '1');
INSERT INTO `bms_system_task` VALUES ('2', 'Module & Task', 'TASK', '1', 'Sys_module_task', '1', 'menu.png', 'Active', '1455625924', '1', '1455625924', '1');
INSERT INTO `bms_system_task` VALUES ('3', 'User Role', 'TASK', '1', 'Sys_user_role', '2', 'menu.png', 'Active', '1455625924', '1', '1455625924', '1');
INSERT INTO `bms_system_task` VALUES ('4', 'User Group', 'TASK', '1', 'Sys_user_group', '3', 'menu.png', 'Active', '1455625924', '1', '1455625924', '1');
INSERT INTO `bms_system_task` VALUES ('5', 'TI Budget', 'MODULE', '0', '', '3', 'menu.png', 'Active', '1455625924', '1', '1462045695', '1');
INSERT INTO `bms_system_task` VALUES ('6', 'Assign User To Group', 'TASK', '1', 'Sys_assign_user_group', '4', 'menu.png', 'Active', '1455778051', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('7', 'Assign User to Area', 'TASK', '1', 'Sys_assign_user_area', '5', 'menu.png', 'Active', '1461006068', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('8', 'ZI Budget', 'MODULE', '0', '', '4', 'menu.png', 'Active', '1461416074', '1', '1462045702', '1');
INSERT INTO `bms_system_task` VALUES ('9', 'DI Budget', 'MODULE', '0', '', '5', 'menu.png', 'Active', '1461416092', '1', '1462045709', '1');
INSERT INTO `bms_system_task` VALUES ('10', 'HOM Budget', 'MODULE', '0', '', '6', 'menu.png', 'Active', '1461416118', '1', '1462045717', '1');
INSERT INTO `bms_system_task` VALUES ('11', 'Customer Budget', 'TASK', '5', 'Ti_bud_customer_budget', '1', 'menu.png', 'Active', '1461416386', '1', '1461574971', '1');
INSERT INTO `bms_system_task` VALUES ('12', 'Budget', 'TASK', '5', 'Ti_bud_budget', '2', 'menu.png', 'Active', '1461416447', '1', '1461574985', '1');
INSERT INTO `bms_system_task` VALUES ('13', 'Customer Target', 'TASK', '5', 'Ti_bud_customer_target', '3', 'menu.png', 'Active', '1461416534', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('14', 'Monthwise Target', 'TASK', '5', 'Ti_bud_monthwise_target', '4', 'menu.png', 'Active', '1461416573', '1', '1461416642', '1');
INSERT INTO `bms_system_task` VALUES ('15', 'Budget', 'TASK', '8', 'Zi_bud_budget', '1', 'menu.png', 'Active', '1461761120', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('16', 'Assign TI Target', 'TASK', '8', 'Zi_bud_ti_target', '2', 'menu.png', 'Active', '1461761198', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('17', 'Budget', 'TASK', '9', 'Di_bud_budget', '1', 'menu.png', 'Active', '1461761242', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('18', 'Assign ZI Target', 'TASK', '9', 'Di_bud_zi_target', '2', 'menu.png', 'Active', '1461761276', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('19', 'Budget', 'TASK', '10', 'Hom_bud_budget', '1', 'menu.png', 'Active', '1462045758', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('20', 'Setup', 'MODULE', '0', '', '2', 'menu.png', 'Active', '1462045794', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('21', 'Minimum Stock Setup', 'TASK', '20', 'Setup_min_stock', '1', 'menu.png', 'Active', '1462045858', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('22', 'Variance Finalize', 'TASK', '10', 'Hom_bud_variance_finalize', '2', 'menu.png', 'Active', '1462652588', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('23', 'Purchase Budget', 'TASK', '31', 'Mgt_purchase_bud', '4', 'menu.png', 'Active', '1462652678', '1', '1465455297', '1');
INSERT INTO `bms_system_task` VALUES ('24', 'Target Finalize', 'TASK', '10', 'Hom_bud_target_finalize', '4', 'menu.png', 'Active', '1462652717', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('25', 'Site Offline', 'TASK', '1', 'Sys_site_offline', '6', 'menu.png', 'Active', '1464183470', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('26', 'MGT', 'MODULE', '0', '', '7', 'menu.png', 'Active', '1465362534', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('27', 'Currency List', 'TASK', '20', 'Setup_currency', '2', 'menu.png', 'Active', '1465362802', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('28', 'Direct Cost Items', 'TASK', '20', 'Setup_direct_cost', '3', 'menu.png', 'Active', '1465362892', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('29', 'Currency Rate Setup', 'TASK', '31', 'Mgt_currency_rate', '1', 'menu.png', 'Active', '1465366525', '1', '1465454913', '1');
INSERT INTO `bms_system_task` VALUES ('30', 'Direct Cost Percentage Setup', 'TASK', '31', 'Mgt_direct_cost_percentage', '2', 'menu.png', 'Active', '1465366597', '1', '1465454924', '1');
INSERT INTO `bms_system_task` VALUES ('31', 'Purchase', 'MODULE', '26', '', '1', 'menu.png', 'Active', '1465454894', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('32', 'Sales', 'MODULE', '26', '', '2', 'menu.png', 'Active', '1465454904', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('33', 'Packing Material Setup', 'TASK', '20', 'Setup_packing_material', '4', 'menu.png', 'Active', '1465455055', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('34', 'Packing Material Cost Setup', 'TASK', '31', 'Mgt_packing_material_cost', '3', 'menu.png', 'Active', '1465455120', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('35', 'Assign DI Target', 'TASK', '10', 'Hom_bud_di_target', '5', 'menu.png', 'Active', '1465897370', '1', '1465897426', '1');
INSERT INTO `bms_system_task` VALUES ('36', 'Reports', 'MODULE', '0', '', '8', 'menu.png', 'Active', '1466402248', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('37', 'Month Wise target Report', 'TASK', '36', 'Reports_month_target', '1', 'menu.png', 'Active', '1466402286', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('38', 'Consignmet Cost Setup', 'TASK', '31', 'Mgt_purchase_consignment_cost', '6', 'menu.png', 'Active', '1468160962', '1', '1468755171', '1');
INSERT INTO `bms_system_task` VALUES ('39', 'Varieties in Consignment', 'TASK', '31', 'Mgt_purchase_varieties_actual', '7', 'menu.png', 'Active', '1468164346', '1', '1468755140', '1');
INSERT INTO `bms_system_task` VALUES ('40', 'Consignment Setup', 'TASK', '31', 'Mgt_purchase_consignment', '5', 'menu.png', 'Active', '1468755235', '1', '1468755258', '1');
INSERT INTO `bms_system_task` VALUES ('41', 'Indirect Cost Setup', 'TASK', '32', 'Mgt_sales_indirect_cost', '1', 'menu.png', 'Active', '1469962161', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('42', 'Pricing Automated', 'TASK', '32', 'Mgt_sales_pricing_automated', '2', 'menu.png', 'Active', '1469962202', '1', '1469962324', '1');
INSERT INTO `bms_system_task` VALUES ('43', 'Pricing Management', 'TASK', '32', 'Mgt_sales_pricing_management', '3', 'menu.png', 'Active', '1470063561', '1', '1470063598', '1');
INSERT INTO `bms_system_task` VALUES ('44', 'Pricing Marketing', 'TASK', '10', 'Hom_sales_pricing', '6', 'menu.png', 'Active', '1470343236', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('45', 'Marketing Pricing Detail', 'TASK', '32', 'Mgt_sales_pricing_marketing_review', '4', 'menu.png', 'Active', '1470515903', '1', '1470516024', '1');
INSERT INTO `bms_system_task` VALUES ('46', 'Pricing Final', 'TASK', '32', 'Mgt_sales_pricing_final', '5', 'menu.png', 'Active', '1470515955', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('47', 'Predict 3years target', 'TASK', '10', 'Hom_predict_target', '7', 'menu.png', 'Active', '1470852163', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('48', 'Assign 3years target', 'TASK', '10', 'Hom_bud_di_target_prediction', '8', 'menu.png', 'Active', '1471111209', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('49', ' Assign Customer 3years Target', 'TASK', '5', 'Ti_bud_customer_target_prediction', '5', 'menu.png', 'Active', '1471434345', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('50', 'Assign TI 3years Target', 'TASK', '8', 'Zi_bud_ti_target_prediction', '3', 'menu.png', 'Active', '1471434383', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('51', 'Assign ZI 3years target', 'TASK', '9', 'Di_bud_zi_target_prediction', '3', 'menu.png', 'Active', '1471434408', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('52', 'Incentive Ratio Setup', 'TASK', '20', 'Setup_incentive_ratio', '5', 'menu.png', 'Active', '1471767849', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('53', 'Incentive Report', 'TASK', '36', 'Reports_incentive', '2', 'menu.png', 'Active', '1471767922', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('54', 'Budget & Target Report', 'TASK', '36', 'Reports_budget_target', '3', 'menu.png', 'Active', '1472116312', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('55', 'Budgeted Purchase Report', 'TASK', '36', 'Reports_mgt_purchase_budget', '4', 'menu.png', 'Active', '1472209656', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('56', 'Analysis', 'MODULE', '0', '', '9', 'menu.png', 'Active', '1472629345', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('57', 'Monthwise Sales Analysis', 'TASK', '56', 'Analysis_sales_month', '1', 'menu.png', 'Active', '1472629436', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('58', 'Sales Ordering Analysis', 'TASK', '56', 'Analysis_sales_order', '2', 'menu.png', 'Active', '1472929085', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('59', 'Actual Purchase Report', 'TASK', '36', 'Reports_mgt_purchase_actual', '5', 'menu.png', 'Active', '1474072334', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('60', 'Budgeted v/s Actual Purchase Report', 'TASK', '36', 'Reports_mgt_purchase_budgetvsactual', '6', 'menu.png', 'Active', '1474211751', '1', null, null);
INSERT INTO `bms_system_task` VALUES ('61', 'Budgeted v/s Actual Pricing Report', 'TASK', '36', 'Reports_mgt_cogs_budgetvsactual', '7', 'menu.png', 'Active', '1474211985', '1', null, null);

-- ----------------------------
-- Table structure for `bms_system_user_group`
-- ----------------------------
DROP TABLE IF EXISTS `bms_system_user_group`;
CREATE TABLE `bms_system_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(11) NOT NULL DEFAULT 'Active',
  `ordering` tinyint(4) NOT NULL DEFAULT '99',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  `date_updated` int(11) DEFAULT NULL,
  `user_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bms_system_user_group
-- ----------------------------
INSERT INTO `bms_system_user_group` VALUES ('1', 'Super Admin', 'Active', '1', '1455625924', '1', '1455625924', '1');
INSERT INTO `bms_system_user_group` VALUES ('2', 'Admin', 'Active', '2', '1455777728', '1', null, null);
INSERT INTO `bms_system_user_group` VALUES ('3', 'Managing Director', 'Active', '3', '1460516113', '2', '1460516206', '2');
INSERT INTO `bms_system_user_group` VALUES ('4', 'Head of Marketing ', 'Active', '4', '1460516229', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('5', 'Division', 'Active', '5', '1460516244', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('6', 'Zone', 'Active', '6', '1460516257', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('7', 'Territory', 'Active', '7', '1460516272', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('8', 'Business Analyst ', 'Active', '8', '1460516367', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('9', 'Trainer', 'Active', '99', '1464057789', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('10', 'Foreign Correspondent', 'Active', '99', '1465961262', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('11', 'ICT (Marketing)', 'Active', '99', '1471854923', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('12', 'Accounts', 'Active', '99', '1472281295', '2', null, null);
INSERT INTO `bms_system_user_group` VALUES ('13', 'Operations Coordinator', 'Active', '20', '1477109578', '2', null, null);

-- ----------------------------
-- Table structure for `bms_system_user_group_role`
-- ----------------------------
DROP TABLE IF EXISTS `bms_system_user_group_role`;
CREATE TABLE `bms_system_user_group_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `action0` tinyint(2) DEFAULT '0',
  `action1` tinyint(2) DEFAULT '0',
  `action2` tinyint(2) DEFAULT '0',
  `action3` tinyint(2) DEFAULT '0',
  `action4` tinyint(2) DEFAULT '0',
  `action5` tinyint(2) DEFAULT '0',
  `action6` tinyint(2) DEFAULT '0',
  `revision` int(11) NOT NULL DEFAULT '1',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bms_system_user_group_role
-- ----------------------------
INSERT INTO `bms_system_user_group_role` VALUES ('1', '10', '23', '1', '1', '1', '1', '1', '1', '1', '1', '1472281246', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('2', '10', '55', '1', '1', '1', '1', '1', '1', '1', '1', '1472281246', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('3', '11', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1472281276', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('4', '11', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1472281276', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('5', '11', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1472281276', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('6', '12', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1472281456', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('7', '12', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1472281456', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('8', '12', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1472281456', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('9', '12', '55', '1', '1', '1', '1', '1', '1', '1', '1', '1472281456', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('10', '4', '11', '1', '0', '0', '0', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('11', '4', '12', '1', '0', '0', '0', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('12', '4', '13', '1', '0', '0', '0', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('13', '4', '14', '1', '0', '0', '0', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('14', '4', '15', '1', '1', '1', '0', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('15', '4', '16', '1', '1', '1', '0', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('16', '4', '17', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('17', '4', '18', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('18', '4', '19', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('19', '4', '22', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('20', '4', '24', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('21', '4', '35', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('22', '4', '44', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('23', '4', '47', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('24', '4', '48', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('25', '4', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('26', '4', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('27', '4', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('28', '4', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('29', '4', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1472962021', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('30', '5', '11', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('31', '5', '12', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('32', '5', '13', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('33', '5', '14', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('34', '5', '49', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('35', '5', '15', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('36', '5', '16', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('37', '5', '50', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('38', '5', '17', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('39', '5', '18', '1', '1', '0', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('40', '5', '51', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('41', '5', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('42', '5', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('43', '5', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('44', '5', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('45', '5', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1472962058', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('46', '7', '11', '1', '1', '0', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('47', '7', '12', '1', '1', '0', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('48', '7', '13', '1', '1', '0', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('49', '7', '14', '1', '1', '0', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('50', '7', '49', '1', '1', '1', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('51', '7', '37', '1', '1', '1', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('52', '7', '53', '1', '1', '1', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('53', '7', '54', '1', '1', '1', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('54', '7', '57', '1', '1', '1', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('55', '7', '58', '1', '1', '1', '1', '1', '1', '1', '2', '1472962086', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('56', '9', '11', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('57', '9', '12', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('58', '9', '13', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('59', '9', '14', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('60', '9', '49', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('61', '9', '15', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('62', '9', '16', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('63', '9', '50', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('64', '9', '17', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('65', '9', '18', '1', '1', '0', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('66', '9', '51', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('67', '9', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('68', '9', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('69', '9', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('70', '9', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('71', '9', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1472962131', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('72', '1', '2', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('73', '1', '3', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('74', '1', '4', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('75', '1', '6', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('76', '1', '7', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('77', '1', '25', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('78', '1', '21', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('79', '1', '27', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('80', '1', '28', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('81', '1', '33', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('82', '1', '52', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('83', '1', '11', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('84', '1', '12', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('85', '1', '13', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('86', '1', '14', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('87', '1', '49', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('88', '1', '15', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('89', '1', '16', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('90', '1', '50', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('91', '1', '17', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('92', '1', '18', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('93', '1', '51', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('94', '1', '19', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('95', '1', '22', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('96', '1', '24', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('97', '1', '35', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('98', '1', '44', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('99', '1', '47', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('100', '1', '48', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('101', '1', '29', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('102', '1', '30', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('103', '1', '34', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('104', '1', '23', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('105', '1', '40', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('106', '1', '38', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('107', '1', '39', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('108', '1', '41', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('109', '1', '42', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('110', '1', '43', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('111', '1', '45', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('112', '1', '46', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('113', '1', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('114', '1', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('115', '1', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('116', '1', '55', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('117', '1', '59', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('118', '1', '60', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('119', '1', '61', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('120', '1', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('121', '1', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1474213143', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('122', '2', '2', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('123', '2', '3', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('124', '2', '4', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('125', '2', '6', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('126', '2', '7', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('127', '2', '25', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('128', '2', '21', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('129', '2', '27', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('130', '2', '28', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('131', '2', '33', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('132', '2', '52', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('133', '2', '11', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('134', '2', '12', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('135', '2', '13', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('136', '2', '14', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('137', '2', '49', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('138', '2', '15', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('139', '2', '16', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('140', '2', '50', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('141', '2', '17', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('142', '2', '18', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('143', '2', '51', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('144', '2', '19', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('145', '2', '22', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('146', '2', '24', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('147', '2', '35', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('148', '2', '44', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('149', '2', '47', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('150', '2', '48', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('151', '2', '29', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('152', '2', '30', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('153', '2', '34', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('154', '2', '23', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('155', '2', '40', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('156', '2', '38', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('157', '2', '39', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('158', '2', '41', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('159', '2', '42', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('160', '2', '43', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('161', '2', '45', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('162', '2', '46', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('163', '2', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('164', '2', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('165', '2', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('166', '2', '55', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('167', '2', '59', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('168', '2', '60', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('169', '2', '61', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('170', '2', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('171', '2', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1474213162', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('172', '3', '21', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('173', '3', '11', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('174', '3', '12', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('175', '3', '13', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('176', '3', '14', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('177', '3', '49', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('178', '3', '15', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('179', '3', '16', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('180', '3', '50', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('181', '3', '17', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('182', '3', '18', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('183', '3', '51', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('184', '3', '19', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('185', '3', '22', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('186', '3', '24', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('187', '3', '35', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('188', '3', '44', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('189', '3', '47', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('190', '3', '48', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('191', '3', '29', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('192', '3', '30', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('193', '3', '34', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('194', '3', '23', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('195', '3', '40', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('196', '3', '38', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('197', '3', '39', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('198', '3', '41', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('199', '3', '42', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('200', '3', '43', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('201', '3', '45', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('202', '3', '46', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('203', '3', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('204', '3', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('205', '3', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('206', '3', '55', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('207', '3', '59', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('208', '3', '60', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('209', '3', '61', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('210', '3', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('211', '3', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1475167530', '1');
INSERT INTO `bms_system_user_group_role` VALUES ('212', '6', '11', '1', '1', '0', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('213', '6', '12', '1', '1', '0', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('214', '6', '13', '1', '1', '0', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('215', '6', '14', '1', '1', '0', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('216', '6', '49', '1', '1', '1', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('217', '6', '15', '1', '1', '0', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('218', '6', '16', '1', '1', '0', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('219', '6', '50', '1', '1', '1', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('220', '6', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('221', '6', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('222', '6', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('223', '6', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1475979909', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('224', '8', '11', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('225', '8', '12', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('226', '8', '15', '1', '1', '0', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('227', '8', '16', '1', '1', '0', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('228', '8', '17', '1', '1', '0', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('229', '8', '18', '1', '1', '0', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('230', '8', '19', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('231', '8', '22', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('232', '8', '24', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('233', '8', '35', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('234', '8', '44', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('235', '8', '47', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('236', '8', '48', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('237', '8', '23', '1', '0', '0', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('238', '8', '40', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('239', '8', '38', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('240', '8', '39', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('241', '8', '41', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('242', '8', '42', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('243', '8', '43', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('244', '8', '45', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('245', '8', '46', '1', '0', '0', '0', '0', '0', '0', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('246', '8', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('247', '8', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('248', '8', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('249', '8', '55', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('250', '8', '59', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('251', '8', '60', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('252', '8', '61', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('253', '8', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('254', '8', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1475980139', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('255', '13', '37', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('256', '13', '53', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('257', '13', '54', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('258', '13', '55', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('259', '13', '59', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('260', '13', '60', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('261', '13', '61', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('262', '13', '57', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('263', '13', '58', '1', '1', '1', '1', '1', '1', '1', '2', '1477109631', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('264', '13', '11', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('265', '13', '12', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('266', '13', '13', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('267', '13', '14', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('268', '13', '49', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('269', '13', '15', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('270', '13', '16', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('271', '13', '50', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('272', '13', '17', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('273', '13', '18', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('274', '13', '51', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('275', '13', '19', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('276', '13', '22', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('277', '13', '24', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('278', '13', '35', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('279', '13', '44', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('280', '13', '47', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('281', '13', '48', '1', '0', '0', '0', '0', '0', '0', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('282', '13', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('283', '13', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('284', '13', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('285', '13', '55', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('286', '13', '59', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('287', '13', '60', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('288', '13', '61', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('289', '13', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('290', '13', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1484370077', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('291', '7', '11', '1', '0', '0', '0', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('292', '7', '12', '1', '0', '0', '0', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('293', '7', '13', '1', '0', '0', '0', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('294', '7', '14', '1', '0', '0', '0', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('295', '7', '49', '1', '0', '0', '0', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('296', '7', '37', '1', '1', '1', '1', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('297', '7', '53', '1', '1', '1', '1', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('298', '7', '54', '1', '1', '1', '1', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('299', '7', '57', '1', '1', '1', '1', '1', '1', '1', '1', '1486975073', '2');
INSERT INTO `bms_system_user_group_role` VALUES ('300', '7', '58', '1', '1', '1', '1', '1', '1', '1', '1', '1486975073', '2');
