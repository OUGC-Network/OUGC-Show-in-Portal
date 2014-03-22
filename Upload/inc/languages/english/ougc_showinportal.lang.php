<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/languages/english/ougc_showinportal.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2012-2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Choose what threads to show in portal while creating / editing.
 *
 ***************************************************************************

****************************************************************************
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

// Plugin API
$l['setting_group_ougc_showinportal'] = 'OUGC Show in Portal';

// Newthread/Newreply
$l['ougc_showinportal_input_newthread'] = '<strong>Portal Thread:</strong> show this thread in the portal.';
$l['ougc_showinportal_input_quickreply'] = '<strong>Portal Thread</strong>';

// MyCode lang
$l['ougc_showinportal_readmore'] = '

Read More: {1}/{2}';
$l['ougc_showinportal_readmore_html'] = '

<a href=\"{1}/{2}\" title=\"Read Full Thread\">Read More...</a>';
$l['ougc_showinportal_readmore_mycode'] = '

[url={1}/{2}]Read More...[/url]';

// Inline moderation
$l['ougc_showinportal_showinportal'] = 'Add To Portal';
$l['ougc_showinportal_unshowinportal'] = 'Remove From Portal';
$l['ougc_showinportal_showinportalthread'] = 'Add/Remove from Portal';
$l['ougc_showinportal_showinportal_done'] = 'Thread(s) Added to the Portal';
$l['ougc_showinportal_unshowinportal_done'] = 'Thread(s) Removed from the Portal';
$l['ougc_showinportal_showinportal_redirect'] = 'The selected thread(s) have been added to the portal.<br />You will now be redirected.';
$l['ougc_showinportal_unshowinportal_redirect'] = 'The selected thread(s) have been removed from the portal.<br />You will now be redirected.';

// MyAlerts
$l['ougc_showinportal_myalerts_showinportal'] = '{1} added your thread "<a href="{2}">{3}</a>" to the portal.';
$l['ougc_showinportal_myalerts_unshowinportal'] = '{1} removed your thread "<a href="{2}">{3}</a>" from the portal.';
$l['ougc_showinportal_myalerts_setting'] = 'Receive alert when one your threads are make (un)visible in the portal?';
$l['ougc_showinportal_myalerts_helpdoc'] = '<strong>Show In Portal</strong>
<p>
	This alert type is received whenever one moderator makes your threads (un)visible in the portal.
</p>';