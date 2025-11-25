SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `__PREFIX__ai_chat_model`
(
    `id`                int UNSIGNED                                                  NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title`             varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '会话窗口标题',
    `logo`              varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '会话AI头像',
    `name`              varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT '' COMMENT 'AI模型',
    `greeting`          varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '欢迎文案',
    `api_url`           varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口URL',
    `api_key`           varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API KEY',
    `proxy`             varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '代理',
    `tokens_multiplier` decimal(7, 2)                                                 NOT NULL DEFAULT 1.00 COMMENT 'tokens收取乘数',
    `weigh`             int                                                           NULL     DEFAULT 0 COMMENT '权重',
    `user_status`       tinyint UNSIGNED                                              NOT NULL DEFAULT 1 COMMENT '前台可用:0=禁用,1=启用',
    `status`            tinyint UNSIGNED                                              NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `create_time`       bigint UNSIGNED                                               NULL     DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = '对话模型表'
  ROW_FORMAT = Dynamic;

INSERT INTO `__PREFIX__ai_chat_model` (id, title, logo, name, greeting, api_url, api_key, proxy, tokens_multiplier, weigh, user_status, status, create_time)
VALUES (1, '知识库-千问(turbo)', '', 'qwen-turbo', '我是千问turbo，您有什么问题？', '', '', '', 1.00, 1, 0, 0, 1695611765),
       (2, '知识库-ChatGPT3.5-turbo', '', 'gpt-3.5-turbo', '我是ChatGPT3.5，欢迎向我提问~', '', '', '', 2.00, 3, 0, 0, 1695695281);


CREATE TABLE IF NOT EXISTS `__PREFIX__ai_config`
(
    `id`    int UNSIGNED                                                  NOT NULL AUTO_INCREMENT,
    `name`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL DEFAULT '' COMMENT '变量名',
    `group` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL DEFAULT '' COMMENT '分组',
    `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '变量标题',
    `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci         NULL COMMENT '变量值',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `name` (`name` ASC) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = 'AI配置'
  ROW_FORMAT = DYNAMIC;

INSERT INTO `__PREFIX__ai_config` (id, name, `group`, title, value)
VALUES (null, 'ai_api_type', 'base', '接口类型', 'ali'),
       (null, 'ai_api_key', 'base', 'API KEY', ''),
       (null, 'ai_api_url', 'base', '接口基础URL', ''),
       (null, 'ai_proxy', 'base', '代理', ''),
       (null, 'ai_work_mode', 'base', '运行模式', 'mysql'),
       (null, 'ai_accurate_hit', 'base', '精准命中相似度', '0.9'),
       (null, 'ai_model', 'chat', 'AI模型', 'qwen-turbo'),
       (null, 'ai_greeting', 'chat', '欢迎文案', '我是知识库AI，请问有什么可以帮您？'),
       (null, 'ai_system_prompt', 'chat', '系统提示词', '你是一个知识库AI。'),
       (null, 'ai_prompt', 'chat', '限定提示词', '你主要提供关于开源后台系统`BuildAdmin`的技术文档支持。请总结归纳以下背景资料并回答问题。你应该尽量在回答中提供背景资料中存在的链接，以Markdown的语法。如果可能的话，给出完整代码示例。请勿让用户自行参考背景资料，请尽可能理解用户提问并从背景资料中分析出有用信息。\n------------------------------\n背景资料：${reference}\n------------------------------\n问题是：${question}'),
       (null, 'ai_short_memory', 'chat', '短期记忆对话论数', '5'),
       (null, 'ai_effective_match_kbs', 'chat', '有效知识相似度', '0.5'),
       (null, 'ai_effective_kbs_count', 'chat', '有效知识数量', '5'),
       (null, 'ai_irrelevant_determination', 'chat', '无关认定相似度', '0.2'),
       (null, 'ai_irrelevant_message', 'chat', '无关问题响应文案', ''),
       (null, 'ai_short_memory_percent', 'chat', '短期记忆tokens占比', '16'),
       (null, 'ai_prompt_percent', 'chat', '身份提示词tokens占比', '3'),
       (null, 'ai_kbs_percent', 'chat', '知识库tokens占比', '60'),
       (null, 'ai_user_input_percent', 'chat', '用户输入tokens占比', '16'),
       (null, 'ai_response_percent', 'chat', 'AI响应tokens占比', '5'),
       (null, 'ai_allow_users_close_kbs', 'chat', '允许用户关闭知识库', '1'),
       (null, 'ai_allow_users_select_kbs', 'chat', '允许用户选择知识库', '1'),
       (null, 'ai_vector_index', 'redis', '向量索引类型', 'FLAT'),
       (null, 'ai_vector_similarity', 'redis', '向量相似性算法', 'COSINE'),
       (null, 'ai_initial_cap', 'redis', '初始索引内存大小', '200'),
       (null, 'ai_flat_block_size', 'redis', '分块大小', '128'),
       (null, 'ai_hnsw_knn_ef_runtime', 'redis', '最大前候选', '20'),
       (null, 'ai_hnsw_epsilon', 'redis', 'EPSILON', '0.8'),
       (null, 'ai_hnsw_m', 'redis', '图的每层每节点最大出边数', '40'),
       (null, 'ai_hnsw_ef_construction', 'redis', '潜在出边候选数', '200'),
       (null, 'auto_new_session_interval', 'chat', '自动新会话间隔时间（分钟）', '60'),
       (null, 'ai_session_title', 'chat', '会话窗口标题', '知识库'),
       (null, 'ai_session_logo', 'chat', 'AI头像', '/static/images/avatar.png'),
       (null, 'ai_send_kbs_update_status', 'chat', '自动发送知识点更新情况', '1'),
       (null, 'ai_allow_users_edit_prompt', 'chat', '允许用户修改提示词', '1'),
       (null, 'ai_allow_users_edit_tokens', 'chat', '允许用户修改 tokens 占比', '1'),
       (null, 'ai_allow_users_edit_effective_kbs', 'chat', '允许用户调整有效知识设定', '1'),
       (null, 'ai_gift_tokens', 'base', 'AI新会员送tokens', '200000'),
       (null, 'ai_score_exchange_tokens', 'base', '每积分可兑换的tokens数', '10000'),
       (null, 'ai_money_exchange_tokens', 'base', '每1元余额可兑换的tokens数', '100000'),
       (null, 'ai_prompt_no_reference', 'chat', '限定提示词（无匹配知识点）', '问题是：${question}');


CREATE TABLE IF NOT EXISTS `__PREFIX__ai_kbs`
(
    `id`          int UNSIGNED                                                  NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`        varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
    `weigh`       int                                                           NULL     DEFAULT 0 COMMENT '权重',
    `status`      tinyint UNSIGNED                                              NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `create_time` bigint UNSIGNED                                               NULL     DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = '知识库表'
  ROW_FORMAT = Dynamic;

INSERT INTO `__PREFIX__ai_kbs` (id, name, weigh, status, create_time)
VALUES (1, '文档', 99, 1, 1695154631),
       (2, '社区', 2, 1, 1695154694);


CREATE TABLE IF NOT EXISTS `__PREFIX__ai_kbs_content`
(
    `id`             int UNSIGNED                                                                                          NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `ai_kbs_ids`     varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                                         NOT NULL DEFAULT '' COMMENT '知识库',
    `type`           enum ('qa','text') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                                   NOT NULL DEFAULT 'qa' COMMENT '类型:qa=问答对,text=普通文档',
    `title`          text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                                                 NULL COMMENT '标题',
    `content_source` enum ('input','quote') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                               NOT NULL DEFAULT 'input' COMMENT '内容来源:input=手动输入,quote=引用',
    `content`        text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                                                 NULL COMMENT '内容',
    `content_quote`  int UNSIGNED                                                                                          NOT NULL DEFAULT 0 COMMENT '被引用知识点',
    `model`          varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                                          NOT NULL DEFAULT '' COMMENT '向量模型',
    `embedding`      blob                                                                                                  NULL COMMENT '向量数据',
    `weigh`          int                                                                                                   NULL     DEFAULT 0 COMMENT '权重',
    `source`         varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                                         NOT NULL DEFAULT '' COMMENT '来源',
    `hits`           int                                                                                                   NOT NULL DEFAULT 0 COMMENT '精准命中数',
    `tokens`         int                                                                                                   NOT NULL DEFAULT 0 COMMENT 'token数',
    `extend`         varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                                         NOT NULL DEFAULT '' COMMENT '扩展数据',
    `text_type`      enum ('query','document') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                            NOT NULL DEFAULT 'query' COMMENT '向量数据类型:query=检索优化,document=底库文本',
    `status`         enum ('pending','fail','success','usable','offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '向量化状态:pending=待处理,fail=处理失败,success=处理成功,usable=已可用,offline=已下线',
    `update_time`    bigint UNSIGNED                                                                                       NULL     DEFAULT NULL COMMENT '更新时间',
    `create_time`    bigint UNSIGNED                                                                                       NULL     DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = '知识点表'
  ROW_FORMAT = DYNAMIC;

INSERT INTO `__PREFIX__ai_kbs_content` (id, ai_kbs_ids, type, title, content, model, embedding, weigh, source, hits, tokens, extend, text_type, status, update_time, create_time)
VALUES (1, '1', 'qa', 'BuildAdmin是什么？介绍', '# buildadmin介绍.md\n\nBuildAdmin是一个基于`Apache2.0`协议开源的后台管理系统，使用了多种非常流行的技术栈，包括：\n- [ThinkPHP8](https://www.thinkphp.cn/)：后端框架\n- [Vue3](https://v3.cn.vuejs.org/)：前端框架\n- [TypeScript](https://www.tslang.cn/docs/home.html)：提供强类型支持\n- [Vite](https://vitejs.cn/)：前端构建工具\n- [Element Plus](https://element-plus.gitee.io/)：前端 UI 框架\n- [Pinia](https://pinia.vuejs.org/)：前端状态管理库\n\nBuildAdmin 具有以下特点：\n- 可视化CRUD代码生成：提供图形化拖拽界面，可自动生成后台增删改查（CRUD）代码，包括数据表创建、表单组件代码生成、权限控制等。\n- 内置WEB终端：集成了WEB终端，方便执行开发和维护任务，无需手动执行命令。\n- 前后端分离：前端和后端代码分离，纯前端开发者可以独立使用系统，无需了解后端代码。\n- 权限验证：提供可视化权限管理，支持动态注册路由、菜单、页面和按钮权限。\n- 模块市场：后台模块市场还可一键安装数据导出、短信发送、云存储、单页或是纯前端技术栈的学习案例项目等等，随时随地为系统添砖加瓦。\n- 其他功能：字段级数据保护、数据回收与还原、管理员、会员管理等。\n- 高颜值：提供多种布局模式，默认采用无边框设计，界面美观。\n- 多平台支持：适应PC、平板、手机等多种设备。\n- 持续维护：计划不断维护系统，添加基础设施模块和各行业应用。\n- 开源免费，提供无需授权的商业可用。', 'text-embedding-v1', '', 1, '[BuildAdmin文档站](https://wonderful-code.gitee.io/)', 0, 0, '[]', 'query', 'pending', 1696584186, 1695241441),
       (2, '1', 'qa', '相关链接\n官方网站\n演示站\n有文档吗？', '# BuildAdmin的资源链接列表.md\n\n- [BuildAdmin文档链接](https://wonderful-code.gitee.io/)\n- [BuildAdmin文档备用链接](https://doc.buildadmin.com/)\n- [BuildAdmin官网链接](https://uni.buildadmin.com/)\n- [BuildAdmin问答社区链接](https://ask.buildadmin.com/)\n- [BuildAdmin模块市场链接](https://modules.buildadmin.com/)\n- [BuildAdmin演示站链接](https://demo.buildadmin.com/)\n- [BuildAdmin的Gitee代码仓库链接](https://gitee.com/wonderful-code/buildadmin)\n- [BuildAdmin的Github代码仓库链接](https://github.com/build-admin/buildadmin)\n- [BuildAdmin的发行日志](https://gitee.com/wonderful-code/buildadmin/releases)', 'text-embedding-v1', '', 2, '[BuildAdmin文档站](https://wonderful-code.gitee.io/)', 0, 0, '[]', 'query', 'pending', NULL, 1695241615),
       (3, '1', 'qa', '我应该从哪里下载完整包、资源包？下载buildadmin', '# BuildAdmin完整包下载.md\n\n- BuildAdmin的完整包下载地址：[buildadmin的完整包下载链接](https://gitee.com/wonderful-code/buildadmin/releases)\n- BuildAdmin的代码版本与下载地址：[代码版本与下载](https://wonderful-code.gitee.io/guide/#%E4%BB%A3%E7%A0%81%E7%89%88%E6%9C%AC%E4%B8%8E%E4%B8%8B%E8%BD%BD)', 'text-embedding-v1', '', 3, '[BuildAdmin的发行页面](https://gitee.com/wonderful-code/buildadmin/releases)', 0, 0, '[]', 'query', 'pending', 1696307509, 1695241818),
       (4, '1', 'qa', '模块市场链接', '# BuildAdmin模块市场链接.md\n\n- [https://modules.buildadmin.com/](https://modules.buildadmin.com/)。', 'text-embedding-v1', '', 4, '[BuildAdmin模块市场](https://modules.buildadmin.com/)', 0, 0, '[]', 'query', 'pending', NULL, 1695241893),
       (5, '1', 'qa', '演示站点链接是什么？', '# BuildAdmin演示站链接.md\n\n- [https://demo.buildadmin.com/](https://demo.buildadmin.com/)', 'text-embedding-v1', '', 5, '[BuildAdmin演示站](https://demo.buildadmin.com/)', 0, 0, '[]', 'query', 'pending', NULL, 1695241934),
       (6, '1', 'qa', '代码仓库链接、gitee、github？', 'BuildAdmin有以下两个代码仓库链接：\n- [BuildAdmin的Gitee代码仓库链接](https://gitee.com/wonderful-code/buildadmin)\n- [BuildAdmin的Github代码仓库链接](https://github.com/build-admin/buildadmin)', 'text-embedding-v1', '', 6, '[BuildAdmin的Gitee代码仓库](https://gitee.com/wonderful-code/buildadmin)', 0, 0, '[]', 'query', 'pending', NULL, 1695241997),
       (7, '1', 'qa', '免费开源的吗、可以商用吗？', 'BuildAdmin是一个基于Apache2.0协议开源的后台管理系统，提供无需授权的免费商用。', 'text-embedding-v1', '', 7, '[BuildAdmin介绍文档](https://wonderful-code.gitee.io/)', 0, 0, '[]', 'query', 'pending', 1696397556, 1695242070);


CREATE TABLE IF NOT EXISTS `__PREFIX__ai_session`
(
    `id`                int UNSIGNED                                                  NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title`             varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '会话标题',
    `user_id`           int UNSIGNED                                                  NOT NULL DEFAULT 0 COMMENT 'AI用户ID',
    `status`            tinyint UNSIGNED                                              NOT NULL DEFAULT 1 COMMENT '状态:0=已删除,1=有效',
    `create_time`       bigint UNSIGNED                                               NULL     DEFAULT NULL COMMENT '创建时间',
    `last_message_time` bigint UNSIGNED                                               NULL     DEFAULT NULL COMMENT '最后消息时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = '会话表'
  ROW_FORMAT = Dynamic;


CREATE TABLE IF NOT EXISTS `__PREFIX__ai_session_message`
(
    `id`                 int UNSIGNED                                                                  NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `type`               enum ('text','image','link') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text' COMMENT '类型:text=普通文本,image=图片,link=链接',
    `session_id`         int UNSIGNED                                                                  NOT NULL DEFAULT 0 COMMENT '所属会话',
    `sender_type`        enum ('ai','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci           NOT NULL DEFAULT 'ai' COMMENT '发送人类型:ai=AI,user=用户',
    `sender_id`          varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                  NOT NULL DEFAULT '' COMMENT '发送人',
    `content`            text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                         NULL COMMENT '消息内容',
    `tokens`             int                                                                           NOT NULL DEFAULT 0 COMMENT 'Tokens',
    `kbs`                varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci                 NOT NULL DEFAULT '' COMMENT '知识点引用',
    `consumption_tokens` int UNSIGNED                                                                  NOT NULL DEFAULT 0 COMMENT '消耗tokens数额',
    `create_time`        bigint UNSIGNED                                                               NULL     DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = '会话消息表'
  ROW_FORMAT = Dynamic;


CREATE TABLE IF NOT EXISTS `__PREFIX__ai_user`
(
    `id`            int UNSIGNED                                                           NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_type`     enum ('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user' COMMENT '绑定用户类型:user=会员,admin=管理员',
    `user_id`       int UNSIGNED                                                           NOT NULL DEFAULT 0 COMMENT '绑定用户',
    `tokens`        int                                                                    NOT NULL DEFAULT 0 COMMENT 'tokens余额',
    `messages`      int                                                                    NOT NULL DEFAULT 0 COMMENT '消息数',
    `status`        tinyint UNSIGNED                                                       NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `last_use_time` bigint UNSIGNED                                                        NULL     DEFAULT NULL COMMENT '上次使用时间',
    `create_time`   bigint UNSIGNED                                                        NULL     DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = 'AI会员管理'
  ROW_FORMAT = Dynamic;


CREATE TABLE IF NOT EXISTS `__PREFIX__ai_user_tokens`
(
    `id`          int UNSIGNED                                                  NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `ai_user_id`  int UNSIGNED                                                  NOT NULL DEFAULT 0 COMMENT 'AI会员ID',
    `tokens`      int                                                           NOT NULL DEFAULT 0 COMMENT '变更tokens',
    `before`      int                                                           NOT NULL DEFAULT 0 COMMENT '变更前tokens',
    `after`       int                                                           NOT NULL DEFAULT 0 COMMENT '变更后tokens',
    `memo`        varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `create_time` bigint UNSIGNED                                               NULL     DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = 'AI会员tokens记录'
  ROW_FORMAT = Dynamic;

BEGIN;
ALTER TABLE `__PREFIX__ai_session_message` ADD COLUMN `reasoning_content` text NULL COMMENT '推理过程' AFTER `content`;
COMMIT;