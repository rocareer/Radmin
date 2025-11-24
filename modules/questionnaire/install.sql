CREATE TABLE
    IF
    NOT EXISTS `__PREFIX__questionnaire_answer` (
    `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '题目',
    `type` TINYINT ( 3 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类型:0=单选题,1=多选题,2=填空题',
    `options` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '选项',
    `sid` INT ( 8 ) NOT NULL COMMENT '答卷ID',
    `checked` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '答案',
    `must` TINYINT ( 3 ) NOT NULL DEFAULT '1' COMMENT '必答题:0=否,1=是',
    `update_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '修改时间',
    `create_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY ( `id` ) USING BTREE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC COMMENT = '答题表';

CREATE TABLE
    IF
    NOT EXISTS `__PREFIX__questionnaire_answer_sheet` (
    `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `platform` TINYINT ( 3 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '平台:0=网页,1=公众号,2=小程序',
    `user` VARCHAR ( 200 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '答题者',
    `eid` INT ( 10 ) NOT NULL DEFAULT '0' COMMENT '问卷ID',
    `update_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '修改时间',
    `create_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY ( `id` ) USING BTREE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC COMMENT = '答卷表';

CREATE TABLE
    IF
    NOT EXISTS `__PREFIX__questionnaire_examination` (
    `id` INT ( 8 ) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `begin_time` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '开始时间',
    `end_time` BIGINT ( 20 ) NOT NULL COMMENT '结束时间',
    `description` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
    `questions` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '题目',
    `status` TINYINT ( 3 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态:0=禁用,1=启用',
    `num` INT ( 5 ) NOT NULL DEFAULT '0' COMMENT '回答次数',
    `update_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '修改时间',
    `create_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY ( `id` ) USING BTREE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC COMMENT = '问卷表';

CREATE TABLE
    IF
    NOT EXISTS `__PREFIX__questionnaire_question` (
    `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '题目',
    `must` TINYINT ( 4 ) NOT NULL DEFAULT '1' COMMENT '必答题:0=否,1=是',
    `type` TINYINT ( 3 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类型:0=单选题,1=多选题,2=填空题',
    `options` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '选项',
    `status` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    `weigh` INT ( 10 ) DEFAULT '0' COMMENT '排序',
    `update_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '修改时间',
    `create_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY ( `id` ) USING BTREE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC COMMENT = '问题表';

-- v1.0.1
ALTER TABLE `__PREFIX__questionnaire_examination` ADD COLUMN `default` TINYINT ( 3 ) NOT NULL DEFAULT '0' COMMENT '默认:0=否,1=是' AFTER `status`;
ALTER TABLE `__PREFIX__questionnaire_examination` ADD COLUMN `qrcode` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '二维码' AFTER `status`;

-- v1.0.4
ALTER TABLE `__PREFIX__questionnaire_examination` ADD COLUMN `mp` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'H5二维码' AFTER `status`;
ALTER TABLE `__PREFIX__questionnaire_examination` ADD COLUMN `mini` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '小程序码' AFTER `mp`;
ALTER TABLE `__PREFIX__questionnaire_examination` DROP COLUMN `qrcode`;

-- v1.0.5
ALTER TABLE `__PREFIX__questionnaire_answer` MODIFY COLUMN `type` TINYINT ( 3 ) NOT NULL DEFAULT '0' COMMENT '类型:0=单选题,1=多选题,2=填空题,3=简答题,4=下拉框,5=图片,6=视频,7=文件';
ALTER TABLE `__PREFIX__questionnaire_answer` MODIFY COLUMN `checked` TEXT COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '答案';
ALTER TABLE `__PREFIX__questionnaire_answer_sheet` MODIFY COLUMN `user` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '答题者';
ALTER TABLE `__PREFIX__questionnaire_examination` DROP COLUMN `mp`;
ALTER TABLE `__PREFIX__questionnaire_examination` DROP COLUMN `default`;
ALTER TABLE `__PREFIX__questionnaire_examination` MODIFY COLUMN `status`tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用';
ALTER TABLE `__PREFIX__questionnaire_examination` ADD COLUMN `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'H5链接' AFTER `mini`;
ALTER TABLE `__PREFIX__questionnaire_question` MODIFY COLUMN `type` TINYINT ( 3 ) NOT NULL DEFAULT '0' COMMENT '类型:0=单选题,1=多选题,2=填空题,3=简答题,4=下拉框,5=图片,6=视频,7=文件';
ALTER TABLE `__PREFIX__questionnaire_question` ADD COLUMN `file_size` tinyint(4) DEFAULT '10' COMMENT '文件大小M' AFTER `options`;
ALTER TABLE `__PREFIX__questionnaire_question` ADD COLUMN `file_num` tinyint(4) DEFAULT '1' COMMENT '文件数量' AFTER `options`;
ALTER TABLE `__PREFIX__questionnaire_question` ADD COLUMN `file_suffix` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支持的文件后缀' AFTER `options`;