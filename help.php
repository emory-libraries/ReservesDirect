<?php
/*******************************************************************************
help.php
wrapper form index.php for display of help iframe; forces "barebones" look

Created by Dmitriy Panteleyev (dpantel@emory.edu)

This file is part of ReservesDirect

/*******************************************************************************
AJAX_functions.php
returns data for ajax data fields

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

ReservesDirect is located at:
http://www.reservesdirect.org/
*******************************************************************************/

//show only content (no logos, menus, etc)
$_REQUEST['no_table'] = 1;
require('index.php');
?>
