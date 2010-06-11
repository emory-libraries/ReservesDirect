<?php
require_once('../UnitTest.php');
require_once('secure/classes/rightsholder.class.php');

class TestRightsholderClass extends UnitTest {
  function setUp()
  {
    $this->loadDB('../fixtures/rightsholders.sql');
  }
  function tearDown()
  {
    $this->loadDB('../fixtures/truncateTables.sql');
  }

  function testLoadExisting()
  {
    $rh = new rightsholder('FAKE ISBN');
    $this->assertEqual($rh->getISBN(), 'FAKE ISBN');
    $this->assertEqual($rh->getName(), 'Somebody Book Publishers, inc');
    $this->assertEqual($rh->getContactName(), 'Jane Doe');
    $this->assertEqual($rh->getContactEmail(), 'jdoe@somebody.com');
    $this->assertEqual($rh->getFax(), '308-555-6789');
    $this->assertEqual($rh->getPostAddress(), "123 Example Pl\nAtlanta, NE  68923");
    $this->assertEqual($rh->getRightsUrl(), 'http://www.somebody.com/copyright/policy/');
    $this->assertEqual($rh->getPolicyLimit(), 'free up to 83%');
  }

  function testSetters()
  {
    $rh = new rightsholder('FAKE ISBN');
    $rh->setName('Somebody Else Book Publishers, LLC');
    $rh->setContactName('John Doe');
    $rh->setContactEmail('john@somebody-else.com');
    $rh->setFax('620-555-4321');
    $rh->setPostAddress("321 Example Blvd\nAtlanta, KS  67008");
    $rh->setRightsUrl('http://www.somebody-else.com/policies/copyright/');
    $rh->setPolicyLimit('Get outta my lawn, you poachers!');

    $this->assertEqual($rh->getName(), 'Somebody Else Book Publishers, LLC');
    $this->assertEqual($rh->getContactName(), 'John Doe');
    $this->assertEqual($rh->getContactEmail(), 'john@somebody-else.com');
    $this->assertEqual($rh->getFax(), '620-555-4321');
    $this->assertEqual($rh->getPostAddress(), "321 Example Blvd\nAtlanta, KS  67008");
    $this->assertEqual($rh->getRightsUrl(), 'http://www.somebody-else.com/policies/copyright/');
    $this->assertEqual($rh->getPolicyLimit(), 'Get outta my lawn, you poachers!');

    // and then verify that the changes went to the db by reloading the
    // object and reverifying
    $rh = new rightsholder('FAKE ISBN');
    $this->assertEqual($rh->getName(), 'Somebody Else Book Publishers, LLC');
    $this->assertEqual($rh->getContactName(), 'John Doe');
    $this->assertEqual($rh->getContactEmail(), 'john@somebody-else.com');
    $this->assertEqual($rh->getFax(), '620-555-4321');
    $this->assertEqual($rh->getPostAddress(), "321 Example Blvd\nAtlanta, KS  67008");
    $this->assertEqual($rh->getRightsUrl(), 'http://www.somebody-else.com/policies/copyright/');
    $this->assertEqual($rh->getPolicyLimit(), 'Get outta my lawn, you poachers!');
  }

  function testCreateDestroy()
  {
    $rh = new rightsholder('NONEXISTENT');
    $this->assertEqual($rh->getName(), null);

    $rh->setName('Nobody, inc');
    $this->assertEqual($rh->getName(), 'Nobody, inc');

    $rh = new rightsholder('NONEXISTENT');
    $this->assertEqual($rh->getName(), 'Nobody, inc');

    $rh->destroy();
    $rh = new rightsholder('NONEXISTENT');
    $this->assertEqual($rh->getName(), null);
  }

}

if ( ! defined('RUNNER'))
{
  $test = &new TestRightsholderClass();
  $test->run(new HtmlReporter());
}
?>
