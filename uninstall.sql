
DELETE FROM `core_config_data` WHERE `path` LIKE 'payment/smspay%';

DELETE FROM `core_resource` WHERE `code` = 'smspay_setup';

DELETE FROM `log_url_info` WHERE `url` LIKE '%smspay%';

-- Enabled delete
UPDATE `admin_user` SET `extra` = REPLACE( `extra`, 's:14:"payment_smspay";s:1:"1";', '' )
-- Disabled delete
UPDATE `admin_user` SET `extra` = REPLACE( `extra`, 's:14:"payment_smspay";s:1:"0";', '' )

