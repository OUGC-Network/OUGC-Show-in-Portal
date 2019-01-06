<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/languages/english/ougc_showinportal.lang.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2012-2019 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Allows moderators to choose what threads to display inside the portal system.
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
$l['ougc_showinportal_input_newthread'] = '<strong>Portal Thread:</strong> Show this thread in the portal.';
$l['ougc_showinportal_input_quickreply'] = '<strong>Portal Thread</strong>';

// MyCode lang
$l['ougc_showinportal_readmore'] = '

Read More: {1}/{2}';
$l['ougc_showinportal_readmore_html'] = '

<a href=\"{1}/{2}\" title=\"Read Full Thread\">Read More...</a>';
$l['ougc_showinportal_readmore_mycode'] = '

[url={1}/{2}]Read More...[/url]';

// Send PM
$l['ougc_showinportal_pm_subject'] = 'Your thread has been added to the portal.';
$l['ougc_showinportal_pm_message'] = 'Hi! This PM is an automatic notification to notify you that one of your threads have been added to the portal page by a moderators.

Greetings, {1}.';
$l['ougc_showinportal_pm_subject_removed'] = 'Your thread has been removed from the portal.';
$l['ougc_showinportal_pm_message_removed'] = 'Hi! This PM is an automatic notification to notify you that one of your threads have been removed from the portal page by a moderators.

Greetings, {1}.';

// MyAlerts
$l['ougc_showinportal_myalerts_display'] = '{1}, your thread "{3}" was added to the portal by {2}.';
$l['ougc_showinportal_myalerts_removed'] = '{1}, your thread "{3}" was removed from the portal by {2}.';
$l['myalerts_setting_ougc_showinportal'] = 'Receive alerts when my threads are added or removed from the portal.';