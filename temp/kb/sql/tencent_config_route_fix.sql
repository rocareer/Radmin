-- 修复腾讯云配置路由无效问题
-- 修复菜单类型为 NULL 的问题，确保路由能够正确识别和加载

-- 修复腾讯云主菜单的 menu_type
UPDATE `ra_admin_rule` SET `menu_type` = 'tab' WHERE `id` = 335 AND `menu_type` IS NULL;

-- 修复配置管理菜单的 menu_type
UPDATE `ra_admin_rule` SET `menu_type` = 'tab' WHERE `id` = 336 AND `menu_type` IS NULL;

-- 修复文件上传菜单的 menu_type
UPDATE `ra_admin_rule` SET `menu_type` = 'tab' WHERE `id` = 342 AND `menu_type` IS NULL;

-- 修复内容同步菜单的 menu_type
UPDATE `ra_admin_rule` SET `menu_type` = 'tab' WHERE `id` = 345 AND `menu_type` IS NULL;

-- 验证修复结果
SELECT 
    id, 
    name, 
    title, 
    menu_type, 
    component 
FROM `ra_admin_rule` 
WHERE `id` IN (335, 336, 342, 345);