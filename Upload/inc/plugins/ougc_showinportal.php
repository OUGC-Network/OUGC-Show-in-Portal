<?php

/***************************************************************************
 *
 *   OUGC Show in Portal plugin (/inc/plugins/ougc_showinportal.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   Choose what threads to show in portal while creating / editing.
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

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run the ACP hooks.
if(!defined('IN_ADMINCP'))
{
	$plugins->add_hook('postbit', 'ougc_showinportal_postbit');
	$plugins->add_hook('portal_start', 'ougc_showinportal_portal');
	$plugins->add_hook('editpost_end', 'ougc_showinportal_editpost');
	$plugins->add_hook('newthread_start', 'ougc_showinportal_newthread');
	$plugins->add_hook('moderation_start', 'ougc_showinportal_moderation');
	$plugins->add_hook('forumdisplay_start', 'ougc_showinportal_forumdisplay');
	$plugins->add_hook('datahandler_post_update_thread', 'ougc_showinportal_update_thread');
	$plugins->add_hook('datahandler_post_insert_thread', 'ougc_showinportal_insert_thread');

	if(in_array(THIS_SCRIPT, array('forumdisplay.php', 'newthread.php', 'editpost.php')))
	{
		global $templatelist;

		if(isset($templatelist))
		{
			$templatelist .= ', ';
		}
		else
		{
			$templatelist = '';
		}

		if(THIS_SCRIPT == 'forumdisplay.php')
		{
			$templatelist .= 'ougcshowinportal_inlinemod';
		}
		else
		{
			$templatelist .= 'ougcshowinportal_input';
		}
	}

	global $settings;

	// All right, so what if fid = -1? Lest make that equal to all forums ¬_¬
	if($settings['portal_announcementsfid'] == '-1')
	{
		$forums = cache_forums();
		$fids = array(0);
		foreach($forums as $forum)
		{
			if($forum['type'] == 'f' && $forum['active'] == 1 && $forum['open'] == 1)
			{
				$fids[] = (int)$forum['fid'];
			}
		}
		$settings['portal_announcementsfid'] = implode(',', array_unique($fids));
	}
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Necessary plugin information for the ACP plugin manager.
function ougc_showinportal_info()
{
	global $lang;
	isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');

	return array(
		'name'			=> 'OUGC Show in Portal',
		'description'	=> $lang->ougc_showinportal_d,
		'website'		=> 'http://mods.mybb.com/view/ougc-mark-pm-as-unread',
		'author'		=> 'Omar Gonzalez',
		'authorsite'	=> 'http://community.mybb.com/user-25096.html',
		'version'		=> '1.1',
		'compatibility'	=> '16*',
		'guid'			=> '',
		'pl_version'	=> 11,
		'pl_url'		=> 'http://mods.mybb.com/view/pluginlibrary'
	);
}

// Activate the plugin
function ougc_showinportal_activate()
{
	global $PL, $lang;
	isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');
	ougc_showinportal_reqpl();

	// Add our settings
	$PL->settings('ougc_showinportal', $lang->ougc_showinportal, $lang->ougc_showinportal_d, array(
		'groups'	=> array(
		   'title'			=> $lang->ougc_showinportal_groups,
		   'description'	=> $lang->ougc_showinportal_groups_d,
		   'optionscode'	=> 'text',
			'value'			=>	'3,4,6',
		),
		'tag'	=> array(
		   'title'			=> $lang->ougc_showinportal_tag,
		   'description'	=> $lang->ougc_showinportal_tag_d,
		   'optionscode'	=> 'text',
			'value'			=>	'[!--more--]',
		)
	));

	// Insert template/group
	$PL->templates('ougcshowinportal', $lang->ougc_showinportal, array(
		'input'	=> '<br />
<label><input type="checkbox" class="checkbox" name="{$name}" value="1"{$checked} />&nbsp;{$lang->ougc_showinportal_newthread}</label>',
		'inlinemod'	=> '<option value="multishowinportal">{$lang->ougc_showinportal_mycode_showinportal}</option>
<option value="multiunshowinportal">{$lang->ougc_showinportal_mycode_unshowinportal}</option>'
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
	ougc_showinportal_reqpl();

	// Remove our variables
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('editpost', '#'.preg_quote('{$ougc_showinportal}').'#', '', 0);
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('{$ougc_showinportal}').'#', '', 0);
	find_replace_templatesets('forumdisplay_inlinemoderation', '#'.preg_quote('{$ougc_showinportal}').'#', '', 0);
}

// Install the plugin.
function ougc_showinportal_install()
{
	global $db;
	ougc_showinportal_reqpl();

	// Insert our two columns
	$db->field_exists('showinportal', 'threads') or $db->add_column('threads', 'showinportal', "int(1) NOT NULL DEFAULT '0'");
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
	global $db, $PL;
	ougc_showinportal_reqpl();

	// Delete our settings
	$PL->settings_delete('ougc_showinportal');

	// Delete our templates
	$PL->templates_delete('ougcshowinportal');

	// Drop our columns
	if($db->field_exists('showinportal', 'threads'))
	{
		$db->drop_column('threads', 'showinportal');
	}
}

// Remove MyCode from posts
function ougc_showinportal_postbit(&$post)
{
	global $thread, $settings, $plugins;
	$plugins->remove_hook('postbit', 'ougc_showinportal_postbit'); // we just need this to run once

	// Check if first post
	// Check MyCode option
	// Check if thread is to be shown in portal
	// Check forum id
	if($post['pid'] == $thread['firstpost'] && !empty($settings['ougc_showinportal_tag']) && (bool)$thread['showinportal'] && in_array($thread['fid'], array_unique((array_map('intval', (explode(',', $settings['portal_announcementsfid'])))))))
	{
		$post['message'] = preg_replace('#'.preg_quote($settings['ougc_showinportal_tag']).'#', '', $post['message']);
	}
}

// Alter portal behavior
function ougc_showinportal_portal()
{
	global $settings;

	ougc_showinportal_control_object($GLOBALS['db'], '
		function query($string, $hide_errors=0, $write_query=0)
		{
			if(!$write_query && strpos($string, \'ORDER BY t.dateline DESC\'))
			{
				$string = strtr($string, array(
					\'t.closed\' => \'t.showinportal=\\\'1\\\' AND t.closed\'
				));
			}
			return parent::query($string, $hide_errors, $write_query);
		}
	');

	// Replace MyCode with a "Read More..." kind of link
	if(!empty($settings['ougc_showinportal_tag']))
	{
		global $plugins;
		$plugins->add_hook('portal_announcement', create_function('', 'global $announcement;	ougc_showinportal_readmore($announcement[\'message\'], $announcement[\'fid\'], $announcement[\'tid\']);'));
	}
}

// Insert variable whle editing
function ougc_showinportal_editpost()
{
	global $mybb, $thread, $pid, $ougc_showinportal;
	$ougc_showinportal = '';

	// Post is first post of a thread?
	if(is_moderator($fid) && ougc_showinportal_check_groups($mybb->settings['ougc_showinportal_groups']) && $pid == $thread['firstpost'] && in_array($thread['fid'], array_unique((array_map('intval', (explode(',', $mybb->settings['portal_announcementsfid'])))))))
	{
		global $ougc_showinportal, $templates, $lang;
		isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');

		// Figure out if checked
		$checked = '';
		if($thread['showinportal'] == 1 && !isset($mybb->input['processed']))
		{
			$checked = ' checked="checked"';
		}
		elseif($mybb->input['showinportal'] == 1)
		{
			$checked = ' checked="checked"';
		}

		// Show the option
		$name = 'showinportal';
		eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_input').'";');
	}
}

// Insert variable while trying to create a new thread
function ougc_showinportal_newthread()
{
	global $settings, $fid, $ougc_showinportal;

	$ougc_showinportal = '';
	if(is_moderator($fid) && ougc_showinportal_check_groups($settings['ougc_showinportal_groups']) && in_array($fid, array_unique((array_map('intval', (explode(',', $settings['portal_announcementsfid'])))))))
	{
		global $templates, $lang;
		isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');

		// Figure out if checked
		$checked = '';
		if(isset($mybb->input['modoptions']['showinportal']) && (int)$mybb->input['modoptions']['showinportal'] == 1)
		{
			$checked = ' checked="checked"';
		}

		// Show the option
		$name = 'modoptions[showinportal]';
		eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_input').'";');
	}
}

// Acual moderation magic
function ougc_showinportal_moderation()
{
	global $mybb;

	// Check input
	if(!in_array($mybb->input['action'], array('multishowinportal', 'multiunshowinportal')))
	{
		return;
	}

	// Invalid forum
	if(!is_moderator((int)$mybb->input['fid']) || !ougc_showinportal_check_groups($mybb->settings['ougc_showinportal_groups']) || !in_array(($fid = (int)$mybb->input['fid']), array_unique((array_map('intval', (explode(',', $mybb->settings['portal_announcementsfid'])))))))
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
	isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');

	// No threads selected, show error
	if(count($threads) < 1)
	{
		error($lang->error_inline_nothreadsselected);
	}

	// Do the magic.. not much really...
	$do = 0;
	$log = $lang->ougc_showinportal_mycode_unshowinportal_done;
	$redirect = $lang->ougc_showinportal_mycode_unshowinportal_redirect;
	if($mybb->input['action'] == 'multishowinportal')
	{
		$do = 1;
		$log = $lang->ougc_showinportal_mycode_showinportal_done;
		$redirect = $lang->ougc_showinportal_mycode_showinportal_redirect;
	}

	global $db;

	// Update threads
	$tids = implode(',', (array_unique((array_map('intval', $threads)))));
	$db->update_query('threads', array('showinportal' => $do), "tid IN ({$tids}) AND showinportal!='{$do}'");

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
	global $settings, $fid;

	// Show inline moderation tool
	if(is_moderator($fid) && ougc_showinportal_check_groups($settings['ougc_showinportal_groups']) && in_array($fid, array_unique((array_map('intval', (explode(',', $settings['portal_announcementsfid'])))))))
	{
		global $templates, $ougc_showinportal, $lang;
		isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');

		eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_inlinemod').'";');
	}
}

// Remove the "read more" mycode
function ougc_showinportal_readmore(&$message, $fid, $tid)
{
	global $settings;

	// Remove MyCode only if it is found
	if(preg_match('#'.($tag = preg_quote($settings['ougc_showinportal_tag'])).'#', $message))
	{
		$msg = preg_split('#'.$tag.'#', $message);
		if(isset($msg[0]) && my_strlen($msg[0]) >= (int)$settings['minmessagelength'])
		{
			global $lang, $forum_cache;
			isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');
			$forum_cache or ($forum_cache = cache_forums());

			// We need to know what lang variable to use.
			$lang_val = 'ougc_showinportal_mycode';
			if((bool)$forum_cache[$fid]['allowmycode'])
			{
				$lang_val .= '_mycode';
			}
			elseif((bool)$forum_cache[$fid]['allowhtml'])
			{
				$lang_val .= '_html';
			}

			$message = $msg[0].$lang->sprintf($lang->$lang_val, $settings['bburl'], get_thread_link($tid));
		}
	}
}

// Update a thread
function ougc_showinportal_update_thread(&$dh)
{
	global $mybb;

	// Only moderators and allowed groups can use this
	if(is_moderator($dh->data['fid']) && ougc_showinportal_check_groups($mybb->settings['ougc_showinportal_groups']))
	{
		// Check if a valid forum
		if(!in_array($dh->data['fid'], array_unique((array_map('intval', (explode(',', $mybb->settings['portal_announcementsfid'])))))))
		{
			$dh->thread_update_data['showinportal'] = 0;
		}
		// Set this thread to be shown in portal
		elseif(isset($mybb->input['showinportal']) && (int)$mybb->input['showinportal'] == 1)
		{
			$dh->thread_update_data['showinportal'] = 1;
		}
	}
}

// Validate thread input
function ougc_showinportal_insert_thread(&$dh)
{
	global $settings;
	$dh->thread_insert_data['showinportal'] = 0;

	// Only moderators  and valid groups can use this
	if(is_moderator($dh->data['fid']) && ougc_showinportal_check_groups($settings['ougc_showinportal_groups']))
	{
		global $modoptions;

		// Check if a valid forum
		// Set this thread to be shown in portal
		if(in_array($dh->data['fid'], array_unique((array_map('intval', (explode(',', $settings['portal_announcementsfid'])))))) && isset($dh->data['modoptions']['showinportal']) && (int)$dh->data['modoptions']['showinportal'] == 1)
		{
			$dh->thread_insert_data['showinportal'] = 1;
		}
	}
}

// This will check current user's groups.
function ougc_showinportal_check_groups($groups, $empty=true)
{
	if(empty($groups) && $empty)
	{
		return true;
	}

	global $PL;
	$PL or require_once PLUGINLIBRARY;

	return (bool)$PL->is_member($groups);
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
function ougc_showinportal_control_object(&$obj, $code)
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
			{
				$k = substr($k, $p+1);
			}
			$vars[$k] = $v;
		}
		if(!empty($vars))
		{
			$code .= '
				function ___setvars(&$a) {
					foreach($a as $k => &$v)
						$this->$k = $v;
				}
			';
		}
		eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
		$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
		if(!empty($vars))
		{
			$obj->___setvars($vars);
		}
	}
	// else not a valid object or PHP serialize has changed
}

// Check for PluginLibrary dependencies
function ougc_showinportal_reqpl()
{
	if(!file_exists(PLUGINLIBRARY))
	{
		global $lang;
		isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');
		$info = ougc_showinportal_info();

		flash_message($lang->sprintf($lang->ougc_showinportal_plreq, $info['pl_url'], $info['pl_version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;
	$PL or require_once PLUGINLIBRARY;
	$info = ougc_showinportal_info();

	if($PL->version < $version)
	{
		global $lang;
		isset($lang->ougc_showinportal) or $lang->load('ougc_showinportal');

		flash_message($lang->sprintf($lang->ougc_showinportal_plold, $PL->version, $info['pl_version'], $info['pl_url']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}