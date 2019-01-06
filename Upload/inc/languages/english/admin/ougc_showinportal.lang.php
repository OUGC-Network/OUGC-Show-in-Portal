<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/languages/english/admin/ougc_showinportal.lang.php)
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
$l['setting_group_ougc_showinportal_desc'] = 'Allows moderators to choose what threads to display inside the portal system.';

// Settings
$l['setting_ougc_showinportal_groups'] = 'Allowed Groups';
$l['setting_ougc_showinportal_groups_desc'] = 'Allowed groups to use this feature.';
$l['setting_ougc_showinportal_forums'] = 'Ignored Forums';
$l['setting_ougc_showinportal_forums_desc'] = 'Forums to exclude from this feature.';
$l['setting_ougc_showinportal_tag'] = 'CutOff MyCode Tag';
$l['setting_ougc_showinportal_tag_desc'] = 'Do you want to use a MyCode to cut portal messages? Leave empty to disable. Default "[!--more--]".';
$l['setting_ougc_showinportal_sendpm'] = 'Send PM';
$l['setting_ougc_showinportal_sendpm_desc'] = 'Send a PM to users when one of their threads is added or removed from the portal.';
$l['setting_ougc_showinportal_myalerts'] = 'MyAlerts Integration';
$l['setting_ougc_showinportal_myalerts_desc'] = 'Send a MyAlerts alert to users when one of their threads is added or removed from the portal.';

// Moderator Tools
$l['ougc_showinportal_modtool'] = 'Show in Portal';
$l['ougc_showinportal_modtool_show'] = 'Show';
$l['ougc_showinportal_modtool_remove'] = 'Remove';

// PluginLibrary
$l['ougc_showinportal_pluginlibrary_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
$l['ougc_showinportal_pluginlibrary_update'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later, whereas your current version is {3}.';