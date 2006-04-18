<?
/*
Created by Jason White (jbwhite@emory.edu)

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
/*
OPUS uses a strange format to encode semester and year information.  This function will translate the 
encode value to something useful 

We can translate Term to verbiage such as "Fall 2005" if you want.  The
Term code itself is easy to interpret.  Term consists of CYYM where
C is century (first two digits of year) where C value of '5' is
century of '20'
	YY is last two digits of year
	M value of '1' is Spring term
	M value of '6' is Summer term
	M value of '9' is Fall term

	Thus,
	5051 is Spring 2005
	5056 is Summer 2005
	5059 is Fall 2005
	5061 is Spring 2006
	5066 is Summer 2006 
*/

function decode_semester($CYYM)
{
	$C = array(5 => '20');
	$M = array(1 => 'SPRING', 6 => 'SUMMER', 9 => 'FALL');	
	
	$c = $CYYM[0];
	$m = $CYYM[3];
	
	return array('semester' => $M[$m], 'year' => $C[$c] . $CYYM[1] . $CYYM[2]);
}
?>