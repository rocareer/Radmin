CREATE TABLE IF NOT EXISTS `__PREFIX__user_message`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '发送人',
    `recipient_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '接受人',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '消息内容',
    `status` enum('unread','read') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unread' COMMENT '状态:unread=未读,read=已读',
    `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    `del_user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '消息删除状态',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id`(`user_id` ASC) USING BTREE,
    INDEX `recipient_id`(`recipient_id` ASC) USING BTREE,
    INDEX `del_user_id`(`del_user_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员消息表';

CREATE TABLE IF NOT EXISTS `__PREFIX__user_card_component`  (
   `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
   `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
   `position` enum('statistic','under_motto','opt','middle','tab') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tab' COMMENT '位置:statistic=个性签名之下-统计信息(inline),under_motto=个性签名之下-独占行(block),opt=统计信息之下-操作按钮(inline),middle=会员信息版块与选项卡版块之间(block),tab=作为底部选项卡之一(block)',
   `component` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件名称',
   `weigh` int NULL DEFAULT NULL COMMENT '权重',
   `status` tinyint UNSIGNED NULL DEFAULT 0 COMMENT '状态:0=禁用,1=启用',
   `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员名片组件表';

CREATE TABLE IF NOT EXISTS `__PREFIX__user_recent`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '动态详情',
    `weigh` int NULL DEFAULT NULL COMMENT '权重',
    `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员最近动态表';

BEGIN;
REPLACE INTO `__PREFIX__user_card_component` VALUES (1, '性别', 'statistic', 'gender', 99, 1, 1684836296);
REPLACE INTO `__PREFIX__user_card_component` VALUES (2, '注册时间', 'statistic', 'regTime', 98, 1, 1684836339);
REPLACE INTO `__PREFIX__user_card_component` VALUES (3, '私信按钮', 'opt', 'chat', 89, 1, 1684836470);
REPLACE INTO `__PREFIX__user_card_component` VALUES (4, '最近动态', 'tab', 'recent', 79, 1, 1684836541);
COMMIT;

BEGIN;
REPLACE INTO `__PREFIX__config` VALUES (null, 'official_account', 'user', '官方账号', '在这些账号的昵称之后做官方账号标记', 'remoteSelects', '1', NULL, '', '{\"baInputExtend\":{\"pk\":\"user.id\",\"field\":\"nickname\",\"remote-url\":\"\\/admin\\/user.User\\/index\",\"multiple\":true}}', 1, 0);
REPLACE INTO `__PREFIX__config` VALUES (null, 'system_message_account', 'user', '系统消息账号', '', 'remoteSelect', '1', NULL, 'required', '{\"baInputExtend\":{\"pk\":\"user.id\",\"field\":\"nickname\",\"remote-url\":\"\\/admin\\/user.User\\/index\"}}', 1, 0);
REPLACE INTO `__PREFIX__config` VALUES (null, 'polling_interval', 'user', '前台轮询新消息间隔', '每隔多少秒查询一次是否有新消息', 'number', '30', NULL, 'required,number', '', 1, 0);
COMMIT;