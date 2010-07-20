-- Migration script for Release 2.6.X
-- This release is performed in conjunction with rd_registrar_scripts update.

-- Update the override_feed flag for selected courses, so their
-- crosslisting will not be updated during the opus import scripts feeds.

update course_aliases set override_feed = 2 where 
registrar_key = '5106_EMORY_BIOL_141_SECL1A' OR
registrar_key = '5106_EMORY_BIOL_141_SECL2A' OR
registrar_key = '5106_EMORY_BUS_531P_SEC01F' OR
registrar_key = '5106_EMORY_BUS_634P_SEC01F' OR
registrar_key = '5106_EMORY_BUS_551E_SECEXF2' OR
registrar_key = '5106_EMORY_BUS_531E_SECEXF2' OR
registrar_key = '5109_EMORY_ARTHIST_101_SEC001' OR
registrar_key = '5109_EMORY_ARTHIST_101_SEC002' OR
registrar_key = '5109_EMORY_ARTHIST_101_SEC003' OR
registrar_key = '5109_EMORY_ARTHIST_101_SEC004' OR
registrar_key = '5109_EMORY_ARTHIST_101_SEC005' OR
registrar_key = '5109_EMORY_ARTHIST_101_SEC006' OR
registrar_key = '5109_EMORY_ARTHIST_101_SEC007' OR
registrar_key = '5109_EMORY_BIOL_205_SEC00P' OR
registrar_key = '5109_EMORY_BIOL_205_SECLA1' OR
registrar_key = '5109_EMORY_BIOL_205_SECLC1' OR
registrar_key = '5109_EMORY_BIOL_205_SECLD1' OR
registrar_key = '5109_EMORY_MESAS_370_SEC000' OR
registrar_key = '5109_EMORY_IDS_385_SEC001' OR
registrar_key = '5109_EMORY_POLS_227_SEC000' OR
registrar_key = '5109_EMORY_AEPI_515D_SECCMP2' OR
registrar_key = '5109_EMORY_APHI_501D_SECCMP2';
