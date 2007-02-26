/*******************************************************************************
copyClassOptions.js

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


*******************************************************************************

javascript functionality specific to the copyClassOptions displayer
	
*******************************************************************************/

/* force checkbox values necessary for various copy functions */
function setCopyOptions(func)
{
	switch (func)
	{
		case 'crossList':
			chkVal = document.getElementById('crosslistSource').checked;
			
			document.getElementById('copyReserves').checked = chkVal;												
			document.getElementById('copyInstructors').checked = chkVal;
			document.getElementById('copyProxies').checked = chkVal;
			
			document.getElementById('copyEnrollment').disabled = chkVal;
			document.getElementById('copyCrossListings').disabled = chkVal;
			
			if (chkVal) //enrollment should remain with course do not transfer
			{
				document.getElementById('copyEnrollment').checked 	 = !chkVal;
				document.getElementById('copyCrossListings').checked = !chkVal;
			}
					
			if (document.getElementById('deleteSource').checked)
				document.getElementById('deleteSource').checked = !chkVal;
				
			document.getElementById('deleteSource').disabled = chkVal;
			
		break;
		
		default:
	}
}