<?php

/***************************************************************************
 *
 *   OUGC Show in Portal plugin
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://www.udezain.com.ar
 *
 *   Choose what threads to show in portal while creating / editing.
 *
 ***************************************************************************/
 
/****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Plugin information.
$l['ougc_showinportal'] = "OUGC Show in Portal";

// Option text
$l['ougc_showinportal_newthread'] = "<b>Portal Thread:</b> show this thread in the front portal.";

// MyCode lang
$l['ougc_showinportal_mycode'] = "

Read More: {1}/{2}";
$l['ougc_showinportal_mycode_html'] = "

<a href=\"{1}/{2}\" title=\"Read Full Thread\">Read More...</a>";
$l['ougc_showinportal_mycode_mycode'] = "

[url={1}/{2}]Read More...[/url]";

// Inline moderation
$l['ougc_showinportal_mycode_showinportal'] = "Portal Threads: Add";
$l['ougc_showinportal_mycode_unshowinportal'] = "Portal Threads: Remove";
$l['ougc_showinportal_mycode_showinportal_done'] = "Threads Added to Portal";
$l['ougc_showinportal_mycode_unshowinportal_done'] = "Threads Removed from Portal";
$l['ougc_showinportal_mycode_showinportal_redirect'] = "The selected threads have been added to the portal.<br />You will now be returned to your previous location.";
$l['ougc_showinportal_mycode_unshowinportal_redirect'] = "The selected threads have been removed from portal.<br />You will now be returned to your previous location.";