<?php
/*******************************************************************************
baseManager.class.php
Base Manager abstract class

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

/**
 * Base Manager abstract class
 * - Contains functions common to many managers
 * - To be extended by other manager classes
 */
abstract class baseManager {
  public $displayClass;
  public $displayFunction;
  public $argList;

  /**
   * call the display class to render the appropriate display
   * function based on selected command
   */
  public function display() {
    if (isset($_SESSION['debug'])) {
      echo "Manager calling display function ". $this->displayClass ."->". $this->displayFunction ."<br>\n";
    }

    if (is_callable(array($this->displayClass, $this->displayFunction))) {
      call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);
    } else {
      trigger_error("Configured display function ". $this->displayClass ."->". $this->displayFunction
        ."is not callable", E_USER_WARNING);
    }
  }
}

?>
