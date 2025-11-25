-- 修复 ra_admin_rule 表中的腾讯云菜单冗余和错误数据
-- 删除重复的腾讯云菜单数据，保留第一套完整的数据

-- 删除重复的腾讯云主菜单（保留ID=335，删除349、350）
DELETE FROM `ra_admin_rule` WHERE `name` = 'TencentCloud' AND `id` IN (349, 350);

-- 删除重复的配置管理菜单（保留ID=336下的按钮，删除351）
DELETE FROM `ra_admin_rule` WHERE `name` = 'TencentConfig' AND `id` = 351;

-- 删除重复的配置管理按钮（保留ID=336下的按钮，删除352-356）
DELETE FROM `ra_admin_rule` WHERE `pid` = 336 AND `id` IN (352, 353, 354, 355, 356);

-- 删除重复的文件上传菜单（保留ID=342下的按钮，删除357）
DELETE FROM `ra_admin_rule` WHERE `name` = 'TencentUpload' AND `id` = 357;

-- 删除重复的文件上传按钮（保留ID=342下的按钮，删除358-359）
DELETE FROM `ra_admin_rule` WHERE `pid` = 342 AND `id` IN (358, 359);

-- 删除重复的内容同步菜单（保留ID=345下的按钮，删除360）
DELETE FROM `ra_admin_rule` WHERE `name` = 'TencentSync' AND `id` = 360;

-- 删除重复的内容同步按钮（保留ID=345下的按钮，删除361-363）
DELETE FROM `ra_admin_rule` WHERE `pid` = 345 AND `id` IN (361, 362, 363);

-- 验证修复结果
SELECT 
    id, pid, name, title, type, weigh, status, remark 
FROM `ra_admin_rule` 
WHERE `name` IN ('TencentCloud', 'TencentConfig', 'TencentUpload', 'TencentSync') 
    OR `name` LIKE 'TencentConfig/%' 
    OR `name` LIKE 'TencentCloud/%'
ORDER BY id;