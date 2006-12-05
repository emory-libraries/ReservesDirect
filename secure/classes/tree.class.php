<?php
/*******************************************************************************
tree.class.php
A Tree implementation

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

class Tree implements IteratorAggregate {
	/**
	 * declaration
	 */
	protected $id;				//this node's id (and payload)
	protected $parentNode;		//reference to parent's node
	protected $children;		//array of references to children nodes
	
	
	/**
	 * @return void
	 * @param int $id ID of this object. (essentially the payload)
	 * @param Tree $parent_node Reference to the parent node Tree object
	 * @desc constructor. initializes object, adds self to parent's children list, if parent exists
	 */	
	public function __construct($id, &$parent_node=null) {
		$this->id = $id;
		
		//if parent exists
		if(!empty($parent_node)) {
			$this->setParent($parent_node);
		}
	}
	
	
	/**
	 * @return void
	 * @param Tree $parent_node Reference to the parent node Tree object
	 * @desc Set the parent id and add self to parent's list of children
	 */
	public function setParent(&$parent_node) {
		$this->parentNode = &$parent_node;	//set parent
		$this->parentNode->addChild($this);	//add self to list of children		
	}
	
	
	/**
	 * @return void
	 * @param Tree $child_node Reference to a child Tree object
	 * @desc adds reference to the child object to the children array
	 */
	public function addChild(&$child_node) {
		$this->children[] = &$child_node;
	}
	
	
	/**
	 * @return int
	 * @desc Return this object's ID
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * @return Tree Object reference
	 * @param int $index Index for the children array
	 * @desc Returns the child Tree element at index or null if out of bounds.
	 */
	public function &getChild($index) {
		//php5 complains if you try to return NULL ("not a reference"), so skip all the checking and just attempt to return the object ref
		return $this->children[$index];
		//return (is_array($this->children) && array_key_exists($index, $this->children)) ? $this->children[$index] : null;
	}
	
	
	/**
	 * @return Tree Object reference
	 * @desc returns this object's parent
	 */
	public function &getParent() {
		//php5 complains if you try to return NULL ("not a reference"), so skip all the checking and just attempt to return the object ref
		return $this->parentNode;
		//return !empty($this->parentNode) ? $this->parentNode : null;
	}
	
	
	/**
	 * @return Tree object reference
	 * @desc returns this object's top-most ancestor
	 */
	public function &getRoot() {
		$node = &$this;		
		while(!is_null($node->getParent())) {
			$node = $node->getParent();			
		}
		
		return $node;		
	}
	
	
	/**
	 * @return Tree object reference
	 * @param int $id note ID
	 * @desc Looks for a node with the given ID.  Node can be anywhere in the tree
	 */
	public function &findNode($id) {
		return $this->findDescendant($this->getRoot());
	}

	
	/**
	 * @return Tree Object reference
	 * @param int $id node ID
	 * @desc Looks for a descendant node with the given ID
	 */
	public function &findDescendant($id) {
		$walker = new treeWalker($this);
		foreach($walker as $node) {
			if($id == $node->getID()) {
				return $node;
			}
		}
		return null;
	}
	
	
	/**
	 * @return array
	 * @desc Returns array of children
	 */
	public function &getChildren() {
		return $this->children;
	}
	
	
	/**
	 * @return boolean
	 * @desc returns true if this node has leaves; false if it is a terminal leaf
	 */	
	public function hasChildren() {
		return !empty($this->children);
	}
	
	
	/**
	 * @return int
	 * @desc returns number of children
	 */
	public function numChildren() {
		return count($this->children);
	}
	
	
	/**
	 * @return tree iterator (custom)
	 * @desc initializes and returns a custom iterator to make this tree accessible to common constructs like `foreach`
	 */
	public function getIterator() {
		//return new treeWalker($this);
		return new treeIterator($this);
	}
	
	
	/**
	 * @return void
	 * @param array $data Original data array. Must be of the form array(ID=>parent ID, ...)
	 * @param array $order_data (optional) Array of custom sort orders.  Must be of the form array(ID=>sort_order) *ID must match ID in $data 
	 * @desc Given and array indexed by ID (payload) with parent IDs as values, builds a tree of Tree objects
	 */
	public function buildTree($data, $order_data=null) {
		$tree_array = $this->buildArrayTreeFromArray($data, $order_data);
		$this->buildObjectTreeFromArrayTree($tree_array, $this);
	}
	
	
	/**
	 * @return array
	 * @param array $original_data Original data array. Must be of the form array(ID=>parent ID, ...)
	 * @param array $order_data (optional) Array of custom sort orders.  Must be of the form array(ID=>sort_order) *ID must match ID in $data 
	 * @desc Given and array indexed by ID (payload) with parent IDs as values, reorganizes array into a tree structure
	 */	
	protected function buildArrayTreeFromArray($original_data, $sort_orders=null) {
		$this->arsortCustom($original_data, $sort_orders);
		$tree_data = array();

		foreach($original_data as $id=>$pid) {
			if(empty($tree_data[$pid])) {	//target index empty --> straight assignment
				$tree_data[$pid] = !empty($tree_data[$id]) ? array($id=>$tree_data[$id]) : array($id); 			
			}
			else {	//target index not empty --> merge
				if(!empty($tree_data[$id])) {	//this child has children
					$tree_data[$pid][$id] = $tree_data[$id];	//copy the whole branch to this node
				}
				else {
					//cannot allow php to assign the key ($tree_data[$pid][] = $id)
					//because key assigned by php may clash with a pid
					//generating a unique key bypasses this issue
					$tree_data[$pid][md5($id)] = $id;
				}			
			}
			unset($tree_data[$id]);	
		}
		return isset($tree_data[0]) ? $tree_data[0] : array();
	}
	
	
	/**
	 * @return array
	 * @param array $data Original data array. Must be of the form array(ID=>parent ID, ...)
	 * @param array $order_data (optional) Array of custom sort orders.  Must be of the form array(ID=>sort_order) *ID must match ID in $data 
	 * @desc Before array can be organized into an array tree, the data must be ARSORTed.  This wrapper allows for custom ordering of elements, if they share a common value.
	 */	
	protected function arsortCustom(&$data_ar, &$order_ar) {
		if(!empty($order_ar)) {
			foreach($data_ar as $key=>$val) {
				$data_ar[$key] = floatval($val.'.'.(9999-$order_ar[$key]));
			}

			arsort($data_ar, SORT_NUMERIC);
	
			foreach($data_ar as $key=>$val) {
				$data_ar[$key] = intval($val);
			}
		}
		else {
			arsort($data_ar);
		}
	}
	
	
	/**
	 * @return void
	 * @param array $array_tree An array with a tree structure
	 * @param Tree $parent_node Reference to the parent node of objects made by this method
	 * @desc builds a Tree object tree from an array tree. Recursive
	 */	
	protected function buildObjectTreeFromArrayTree($array_tree, &$parent_node) {
		if(empty($array_tree)) {
			return;
		}
		
		foreach($array_tree as $key=>$val) {
			if(is_array($val)) {
				$leaf = new Tree($key, $parent_node);
				$this->buildObjectTreeFromArrayTree($val, $leaf);
			}
			else {
				$leaf = new Tree($val, $parent_node);
			}
		}
	}
}


/**
 * wrapper for RecursiveIteratorIterator
 */
class treeWalker extends RecursiveIteratorIterator {
	public function __construct(&$tree) {
		if(!is_null($tree)) {
			//this should be used if php is php = 5.0.x
			//parent::__construct(new treeIterator($tree), RIT_SELF_FIRST);
			
			//this should be used if php >= 5.1.1
			//parent::__construct(new treeIterator($tree), RecursiveIteratorIterator::SELF_FIRST);
			
			//this is hack to work with both
			parent::__construct(new treeIterator($tree), 1);
		}
	}
}


/**
 * custom recursive iterator class for Tree objects
 */
class treeIterator implements RecursiveIterator {
	protected $rootNode;
	protected $currentNode;
	protected $key;
	protected $valid;
	
	public function __construct(&$node) {
		$this->rootNode = &$node;
		$this->rewind();
	}
	
	public function current() {
		return $this->currentNode;
	}
	
	public function key() {
		return $this->key;
	}
	
	public function next() {
		$this->valid = !is_null($this->currentNode = &$this->rootNode->getChild(++$this->key));
	}
	
	public function rewind() {
		$this->key = -1;		
		$this->next();
	}
	
	public function valid() {
		return $this->valid;
	}

	public function hasChildren() {
		return $this->currentNode->hasChildren();
	}
	
	public function getChildren() {
		return new treeIterator($this->currentNode);
	}
}
?>
