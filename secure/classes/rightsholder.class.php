<?php
/*******************************************************************************
rightsholder.class.php
Materials rightsholder information

Created by Ben Ranker (branker@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2010 Emory University, Atlanta, Georgia.

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

class rightsholder
{
  private $isbn;
  private $name;
  private $contactName;
  private $contactEmail;
  private $fax;
  private $postAddress;
  private $rightsUrl;
  private $policyLimit;

  function rightsholder($isbn)
  {
    $this->loadByISBN($isbn);
  }

  private function loadByISBN($isbn)
  {
    global $g_dbConn;

    if (empty($isbn))
    {
      return false;
    }

    $sql = "SELECT ISBN, name, contact_name, contact_email, fax, " .
                  "post_address, rights_url, policy_limit " .
           "FROM rightsholders " .
           "WHERE ISBN = ?";
    $rs = $this->simple_query($sql, array($isbn))->fetchRow();

    if (empty($rs))
    {
      $sql = 'INSERT INTO rightsholders (ISBN) VALUES (?)';
      $this->simple_query($sql, array($isbn));
      $this->isbn = $isbn;
    } else {
      list($this->isbn, $this->name, $this->contactName,
           $this->contactEmail, $this->fax, $this->postAddress,
           $this->rightsUrl, $this->policyLimit) = $rs;
    }

    return true;
  }

  function destroy()
  {
    $sql = "DELETE FROM rightsholders WHERE ISBN = ?";
    $this->simple_query($sql, array($this->isbn));
  }

  private function simple_query($sql, $args)
  {
    global $g_dbConn;
    $rs = $g_dbConn->query($sql, $args);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    return $rs;
  }

  function getISBN() { return $this->isbn; }
  function getName() { return $this->name; }
  function getContactName() { return $this->contactName; }
  function getContactEmail() { return $this->contactEmail; }
  function getFax() { return $this->fax; }
  function getPostAddress() { return $this->postAddress; }
  function getRightsUrl() { return $this->rightsUrl; }
  function getPolicyLimit() { return $this->policyLimit; }

  private function updateField($field, $val)
  {
    $sql = "UPDATE rightsholders SET $field = ? WHERE ISBN = ?";
    $this->simple_query($sql, array($val, $this->isbn));
  }

  function setName($val) { $this->updateField('name', $val); $this->name=$val; }
  function setContactName($val) { $this->updateField('contact_name', $val); $this->contactName=$val; }
  function setContactEmail($val) { $this->updateField('contact_email', $val); $this->contactEmail=$val; }
  function setFax($val) { $this->updateField('fax', $val); $this->fax=$val; }
  function setPostAddress($val) { $this->updateField('post_address', $val); $this->postAddress=$val; }
  function setRightsUrl($val) { $this->updateField('rights_url', $val); $this->rightsUrl=$val; }
  function setPolicyLimit($val) { $this->updateField('policy_limit', $val); $this->policyLimit=$val; }
}
?>
