DROP TABLE IF EXISTS `syy_admins`;
CREATE TABLE `syy_admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员账号',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '管理员账号状态 0：禁用 1：开启',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员表';

INSERT INTO `syy_admins` VALUES (1, 'root', 'e10adc3949ba59abbe56e057f20f883e', 1, 1642750967, 1642750967);

DROP TABLE IF EXISTS `syy_menus`;
CREATE TABLE `syy_menus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `url` varchar(300) NOT NULL DEFAULT '' COMMENT '前端路由',
  `icon` varchar(300) NOT NULL DEFAULT '' COMMENT 'icon',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用 0：不启用 1：启用',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` int(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='菜单表';

DROP TABLE IF EXISTS `syy_role_has_menus`;
CREATE TABLE `syy_role_has_menus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色id',
  `menu_id` int(11) NOT NULL DEFAULT '0' COMMENT '菜单id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='角色菜单表';

DROP TABLE IF EXISTS `syy_office_contrasts`;
CREATE TABLE `syy_office_contrasts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `key` varchar(50) NOT NULL DEFAULT '' COMMENT 'key',
  `value` varchar(50) NOT NULL DEFAULT '' COMMENT 'value',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0：非临床科室 1：临床科室',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='科室名称对应表';

DROP TABLE IF EXISTS `syy_charge_projects`;
CREATE TABLE `syy_charge_projects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `key` varchar(50) NOT NULL DEFAULT '' COMMENT 'key',
  `value` varchar(50) NOT NULL DEFAULT '' COMMENT 'value',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='收费项目子类对应表';

DROP TABLE IF EXISTS `syy_financial_spends`;
CREATE TABLE `syy_financial_spends` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `key` varchar(50) NOT NULL DEFAULT '' COMMENT 'key',
  `value` varchar(50) NOT NULL DEFAULT '' COMMENT 'value',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='财务支出科目对应表';

DROP TABLE IF EXISTS `syy_billing_incomes`;
CREATE TABLE `syy_billing_incomes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `date` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '日期（年月）',
  `year` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '年',
  `month` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '月',
  `type` tinyint(10) unsigned NOT NULL DEFAULT 0 COMMENT '0:上半月 1：下半月 2：整月',
  `billing_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '开单科室',
  `patient_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '病人科室',
  -- `charge_subclass` varchar(50) NOT NULL DEFAULT '' COMMENT '收费子类',
  -- `num` int(11) NOT NULL DEFAULT 0 COMMENT '数量',
  -- `money` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '金额',
  `pathology_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '病理学诊断收入',
  `material_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '材料费收入',
  `ultrasound_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '超声检查收入',
  `radiation_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '放射检查收入',
  `check_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '检查费收入',
  `checkout_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '检验收入',
  `surgery_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '手术项目收入',
  `xiyao_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '西药费收入',
  `general_medical_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '一般医疗服务收入',
  `zhongyao_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '中药收入',
  `total_money` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '总金额',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='开单收入表';

DROP TABLE IF EXISTS `syy_billing_charge_names`;
CREATE TABLE `syy_billing_charge_names` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `date` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '日期（年月）',
  `year` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '年',
  `month` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '月',
  `type` tinyint(10) unsigned NOT NULL DEFAULT 0 COMMENT '0:上半月 1：下半月 2：整月',
  `billing_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '开单科室',
  `patient_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '病人科室',
  `charge_name` varchar(300) NOT NULL DEFAULT '' COMMENT '收费名称',
  `num` int(11) NOT NULL DEFAULT 0 COMMENT '数量',
  `money` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '金额',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='开单收费名称表';

DROP TABLE IF EXISTS `syy_receive_incomes`;
CREATE TABLE `syy_receive_incomes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `date` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '日期（年月）',
  `year` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '年',
  `month` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '月',
  `type` tinyint(10) unsigned NOT NULL DEFAULT 0 COMMENT '0:上半月 1：下半月 2：整月',
  `receive_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '开单科室',
  `patient_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '病人科室',
  -- `charge_subclass` varchar(50) NOT NULL DEFAULT '' COMMENT '收费子类',
  -- `num` int(11) NOT NULL DEFAULT 0 COMMENT '数量',
  -- `money` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '金额',
  `pathology_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '病理学诊断收入',
  `material_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '材料费收入',
  `ultrasound_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '超声检查收入',
  `radiation_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '放射检查收入',
  `check_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '检查费收入',
  `checkout_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '检验收入',
  `surgery_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '手术项目收入',
  `xiyao_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '西药费收入',
  `general_medical_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '一般医疗服务收入',
  `zhongyao_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '中药收入',
  `total_money` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '总金额',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='接单收入表';

DROP TABLE IF EXISTS `syy_receive_charge_names`;
CREATE TABLE `syy_receive_charge_names` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `date` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '日期（年月）',
  `year` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '年',
  `month` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '月',
  `type` tinyint(10) unsigned NOT NULL DEFAULT 0 COMMENT '0:上半月 1：下半月 2：整月',
  `receive_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '接单科室',
  `patient_dep` varchar(50) NOT NULL DEFAULT '' COMMENT '病人科室',
  `charge_name` varchar(300) NOT NULL DEFAULT '' COMMENT '收费名称',
  `num` int(11) NOT NULL DEFAULT 0 COMMENT '数量',
  `money` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '金额',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='接单收费名称表';

DROP TABLE IF EXISTS `syy_pays`;
CREATE TABLE `syy_pays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `date` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '日期（年月）',
  `year` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '年',
  `month` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '月',
  `type` tinyint(10) unsigned NOT NULL DEFAULT 0 COMMENT '0:上半月 1：下半月 2：整月',
  `dep` varchar(50) NOT NULL DEFAULT '' COMMENT '科室',
  `personnel_pay` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '人员经费',
  `fixed_asset_pay` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '固定资产折旧费',
  `material_pay` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '卫生材料费',
  `medicine_pay` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '药品费',
  `other_pay` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '其他费用',
  `total_money` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '总金额',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='支出表';

DROP TABLE IF EXISTS `syy_indicators`;
CREATE TABLE `syy_indicators` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `date` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '日期（年月）',
  `year` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '年',
  `month` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '月',
  `dep` varchar(50) NOT NULL DEFAULT '' COMMENT '科室',
  `billing_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '开单收入',
  `direct_cost` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '直接成本',
  `balance` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '收支结余',
  `balance_rate` varchar(20) NOT NULL DEFAULT '' COMMENT '结余率',
  `drug_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '药品收入',
  `consumable_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '耗材收入',
  `drug_pay` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '药品支出',
  `consumable_pay` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '耗材支出',
  `drug_rate` varchar(20) NOT NULL DEFAULT '' COMMENT '药占比',
  `consumable_rate` varchar(20) NOT NULL DEFAULT '' COMMENT '耗占比',
  `drug_profit` varchar(20) NOT NULL DEFAULT '' COMMENT '药品利润',
  `consumable_profit` varchar(20) NOT NULL DEFAULT '' COMMENT '耗材利润',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='重点指标表';

DROP TABLE IF EXISTS `syy_cost_controls`;
CREATE TABLE `syy_cost_controls` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `date` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '日期（年月）',
  `year` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '年',
  `month` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '月',
  `dep` varchar(50) NOT NULL DEFAULT '' COMMENT '科室',
  `personnel_cost` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '人员经费成本',
  `consumable_cost` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '耗材支出成本',
  `drug_cost` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '药品费成本',
  `fixed_asset_cost` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '固定资产折旧费成本',
  `other_cost` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '其他支出成本',
  `total_cost` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '合计成本',
  `billing_income` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '开单收入',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='成本控制及工作量表';
