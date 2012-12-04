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

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run the ACP hooks.
if(!defined('IN_ADMINCP'))
{
	define('SHOWINPORTAL_MYCODE_STRIGN', '[!--more--]');
	$plugins->add_hook('postbit', 'ougc_showinportal_postbit');
	$plugins->add_hook('portal_start', 'ougc_showinportal_portal');
	$plugins->add_hook('editpost_end', 'ougc_showinportal_editpost');
	$plugins->add_hook('newthread_start', 'ougc_showinportal_newthread');
	$plugins->add_hook('moderation_start', 'ougc_showinportal_moderation');
	$plugins->add_hook('forumdisplay_start', 'ougc_showinportal_forumdisplay');
	$plugins->add_hook('portal_announcement', 'ougc_showinportal_portal_announcement');
	$plugins->add_hook('datahandler_post_update_thread', 'ougc_showinportal_do_editpost');
	$plugins->add_hook('datahandler_post_insert_thread', 'ougc_showinportal_do_newthread');
	if(in_array(THIS_SCRIPT, array('forumdisplay.php', 'newthread.php', 'editpost.php')))
	{
		global $templatelist;

		if(isset($templatelist))
			$templatelist .= ', ';

		if(THIS_SCRIPT == 'forumdisplay.php')
			$templatelist .= 'ougc_showinportal_inlinemoderation';
		elseif(THIS_SCRIPT == 'newthread.php')
			$templatelist .= 'ougc_showinportal_newthread';
		else
			$templatelist .= 'ougc_showinportal_editpost';
	}
}

// Necessary plugin information for the ACP plugin manager.
function ougc_showinportal_info()
{
	global $lang;
	$lang->load('ougc_showinportal');

	return array(
		'name'			=> 'OUGC Show in Portal',
		'description'	=> $lang->ougc_showinportal_d,
		'website'		=> 'http://udezain.com.ar/',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://udezain.com.ar/',
		'version'		=> '1.0',
		'compatibility'	=> '16*',
		'guid'			=> ''
	);
}

// Activate the plugin
function ougc_showinportal_activate()
{
	global $db, $lang;
    $lang->load('ougc_showinportal');
	ougc_showinportal_deactivate(false);

	$query = $db->simple_select('settinggroups', 'COUNT(*) AS settinggroups');
	$disporder = $db->fetch_field($query, 'settinggroups');

	// Insert our settings
	$gid = $db->insert_query('settinggroups',array(
		'name'			=> 'ougc_showinportal',
		'title'			=> $db->escape_string($lang->ougc_showinportal_s),
		'description'	=> $db->escape_string($lang->ougc_showinportal_d),
		'disporder'		=> ++$disporder,
		'isdefault'		=> 'no'
	));
	$db->insert_query('settings',array(
		'name'			=> 'ougc_showinportal_power',
		'title'			=> $db->escape_string($lang->ougc_showinportal_power),
		'description'	=> $db->escape_string($lang->ougc_showinportal_power_d),
		'optionscode'	=> 'onoff',
		'value'			=> 1,
		'disporder'		=> 1,
		'gid'			=> intval($gid)
	));
	$db->insert_query('settings',array(
		'name'			=> 'ougc_showinportal_groups',
		'title'			=> $db->escape_string($lang->ougc_showinportal_groups),
		'description'	=> $db->escape_string($lang->ougc_showinportal_groups_d),
		'optionscode'	=> 'text',
		'value'			=> '3,4,6',
		'disporder'		=> 2,
		'gid'			=> intval($gid)
	));
	$db->insert_query('settings',array(
		'name'			=> 'ougc_showinportal_short',
		'title'			=> $db->escape_string($lang->ougc_showinportal_short),
		'description'	=> $db->escape_string($lang->ougc_showinportal_short_d),
		'optionscode'	=> 'yesno',
		'value'			=> 1,
		'disporder'		=> 3,
		'gid'			=> intval($gid)
	));
	rebuild_settings();

	// Inser our templates
	$db->insert_query('templates', array(
		'title'		=>	'ougc_showinportal_newthread',
		'template'	=>	$db->escape_string('<br />
<label><input type="checkbox" class="checkbox" name="modoptions[showinportal]" value="1"{$showinportalcheck} />&nbsp;{$lang->ougc_showinportal_newthread}</label>'),
		'sid'		=>	-1,
	));
	$db->insert_query('templates', array(
		'title'		=>	'ougc_showinportal_editpost',
		'template'	=>	$db->escape_string('<br />
<label><input type="checkbox" class="checkbox" name="showinportal" value="1"{$showinportalcheck} />&nbsp;{$lang->ougc_showinportal_newthread}</label>'),
		'sid'		=>	-1,
	));
	$db->insert_query('templates', array(
		'title'		=>	'ougc_showinportal_inlinemoderation',
		'template'	=>	$db->escape_string('<option value="multishowinportal">{$lang->ougc_showinportal_mycode_showinportal}</option>
<option value="multiunshowinportal">{$lang->ougc_showinportal_mycode_unshowinportal}</option>'),
		'sid'		=>	-1,
	));

	// Add our variables
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('editpost', '#'.preg_quote('{$disablesmilies}').'#', '{$disablesmilies}{$ougc_showinportal}');
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('stick_thread}</label>').'#', 'stick_thread}</label>{$ougc_showinportal}');
	find_replace_templatesets('forumdisplay_inlinemoderation', '#'.preg_quote('unapprove_threads}</option>').'#', 'unapprove_threads}</option>{$ougc_showinportal}');
}

//Deactivate the plugin.
function ougc_showinportal_deactivate($rebuilt=true)
{
	global $db;
	$gid = $db->fetch_field($db->simple_select('settinggroups', 'gid', "name='ougc_showinportal'"), 'gid');
	if($gid)
	{
		$db->delete_query("settings", "gid='{$gid}'");
		$db->delete_query("settinggroups", "gid='{$gid}'");
		if($rebuilt == true)
		{
			rebuild_settings();
		}
	}

	// Delete our templates
	$db->delete_query('templates', "title IN('ougc_showinportal_newthread', 'ougc_showinportal_editpost', 'ougc_showinportal_inlinemoderation')");

	// Remove our variables
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('editpost', '#'.preg_quote('{$ougc_showinportal}').'#', '', 0);
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('{$ougc_showinportal}').'#', '', 0);
	find_replace_templatesets('forumdisplay_inlinemoderation', '#'.preg_quote('{$ougc_showinportal}').'#', '', 0);
}

// Install the plugin.
function ougc_showinportal_install()
{
	global $db, $cache, $settings;
	ougc_showinportal_uninstall();

	// Insert our two columns
	$db->add_column('threads', 'showinportal', "int(1) NOT NULL DEFAULT '0'");
}

// Is this plugin installed?
function ougc_showinportal_is_installed()
{
	global $db;

	return $db->field_exists('showinportal', 'threads');
}

// Uninstall the plugin.
function ougc_showinportal_uninstall()
{
	global $db, $cache;

	// Drop our columns
	if($db->field_exists('showinportal', 'threads'))
	{
		$db->drop_column('threads', 'showinportal');
	}
}

// Remove MyCode from posts
function ougc_showinportal_postbit(&$post)
{
	global $thread;

	// Return
	if(!ougc_showinportal_check_perm($thread['fid'], $thread['firstpost'], $post['pid'], true))
	{
		return false;
	}

	// Remove MyCode only if post is suppose to be show in portal
	if($thread['showinportal'] == 1)
	{
		$post['message'] = preg_replace("#\\".SHOWINPORTAL_MYCODE_STRIGN."#", '', $post['message']);
	}
}

// Alter portal behavior
function ougc_showinportal_portal()
{
	global $mybb;

	// Deactivated
	if($mybb->settings['ougc_showinportal_power'] != 1)
	{
		return false;
	}

	control_object($GLOBALS['db'], '
		function query($string, $hide_errors=0, $write_query=0) {
			static $done=false;
			if(!$done && !$write_query && strpos($string, \'ORDER BY t.dateline DESC\')) {
				$done = true;
				$string = strtr($string, array(
					\'t.closed\' => \'t.showinportal=1 AND t.closed\'
				));
			}
			return parent::query($string, $hide_errors, $write_query);
		}
	');

}

// Insert variable whle editing
function ougc_showinportal_editpost()
{
	global $mybb, $thread, $pid;

	// Return
	if(!ougc_showinportal_check_perm($thread['fid'], $thread['firstpost'], $pid))
	{
		return false;
	}

	global $ougc_showinportal, $templates, $lang;
	$lang->load('ougc_showinportal');

	// Figure out if checked
	$showinportalcheck = '';
	if($thread['showinportal'] == 1 && !isset($mybb->input['processed']))
	{
		$showinportalcheck = ' checked="checked"';
	}
	elseif($mybb->input['showinportal'] == 1)
	{
		$showinportalcheck = ' checked="checked"';
	}

	// Show the option
	eval('$ougc_showinportal = "'.$templates->get('ougc_showinportal_editpost').'";');
}

// Insert variable while trying to create a new thread
function ougc_showinportal_newthread()
{
	global $mybb, $fid;

	// Return
	if(!ougc_showinportal_check_perm($fid))
	{
		return false;
	}

	global $ougc_showinportal, $templates, $lang;
	$lang->load('ougc_showinportal');

	// Figure out if checked
	$showinportalcheck = '';
	if(is_array($mybb->input['modoptions']))
	{
		if(isset($mybb->input['modoptions']['showinportal']) && $mybb->input['modoptions']['showinportal'] == 1)
		{
			$showinportalcheck = ' checked="checked"';
		}
	}

	// Show the option
	eval('$ougc_showinportal = "'.$templates->get('ougc_showinportal_newthread').'";');
}

// Acual moderation magic
function ougc_showinportal_moderation()
{
	global $mybb;
	$fid = intval($mybb->input['fid']);

	// Check input
	if(!in_array($mybb->input['action'], array('multishowinportal', 'multiunshowinportal')))
	{
		return;
	}

	// Return
	if(!ougc_showinportal_check_perm($fid))
	{
		error_no_permission();
	}

	// Check forum password 
	check_forum_password($fid);

	// Verify post check
	verify_post_check($mybb->input['my_post_key']);

	// Get threads array
	$threads = getids($fid, 'forum');

	global $lang;
	$lang->load('ougc_showinportal');

	// No threads selected, show error
	if(count($threads) < 1)
	{
		error($lang->error_inline_nothreadsselected);
	}

	// Do the magic.. not much magic really...
	if($mybb->input['action'] == 'multishowinportal')
	{
		$do = 1;
		$log = $lang->ougc_showinportal_mycode_showinportal_done;
		$redirect = $lang->ougc_showinportal_mycode_showinportal_redirect;
	}
	else
	{
		$do = 0;
		$log = $lang->ougc_showinportal_mycode_unshowinportal_done;
		$redirect = $lang->ougc_showinportal_mycode_unshowinportal_redirect;
	}

	global $db;

	// Update threads
	$threads = implode(',', (array_unique((array_map('intval', $threads)))));
	$db->update_query('threads', array('showinportal' => $do), "tid IN ({$threads}) AND showinportal!='{$do}'");

	// Log moderation action
	log_moderator_action(array('fid' => $fid), $log);

	// Clear inline moderation for those threads
	clearinline($fid, 'forum');

	// Redirect
	moderation_redirect(get_forum_link($fid), $redirect);
	exit;
}

// Inline moderation tool
function ougc_showinportal_forumdisplay()
{
	global $mybb;

	// Return
	if(!ougc_showinportal_check_perm($mybb->input['fid']))
	{
		return false;
	}

	global $templates, $ougc_showinportal, $lang;
	$lang->load('ougc_showinportal');

	eval('$ougc_showinportal = "'.$templates->get('ougc_showinportal_inlinemoderation').'";');
}

// Replace MyCode with a "Read More..." kind of link
function ougc_showinportal_portal_announcement()
{
	global $announcement, $forum;

	// Return
	if(!ougc_showinportal_check_perm($announcement['fid'], $announcement['firstpost'], $announcement['pid'], true))
	{
		return false;
	}

	// Remove MyCode only if it is found
	if(preg_match("#\\".SHOWINPORTAL_MYCODE_STRIGN."#", $announcement['message']))
	{
		global $lang, $settings;
		if(!isset($lang->ougc_showinportal))
		{
			$lang->load('ougc_showinportal');
		}

		// We need to know what lang variable to use.
		$lang_val = '';
		if($forum[$announcement['fid']]['allowhtml'])
		{
			$lang_val = '_html';
		}
		elseif($forum[$announcement['fid']]['allowmycode'])
		{
			$lang_val = '_mycode';
		}
		$lang_val = 'ougc_showinportal_mycode'.$lang_val;

		$announcement['message'] = preg_split("#\\".SHOWINPORTAL_MYCODE_STRIGN."#", $announcement['message']);
		if(my_strlen($announcement['message'][0]) >= intval($mybb->settings['minmessagelength']))
		{
			$announcement['message'] = $announcement['message'][0].$lang->sprintf($lang->$lang_val, $settings['bburl'], $announcement['threadlink']);
		}
		else
		{
			$announcement['message'] = $announcement['message'][0].$announcement['message'][1];
		}
	}
}

// Update a thread
function ougc_showinportal_do_editpost(&$data)
{
	global $mybb, $modoptions;

	// Return
	if(!ougc_showinportal_check_perm($data->data['fid']))
	{
		return false;
	}

	// Set this thread to be shown in portal
	$data->thread_update_data['showinportal'] = 0;
	if(isset($mybb->input['showinportal']) && $mybb->input['showinportal'] == 1)
	{
		$data->thread_update_data['showinportal'] = 1;
	}
}

// Validate thread input
function ougc_showinportal_do_newthread(&$data)
{
	global $mybb, $modoptions;
	$thread = $data->data;

	// Return
	if(!ougc_showinportal_check_perm($thread['fid']))
	{
		return false;
	}

	// Set this thread to be shown in portal
	if(isset($thread['modoptions']['showinportal']) && $thread['modoptions']['showinportal'] == 1)
	{
		$data->thread_insert_data['showinportal'] = 1;
	}
}

// Check permissions
function ougc_showinportal_check_perm($fid, $firstpost=0, $pid=0, $mycode=false)
{
	global $mybb;
	$fid = intval($fid);

	// Deactivated or guest
	if($mybb->settings['ougc_showinportal_power'] != 1 || (!$mycode && !$mybb->user['uid']))
	{
		return false;
	}

	// Check for valid users
	if(!$mycode && !ougc_check_groups($mybb->settings['ougc_showinportal_groups']))
	{
		return false;
	}

	// Only moderators can use this
	if(!$mycode && !is_moderator($fid))
	{
		return false;
	}
	
	// Check if a valid forum
	$forums_a = array_unique((array_map('intval', (explode(',', $mybb->settings['portal_announcementsfid'])))));
	if(!$mycode && (empty($mybb->settings['portal_announcementsfid']) || !in_array($fid, $forums_a)))
	{
		return false;
	}

	// This is not a 'thread' but a post
	if($firstpost != $pid)
	{
		return false;
	}

	// Check MyCode option
	if($mycode && $mybb->settings['ougc_showinportal_short'] != 1)
	{
		return false;
	}

	return true;
}

// This will check current user's groups.
if(!function_exists('ougc_check_groups'))
{
	function ougc_check_groups($groups, $empty=true)
	{
		global $mybb;
		if(empty($groups) && $empty == true)
		{
			return true;
		}
		if(!empty($mybb->user['additionalgroups']))
		{
			$usergroups = explode(',', $mybb->user['additionalgroups']);
		}
		if(!is_array($usergroups))
		{
			$usergroups = array();
		}
		$usergroups[] = $mybb->user['usergroup'];
		$groups = explode(',', $groups);
		foreach($usergroups as $gid)
		{
			if(in_array($gid, $groups))
			{
				return true;
			}
		}
		return false;
	}
}

// Control object written by Zinga Burga / Yumi from ( http://mybbhacks.zingaburga.com/ )
if(!function_exists('control_object'))
{
	function control_object(&$obj, $code)
	{
		static $cnt = 0;
		$newname = '_objcont_'.(++$cnt);
		$objserial = serialize($obj);
		$classname = get_class($obj);
		$checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
		$checkstr_len = strlen($checkstr);
		if(substr($objserial, 0, $checkstr_len) == $checkstr)
		{
			$vars = array();
			// grab resources/object etc, stripping scope info from keys
			foreach((array)$obj as $k => $v)
			{
				if($p = strrpos($k, "\0"))
					$k = substr($k, $p+1);
				$vars[$k] = $v;
			}
			if(!empty($vars))
				$code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
			eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
			$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
			if(!empty($vars))
				$obj->___setvars($vars);
		}
		// else not a valid object or PHP serialize has changed
	}
}