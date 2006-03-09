<?
/*******************************************************************************
news.class.php
news object handles term table

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
class news
{
	function getNews($permission_level = 0)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$now = date("Y-m-d H:i:s",strtotime("now"));
			
				$sql = 	"SELECT news_id, news_text, font_class, begin_time, end_time FROM news 
						 WHERE (permission_level = '$permission_level' OR permission_level is null) 
						 	AND ((begin_time IS NULL AND end_time IS NULL) OR (begin_time <= '$now' AND '$now' <= end_time))
						 ORDER BY news_id DESC
				"
				;
		}
	
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$news = array(0);
		while ($row = $rs->fetchRow())
		{
			$n['id'] 	= $row[0];
			$n['text'] 	= $row[1];
			$n['class']	= $row[2];
			
			$news[] = $n;
		}
		return $news;
	}


}
?>