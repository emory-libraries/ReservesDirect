<?php
/*******************************************************************************
help.class.php
Manipulates context-help data
Contains 3 classes: Help, Help_Article, and Help_Category

Created by Dmitriy Panteleyev (dpantel@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/

/**
 * @desc Class for manipulating groups of help categories, articles, tags, etc
 */
class Help {
	/**
	 * Declaration
	 */
	protected $permission_level;


	/**
	 * @return void
	 * @param int $permission_level
	 * @desc Constructor - Initializes the Help object based on the users's permission level
	 */
	public function __construct($permission_level) {
		//set permission level - set to 0 (student) by default
		$this->permission_level = !empty($permission_level) ? $permission_level : 0;
	}
	

	/**
	 * @return array
	 * @param array $tags Array of tags (as strings)
	 * @desc Returns array of Help_Article objects matching the passed tags that this user is allowed to view
	 */
	public function findArticlesByTags($query) {
		global $g_dbConn, $g_permission;

		if(empty($query)) {
			return array();
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT t.article_id
						FROM help_art_tags AS t";
				
				//only check perms for users < staff
				if($this->permission_level < $g_permission['staff']) {
					$sql .= " JOIN help_art_to_role AS r ON r.article_id = t.article_id AND r.permission_level = {$this->permission_level} AND r.can_view = 1";
				}
				
				$sql .= " WHERE MATCH (t.tag) AGAINST ('$query') LIMIT 100";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$articles = array();
		while($row = $rs->fetchRow()) {
			$articles[$row[0]] = new Help_Article($row[0]);
		}

		return $articles;

		########## NOTE ##############################
		# I am not certain how well the mysql search engine 
		# will match single-word tags out of a query phrase.
		#
		# An alternative method would be to parse the
		# query phrase [ Help_Article::parseTags($query, false); ]
		# and then loop through the resulting single-tag array,
		# running the match query against each tag separately
		##############################################
	}


	/**
	 * @return array
	 * @param string $query String to search for
	 * @desc Performs full-text search against the database and returns relevant articles that the user has permission to view
	 */
	public function findArticlesByKeyword($query) {
		global $g_dbConn, $g_permission;

		if(empty($query)) {
			return array();
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT a.id
						FROM help_articles AS a";
				
				//only check perms for users < staff
				if($this->permission_level < $g_permission['staff']) {
					$sql .= " JOIN help_art_to_role AS r ON r.article_id = a.id AND r.permission_level = {$this->permission_level} AND r.can_view = 1";
				}
							
				$sql .= " WHERE MATCH (a.title, a.body) AGAINST ('$query') LIMIT 100";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$articles = array();
		while($row = $rs->fetchRow()) {
			$articles[$row[0]] = new Help_Article($row[0]);
		}

		return $articles;
	}


	/**
	 * @return array
	 * @param int $category_id Category ID
	 * @desc Returns array of articles that belong to the specified category and that the user has permission to view
	 */
	public function getArticlesByCategory($category_id) {
		global $g_dbConn, $g_permission;

		if(empty($category_id)) {
			return array();
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT a.id
						FROM help_articles AS a";
				
				//only check perms for users < staff
				if($this->permission_level < $g_permission['staff']) {
					$sql .= " JOIN help_art_to_role AS r ON r.article_id = a.id AND r.permission_level = {$this->permission_level} AND r.can_view = 1";
				}
				
				$sql .= " WHERE a.category_id = $category_id";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$articles = array();
		while($row = $rs->fetchRow()) {
			$articles[] = new Help_Article($row[0]);
		}

		return $articles;
	}
	
	
	/**
	 * @return array
	 * @param string $tag_string An exact tag or a sequence of exact tags separated by '+' (ex: 'tag1+tag2+tag3')
	 * @desc Returns array of article objects (indexed by article id) that match the exact tag specified. 
	 */
	public function getArticlesByExactTag($tag_string) {	
		global $g_dbConn, $g_permission;

		if(empty($tag_string)) {
			return array();
		}
		
		//build the query to get articles by a single tag	
		switch($g_dbConn->phptype) {
			default:	//mysql				
				//build the first part of query
				$sql_select = "SELECT DISTINCT a.id FROM help_articles AS a";
				
				//handle input of multiple tags; multiple tags separated by a space or a '+'
				$tags = preg_split("/[+|\s]+/", $tag_string, -1, PREG_SPLIT_NO_EMPTY);
				
				$sql_join = '';
				$x=0;
				foreach($tags as $tag) {
					$sql_join .= " JOIN help_art_tags AS t$x ON t$x.article_id = a.id AND t$x.tag = '$tag'";
					$x++;
				}
				
				//only check perms for users < staff
				if($this->permission_level < $g_permission['staff']) {
					$sql_join .= " JOIN help_art_to_role AS r ON r.article_id = a.id AND r.permission_level = {$this->permission_level} AND r.can_view = 1";
				}
		}
		
		$rs = $g_dbConn->query($sql_select.$sql_join);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$articles = array();
		while($row = $rs->fetchRow()) {
			$articles[$row[0]] = new Help_Article($row[0]);
		}

		return $articles;	
	}	
	
		
	/**
	 * @return array
	 * @param string $tag_string String of extact tag(s)
	 * @desc Returns array of related tags; array indexed by tag, with boolean values.  If value is TRUE, tag is a user tag, else the tag is a general tag
	 */
	public function getRelatedTags($tag_string) {
		global $g_dbConn, $g_permission, $u;

		//get all the articles for this tag_string
		$articles = $this->getArticlesByExactTag($tag_string);
		
		//go through articles and pool their tags
		$tags = array();
		foreach($articles as $article) {
			$user_tags = $article->getUserTags($u->getUserID());
			
			foreach($article->getTags() as $tag) {
				//do not include the queried tag(s)
				if(stripos($tag_string, $tag) !== false) {	//tag found in query
					continue;	//skip
				}
				
				$is_user_tag = in_array($tag, $user_tags) ? true : false;				
				$tags[$tag] = $is_user_tag;
			}
		}
		
		return $tags;
	}


	/**
	 * @return array
	 * @param int $article_id ID of article for which to get related articles
	 * @desc Returns an array of related articles that the user is allowed to view.
	 */
	public function getRelatedArticles($article_id) {
		global $g_dbConn, $g_permission;

		if(empty($article_id)) {
			return array();
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT ra.article1_id, ra.article2_id
						FROM help_art_to_art AS ra";
				
				//only check perms for users < staff
				if($this->permission_level < $g_permission['staff']) {
					$sql .= " JOIN help_art_to_role AS r ON r.article_id = ra.article1_id AND r.permission_level = {$this->permission_level} AND r.can_view = 1
							JOIN help_art_to_role AS r2 ON r2.article_id = ra.article2_id AND r2.permission_level = {$this->permission_level} AND r2.can_view = 1";
				}
				
				$sql .= " WHERE ra.relation_2to1 = 'sibling' AND (ra.article1_id = $article_id OR ra.article2_id = $article_id)";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$articles = array();
		while($row = $rs->fetchRow()) {
			//figure out which is the related article -- if the result is not the original article, then it's related
			$rel_art_id = ($row[0] != $article_id) ? $row[0] : $row[1];
			//init object
			$articles[] = new Help_Article($rel_art_id);
		}

		return $articles;
	}
	
	
	/**
	 * @return array
	 * @param int $article_id ID of article for which to get followup articles
	 * @desc Returns an array of follow-up (child) articles that the user is allowed to view.
	 */
	public function getFollowUpArticles($article_id) {
		global $g_dbConn, $g_permission;

		if(empty($article_id)) {
			return array();
		}
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT ra.article2_id
						FROM help_art_to_art AS ra";
				
				//only check perms for users < staff
				if($this->permission_level < $g_permission['staff']) {
					$sql .= " JOIN help_art_to_role AS r ON r.article_id = ra.article2_id AND r.permission_level = {$this->permission_level} AND r.can_view = 1";
				}
				
				$sql .= " WHERE ra.relation_2to1 = 'child' AND ra.article1_id = $article_id";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$articles = array();
		while($row = $rs->fetchRow()) {
			$articles[] = new Help_Article($row[0]);
		}

		return $articles;
	}


	/**
	 * @return array
	 * @desc Returns an array of Help_Category objects
	 */
	public function getCategories() {
		global $g_dbConn, $g_permission;

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT c.id FROM help_categories AS c";

				//only check perms for users < staff
				if($this->permission_level < $g_permission['staff']) {
					$sql .= " JOIN help_cat_to_role AS r ON r.category_id = c.id AND r.permission_level = {$this->permission_level} AND r.can_view = 1";
				}
				
				$sql .= " ORDER BY c.title ASC";
		}
		
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$categories = array();
		while($row = $rs->fetchRow()) {
			$categories[] = new Help_Category($row[0]);
		}

		return $categories;
	}
}


/**
 * @desc Class for manipulating individual Help-Article data
 */
class Help_Article {
	/**
	 * Declaration
	 * Note: It is very important that the variable NAMES match DB field names; If they do not, set() function must be edited accordingly.
	 */
	protected $id, $category_id, $title, $body, $date_created, $date_modified;
	protected $tags, $user_tags, $view_permissions, $edit_permissions;


	/**
	 * @return void
	 * @param int $article_id (optional)
	 * @desc Constructor
	 */
	public function __construct($article_id=null) {
		//set up some default values
		$this->tags = $this->user_tags = array();
		$this->view_permissions = $this->edit_permissions = array();

		if(!empty($article_id)) {
			$this->getByID($article_id);
		}
	}


	/**
	 * @return boolean
	 * @param int $article_id
	 * @desc Initializes object with data for article with specified ID. Returns true on success, false otherwise
	 */
	public function getByID($article_id) {
		global $g_dbConn;

		if(empty($article_id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT id, category_id, title, body, date_created, date_modified FROM help_articles WHERE id = $article_id";
		}

		//get article info
		$rs = $g_dbConn->getRow($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if(!empty($rs)) {
			//set article info
			list($this->id, $this->category_id, $this->title, $this->body, $this->date_created, $this->date_modified) = $rs;

			//get tags
			$this->fetchTags();

			//get permissions
			$this->fetchPermissions();

			return true;
		}
		else {
			return false;
		}
	}


	/**
	 * @return boolean
	 * @param string $title Article title
	 * @param string $body Article text
	 * @param int $category_id ID of article category
	 * @desc Creates new article record in DB; Returns true on success, false otherwise
	 */
	public function createArticle($title, $body, $category_id) {
		global $g_dbConn;

		//require title, body and category
		if(empty($title) || empty($body) || empty($category_id)) {
			return false;
		}

		$title = trim($title);
		$body = trim($body);

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql_insert = "INSERT INTO help_articles (category_id, title, body, date_created, date_modified)
								VALUES ('$category_id', '$title', '$body', NOW(), NOW())";
				$sql_inserted_id = "SELECT LAST_INSERT_ID() FROM help_articles";
		}

		//create new article
		$rs = $g_dbConn->query($sql_insert);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		//get the id
		$rs = $g_dbConn->getOne($sql_inserted_id);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		//initialize this object with new values
		$this->id = $rs;
		$this->category_id = $category_id;
		$this->title = $title;
		$this->body = $body;
		$this->date_created = $this->date_modified = date('Y-m-d');

		return true;
	}


	/**
	 * @desc Getter methods
	 */
	public function getID() { return $this->id; }
	public function getCategoryID() { return $this->category_id; }
	public function getTitle() { return stripslashes($this->title); }
	public function getBody() { return stripslashes($this->body); }
	public function getDateCreated() { return $this->date_created; }
	public function getDateModified() { return $this->date_modified; }
	public function getTags() { return array_keys($this->tags); }
	public function getUserTags($user_id) {
		return !empty($this->user_tags[$user_id]) ? $this->user_tags[$user_id] : array();
	}


	/**
	 * @desc Setter methods
	 */
	public function setCategoryID($new_cat_id) { return $this->set('category_id', $new_cat_id); }
	public function setTitle($new_title) { return $this->set('title', trim($new_title)); }
	public function setBody($new_body) { return $this->set('body', trim($new_body)); }


	/**
	 * @return array
	 * @param string $tags_string String of tag terms
	 * @param boolean $replace_article_tags (optional) If true, will clear all current tags and add the newly-parsed
	 * @param int $user_id (optional) Required if $replace_article_tags is TRUE; User ID whose tags to replace
	 * @desc Splits the tags string on space, commas, and semicolons. Returns parsed tags as array
	 */
	public function parseTags($tags_string, $replace_article_tags=false, $user_id=null) {
		//split tags on spaces, commas, and semicolons
		$tags = preg_split("/[,;\s]+/", $tags_string, -1, PREG_SPLIT_NO_EMPTY);
		//trim the tags
		array_walk($tags, 'trim');
	
		//replace tags
		if($replace_article_tags && !empty($user_id)) {
			//clear old tags for this user
			$this->removeAllTags($user_id);

			//add new ones
			foreach($tags as $tag) {
				$this->addTag($tag, $user_id);
			}
		}

		return $tags;
	}
	

	/**
	 * @return boolean
	 * @param string $tag
	 * @param int $user_id ID of user adding the tag
	 * @desc Adds tag to article; returns true on success, false on failure
	 */
	public function addTag($tag, $user_id) {
		global $g_dbConn;

		if(empty($tag) || empty($this->id) || empty($user_id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "INSERT INTO help_art_tags (article_id, tag, user_id) VALUES ({$this->id}, '$tag', '$user_id')";
		}

		$rs = $g_dbConn->query($sql);
		if(DB::isError($rs)) {
			//if this is a "duplicate entry" error, it just means this article/tag/user_id tuple already exists, so ignore that message
			if(stripos($rs->getMessage(), 'duplicate') === false) {
				trigger_error($rs->getMessage(), E_USER_ERROR);
			}
		}

		//refetch tags
		$this->fetchTags();
		
		return true;
	}


	/**
	 * @return boolean
	 * @param string $tag Tag to remove
	 * @param int $user_id (optional) If specified only remove this tag for the specified user
	 * @desc Removes tag from article in DB;
	 */
	public function removeTag($tag, $user_id=null) {
		global $g_dbConn;

		if(empty($this->id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "DELETE FROM help_art_tags WHERE article_id = {$this->id} AND tag = '$tag'";
				
				if(!empty($user_id)) {
					$sql .= " AND user_id = '$user_id'";
				}
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		//refetch tags
		$this->fetchTags();

		return true;
	}


	/**
	 * @return boolean
	 * @param int $user_id (optional) If specified, will remove all tags for the specified user only.
	 * @desc Clears all tags from this article
	 */
	public function removeAllTags($user_id=null) {
		global $g_dbConn;

		if(empty($this->id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "DELETE FROM help_art_tags WHERE article_id = {$this->id}";
				
				if(!empty($user_id)) {
					$sql .= " AND user_id = '$user_id'";
				}
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if(!empty($user_id)) {	//if deleting only one user's tags
			//it's easier to re-fetch all tags instead of doing a delta on the object's vars
			$this->fetchTags();
		}
		else {	//clear all tags
			$this->tags = array();
		}

		return true;
	}


	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @desc Returns true if a user with specified permission level is allowed to view this article, false otherwise;
	 */
	public function canUserView($user_permission_level) {
		global $g_permission;
		
		if($user_permission_level >= $g_permission['staff']) {
			return true;
		}
		elseif(isset($this->view_permissions[$user_permission_level])) {
			return $this->view_permissions[$user_permission_level];
		}
		else {
			return false;
		}
	}
	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @desc Returns true if a user with specified permission level is allowed to edit this article, false otherwise;
	 */
	public function canUserEdit($user_permission_level) {
		global $g_permission;
		
		if($user_permission_level >= $g_permission['staff']) {
			return true;
		}
		elseif(isset($this->edit_permissions[$user_permission_level])) {
			return $this->edit_permissions[$user_permission_level];
		}
		else {
			return false;
		}
	}


	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @param boolean $can_edit Flag showing whether the permission should be granted or revoked
	 */
	public function setEditPermission($user_permission_level, $can_edit) {
		$this->setPermissions($user_permission_level, 'edit', $can_edit);
	}
	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @param boolean $can_view Flag showing whether the permission should be granted or revoked
	 */
	public function setViewPermission($user_permission_level, $can_view) {
		$this->setPermissions($user_permission_level, 'view', $can_view);
	}
	
	
	/**
	 * @return boolean
	 * @param int $related_article_id ID of related article
	 * @desc Deletes relationship between this article and related article.
	 */
	public function deleteRelationship($related_article_id) {
		global $g_dbConn;

		if(empty($this->id) || empty($related_article_id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "DELETE FROM help_art_to_art WHERE article1_id = {$this->id} AND article2_id = $related_article_id";
				$sql_r = "DELETE FROM help_art_to_art WHERE article1_id = $related_article_id AND article2_id = {$this->id}";
		}
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		$rs = $g_dbConn->query($sql_r);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		return true;
	}


	/**
	 * @return boolean
	 * @param int $related_article_id ID of related article
	 * @param string $relationship How $related_article_id relates to this article. Currently 'child' or 'sibling' are supported
	 * @desc Adds/updates a relationship between this article and another one.
	 */
	public function setRelatedArticle($related_article_id, $relationship='child') {
		global $g_dbConn;

		if(empty($this->id) || empty($related_article_id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql_select = "SELECT article1_id FROM help_art_to_art WHERE article1_id = {$this->id} AND article2_id = $related_article_id";
				$sql_select_reverse = "SELECT article1_id FROM help_art_to_art WHERE article1_id = $related_article_id AND article2_id = {$this->id}";
				$sql_update = "UPDATE help_art_to_art SET relation_2to1 = '$relationship' WHERE article1_id = {$this->id} AND article2_id = $related_article_id";
				$sql_update_reverse = "UPDATE help_art_to_art SET relation_2to1 = '$relationship' WHERE article1_id = $related_article_id AND article2_id = {$this->id}";
				$sql_insert = "INSERT INTO help_art_to_art (article1_id, article2_id, relation_2to1) VALUES ({$this->id}, $related_article_id, '$relationship')";
		}

		//determine if need to insert or update
		$rs = $g_dbConn->query($sql_select);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		$rs2 = $g_dbConn->query($sql_select_reverse);
		if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }

		if($rs->numRows() > 0) {	//row exists, update
			$rs = $g_dbConn->query($sql_update);
		}
		elseif($rs2->numRows() > 0) {	//row exists (in reverse), update
			$rs = $g_dbConn->query($sql_update_reverse);
		}
		else {	//insert
			$rs = $g_dbConn->query($sql_insert);
		}
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		return true;
	}


	/**
	 * @return boolean
	 * @param string $var Variable/DB-field name
	 * @param string $val New value for that variable/field
	 * @desc Attempts to update db/object variable with new value; Returns true on success, false otherwise
	 */
	protected function set($var, $val) {
		global $g_dbConn;

		//do not allow setting of anything if never initialized object with DB record ID
		if(empty($this->id)) {
			return false;
		}

		//do not allow setting of ID or empty var/val
		if(($var == 'id') || empty($var) || empty($val)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "UPDATE help_articles SET `$var` = '$val', date_modified = NOW() WHERE id = {$this->id}";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->$var = $val;
		return false;
	}


	/**
	 * @return boolean
	 * @param int $permission_level Permission level (by default 0 - 5)
	 * @param string $permission_type Type of permission; Currently only 'view' and 'edit' are supported
	 * @param boolean $allow If true will grant permission, else will revoke it.
	 */
	protected function setPermissions($permission_level, $permission_type='view', $allow=true) {
		global $g_dbConn;

		if(empty($this->id) || (empty($permission_level) && ($permission_level != 0))) {
			return false;
		}
		$allow = $allow ? 1 : 0;

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql_select = "SELECT article_id FROM help_art_to_role WHERE article_id = {$this->id} AND permission_level = $permission_level";
				$sql_insert = "INSERT INTO help_art_to_role (article_id, permission_level, can_$permission_type) VALUES ({$this->id}, '$permission_level', $allow)";
				$sql_update = "UPDATE help_art_to_role SET can_$permission_type = $allow WHERE article_id = {$this->id} AND permission_level = $permission_level";
		}

		//determine if need to insert or update
		$rs = $g_dbConn->query($sql_select);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if($rs->numRows() > 0) {	//row exists, update
			$rs2 = $g_dbConn->query($sql_update);
		}
		else {	//insert
			$rs2 = $g_dbConn->query($sql_insert);
		}
		if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }

		return true;
	}
	
	
	/**
	 * @return boolean
	 * @desc Fetches tags from DB and initializes object's (tag) variables
	 */
	protected function fetchTags() {
		global $g_dbConn;	
		
		if(empty($this->id)) {
			return false;
		}
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT tag, user_id FROM help_art_tags WHERE article_id = {$this->id} ORDER BY tag ASC";
		}
		
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->tags = array();
		$this->user_tags = array();
		while($row = $rs->fetchRow()) {
			//store tags as subarrays indexed by user_id
			if(!empty($row[1])) {
				$this->user_tags[$row[1]][] = $row[0];
			}
			
			//also store tags in a bunch
			//do a little trick to count how many times an article has been tagged with a specific tag
			//store them as $tags['tag'] = count;
			if(isset($this->tags[$row[0]])) {	//tag already exists, increment counter
				$this->tags[$row[0]]++;
			}
			else {	//tag seen for the first time
				$this->tags[$row[0]] = 1;
			}
		}
		
		return true;		
	}
	
	
	/**
	 * @return boolean
	 * @desc Fetches permissions from DB and initializes object's (permissions) variables
	 */
	protected function fetchPermissions() {	
		global $g_dbConn;

		if(empty($this->id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT permission_level, can_view, can_edit FROM help_art_to_role WHERE article_id = {$this->id}";
		}

		//get permissions
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->view_permissions = array();
		$this->edit_permissions = array();
		while($row = $rs->fetchRow()) {
			$this->view_permissions[$row[0]] = ($row[1] == 1) ? true : false;
			$this->edit_permissions[$row[0]] = ($row[2] == 1) ? true : false;
		}
	}
}



/**
 * @desc Class for manipulating individual Help-Category data
 */
class Help_Category {
	/**
	 * Declaration
	 * Note: It is very important that the variable NAMES match DB field names; If they do not, set() function must be edited accordingly.
	 */
	protected $id, $title, $description;
	protected $view_permissions, $edit_permissions;


	/**
	 * @return void
	 * @param int $article_id (optional)
	 * @desc Constructor
	 */
	public function __construct($category_id=null) {
		if(!empty($category_id)) {
			$this->getByID($category_id);
		}
	}


	/**
	 * @return boolean
	 * @param int $article_id
	 * @desc Initializes object with data for article with specified ID. Returns true on success, false otherwise
	 */
	public function getByID($category_id) {
		global $g_dbConn;

		if(empty($category_id)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT id, title, description FROM help_categories WHERE id = $category_id";
				$sql_permissions = "SELECT permission_level, can_view, can_edit FROM help_cat_to_role WHERE category_id = $category_id";
		}

		//get category info
		$rs = $g_dbConn->getRow($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		//set category info
		if(!empty($rs)) {
			list($this->id, $this->title, $this->description) = $rs;

			//get permissions
			$rs = $g_dbConn->query($sql_permissions);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

			$this->view_permissions = array();
			$this->edit_permissions = array();
			while($row = $rs->fetchRow()) {
				$this->view_permissions[$row[0]] = ($row[1] == 1) ? true : false;
				$this->edit_permissions[$row[0]] = ($row[2] == 1) ? true : false;
			}

			return true;
		}
		else {
			return false;
		}
	}


	/**
	 * @return boolean
	 * @param string $title Category title
	 * @param string $description Category description
	 * @desc Creates new category record in DB; Returns true on success, false otherwise
	 */
	public function createCategory($title, $description) {
		global $g_dbConn;


		//require title, body and category
		if(empty($title) || empty($description)) {
			return false;
		}

		$title = trim($title);
		$description = trim($description);

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql_insert = "INSERT INTO help_categories (title, description) VALUES ('$title', '$description')";
				$sql_inserted_id = "SELECT LAST_INSERT_ID() FROM help_categories";
		}

		//create new category
		$rs = $g_dbConn->query($sql_insert);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		//get the id
		$rs = $g_dbConn->getOne($sql_inserted_id);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		//initialize this object with new values
		$this->id = $rs;
		$this->title = $title;
		$this->description = $description;

		return true;
	}


	/**
	 * @desc Getter methods
	 */
	public function getID() { return $this->id; }
	public function getTitle() { return stripslashes($this->title); }
	public function getDescription() { return stripslashes($this->description); }


	/**
	 * @desc Setter methods
	 */
	public function setTitle($new_title) { return $this->set('title', trim($new_title)); }
	public function setDescription($new_description) { return $this->set('description', trim($new_description)); }


	/**
	 * @return boolean
	 * @param string $var Variable/DB-field name
	 * @param string $val New value for that variable/field
	 * @desc Attempts to update db/object variable with new value; Returns true on success, false otherwise
	 */
	protected function set($var, $val) {
		global $g_dbConn;

		//do not allow setting of anything if never initialized object with DB record ID
		if(empty($this->id)) {
			return false;
		}

		//do not allow setting of ID or empty var/val
		if(($var == 'id') || empty($var) || empty($val)) {
			return false;
		}

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "UPDATE help_categories SET `$var` = '$val' WHERE id = {$this->id}";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->$var = $val;
		return false;
	}


	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @desc Returns true if a user with specified permission level is allowed to view this article, false otherwise;
	 */
	public function canUserView($user_permission_level) {
		global $g_permission;
		
		if($user_permission_level >= $g_permission['staff']) {
			return true;
		}
		elseif(isset($this->view_permissions[$user_permission_level])) {
			return $this->view_permissions[$user_permission_level];
		}
		else {
			return false;
		}
	}
	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @desc Returns true if a user with specified permission level is allowed to edit this article, false otherwise;
	 */
	public function canUserEdit($user_permission_level) {
		global $g_permission;
		
		if($user_permission_level >= $g_permission['staff']) {
			return true;
		}
		elseif(isset($this->edit_permissions[$user_permission_level])) {
			return $this->edit_permissions[$user_permission_level];
		}
		else {
			return false;
		}
	}


	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @param boolean $can_edit Flag showing whether the permission should be granted or revoked
	 */
	public function setEditPermission($user_permission_level, $can_edit) {
		$this->setPermissions($user_permission_level, 'edit', $can_edit);
	}
	/**
	 * @return boolean
	 * @param int $user_permission_level User's permission level (by default 0 - 5)
	 * @param boolean $can_view Flag showing whether the permission should be granted or revoked
	 */
	public function setViewPermission($user_permission_level, $can_view) {
		$this->setPermissions($user_permission_level, 'view', $can_view);
	}


	/**
	 * @return boolean
	 * @param int $permission_level Permission level (by default 0 - 5)
	 * @param string $permission_type Type of permission; Currently only 'view' and 'edit' are supported
	 * @param boolean $allow If true will grant permission, else will revoke it.
	 */
	protected function setPermissions($permission_level, $permission_type='view', $allow=true) {
		global $g_dbConn;

		if(empty($this->id) || (empty($permission_level) && ($permission_level != 0))) {	//make sure we still insert level 0
			return false;
		}
		$allow = $allow ? 1 : 0;

		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql_select = "SELECT category_id FROM help_cat_to_role WHERE category_id = {$this->id} AND permission_level = $permission_level";
				$sql_insert = "INSERT INTO help_cat_to_role (category_id, permission_level, can_$permission_type) VALUES ({$this->id}, '$permission_level', $allow)";
				$sql_update = "UPDATE help_cat_to_role SET can_$permission_type = $allow WHERE category_id = {$this->id} AND permission_level = $permission_level";
		}
		
		//determine if need to insert or update
		$rs = $g_dbConn->query($sql_select);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if($rs->numRows() > 0) {	//row exists, update
			$rs = $g_dbConn->query($sql_update);
		}
		else {	//insert
			$rs = $g_dbConn->query($sql_insert);
		}
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		return true;
	}
}
?>