<?
/*******************************************************************************
bootstrap.inc.php
Read config.xml for tests

Created by Jason White (jbwhite@emory.edu)

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
  //set include path to allow RD files to load
  set_include_path(get_include_path().":". realpath(dirname(__FILE__)) . "/../");

  require_once("DB.php");
  require_once("secure/common.inc.php");
  require_once("UnitTest.php");
//  require_once('scripts/installer.php');    // for function to parse create_db.sql

  //sets $xmlConfig to path of config.xml file
  require_once(realpath(dirname(__FILE__) . "/../config_loc.inc.php"));
  if (!is_readable($xmlConfig)) { trigger_error("Could not read configure xml file path=$xmlConfig", E_USER_ERROR); }

  $configure = simplexml_load_file($xmlConfig);
  
//  $g_authenticationType = (string)$configure->authentication->type;
  
  $dsn = array(
      'phptype'  => (string)$configure->test_database->dbtype,
      'username' => (string)$configure->test_database->username,
      'password' => (string)$configure->test_database->pwd,
      'hostspec' => (string)$configure->test_database->host,
      'database' => (string)$configure->test_database->dbname,
      'key'      => (string)$configure->test_database->dbkey,
      'cert'     => (string)$configure->test_database->dbcert,
      'ca'       => (string)$configure->test_database->dbca,
      'capath'   => (string)$configure->test_database->capath,
      'cipher'   => (string)$configure->test_database->cipher
  );

  $options = array(
      'ssl'     => (string)$configure->test_database->ssl,
      'debug'     => (string)$configure->test_database->debug
  );

//    $g_ldap = array(
//        'host'      => (string)$configure->ldap->ldapHost,
//        'domain'    => (string)$configure->ldap->ldapDomain,
//        'port'      => (string)$configure->ldap->ldapPort,
//        'version'   => (string)$configure->ldap->ldapVersion,
//        'basedn'    => (string)$configure->ldap->baseDistinguishedName,
//        'canonicalName'    => (string)$configure->ldap->userAttributes->canonicalName,
//        'firstname' => (string)$configure->ldap->userAttributes->firstName,
//        'lastname'  => (string)$configure->ldap->userAttributes->lastName,
//        'email'     => (string)$configure->ldap->userAttributes->email,
//        'searchdn'  => (string)$configure->ldap->searchDistinguishedName,
//        'searchpw'  => (string)$configure->ldap->searchPassword,
//        'error'   => NULL
//    );

// create test db
createDB($dsn, $options);


//  $g_error_log      = (string)$configure->error_log;
//  $g_errorEmail     = (string)$configure->errorEmail;
//  $g_adminEmail     = (string)$configure->adminEmail;
//  $g_reservesEmail    = (string)$configure->reservesEmail;
//  $g_documentDirectory  = (string)$configure->documentDirectory;
//  $g_documentURL      = (string)$configure->documentURL;
//  $g_docCover       = (string)$configure->documentCover;
//
//  $g_siteURL        = (string)$configure->siteURL;
//  $g_serverName           = (string)$configure->serverName;
//    
//  $g_copyrightNoticeURL = (string)$configure->copyrightNoticeURL;
//
//  $g_newUserEmail['subject']  = (string)$configure->newUserEmail->subject;
//  $g_newUserEmail['msg']  = (string)$configure->newUserEmail->msg;  
//  
//  $g_specialUserEmail['subject']  = (string)$configure->specialUserEmail->subject;
//  $g_specialUserEmail['msg']  = (string)$configure->specialUserEmail->msg;
//  
//  $g_specialUserDefaultPwd = (string)$configure->specialUserDefaultPwd;
//
//  $g_EmailRegExp = (string)$configure->EmailRegExp;
//
//  //Euclid/Aleph configuration
//  $g_catalogName    = (string)$configure->catalog->catalogName;
//  $g_reserveScript  = (string)$configure->catalog->reserve_script;
//  $g_holdingsScript = (string)$configure->catalog->holdings_script;
//  $g_reservesViewer = (string)$configure->catalog->web_search;
//  $g_getBibRecordScript  = (string)$configure->catalog->get_bibrecord_script;
//
//  $g_libraryURL   = (string)$configure->library_url;
//
//  $g_no_javascript_msg = (string)$configure->no_javascript_msg;
//
//  $g_request_notifier_lastrun = (string)$configure->request_notifier->last_run;
//  
//    $g_activation_padding_days = (integer)$configure->registar_feed->activation_padding_days;
//    $g_expiration_padding_days = (integer)$configure->registar_feed->expiration_padding_days;
//    
//    $g_EZproxyAuthorizationKey = (string)$configure->EZproxyAuthorizationKey;
//    
//    $g_BlackboardLink = (string)$configure->BlackBoardLink;
//    
//    $trustedSystems = $configure->trusted_systems;
//    foreach ($trustedSystems->system as $sys)
//    { 
//      $k = (string)$sys['id'];
//      $t = (string)$sys['timeout'];
//      $g_trusted_systems[$k]['secret'] = (string)$sys;      
//      $g_trusted_systems[$k]['timeout'] = $t;     
//      unset($k, $t);
//    }
//    unset($trustedSystems);    
//    
    $g_ils = (array)$configure->ils;
    
  if (! defined('SIMPLE_TEST')) {
      define('SIMPLE_TEST', 'simpletest/');
  }



/**
 * create new test database & set up for testing
 */
function createDB($dsn, $options) {
  global $g_dbConn;
  
  // copy connection info & remove db name param
  $dsn_nodb = $dsn;
  unset($dsn_nodb['database']);

  // open connection to db host
  $g_dbConn = DB::connect($dsn_nodb, $options);
  if (DB::isError($g_dbConn)) { trigger_error($g_dbConn->getMessage(), E_USER_ERROR); }
  $queries = array("DROP DATABASE IF EXISTS `{$dsn['database']}`",
       "CREATE DATABASE `{$dsn['database']}`");
  foreach ($queries as $sql) {
    $rs = $g_dbConn->query($sql);
    if(DB::isError($rs)) {
      print_step3_error('Problem executing query: '.$rs->getMessage());
      die(-1);
    }
  }

  //open connection to newly created db
  $g_dbConn = DB::connect($dsn, $options);
  if (DB::isError($g_dbConn)) { trigger_error($g_dbConn->getMessage(), E_USER_ERROR); }


  // initialize with create db used by installer script
  UnitTest::loadDB("../../db/create_db.sql");
  // clear out unwanted stuff
  UnitTest::loadDB('../fixtures/truncateTables.sql');
}


?>


