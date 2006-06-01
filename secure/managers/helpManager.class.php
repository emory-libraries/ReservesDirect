<?php
/*******************************************************************************
helpManager.class.php


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

require_once('secure/classes/help.class.php');
require_once('secure/displayers/helpDisplayer.class.php');
require_once('secure/managers/baseManager.class.php');

class helpManager extends baseManager {
	/**
	 * Declaration
	 */
	protected $help;	//Help object


	/**
	 * @return void
	 * @desc Constructor
	 */
	public function __construct($cmd) {
		global $u, $loc;

		$this->help = new Help($u->getRole());
		$this->displayClass = 'helpDisplayer';

		switch($cmd) {
			case 'helpViewArticle':
				$this->viewArticle();
			break;

			case 'helpEditArticle':
				$this->editArticle();
			break;

			case 'helpViewCategory':
				$this->viewCategory();
			break;

			case 'helpEditCategory':
				$this->editCategory();
			break;

			case 'helpViewTag':
				$this->viewTag();
			break;
			
			case 'helpSetRelated':
				$this->setRelated();
			break;

			case 'helpSearch':
				$this->search();
			break;

			case 'help':
			default:
				$loc = 'help';
				$this->displayFunction = 'displayHome';
			break;
		}
	}
	
	
	/**
	 * @desc Overwrites parent method to add a wrapper to sidebar and normal pages
	 */
	public function display() {
		if(isset($_REQUEST['no_table'])) {	//if in sidebar mode
			echo '<div id="help-sidebar">';
			parent::display();
			echo '</div>';
		}
		else {	//else normal mode
			echo '<div id="help">';
			parent::display();
			echo '</div>';
		}
	}
	

	/**
	 * @desc Performs searches against the DB; returns results
	 */
	public function search() {
		global $loc;

		if(isset($_REQUEST['help_search_submit'])) {	//form submitted - perform search
			//determine the type of search to perform
			switch($_REQUEST['help_search_type']) {
				case 'tags':
					$articles = $this->help->findArticlesByTags($_REQUEST['help_search_query']);
				break;

				case 'fulltext':
					$articles = $this->help->findArticlesByKeyword($_REQUEST['help_search_query']);
				break;

				default:	//search both
					$articles = array_merge($this->help->findArticlesByTags($_REQUEST['help_search_query']), $this->help->findArticlesByKeyword($_REQUEST['help_search_query']));
			}
			
			$next_cmd = !empty($_REQUEST['next_cmd']) ? $_REQUEST['next_cmd'] : null;

			$loc = 'help &gt;&gt; search &gt;&gt; results';
			$this->displayFunction = 'displaySearchResults';
			$this->argList = array($articles, $_REQUEST['help_search_query'], $next_cmd);
		}
		else {	//display form
			$loc = 'help &gt;&gt; search';
			$this->displayFunction = 'displaySearchForm';
		}
	}


	/**
	 * @return void
	 * @desc Processes requests to view an article
	 */
	public function viewArticle() {
		global $u, $loc;

		$loc = 'help &gt;&gt; view article';

		if(!empty($_REQUEST['h_a_id'])) {
			$article = new Help_Article();

			if($article->getByID($_REQUEST['h_a_id'])) {	//article found
				if($article->canUserView($u->getRole())) {	//user allowed to view
					$query = isset($_REQUEST['help_search_query']) ? $_REQUEST['help_search_query'] : null;
					
					$this->displayFunction = 'displayArticle';
					$this->argList = array($article, $query);

					return;	//return now
				}
			}
		}

		//if we get this far, then something went wrong (no id, no article, no permission, etc)
		$error_msg = 'Article not found.';
		$this->displayFunction = 'displayError';
		$this->argList = array($error_msg);
	}
	
	
	/**
	 * @return void
	 * @desc Handles category editing
	 */
	public function editCategory() {
		global $u, $loc, $g_permission;

		$loc = 'help &gt;&gt; add/edit category';

		if(isset($_REQUEST['help_category_submit'])) {	//form submitted
			$category = new Help_Category();

			if($category->getByID($_REQUEST['help_category_id'])) {	//found category - edit record
				//check if user can edit article
				if(!$category->canUserEdit($u->getRole())) {	//cannot edit
					$err_msg = 'You may not edit this category.';
					$this->displayFunction = 'displayError';
					$this->argList = array($err_msg);

					return;	//do nothing else
				}

				//set category info
				$category->setTitle($_REQUEST['help_category_title']);
				$category->setDescription($_REQUEST['help_category_description']);
			}
			else {	//create new category
				//only staff or above can add categories
				if($u->getRole() < $g_permission['staff']) {
					return;
				}
				$category->createCategory($_REQUEST['help_category_title'], $_REQUEST['help_category_description']);
			}

			//set permissions
			foreach($g_permission as $perm) {
				if($perm > $g_permission['instructor']) {
					continue;
				}
				
				$allow_view = (isset($_REQUEST['perms_view']) && isset($_REQUEST['perms_view'][$perm])) ? true : false;						
				$category->setViewPermission($perm, $allow_view);
			}
			
			$loc = 'help';
			$this->displayFunction = 'displayHome';
		}
		else {	//display form
			if(!empty($_REQUEST['help_category_id'])) {	//Category ID present -- editing
				//init category object
				$category = new Help_Category($_REQUEST['help_category_id']);
				
				//determine if user may edit category
				if($category->canUserEdit($u->getRole())) {	//can edit
					$this->displayFunction = 'displayCategoryForm';
				$this->argList = array($category);
				}
				else {	//cannot edit
					$err_msg = 'You may not edit this category.';

					$this->displayFunction = 'displayError';
					$this->argList = array($err_msg);
				}				
			}
			else {	//adding new article
				$this->displayFunction = 'displayCategoryForm';
				$this->argList = array();
			}
		}
	}
	

	/**
	 * @return void
	 * @desc Handles editing or articles
	 */
	public function editArticle() {
		global $u, $loc, $g_permission;

		$loc = 'help &gt;&gt; add/edit article';

		if(isset($_REQUEST['help_article_submit'])) {	//form submitted
			$article = new Help_Article();

			if($article->getByID($_REQUEST['help_article_id'])) {	//found article - edit record
				//check if user can edit article
				if(!$article->canUserEdit($u->getRole())) {	//cannot edit
					$err_msg = 'You may not edit this article.';
					$this->displayFunction = 'displayError';
					$this->argList = array($err_msg);

					return;	//do nothing else
				}

				//set article info
				$article->setTitle($_REQUEST['help_article_title']);
				$article->setBody($_REQUEST['help_article_body']);
				$article->setCategoryID($_REQUEST['help_category_id']);
			}
			else {	//create new article
				//only staff or above can add articles
				if($u->getRole() < $g_permission['staff']) {
					return;
				}
				$article->createArticle($_REQUEST['help_article_title'], $_REQUEST['help_article_body'], $_REQUEST['help_category_id']);
			}

			//set permissions
			foreach($g_permission as $perm) {
				if($perm > $g_permission['instructor']) {
					continue;
				}
				
				$allow_view = (isset($_REQUEST['perms_view']) && isset($_REQUEST['perms_view'][$perm])) ? true : false;
				$allow_edit = (isset($_REQUEST['perms_edit']) && isset($_REQUEST['perms_edit'][$perm])) ? true : false;
							
				$article->setViewPermission($perm, $allow_view);
				$article->setEditPermission($perm, $allow_edit);
			}

			//display result
			$this->displayFunction = 'displayArticle';
			$this->argList = array($article);
		}
		else {	//display form
			if(!empty($_REQUEST['h_a_id'])) {	//Article ID present -- editing
				//init article object
				$article = new Help_Article($_REQUEST['h_a_id']);

				//determine if user may edit article
				if($article->canUserEdit($u->getRole())) {	//can edit
					$this->displayFunction = 'displayArticleForm';
					$this->argList = array($article);

				}
				else {	//cannot edit
					$err_msg = 'You may not edit this article.';

					$this->displayFunction = 'displayError';
					$this->argList = array($err_msg);
				}
			}
			else {	//adding new article
				$this->displayFunction = 'displayArticleForm';
				$this->argList = array();
			}
		}
	}


	public function viewCategory() {
		global $u, $loc;

		if(!empty($_REQUEST['help_category_id'])) {
			$category = new Help_Category();

			if($category->getByID($_REQUEST['help_category_id'])) {
				if($category->canUserView($u->getRole())) {
					$articles = $this->help->getArticlesByCategory($_REQUEST['help_category_id']);
					$next_cmd = !empty($_REQUEST['next_cmd']) ? $_REQUEST['next_cmd'] : null;

					$loc = 'help &gt;&gt; view category';
					$this->displayFunction = 'displayCategory';
					$this->argList = array($category, $articles, $next_cmd);

					return;
				}
			}
		}

		//error
		$error_msg = 'Category not found.';
		$this->displayFunction = 'displayError';
		$this->argList = array($error_msg);

	}


	public function viewTag() {
		global $loc;
		
		if(!empty($_REQUEST['tag'])) {
			//fetch articles matching tag
			$articles = $this->help->getArticlesByExactTag($_REQUEST['tag']);

			if(!empty($articles)) {
				$loc = 'help &gt;&gt; view tag';
				$this->displayFunction = 'displayTagArticles';
				$this->argList = array($_REQUEST['tag'], $articles);
				
				return;
			}
		}
		
		//error
		$error_msg = 'Tag not found.';
		$this->displayFunction = 'displayError';
		$this->argList = array($error_msg);			
	}
	
	
	/**
	 * @return string
	 * @param int $article_id ID of article
	 * @desc returns formatted output string of tags for specified article; (mostly just a wrapper for AJAX-responder use
	 */
	public function getTags($article_id) {
		global $u;
		
		$article = new Help_Article($article_id);
		
		return helpDisplayer::formatTagsOutput($article->getTags(), $article->getUserTags($u->getUserID()), false);
	}
	
	
	/**
	 * @return void
	 * @param int $article_id ID of article
	 * @param string $tags_string Tags to set
	 * @desc Clears a user's tag for the specified article and replaces them with the passes tags
	 */
	public function setTags($article_id, $tags_string) {
		global $u;
		
		$article = new Help_Article();
		if($article->getByID($article_id)) {
			$article->parseTags($tags_string, true, $u->getUserID());
		}
	}
	
	
	/**
	 * @return void
	 * @desc Manages relationship b/n two articles; adds/removes/switches relationship type
	 */
	public function setRelated() {
		global $u;
		
		$article1_id = !empty($_REQUEST['h_a1_id']) ? $_REQUEST['h_a1_id'] : null;
		$article2_id = !empty($_REQUEST['h_a_id']) ? $_REQUEST['h_a_id'] : null;
		$rel_op = !empty($_REQUEST['rel_op']) ? $_REQUEST['rel_op'] : null;
		$relationship = (!empty($_REQUEST['rel']) && ($_REQUEST['rel']=='child')) ? 'child' : 'sibling';		
				
		if(!empty($article1_id) && !empty($article2_id)) {
			$article1 = new Help_Article();
			if($article1->getByID($article1_id)) {
				if($article1->canUserEdit($u->getRole())) {
					//decide what to do
					switch($rel_op) {
						case 'add':
							$article1->setRelatedArticle($article2_id, $relationship);
						break;
						
						case 'remove':
							$article1->deleteRelationship($article2_id);
						break;
						
						case 'switch':
							$article1->deleteRelationship($article2_id);
							$article1->setRelatedArticle($article2_id, $relationship);
						break;
					}
				}
			}
			
			$this->displayFunction = 'displayArticleForm';
			$this->argList = array($article1);
		}
		else {	//trying to find the articles
			//if article1 is blank and an article is submitted, assume it's 1
			if(empty($article1_id) && !empty($_REQUEST['h_a_id'])) {
				$article1_id = $_REQUEST['h_a_id'];
			}
			
			if(empty($article1_id)) {	//need a1
				if($relationship=='child') {
					$msg = "Please find the parent article.";
				}
				else {
					$msg = "Please find the first article.";
				}
			}
			else {	//need a2
				if($relationship=='child') {
					$msg = "Please find the child article.";
				}
				else {
					$msg = "Please find the second article.";
				}
			}
			
			$msg .= " Click on the article title to continue.";
			
			$this->displayFunction = 'displayFindRelated';
			$this->argList = array($msg, $article1_id, $rel_op, $relationship);
		}
	}
}
?>