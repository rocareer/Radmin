-- 修复 ra_admin_rule 表中腾讯云相关菜单的菜单类型问题
-- 将 menu_type 从 NULL 修改为 'tab'，使系统能够正确识别菜单类型

-- 修复腾讯云主菜单的菜单类型
UPDATE `ra_admin_rule` 
SET `menu_type` = 'tab' 
WHERE `id` = 335 AND `name` = 'TencentCloud';

-- 修复配置管理菜单的菜单类型
UPDATE `ra_admin_rule` 
SET `menu_type` = 'tab' 
WHERE `id` = 336 AND `name` = 'TencentConfig';

-- 修复文件上传菜单的菜单类型
UPDATE `ra_admin_rule` 
SET `menu_type` = 'tab' 
WHERE `id` = 342 AND `name` = 'TencentUpload';

-- 修复内容同步菜单的菜单类型
UPDATE `ra_admin_rule` 
SET `menu_type` = 'tab' 
WHERE `id` = 345 AND `name` = 'TencentSync';

-- 确保内容同步菜单有正确的图标
UPDATE `ra_admin_rule` 
SET `icon` = 'fa fa-sync' 
WHERE `id` = 345 AND `name` = 'TencentSync';

-- 验证修复结果
SELECT 
    id, pid, name, title, type, menu_type, icon, weigh, status, remark 
FROM `ra_admin_rule` 
WHERE `name` IN ('TencentCloud', 'TencentConfig', 'TencentUpload', 'TencentSync') 
ORDER BY id;