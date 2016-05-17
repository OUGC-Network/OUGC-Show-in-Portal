<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/plugins/ougc_showinportal.php)
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

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Run/Add Hooks
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_start', array('OUGC_ShowInPortal', 'lang_load'));
	$plugins->add_hook('admin_style_templates_set', array('OUGC_ShowInPortal', 'lang_load'));
	$plugins->add_hook('admin_config_settings_change', array('OUGC_ShowInPortal', 'lang_load'));
	$plugins->add_hook('admin_formcontainer_end', 'ougc_showinportal_modtools');
	$plugins->add_hook('admin_config_mod_tools_add_thread_tool_commit', 'ougc_showinportal_modtools_commit');
	$plugins->add_hook('admin_config_mod_tools_edit_thread_tool_commit', 'ougc_showinportal_modtools_commit');
}
else
{
	global $settings;

	$plugins->add_hook('moderation_start', 'ougc_showinportal_moderation');
	$plugins->add_hook('newthread_end', 'ougc_showinportal_newthread_end');
	$plugins->add_hook('datahandler_post_insert_thread', 'ougc_showinportal_insert_thread');
	$plugins->add_hook('showthread_end', 'ougc_showinportal_showthread_end');
	$plugins->add_hook('datahandler_post_insert_post', 'ougc_showinportal_post_insert_post');
	$plugins->add_hook('newreply_end', 'ougc_showinportal_newthread_end');
	$plugins->add_hook('postbit', 'ougc_showinportal_postbit');
	$plugins->add_hook('portal_start', 'ougc_showinportal_portal');
	$plugins->add_hook('syndication_start', 'ougc_showinportal_syndication');
	$plugins->add_hook('xmlhttp_update_post', 'ougc_showinportal_xmlhttp');

	// My Alerts
	$plugins->add_hook('myalerts_load_lang', array('OUGC_ShowInPortal', 'lang_load'));
	$plugins->add_hook('misc_help_helpdoc_start', 'ougc_showinportal_myalerts_helpdoc');
	$plugins->add_hook('myalerts_alerts_output_end', 'ougc_showinportal_myalerts_output');

	if(in_array(THIS_SCRIPT, array('forumdisplay.php', 'showthread.php', 'newthread.php', 'newreply.php')))
	{
		global $templatelist;

		if(!isset($templatelist))
		{
			$templatelist = '';
		}
		else
		{
			$templatelist .= ',';
		}

		$templatelist .= 'ougcshowinportal_input';
	}
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_showinportal_info()
{
	global $lang, $showinportal;
	$showinportal->lang_load();

	return array(
		'name'			=> 'OUGC Show in Portal',
		'description'	=> $lang->setting_group_ougc_showinportal_desc,
		'website'		=> 'http://omarg.me',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '1.8',
		'versioncode'	=> 1800,
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_showinportal',
		'myalerts'		=> 105,
		'pl'			=> array(
			'version'	=> 12,
			'url'		=> 'http://mods.mybb.com/view/pluginlibrary'
		)
	);
}

// _activate() routine
function ougc_showinportal_activate()
{
	global $PL, $lang, $cache, $showinportal;
	$showinportal->lang_load();
	ougc_showinportal_deactivate();

	// Add settings group
	$PL->settings('ougc_showinportal', $lang->setting_group_ougc_showinportal, $lang->setting_group_ougc_showinportal_desc, array(
		'groups'	=> array(
		   'title'			=> $lang->setting_ougc_showinportal_groups,
		   'description'	=> $lang->setting_ougc_showinportal_groups_desc,
		   'optionscode'	=> 'groupselect',
			'value'			=>	'3,4,6',
		),
		'forums'	=> array(
		   'title'			=> $lang->setting_ougc_showinportal_forums,
		   'description'	=> $lang->setting_ougc_showinportal_forums_desc,
		   'optionscode'	=> 'forumselect',
			'value'			=>	'',
		),
		'tag'		=> array(
		   'title'			=> $lang->setting_ougc_showinportal_tag,
		   'description'	=> $lang->setting_ougc_showinportal_tag_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'[!--more--]',
		),
		'myalerts'	=> array(
		   'title'			=> $lang->setting_ougc_showinportal_myalerts,
		   'description'	=> $lang->setting_ougc_showinportal_myalerts_desc,
		   'optionscode'	=> 'yesno',
			'value'			=>	0,
		)
	));

	// Add template group
	$PL->templates('ougcshowinportal', '<lang:setting_group_ougc_showinportal>', array(
		'input'		=> '<br /><label><input type="checkbox" class="checkbox" name="{$name}" value="1"{$checked} />&nbsp;{$message}</label>'
	));

	// Modify templates
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('stick_thread}</label>').'#', 'stick_thread}</label><!--OUGC_SHOWINPORTAL-->');
	find_replace_templatesets('showthread_quickreply', '#'.preg_quote('{$closeoption}').'#', '{$closeoption}<!--OUGC_SHOWINPORTAL-->');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_showinportal_info();

	if(!isset($plugins['showinportal']))
	{
		$plugins['showinportal'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/
	if($plugins['showinportal'] <= 1200)
	{
		// Update DB entries
		ougc_showinportal_install();
	}
	/*~*~* RUN UPDATES END *~*~*/

	$plugins['showinportal'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate() routine
function ougc_showinportal_deactivate($rebuilt=true)
{
	ougc_showinportal_pl_check();

	// Revert template edits
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
	find_replace_templatesets('showthread_quickreply', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
	find_replace_templatesets('forumdisplay_inlinemoderation', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
	find_replace_templatesets('showthread_moderationoptions', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
}

// _install() routine
function ougc_showinportal_install()
{
	global $db;

	// Add DB entries
	if(!$db->field_exists('showinportal', 'threads'))
	{
		$db->add_column('threads', 'showinportal', 'int(1) NOT NULL DEFAULT \'0\'');
	}

	if($db->table_exists('alert_settings') && $db->table_exists('alert_setting_values'))
	{
		$query = $db->simple_select('alert_settings', 'id', 'code=\'ougc_showinportal\'');

		if(!($id = (int)$db->fetch_field($query, 'id')))
		{
			$id = (int)$db->insert_query('alert_settings', array('code' => 'ougc_showinportal'));
	
			// Only update the first time
			$db->delete_query('alert_setting_values', 'setting_id=\''.$id.'\'');

			$query = $db->simple_select('users', 'uid');
			while($uid = (int)$db->fetch_field($query, 'uid'))
			{
				$settings[] = array(
					'user_id'		=> $uid,
					'setting_id'	=> $id,
					'value'			=> 1
				);
			}

			if(!empty($settings))
			{
				$db->insert_query_multiple('alert_setting_values', $settings);
			}
		}
	}
}

// _is_installed() routine
function ougc_showinportal_is_installed()
{
	global $db;

	return $db->field_exists('showinportal', 'threads');
}

// _uninstall() routine
function ougc_showinportal_uninstall()
{
	global $db, $PL, $cache;
	ougc_showinportal_pl_check();

	// Drop DB entries
	if($db->field_exists('showinportal', 'threads'))
	{
		$db->drop_column('threads', 'showinportal');
	}

	if($db->table_exists('alert_settings'))
	{
		$db->delete_query('alert_settings', 'code=\'ougc_showinportal\'');
	}

	$PL->settings_delete('ougc_showinportal');
	$PL->templates_delete('ougcshowinportal');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['showinportal']))
	{
		unset($plugins['showinportal']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}
}

// PluginLibrary dependency check & load
function ougc_showinportal_pl_check()
{
	global $lang, $showinportal;
	$showinportal->lang_load();
	$info = ougc_showinportal_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_showinportal_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_showinportal_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Moderator Tools
function ougc_showinportal_modtools()
{
	global $mybb, $run_module, $form_container, $lang;

	if(!($run_module == 'config' && !empty($form_container->_title) && !empty($lang->thread_moderation) && $form_container->_title == $lang->thread_moderation && $mybb->get_input('action') != 'add_post_tool' && $mybb->get_input('action') != 'edit_post_tool'))
	{
		return;
	}

	global $form, $showinportal;
	$showinportal->lang_load();

	if($mybb->get_input('action') != 'add_thread_tool' && !isset($mybb->input['showinportal']))
	{
		global $thread_options;

		$mybb->input['showinportal'] = (int)$thread_options['showinportal'];
	}

	$sip = $mybb->get_input('showinportal', 1);
	$sip = ($sip > 3 || $sip < 0 ? 0 : (int)$sip);

	$form_container->output_row($lang->ougc_showinportal_modtool.' <em>*</em>', '', $form->generate_select_box('showinportal', array(
		0	=> $lang->no_change,
		1	=> $lang->ougc_showinportal_modtool_show,
		2	=> $lang->ougc_showinportal_modtool_remove,
		3	=> $lang->toggle
	), $sip, array('id' => 'showinportal')), 'showinportal');
}

// Save moderator tools input
function ougc_showinportal_modtools_commit()
{
	global $mybb;

	if($mybb->request_method == 'post')
	{
		global $db, $thread_options, $update_tool, $new_tool;

		$sip = $mybb->get_input('showinportal', 1);
		$thread_options['showinportal'] = ($sip > 3 || $sip < 0 ? 0 : $sip);

		$var = $mybb->get_input('action') == 'add_thread_tool' ? 'new_tool' : 'update_tool';

		${$var}['threadoptions'] = $db->escape_string(serialize($thread_options));

		if($mybb->get_input('action') == 'add_thread_tool')
		{
			global $tid;

			$db->update_query('modtools', $new_tool, 'tid=\''.$tid.'\'');
		}
	}
}

// Moderation magic
function ougc_showinportal_moderation()
{
	global $mybb;

	global $mybb;

	// Custom moderator tools process
	if(!in_array($mybb->get_input('action'), array('showinportal', 'multishowinportal', 'multiunshowinportal')))
	{
		if(in_array($mybb->get_input('action'), array('reports', 'allreports', 'getip', 'cancel_delayedmoderation', 'delayedmoderation', 'do_delayedmoderation', 'openclosethread', 'stick', 'removeredirects', 'deletethread', 'do_deletethread', 'deletepoll', 'do_deletepoll', 'approvethread', 'unapprovethread', 'deleteposts', 'do_deleteposts', 'mergeposts', 'do_mergeposts', 'move', 'do_move', 'threadnotes', 'do_threadnotes', 'merge', 'do_merge', 'split', 'do_split', 'removesubscriptions', 'multideletethreads', 'do_multideletethreads', 'multiopenthreads', 'multiclosethreads', 'multiapprovethreads', 'multiunapprovethreads', 'multistickthreads', 'multiunstickthreads', 'multimovethreads', 'do_multimovethreads', 'multideleteposts', 'do_multideleteposts', 'multimergeposts', 'do_multimergeposts', 'multisplitposts', 'do_multisplitposts', 'multiapproveposts', 'multiunapproveposts')) || ($tid = $mybb->get_input('action', 1)) < 1)
		{
			return;
		}

		// Took from xThreads START
		control_object($GLOBALS['db'], '
			function simple_select($table, $fields="*", $conditions="", $options=array())
			{
				static $done=false;
				if(!$done && $table == "modtools" && substr($conditions, 0, 4) == "tid=" && empty($options))
				{
					$done = true;
					ougc_showinportal_moderation_custom();
				}
				return parent::simple_select($table, $fields, $conditions, $options);
			}
		');
		// Took from xThreads END

		return;
	}
}

// Took from xThreads START
function ougc_showinportal_moderation_custom()
{
	global $custommod;

	if(!is_object($custommod))
	{
		return;
	}

	control_object($custommod, '
		function execute_thread_moderation($thread_options, $tids)
		{
			if(!$thread_options[\'deletethread\'])
			{
				ougc_showinportal_moderation_custom_do($tids, $thread_options[\'showinportal\']);
			}
			return parent::execute_thread_moderation($thread_options, $tids);
		}
	');
	
}

function ougc_showinportal_moderation_custom_do($tids, $sip)
{
	$sip = ($sip > 3 || $sip < 0 ? 0 : $sip);

	if(!$sip)
	{
		return;
	}

	global $showinportal;

	switch($sip)
	{
		case 1:
			$showinportal->thread_update(1, $tids);
			break;
		case 2:
			$showinportal->thread_update(0, $tids);
			break;
		case 3:
			global $db;

			$query = $db->simple_select('threads', 'tid, showinportal', 'tid IN (\''.implode('\',\'', $tids).'\')');
			while($thread = $db->fetch_array($query))
			{
				if($thread['showinportal'])
				{
					$remove[] = (int)$thread['tid'];
				}
				else
				{
					$show[] = (int)$thread['tid'];
				}
			}

			empty($remove) or $showinportal->thread_update(0, $remove);
			empty($show) or $showinportal->thread_update(1, $show);
			break;
	}
}
// Took from xThreads END

// Insert plugin variable at new thread
function ougc_showinportal_newthread_end()
{
	global $modoptions;

	if(!isset($modoptions) || my_strpos($modoptions, '<!--OUGC_SHOWINPORTAL-->') === false)
	{
		return;
	}

	global $showinportal, $mybb, $fid;

	if(!$showinportal->can_moderate($fid))
	{
		return;
	}

	global $templates, $lang, $thread;
	$showinportal->lang_load();

	// Figure out if checked
	if(THIS_SCRIPT == 'newreply.php' && !isset($mybb->input['modoptions']) && !isset($mybb->input['modoptions']['showinportal']) && isset($thread['showinportal']))
	{
		$mybb->input['modoptions']['showinportal'] = (int)$thread['showinportal'];
	}
	$sip = (int)$mybb->input['modoptions']['showinportal'];

	$checked = '';
	if(!empty($sip))
	{
		$checked = ' checked="checked"';
	}

	// Show the option
	$name = 'modoptions[showinportal]';
	$message = $lang->ougc_showinportal_input_newthread;
	eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_input').'";');

	$modoptions = str_replace('<!--OUGC_SHOWINPORTAL-->', $ougc_showinportal, $modoptions);
}

// Validate thread input
function ougc_showinportal_insert_thread(&$dh)
{
	global $settings;
	$dh->thread_insert_data['showinportal'] = 0;

	// Only moderators  and valid groups can use this
	if(is_moderator($dh->data['fid']))
	{
		global $showinportal;

		// Check if a valid forum
		// Set this thread to be shown in portal
		if($showinportal->can_moderate($dh->data['fid']) && !empty($dh->data['modoptions']['showinportal']))
		{
			$dh->thread_insert_data['showinportal'] = 1;
		}
	}
}

// Quickreply moderation
function ougc_showinportal_showthread_end()
{
	global $showinportal, $settings, $thread;

	if(!$showinportal->can_moderate($thread['fid']))
	{
		return;
	}

	global $templates, $lang, $quickreply, $moderationoptions;
	$showinportal->lang_load();

	// Figure out if checked
	$checked = '';
	if($thread['showinportal'])
	{
		$checked = ' checked="checked"';
	}

	// Show the option
	$name = 'modoptions[showinportal]';
	$message = $lang->ougc_showinportal_input_quickreply;
	eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_input').'";');

	$quickreply = str_replace('<!--OUGC_SHOWINPORTAL-->', $ougc_showinportal, $quickreply);
}

// Validate reply input
function ougc_showinportal_post_insert_post(&$args)
{
	global $mybb, $showinportal;

	if(!$showinportal->can_moderate($thread['fid']))
	{
		return;
	}

	$thread = get_thread($args->data['tid']);

	if($thread['showinportal'] && empty($args->data['modoptions']['showinportal']))
	{
		$showinportal->thread_update(0, $thread['tid']);
	}
	if(!$thread['showinportal'] && isset($args->data['modoptions']['showinportal']))
	{
		$showinportal->thread_update(1, $thread['tid']);
	}
}

// Remove MyCode from posts (only if visible in portal)
function ougc_showinportal_postbit(&$post)
{
	global $thread, $plugins;
	$plugins->remove_hook('postbit', 'ougc_showinportal_postbit'); // we just need this to run once

	if($thread['firstpost'] != $post['pid'] || !$thread['showinportal'])
	{
		return;
	}

	global $showinportal, $settings;

	if(!$showinportal->can_moderate($thread['fid']))
	{
		return;
	}

	$post['message'] = preg_replace('#'.preg_quote($settings['ougc_showinportal_tag']).'#', '', $post['message']);
}

// AJAX tricks
function ougc_showinportal_xmlhttp()
{
	global $post;

	ougc_showinportal_postbit($post);
}

// Alter portal behaviour
function ougc_showinportal_portal()
{
	global $db, $settings;

	control_object($db, '
		function query($string, $hide_errors=0, $write_query=0)
		{
			if(!$write_query && strpos($string, \'ORDER BY t.dateline DESC\'))
			{
				$string = strtr($string, array(
					\'t.closed\' => \'t.showinportal=\\\'1\\\' AND t.closed\'
				));
			}
			if(!$write_query && strpos($string, \'OUNT(t.tid) AS thread\'))
			{
				$string = strtr($string, array(
					\'t.visible\' => \'t.showinportal=\\\'1\\\' AND t.visible\'
				));
			}
			return parent::query($string, $hide_errors, $write_query);
		}
	');

	// Replace MyCode with a "Read More..." kind of link
	if(!empty($settings['ougc_showinportal_tag']))
	{
		global $plugins;

		$plugins->add_hook('portal_announcement', create_function('', 'global $announcement;	ougc_showinportal_cutoff($announcement[\'message\'], $announcement[\'fid\'], $announcement[\'tid\']);'));
	}
}

// Alter syndication behaviour
function ougc_showinportal_syndication()
{
	global $mybb;

	if(!($mybb->get_input('portal') && $mybb->settings['portal']))
	{
		return;
	}

	control_object($GLOBALS['db'], '
		function simple_select($table, $fields="*", $conditions="", $options=array())
		{
			if($table == "threads" && strpos($fields, \'subject, tid, dateline\') !== false)
			{
				$conditions = strtr($conditions, array(
					\'visible\' => \'showinportal=\\\'1\\\' AND visible\'
				));
			}
			return parent::simple_select($table, $fields, $conditions, $options);
		}
	');
}

// Remove the cutoff mycode
function ougc_showinportal_cutoff(&$message, $fid, $tid)
{
	global $settings;

	if(!$message || !$settings['ougc_showinportal_tag'])
	{
		return;
	}

	if(!preg_match('#'.($tag = preg_quote($settings['ougc_showinportal_tag'])).'#', $message))
	{
		return;
	}

	$msg = preg_split('#'.$tag.'#', $message);
	if(!(isset($msg[0]) && my_strlen($msg[0]) >= (int)$settings['minmessagelength']))
	{
		return;
	}

	global $lang, $forum_cache, $showinportal;
	$showinportal->lang_load();

	$forum_cache or cache_forums();

	// Find out what langguage variable to use
	$lang_var = 'ougc_showinportal_readmore';
	if((bool)$forum_cache[$fid]['allowmycode'])
	{
		$lang_var .= '_mycode';
	}
	elseif((bool)$forum_cache[$fid]['allowhtml'])
	{
		$lang_var .= '_html';
	}

	$message = $msg[0].$lang->sprintf($lang->{$lang_var}, $settings['bburl'], get_thread_link($tid));
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
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
}

// MyAlerts: Help Documents
function ougc_showinportal_myalerts_helpdoc()
{
	global $helpdoc, $lang, $settings;

	if($helpdoc['name'] != $lang->myalerts_help_alert_types)
	{
        return;
    }

    if($settings['ougc_showinportal_myalerts'])
    {
		global $showinportal;
		$showinportal->lang_load();

        $helpdoc['document'] .= $lang->ougc_showinportal_myalerts_helpdoc;
    }
}

// MyAlerts: Output
function ougc_showinportal_myalerts_output(&$args)
{
	global $mybb;

	if($args['alert_type'] != 'ougc_showinportal' || !$mybb->user['myalerts_settings']['ougc_showinportal'])
	{
		return;
    }

	global $showinportal, $lang;
	$showinportal->lang_load();

	$lang_var = 'ougc_showinportal_myalerts_showinportal';
	if(!$args['content'][0])
	{
		$lang_var = 'ougc_showinportal_myalerts_unshowinportal';
	}

	$thread = get_thread($args['tid']);

	if(!$thread)
	{
		return;
	}

	$args['threadLink'] = $mybb->settings['bburl'].'/'.get_thread_link($thread['tid']);
	$args['message'] = $lang->sprintf($lang->{$lang_var}, $args['user'], $args['threadLink'], htmlspecialchars_uni($thread['subject']), $args['dateline']);
	$args['rowType'] = 'showinportal';
}

// Magicfull
class OUGC_ShowInPortal
{
	// Build class
	function __construct()
	{
		global $settings;

		if($settings['ougc_showinportal_myalerts'])
		{
			$settings['myalerts_alert_ougc_showinportal'] = 1;
		}
	}

	// Loads language strings
	function lang_load()
	{
		global $lang;

		isset($lang->setting_group_ougc_showinportal) or $lang->load('ougc_showinportal');

		// MyAlerts, ugly bitch
		if(isset($lang->ougc_showinportal_myalerts_setting))
		{
			$lang->myalerts_setting_ougc_showinportal = $lang->ougc_showinportal_myalerts_setting;
		}
	}

	// $PL->is_member() helper
	function is_member($gids, $usergroup=false)
	{
		global $PL;
		$PL or require_once PLUGINLIBRARY;

		if($usergroup !== false)
		{
			$usergroup = array('usergroup' => (int)$usergroup);
		}

		return (bool)$PL->is_member($gids, $usergroup);
	}

	// Verify if current user can moderate a spesific forum
	function can_moderate($fid)
	{
		if(!is_moderator($fid))
		{
			return false;
		}

		global $settings;

		if(($settings['portal_announcementsfid'] != -1 && !$this->is_member($settings['portal_announcementsfid'], $fid)) && !$settings['portal_announcementsfid'])
		{
			return false;
		}

		if($this->is_member($settings['ougc_showinportal_forums'], $fid))
		{
			return false;
		}

		if(($settings['ougc_showinportal_groups'] != -1 && !$this->is_member($settings['ougc_showinportal_groups'])) || !$settings['ougc_showinportal_groups'])
		{
			return false;
		}

		return true;
	}

	// Update one or more threads
	function thread_update($sip, $tids)
	{
		if(!is_array($tids))
		{
			$tids = array($tids);
		}

		global $db, $lang;
		$this->lang_load();

		$sip = (int)(bool)$sip;
		$where = implode('\',\'', array_filter(array_map('intval', $tids)));
		$db->update_query('threads', array('showinportal' => $sip), 'tid IN (\''.$where.'\')');

		$lang_var = 'ougc_showinportal_pm_subject'.($sip ? '' : '_removed');
		$lang_var_message = 'ougc_showinportal_pm_message'.($sip ? '' : '_removed');

		$this->send_pm(array(
			'subject'	=> $lang->{$lang_var},
			'message'	=> $lang->{$lang_var_message},
			'touid'		=> $uid
		), -1, true, $tids);

		$this->my_alerts($sip, $tids);

		return true;
	}

	// Send a Private Message to a user  (Copied from MyBB 1.7)
	function send_pm($pm, $fromid=0, $admin_override=false, $tids)
	{
		global $mybb;

		if(!$mybb->settings['enablepms'])
		{
			return false;
		}

		if(!is_array($pm))
		{
			return false;
		}

		if(!$pm['subject'] ||!$pm['message'] || (!$pm['receivepms'] && !$admin_override))
		{
			return false;
		}

		global $lang, $db, $session;
		$lang->load('messages');

		require_once MYBB_ROOT."inc/datahandlers/pm.php";

		$pmhandler = new PMDataHandler();

		$pm['touid'] = array();

		$query = $db->simple_select('threads', 'uid', 'uid!=\'0\' AND uid!=\''.(int)$mybb->user['uid'].'\' AND tid IN (\''.implode('\',\'', array_filter(array_map('intval', $tids))).'\')');
		while($uid = (int)$db->fetch_field($query, 'uid'))
		{
			$pm['touid'][$uid] = $uid;
		}

		if(!$pm['touid'])
		{
			return;
		}

		// Build our final PM array
		$pm = array(
			'subject'		=> $pm['subject'],
			'message'		=> $lang->sprintf($pm['message'], $mybb->settings['bbname']),
			'icon'			=> -1,
			'fromid'		=> ($fromid == 0 ? (int)$mybb->user['uid'] : ($fromid < 0 ? 0 : $fromid)),
			'toid'			=> $pm['touid'],
			'bccid'			=> array(),
			'do'			=> '',
			'pmid'			=> '',
			'saveasdraft'	=> 0,
			'options'	=> array(
				'signature'			=> 0,
				'disablesmilies'	=> 0,
				'savecopy'			=> 0,
				'readreceipt'		=> 0
			)
		);

		if(isset($mybb->session))
		{
			$pm['ipaddress'] = $mybb->session->packedip;
		}

		// Admin override
		$pmhandler->admin_override = (int)$admin_override;

		$pmhandler->set_data($pm);

		if($pmhandler->validate_pm())
		{
			$pmhandler->insert_pm();
			return true;
		}

		return false;
	}

	// MyAlerts support
	function my_alerts($sip, $tids)
	{
		global $mybb;

		if(!$mybb->settings['ougc_showinportal_myalerts'])
		{
			return;
		}

		$plugins = (array)$mybb->cache->read('euantor_plugins');

		if(empty($plugins['myalerts']))
		{
			return;
		}

		$info = ougc_showinportal_info();

		if(str_replace('.', '', $plugins['myalerts']['version']) < $info['myalerts'])
		{
			return;
		}

		global $Alerts;

		if(!(!empty($Alerts) && $Alerts instanceof Alerts))
		{
			return;
		}

		global $db;

		// Get list of users
		$query = $db->simple_select('threads', 'uid, tid', 'uid!=\'0\' AND uid!=\''.(int)$mybb->user['uid'].'\' AND tid IN (\''.implode('\',\'', array_filter(array_map('intval', $tids))).'\')');
		while($thread = $db->fetch_array($query))
		{
			$Alerts->addAlert($thread['uid'], 'ougc_showinportal', $thread['tid'], $mybb->user['uid'], array($sip));
		}
	}
}
$GLOBALS['showinportal'] = new OUGC_ShowInPortal;