<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/languages/english/admin/ougc_showinportal.lang.php)
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
$l['setting_group_ougc_showinportal_desc'] = 'Choose what threads to show in portal while creating / editing.';

// Settings
$l['setting_ougc_showinportal_groups'] = 'Allowed Groups';
$l['setting_ougc_showinportal_groups_desc'] = 'Allowed usergroups to use this feature.';
$l['setting_ougc_showinportal_forums'] = 'Ignored Forums';
$l['setting_ougc_showinportal_forums_desc'] = 'Forums to exclude from this feature.';
$l['setting_ougc_showinportal_tag'] = 'CutOff MyCode Tag';
$l['setting_ougc_showinportal_tag_desc'] = 'Do you want to use a MyCode to cut portal messages? Leave empty to disable. Default "[!--more--]".';
$l['setting_ougc_showinportal_sendpm'] = 'Send PM';
$l['setting_ougc_showinportal_sendpm_desc'] = 'Do you want to send an PM to users each time one of their threads is added/removed from the portal?';
$l['setting_ougc_showinportal_myalerts'] = 'MyAlerts Integration';
$l['setting_ougc_showinportal_myalerts_desc'] = 'Do you want to send an alert to users each time one of their threads is added/removed from the portal?';

// Moderator Tools
$l['ougc_showinportal_modtool'] = 'Show in portal?';
$l['ougc_showinportal_modtool_show'] = 'Show';
$l['ougc_showinportal_modtool_remove'] = 'Remove';

// PluginLibrary
$l['ougc_showinportal_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
$l['ougc_showinportal_pl_old'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later, whereas your current version is {3}.';