UPDATE core_config_data SET value = "http://bmw.rockar.local/" WHERE path = "web/unsecure/base_url" AND scope_id = (select store_id from core_store where code = "bmw_store_view");
UPDATE core_config_data SET value = "http://mini.rockar.local/" WHERE path = "web/unsecure/base_url" AND scope_id = (select store_id from core_store where code = "mini_store_view");
UPDATE core_config_data SET value = "http://motorrad.rockar.local/" WHERE path = "web/unsecure/base_url" AND scope_id = (select store_id from core_store where code = "motorrad_store_view");
UPDATE core_config_data SET value = "http://peppermint.admin.local/" WHERE path = "web/unsecure/base_url" AND scope_id = (select store_id from core_store where code = "admin");
UPDATE core_config_data SET value = "http://peppermint.admin.local/" WHERE path = "web/unsecure/base_url" AND scope_id = (select store_id from core_store where code = "demo");
UPDATE core_config_data SET value = "peppermint.admin.local" WHERE path = "web/cookie/cookie_domain" AND scope_id = (select store_id from core_store where code = "admin");
