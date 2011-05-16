-- replace the sfx url base
UPDATE items SET url = replace( url, 'http://sfx.galib.uga.edu/sfx_emu1', 'http://sfxhosted.exlibrisgroup.com/emu');
UPDATE items SET url = replace( url, 'http://sfx.galib.uga.edu.proxy.library.emory.edu/sfx_emu1', 'http://sfxhosted.exlibrisgroup.com/emu');
UPDATE items SET url = replace( url, 'http://sfx.galib.uga.edu/emu', 'http://sfxhosted.exlibrisgroup.com/emu');

-- remove fax from table help_art_tags
DELETE FROM help_art_tags WHERE article_id=17;

-- remove fax from table help_articles
-- should remove 5 entries from 66 with relations to 15,16,18,19,21
DELETE FROM help_art_to_art WHERE article1_id=17 OR article2_id=17;

-- remove fax from table help_art_to_art
DELETE FROM help_art_to_role WHERE article_id=17;

-- remove fax from table help_art_to_role
DELETE FROM help_articles WHERE id=17;
