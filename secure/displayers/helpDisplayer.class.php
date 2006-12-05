<?php
/*******************************************************************************
helpDisplayer.class.php


Created by Dmitriy Panteleyev (dpantel@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2006 Emory University, Atlanta, Georgia.

Licensed under the ReservesDirect License, Version 1.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the full License at
http://www.reservesdirect.org/licenses/LICENSE-1.0

ReservesDirect is distributed in the hope that it will be useful,
but is distributed "AS IS" and WITHOUT ANY WARRANTY, without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE, and without any warranty as to non-infringement of any third
party's rights.  See the License for the specific language governing
permissions and limitations under the License.


ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/

require_once('secure/displayers/baseDisplayer.class.php');
require_once('secure/classes/help.class.php');

class helpDisplayer extends baseDisplayer {

	/**
	 * @desc Display help index page
	 */
	public function displayHome() {
?>
		<h3>Help</h3>
<?php
		self::displaySearchForm();
		self::displayBrowseForm();
?>
		<p />
		<a href="#" onclick="javascript:help('cmd=help'); return false;">sidebar</a>
<?php
	}


	/**
	 * @return void
	 * @param array $hidden_fields Info to pass as hidden fields
	 * @desc Displays search form
	 */
	public function displaySearchForm($hidden_fields=null) {
		$query = !empty($_REQUEST['help_search_query']) ? $_REQUEST['help_search_query'] : '';
		$search_type = !empty($_REQUEST['help_search_type']) ? $_REQUEST['help_search_type'] : 'any';
?>
	<form id="help_search_form" name="help_search_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpSearch">
		<?php self::displayHiddenFields($hidden_fields); ?>
		
		Search for <input type="text" id="help_search_query" name="help_search_query" value="<?php echo $query; ?>" />
		in
		<select id="help_search_type" name="help_search_type">
			<option value="tags"<?php if($search_type=='tags') { echo ' selected="selected"'; }?>>Tags</option>
			<option value="fulltext"<?php if($search_type=='fulltext') { echo ' selected="selected"'; }?>>Articles</option>
			<option value="any"<?php if($search_type=='any') { echo ' selected="selected"'; }?>>All</option>
		</select>
		<input type="submit" id="help_search_submit" name="help_search_submit" value="Search" />
	</form>
<?php
	}
	
	
	/**
	 * @return void
	 * @param array $hidden_fields Info to pass as hidden fields
	 * @desc Displays browse-category form
	 */
	public function displayBrowseForm($hidden_fields=null) {
		$cat = !empty($_REQUEST['help_category_id']) ? $_REQUEST['help_category_id'] : null;
?>
		<form id="help_browse_form" name="help_browse_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpViewCategory">
			<?php self::displayHiddenFields($hidden_fields); ?>
			
			Or browse <?php self::displayCategorySelect($cat); ?> category
			<input type="submit" id="help_browse_submit" name="help_browse_submit" value="Browse" />
		</form>
<?php
	}


	/**
	 * @return void
	 * @param array $articles Reference to array of Help_Article objects
	 * @param string $query The search query; search terms will be highlighted in article title/body
	 * @param string $next_cmd (optional) Link articles to this cmd
	 * @desc Displays list of articles
	 */
	public function displaySearchResults($articles, $query, $next_cmd) {
		self::displaySearchForm();
		echo '<p /><strong>Found '.sizeof($articles).' articles.</strong><p />';
		self::displayArticleList($articles, true, true, $query, $next_cmd);
	}


	/**
	 * @return void
	 * @param array $articles Reference to array of Help_Article objects
	 * @param boolean $show_tags If true, will display a list of tags for the article
	 * @param boolean $preview_content If true, will display a snippet of article body
	 * @param string $query (optional) If search query provided, search terms will be highlighted in article title/body
	 * @param string $next_cmd (optional) If set will link article titles to the specified CMD; Otherwise (and by default) titles are linked to "view-article"
	 * @desc Displays list of articles
	 */
	public function displayArticleList($articles, $show_tags=false, $preview_content=false, $query=null, $next_cmd=null) {
		global $u;
		
		if(empty($articles)) {
			return;
		}
		
		//build portions of the title link
		$cmd = !empty($next_cmd) ? $next_cmd : 'helpViewArticle';
		$propagate_query = !empty($query) ? '&amp;help_search_query='.urlencode($query) : '';
?>
	<ul class="help_article_list">
<?php
		foreach($articles as $article):
			//highlight search terms if present
			$title = !empty($query) ? self::highlightTerms($article->getTitle(), $query) : $article->getTitle();
			//pull out body if needed
			if($preview_content) {
				//determine if need to crop the article body, or if it is short enough
				$snippet = (strlen($article->getBody()) > 300) ? substr($article->getBody(), 0, 300).'...' : $article->getBody();
				
				if(!empty($query)) {	//highlight search terms
					$snippet = self::highlightTerms($snippet, $query);
				}
			}
			//highlight tags
			$tags = !empty($query) ? self::highlightTerms($article->getTags(), $query) : $article->getTags();
			$user_tags = !empty($query) ? self::highlightTerms($article->getUserTags($u->getUserID()), $query) : $article->getUserTags($u->getUserID());
?>
		<li>
			<span class="help_article_title"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=<?php echo $cmd; ?>&amp;h_a_id=<?php echo $article->getID(); ?><?php echo $propagate_query; ?>"><?php echo $title; ?></a></span>
<?php		if($show_tags): ?>
			&mdash;<?php self::displayTags($article->getID(), $tags, $user_tags); ?>
<?php		endif; ?>
<?php		if($preview_content): ?>
			<div class="help_article_body">
				<?php echo $snippet; ?>
			</div>
<?php		endif; ?>
		</li>

<?php	endforeach; ?>
	</ul>
<?php

	}


	/**
	 * @return void
	 * @param Help_Article $article Reference to Help_Article object
	 * @param string $query (optional) If search query provided, search terms will be highlighted in article title/body
	 * @desc Displays article
	 */
	public function displayArticle($article, $query=null) {
		global $u;

		//highlight search terms if present
		if(!empty($query)) {
			$title = self::highlightTerms($article->getTitle(), $query);
			$body = self::highlightTerms($article->getBody(), $query);
			$tags = self::highlightTerms($article->getTags(), $query);
			$user_tags = self::highlightTerms($article->getUserTags($u->getUserID()), $query);
		}
		else {
			$title = $article->getTitle();
			$body = $article->getBody();
			$tags = $article->getTags();
			$user_tags = $article->getUserTags($u->getUserID());
		}

		//get related articles
		$help = new Help($u->getRole());
		$related_articles = $help->getRelatedArticles($article->getID());
		$followup_articles = $help->getFollowUpArticles($article->getID());
?>
	<span class="help_article_title"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpViewArticle&amp;h_a_id=<?php echo $article->getID(); ?>"><?php echo $title; ?></a></span>
<?php	if($article->canUserEdit($u->getRole())): ?>
	<small>[<a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpEditArticle&amp;h_a_id=<?php echo $article->getID(); ?>">edit</a>]</small>
<?php	endif;?>
	<br />
	<?php self::displayTags($article->getID(), $tags, $user_tags, true); ?>
	<br />
	<div class="help_article_body">
		<?php echo $body; ?>
	</div>

<?php	if(!empty($followup_articles)): ?>
	<hr />
	<strong>Follow-up Articles:</strong>
	<?php self::displayArticleList($followup_articles, false, false, $query); ?>
<?php	endif; ?>

<?php	if(!empty($related_articles)): ?>
	<hr />
	<strong>Related Articles:</strong>
	<?php self::displayArticleList($related_articles, false, false, $query); ?>
<?php	
		endif;
	}


	/**
	 * @return void
	 * @param array $tags Array of tag terms
	 * @param array $user_tags Array of tag terms added by current user
	 * @param boolean $allow_edit If true, will allow user to edit his/her tags
	 * @desc Displays a list of tag terms
	 */
	public function displayTags($article_id, $tags, $user_tags, $allow_edit=false) {
?>
	<script type="text/javascript" language="JavaScript1.2" src="secure/javascript/basicAJAX.js"></script>
	<script type="text/javascript" language="JavaScript1.2" src="secure/javascript/help_ajax.js"></script>	

	Tags:
<?php	
	if($allow_edit) {
		self::displayTagsEditFormAJAX($article_id, $user_tags);
	}
?>
	
	<span id="help_tags"><?php echo self::formatTagsOutput($tags, $user_tags, false); ?></span>
	<br />
	
<?php
	}
	
	
	public function displayTagsEditFormAJAX($article_id, $user_tags) {
		global $u;
?>
	<small>[<a href="#" onclick="javascript: help_toggle_tag_edit_form(1); return false;">edit</a>]</small>
	
	<span id="help_tag_list_edit" style="display:none;">
		<?php self::displayTagInput($user_tags); ?>
		<input type="button" value="Cancel" onclick="javascript: help_toggle_tag_edit_form(0);" />
		<input type="button" value="Save" onclick="javascript: help_save_tags(<?php echo $article_id; ?>);" />	
	</span>
<?php
	}
	
	
	/**
	 * @return string
	 * @param array $tags Array of tag terms
	 * @param array $user_tags Array of tag terms added by current user
	 * @param string $format_as_list If true, will return tags as list-items in a list; otherwise will return them as a string
	 */
	public function formatTagsOutput($tags, $user_tags, $format_as_list=false) {
		$formatted_tags = array();
		
		foreach($tags as $tag) {
			//style personal tags
			$tag_style_class = in_array($tag, $user_tags) ? 'help_user_tag' : 'help_tag';
						
			//build linked tag string
			//since the tags may be wrapped for highlighting, strip the tags for the URL portion
			$tag_string = '<a href="'.$_SERVER['PHP_SELF'].'?cmd=helpViewTag&amp;tag='.strip_tags($tag).'"><span class="'.$tag_style_class.'">'.$tag.'</span></a>';
			
			//wrap tag as list-item if building list
			if($format_as_list) {
				$formatted_tags[] = '<li>'.$tag_string.'</li>';
			}
			else {
				$formatted_tags[] = $tag_string;
			}
		}
		
		if(empty($formatted_tags)) {	//empty tag list
			$result = 'none';
		}
		else {		
			if($format_as_list) { //wrap result as list if building list 
				$result = '<ul id="help_tag_list" class="help_tag_list">'.implode("\n", $formatted_tags).'</ul>';
			}
			else {
				$result = '<span id="help_tag_list" class="help_tag_list">'.implode(' ', $formatted_tags).'</span>';
			}
		}
		
		return $result;		
	}


	/**
	 * @return void
	 * @param Help_Article $article (optional) Help_Article object (if editing)
	 * @desc Displays form for adding/editing an article
	 */
	public function displayArticleForm($article=null) {
		global $u, $g_permission;
		
		//prefill form if passed an article object
		if($article instanceof Help_Article) {
			$id = $article->getID();
			$title = $article->getTitle();
			$body = $article->getBody();
			$tags = $article->getTags();
			$category_id = $article->getCategoryID();
			
			//set up permission checkboxes
			foreach($g_permission as $perm_label=>$perm) {
				$checked_perm_view[$perm] = $article->canUserView($perm) ? 'checked="true"' : '';
				$checked_perm_edit[$perm] = $article->canUserEdit($perm) ? 'checked="true"' : '';
			}
			$checked_perm_view['all'] = '';
			
			//get related articles
			$help = new Help($u->getRole());
			$related_articles = $help->getRelatedArticles($article->getID());
			$followup_articles = $help->getFollowUpArticles($article->getID());
			
			$allow_set_relationships = true;	//only allow setting relationships on an existing article, not if creating new
		}
		else {
			$id = $title = $body = $tags = $category_id = '';
			
			//set up permission checkboxes
			foreach($g_permission as $perm_label=>$perm) {
				$checked_perm_view[$perm] = 'checked="true"';
			}
			$checked_perm_view['all'] = 'checked="true"';
			$checked_perm_edit = array();
			
			$allow_set_relationships = false;	//only allow setting relationships on an existing article, not if creating new
		}
?>
	<script type="text/javascript">
		function toggle_checkboxes(checkAllObj, input_classname) {
			var frm = checkAllObj.form;
						
			for(var x=0; x < frm.elements.length; x++) {
				if((frm.elements[x].type == "checkbox") && (frm.elements[x].className == input_classname)) {
					frm.elements[x].checked = checkAllObj.checked;
				}
			}
		}		
	</script>

	<form id="help_article_form" name="help_article_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpEditArticle">
		<input type="hidden" id="help_article_id" name="help_article_id" value="<?php echo $id;?>" />

		<fieldset>
			<legend>Basics</legend>
			<label for="help_article_title">Title:</label>
			<input type="text" id="help_article_title" name="help_article_title" value="<?php echo $title; ?>" />
			<br />
			<label for="help_category_id">Category:</label>
			<?php self::displayCategorySelect($category_id); ?>
			<br />
			<textarea id="help_article_body" name="help_article_body"><?php echo $body; ?></textarea>
		</fieldset>

<?php	if($allow_set_relationships): ?>
		<fieldset>
		<legend>Linked Articles</legend>
			<label>Follow-up Articles<small>[<a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpSetRelated&amp;rel_op=add&amp;h_a1_id=<?php echo $article->getID(); ?>&amp;rel=child">add</a>]</small>:</label>
<?php		if(!empty($followup_articles)): ?>
			<ul class="help_article_list">
<?php			foreach($followup_articles as $art): ?>		
				<li>
					<span class="help_article_title"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpViewArticle&amp;h_a_id=<?php echo $art->getID(); ?>"><?php echo $art->getTitle(); ?></a></span> 
					<small>[<a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpSetRelated&amp;rel_op=remove&amp;h_a1_id=<?php echo $article->getID(); ?>&amp;h_a_id=<?php echo $art->getID(); ?>">remove</a>] [<a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpSetRelated&amp;rel_op=switch&amp;h_a1_id=<?php echo $article->getID(); ?>&amp;h_a_id=<?php echo $art->getID(); ?>">make 'Related'</a>]</small>
				</li>
<?php			endforeach; ?>
			</ul>
<?php		endif; ?>	

			<hr />
			
			<label>Related Articles <small>[<a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpSetRelated&amp;rel_op=add&amp;h_a1_id=<?php echo $article->getID(); ?>">add</a>]</small>:</label>		
<?php		if(!empty($related_articles)): ?>
			<ul class="help_article_list">
<?php			foreach($related_articles as $art): ?>		
				<li>
					<span class="help_article_title"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpViewArticle&amp;h_a_id=<?php echo $art->getID(); ?>"><?php echo $art->getTitle(); ?></a></span> 
					<small>[<a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpSetRelated&amp;rel_op=remove&amp;h_a1_id=<?php echo $article->getID(); ?>&amp;h_a_id=<?php echo $art->getID(); ?>">remove</a>] [<a href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpSetRelated&amp;rel_op=switch&amp;h_a1_id=<?php echo $article->getID(); ?>&amp;h_a_id=<?php echo $art->getID(); ?>&amp;rel=child">make 'Follow-up'</a>]</small>
				</li>
<?php			endforeach; ?>
			</ul>
<?php		endif; ?>
		</fieldset>
<?php	endif; ?>

		<fieldset>
			<legend>Permissions</legend>
			<div id="help_article_perms">
<?php	
		foreach($g_permission as $perm_label=>$perm):
			if($perm > 3) {	//do not go above instructor
				continue;
			}
?>
				<label style="width:80px;"><?php echo ucfirst($perm_label); ?></label>
				<input type="checkbox" class="perms_view" name="perms_view[<?php echo $perm; ?>]" <?php echo $checked_perm_view[$perm]; ?> /> may view
				<input type="checkbox" class="perms_edit" name="perms_edit[<?php echo $perm; ?>]" <?php echo $checked_perm_edit[$perm]; ?> /> may edit
				<br />
<?php	endforeach; ?>	
				<label style="width:80px;">&nbsp;</label>
				<input type="checkbox" onclick="javascript: toggle_checkboxes(this, 'perms_view');" <?php echo $checked_perm_view['all']; ?> /> all may view
				<input type="checkbox" onclick="javascript: toggle_checkboxes(this, 'perms_edit');" /> all may edit
			</div>
		</fieldset>

<?php	if($u->getRole() >= $g_permission['staff']): ?>
		<fieldset>
			<legend>Delete</legend>
			Check this box if you want to delete the article. <strong>This can not be undone!</strong>
			<input type="checkbox" name="help_article_delete" />
		</fieldset>
<?php	endif; ?>

		<p />
		<input type="submit" id="help_article_submit" name="help_article_submit" value="Save" />
	</form>
	


<?php
	}


	/**
	 * @return void
	 * @param Help_Category $category (optional) Help_Category object (if editing)
	 * @desc Displays form for adding/editing a category
	 */
	public function displayCategoryForm($category=null) {
		global $g_permission;
		
		//prefill form if passed a category object
		if($category instanceof Help_Category) {
			$id = $category->getID();
			$title = $category->getTitle();
			$desc = $category->getDescription();
			
			//set up permission checkboxes
			foreach($g_permission as $perm_label=>$perm) {
				$checked_perm_view[$perm] = $category->canUserView($perm) ? 'checked="true"' : '';
			}
			$checked_perm_view['all'] = '';
		}
		else {
			$id = $title = $desc = '';
			
			//set up permission checkboxes
			foreach($g_permission as $perm_label=>$perm) {
				$checked_perm_view[$perm] = 'checked="true"';
			}
			$checked_perm_view['all'] = 'checked="true"';
		}
?>
	<script type="text/javascript">
		function toggle_checkboxes(checkAllObj, input_classname) {
			var frm = checkAllObj.form;
						
			for(var x=0; x < frm.elements.length; x++) {
				if((frm.elements[x].type == "checkbox") && (frm.elements[x].className == input_classname)) {
					frm.elements[x].checked = checkAllObj.checked;
				}
			}
		}		
	</script>
	
	<form id="help_category_form" name="help_category_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=helpEditCategory">
		<input type="hidden" id="help_category_id" name="help_category_id" value="<?php echo $id;?>" />

		<fieldset>
			<legend>Basics</legend>
			<label for="help_category_title">Title:</label>
			<input type="text" id="help_category_title" name="help_category_title" value="<?php echo $title; ?>" />
			<p />
			<label for="help_category_description">Description:</label>
			<br />
			<textarea id="help_category_description" name="help_category_description"><?php echo $desc; ?></textarea>
		</fieldset>
		
		<fieldset>
			<legend>Permissions</legend>
			<div id="help_category_perms">
<?php	
		foreach($g_permission as $perm_label=>$perm):
			if($perm > 3) {	//do not go above instructor
				continue;
			}
?>
				<label style="width:80px;"><?php echo ucfirst($perm_label); ?></label>
				<input type="checkbox" class="perms_view" name="perms_view[<?php echo $perm; ?>]" <?php echo $checked_perm_view[$perm]; ?> /> may view
				<br />
<?php	endforeach; ?>	
				<label style="width:80px;">&nbsp;</label>
				<input type="checkbox" onclick="javascript: toggle_checkboxes(this, 'perms_view');" <?php echo $checked_perm_view['all']; ?> /> all may view
			</div>
		</fieldset>
		<p />
		<input type="submit" id="help_category_submit" name="help_category_submit" value="Submit" />
	</form>
<?php
	}


	/**
	 * @return void
	 * @param array $tags Array of tag terms
	 * @desc Displays input field for tags
	 */
	public function displayTagInput($tags) {
		//strip highlight span from tags
		foreach($tags as $id=>$tag) {
			$tags[$id] = strip_tags($tag);
		}
		//build string out of terms
		$tag_string = !empty($tags) ? implode(' ', $tags) : '';
?>
	<input type="text" id="help_tags_input" name="help_tags_input" value="<?php echo $tag_string; ?>" />
<?php
	}


	/**
	 * @return void
	 * @param int $default_category_id ID of category to select by default
	 * @desc Display category select box
	 */
	public function displayCategorySelect($default_category_id=null) {
		global $u;

		$help = new Help($u->getRole());
		$categories = $help->getCategories();
?>
		<select id="help_category_id" name="help_category_id">
<?php
		foreach($categories as $category):
			$selected = ($category->getID()==$default_category_id) ? ' selected="selected"' : '';
?>
			<option value="<?php echo $category->getID(); ?>"<?php echo $selected; ?>><?php echo $category->getTitle(); ?></option>
<?php	endforeach; ?>
		</select>
<?php
	}
	
	
	/**
	 * @return void
	 * @param Help_Category $category Category Object
	 * @param array $articles Array of Help_Article objects
	 * @param string $next_cmd (optional) Link articles to this cmd
	 * @desc Displays articles in the specified category
	 */
	public function displayCategory($category, $articles, $next_cmd=null) {
?>
		<h3>Category: <?php echo $category->getTitle(); ?></h3>
		<small><?php echo $category->getDescription(); ?></small>
		<p />
<?php
		self::displayArticleList($articles, true, true, null, $next_cmd);
	}
	
	
	/**
	 * @return void
	 * @param string $tag Queried tag
	 * @param array $articles Array of Help_Article objects
	 * @desc Displays articles in the specified category
	 */
	public function displayTagArticles($tag, $articles) {
		global $u;
		
		$help = new Help($u->getRole());
?>
		<h3>Tag: <?php echo $tag; ?></h3>
		<p />
<?php
		self::displayArticleList($articles, true, true);
		self::displayRelatedTags($tag, $help->getRelatedTags($tag));
	}
	
	
	/**
	 * @return void
	 * @param string $origin_tag The original tag
	 * @param array $related_tags Array of tags related to the original tag
	 * @param boolean $format_as_list If true, will display list as <ul>, else will display as string
	 * @desc Displays block of related strings, with the option of adding a related tag to the query
	 */
	public function displayRelatedTags($origin_tag, $related_tags, $format_as_list=false) {
		foreach($related_tags as $tag=>$is_user_tag) {
			//style personal tags
			$tag_style_class = $is_user_tag ? 'help_user_tag' : 'help_tag';
						
			//build linked tag string of origin tag + new tag
			$add_tag_string = '<a href="'.$_SERVER['PHP_SELF'].'?cmd=helpViewTag&amp;tag='.$origin_tag.'+'.$tag.'">&#43;</a>';
			//build linked tag string
			$tag_string = '<a href="'.$_SERVER['PHP_SELF'].'?cmd=helpViewTag&amp;tag='.$tag.'"><span class="'.$tag_style_class.'">'.$tag.'</span></a>';
			
			//wrap tag as list-item if building list
			if($format_as_list) {
				$formatted_tags[] = '<li>'.$add_tag_string.'/'.$tag_string.'</li>';
			}
			else {
				$formatted_tags[] = '<small>(</small>'.$add_tag_string.'<small>)</small>'.$tag_string;
			}
		}
		
		if(empty($formatted_tags)) {	//empty tag list
			$result = 'none';
		}
		else {		
			if($format_as_list) { //wrap result as list if building list 
				$result = '<ul class="help_tag_list">'.implode("\n", $formatted_tags).'</ul>';
			}
			else {
				$result = '<span class="help_tag_list">'.implode(' ', $formatted_tags).'</span>';
			}
		}
		
		echo '<span class="help_related_tags">';
		echo '<strong>Related Tags: </strong>';
		echo $result;
		echo '</span>';
	}


	/**
	 * @return void
	 * @param string $text
	 * @desc Displays text wrapped in span with 'help_error' class
	 */
	public function displayError($text) {
		echo '<span class="help_error">Error: '.$text.'</span>';
	}
	
	
	/**
	 * @return void
	 * @param string $msg (optional) Message to display to user
	 * @param int $article1_id (optional) The first article ID to pass on
	 * @param string $rel_op (optional) Relation-setting Operation to perform
	 * @param string $relationship (optional) Type of relationship
	 * @desc Shows the forms for finding the first and second articles for adding a relationship
	 */
	public function displayFindRelated($msg=null, $article1_id=null, $rel_op=null, $relationship=null) {
		//build hidden fields array
		$hidden_fields = array('next_cmd'=>"helpSetRelated&amp;rel_op=$rel_op&amp;rel=$relationship&amp;h_a1_id=$article1_id");
		
		//display message
		if(!empty($msg)) {
			echo '<span class="helperText">'.$msg.'</span><p />';
		}
		
		//display forms
		self::displaySearchForm($hidden_fields);
		self::displayBrowseForm($hidden_fields);
	}


	/**
	 * @return string
	 * @param string $text Text
	 * @param string $query Query string
	 * @desc Breaks query into keywords and adds a span with 'keyword_highlight' class to all the keywords in text; returns result
	 */
	protected function highlightTerms($text, $query) {
		$keywords = preg_split("/[,;\s]+/", $query, -1, PREG_SPLIT_NO_EMPTY);
		$highlight = '<span class="help_keyword_highlight">$1</span>';

		foreach($keywords as $keyword) {
			$text = preg_replace("/($keyword)/i", $highlight, $text) ;
		}

		return $text;
	}
}
?>
