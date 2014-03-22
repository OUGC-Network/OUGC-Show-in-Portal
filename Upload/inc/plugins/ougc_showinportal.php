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
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run/Add Hooks
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_start', create_function('', 'global $showinportal;$showinportal->lang_load();'));
	$plugins->add_hook('admin_style_templates_set', create_function('', 'global $showinportal;$showinportal->lang_load();'));
	$plugins->add_hook('admin_config_settings_change', 'ougc_showinportal_settings_change');
	$plugins->add_hook('admin_formcontainer_end', 'ougc_showinportal_modtools');
	$plugins->add_hook('admin_config_mod_tools_edit_thread_tool_commit', 'ougc_showinportal_modtools_commit');
	$plugins->add_hook('admin_config_mod_tools_add_thread_tool_commit', 'ougc_showinportal_modtools_commit');
}
else
{
	global $settings;

	// All right, so what if fid = -1? Lest make that equal to all forums
	if($settings['portal_announcementsfid'] == '-1')
	{
		global $forum_cache;
		$forum_cache or cache_forums();

		$fids = array();
		foreach($forum_cache as $forum)
		{
			if($forum['type'] == 'f' && $forum['active'] == 1 && $forum['open'] == 1)
			{
				$fids[(int)$forum['fid']] = (int)$forum['fid'];
			}
		}
		$settings['portal_announcementsfid'] = implode(',', array_unique($fids));
	}

	$plugins->add_hook('moderation_start', 'ougc_showinportal_moderation');
	$plugins->add_hook('newthread_end', 'ougc_showinportal_newthread_end');
	$plugins->add_hook('datahandler_post_insert_thread', 'ougc_showinportal_insert_thread');
	$plugins->add_hook('showthread_end', 'ougc_showinportal_showthread_end');
	$plugins->add_hook('datahandler_post_insert_post', 'ougc_showinportal_post_insert_post');
	$plugins->add_hook('newreply_end', 'ougc_showinportal_newthread_end');
	$plugins->add_hook('forumdisplay_end', 'ougc_showinportal_forumdisplay_end');
	$plugins->add_hook('postbit', 'ougc_showinportal_postbit');
	$plugins->add_hook('portal_start', 'ougc_showinportal_portal');

	// My Alerts
	$plugins->add_hook('myalerts_load_lang', create_function('', 'global $showinportal;$showinportal->lang_load();'));
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

		$templatelist .= 'ougcshowinportal_input,ougcshowinportal_inlinemod';
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
		'version'		=> '1.2',
		'versioncode'	=> 1200,
		'compatibility'	=> '16*',
		'guid'			=> '716bc5bbc9f8485f2ccc954332fe03a7',
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
		   'title'			=> $lang->settings_ougc_showinportal_groups,
		   'description'	=> $lang->settings_ougc_showinportal_groups_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'3,4,6',
		),
		'forums'	=> array(
		   'title'			=> $lang->settings_ougc_showinportal_forums,
		   'description'	=> $lang->settings_ougc_showinportal_forums_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'',
		),
		'tag'		=> array(
		   'title'			=> $lang->settings_ougc_showinportal_tag,
		   'description'	=> $lang->settings_ougc_showinportal_tag_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'[!--more--]',
		),
		'myalerts'	=> array(
		   'title'			=> $lang->settings_ougc_myalerts_tag,
		   'description'	=> $lang->settings_ougc_myalerts_tag_desc,
		   'optionscode'	=> 'yesno',
			'value'			=>	0,
		)
	));

	// Add template group
	$PL->templates('ougcshowinportal', '<lang:setting_group_ougc_showinportal>', array(
		'input'		=> '<br /><label><input type="checkbox" class="checkbox" name="{$name}" value="1"{$checked} />&nbsp;{$message}</label>',
		'inlinemod'	=> '<option value="{$value}">{$message}</option>'
	));

	// Modify templates
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('stick_thread}</label>').'#', 'stick_thread}</label><!--OUGC_SHOWINPORTAL-->');
	find_replace_templatesets('showthread_quickreply', '#'.preg_quote('{$closeoption}').'#', '{$closeoption}<!--OUGC_SHOWINPORTAL-->');
	find_replace_templatesets('forumdisplay_inlinemoderation', '#'.preg_quote('unapprove_threads}</option>').'#', 'unapprove_threads}</option><!--OUGC_SHOWINPORTAL-->');
	find_replace_templatesets('showthread_moderationoptions', '#'.preg_quote('{$approveunapprovethread}').'#', '{$approveunapprovethread}<!--OUGC_SHOWINPORTAL-->');

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

// Language support for settings
function ougc_showinportal_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', 'gid=\''.(int)$mybb->input['gid'].'\'');
	$groupname = $db->fetch_field($query, 'name');
	if($groupname == 'ougc_showinportal')
	{
		global $plugins, $showinportal;
		$showinportal->lang_load();

		if($mybb->request_method == 'post')
		{
			global $settings;

			$gids = '';
			if(isset($mybb->input['ougc_showinportal_groups']) && is_array($mybb->input['ougc_showinportal_groups']))
			{
				$gids = implode(',', (array)array_filter(array_map('intval', $mybb->input['ougc_showinportal_groups'])));
			}

			$mybb->input['upsetting']['ougc_showinportal_groups'] = $gids;

			$fids = '';
			if(isset($mybb->input['ougc_defaultpoststyle_forums']) && is_array($mybb->input['ougc_defaultpoststyle_forums']))
			{
				$fids = implode(',', (array)array_filter(array_map('intval', $mybb->input['ougc_defaultpoststyle_forums'])));
			}

			$mybb->input['upsetting']['ougc_defaultpoststyle_forums'] = $fids;

			return;
		}

		$plugins->add_hook('admin_formcontainer_output_row', 'ougc_showinportal_formcontainer_output_row');
	}
}

// Friendly settings
function ougc_showinportal_formcontainer_output_row(&$args)
{
	if($args['row_options']['id'] == 'row_setting_ougc_showinportal_groups')
	{
		global $form, $settings;

		$args['content'] = $form->generate_group_select('ougc_showinportal_groups[]', explode(',', $settings['ougc_showinportal_groups']), array('multiple' => true, 'size' => 5));
	}
	if($args['row_options']['id'] == 'row_setting_ougc_showinportal_forums')
	{
		global $form, $settings;

		$args['content'] = $form->generate_forum_select('ougc_showinportal_forums[]', explode(',', $settings['ougc_showinportal_forums']), array('multiple' => true, 'size' => 5));
	}
}

// Moderator Tools
function ougc_showinportal_modtools()
{
	global $mybb, $run_module, $form_container, $lang;

	if(!($run_module == 'config' && !empty($form_container->_title) && !empty($lang->thread_moderation) && $form_container->_title == $lang->thread_moderation && $mybb->input['action'] != 'add_post_tool' && $mybb->input['action'] != 'edit_post_tool'))
	{
		return;
	}

	global $form, $showinportal;
	$showinportal->lang_load();

	if($mybb->input['action'] != 'add_thread_tool')
	{
		global $thread_options;

		$mybb->input['showinportal'] = (int)$thread_options['showinportal'];
	}

	$val = (int)$mybb->input['showinportal'];
	$val = ($val > 3 || $val < 0 ? 0 : $val);

	$form_container->output_row($lang->ougc_showinportal_modtool.' <em>*</em>', '', $form->generate_select_box('showinportal', array(
		0	=> $lang->no_change,
		1	=> $lang->ougc_showinportal_modtool_show,
		2	=> $lang->ougc_showinportal_modtool_remove,
		3	=> $lang->toggle
	), $val, array('id' => 'showinportal')), 'showinportal');
}

// Save moderator tools input
function ougc_showinportal_modtools_commit()
{
	global $mybb;

	if($mybb->request_method != 'post')
	{
		return;
	}

	global $db, $tid, $thread_options;

	$tid = ($mybb->input['action'] == 'add_thread_tool' ? $tid : $mybb->input['tid']);

	$val = (int)$mybb->input['showinportal'];
	$thread_options['showinportal'] = ($val > 3 || $val < 0 ? 0 : $val);

	$thread_options = $db->escape_string(serialize($thread_options));

	$db->update_query('modtools', array('threadoptions' => $thread_options), 'tid=\''.(int)$tid.'\'');
}

// Moderation magic
function ougc_showinportal_moderation()
{
	global $mybb;

	global $mybb;

	// Custom moderator tools process
	if(!in_array($mybb->input['action'], array('showinportal', 'multishowinportal', 'multiunshowinportal')))
	{
		if(in_array($mybb->input['action'], array('reports', 'allreports', 'getip', 'cancel_delayedmoderation', 'delayedmoderation', 'do_delayedmoderation', 'openclosethread', 'stick', 'removeredirects', 'deletethread', 'do_deletethread', 'deletepoll', 'do_deletepoll', 'approvethread', 'unapprovethread', 'deleteposts', 'do_deleteposts', 'mergeposts', 'do_mergeposts', 'move', 'do_move', 'threadnotes', 'do_threadnotes', 'merge', 'do_merge', 'split', 'do_split', 'removesubscriptions', 'multideletethreads', 'do_multideletethreads', 'multiopenthreads', 'multiclosethreads', 'multiapprovethreads', 'multiunapprovethreads', 'multistickthreads', 'multiunstickthreads', 'multimovethreads', 'do_multimovethreads', 'multideleteposts', 'do_multideleteposts', 'multimergeposts', 'do_multimergeposts', 'multisplitposts', 'do_multisplitposts', 'multiapproveposts', 'multiunapproveposts')) || ($tid = (int)$mybb->input['action']) < 1)
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

	// In-line moderation tools
	global $mybb, $showinportal, $lang, $db;
	$showinportal->lang_load();

	$isthread = ($mybb->input['action'] == 'showinportal');
	$fid = (int)$mybb->input['fid'];

	if($isthread)
	{
		$thread = get_thread($mybb->input['tid']);
		$fid = $thread['fid'];
		$thread['tid'] = (int)$thread['tid'];
		if(!$thread['tid'])
		{
			error($lang->error_invalidthread);
		}
	}

	if(!$showinportal->can_moderate($fid))
	{
		error_no_permission();
	}

	// Check forum password 
	check_forum_password($fid);

	// Verify post check
	verify_post_check($mybb->input['my_post_key']);

	// Get threads array
	if($isthread)
	{
		$threads = array($thread['tid']);
	}
	else
	{
		$threads = getids($fid, 'forum');
	}

	// No threads selected, show error
	if(count($threads) < 1)
	{
		error($lang->error_inline_nothreadsselected);
	}

	// Do the magic.. not much really...
	$loglangvar = 'ougc_showinportal_unshowinportal_done';
	$redirectlangvar = 'ougc_showinportal_unshowinportal_redirect';

	if($isthread)
	{
		$url = get_thread_link($thread['tid']);
		$sip = !$thread['showinportal'];
		$threads = array($thread['tid']);
	}
	else
	{
		$url = get_forum_link($fid);
		$sip = ($mybb->input['action'] == 'multishowinportal');
	}

	if($sip)
	{
		$loglangvar = 'ougc_showinportal_showinportal_done';
		$redirectlangvar = 'ougc_showinportal_showinportal_redirect';
	}


	// Update threads
	$showinportal->thread_update($sip, $threads);

	// Log moderation action
	$data = array('fid' => $fid);
	if($isthread)
	{
		$data['tid'] = $thread['tid'];
	}
	else
	{
		$data['tids'] = implode(',', $threads);
	}

	log_moderator_action($data, $lang->{$loglangvar});

	// Clear inline moderation for those threads
	$isthread or clearinline($fid, 'forum');

	// Redirect
	moderation_redirect($url, $lang->{$redirectlangvar});
	exit;
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
		case 2:
			$showinportal->thread_update($sip, $tids);
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

	global $templates, $lang;
	$showinportal->lang_load();

	// Figure out if checked
	$sip = (int)$mybb->input['modoptions']['showinportal'];
	if(THIS_SCRIPT == 'newreply.php' && !$mybb->input['processed'])
	{
		$sip = (int)$thread['closed'];
	}
	
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

	$value = 'showinportal';
	$message = $lang->ougc_showinportal_showinportalthread;
	eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_inlinemod').'";');

	$moderationoptions = str_replace('<!--OUGC_SHOWINPORTAL-->', $ougc_showinportal, $moderationoptions);
}

// Validate reply input
function ougc_showinportal_post_insert_post(&$args)
{
	$thread = get_thread($args->data['tid']);

	if($thread['showinportal'] && !$args->data['modoptions']['showinportal'])
	{
		$showinportal->thread_update(0, $thread['tid']);
	}
	if(!$thread['showinportal'] && $args->data['modoptions']['showinportal'])
	{
		$showinportal->thread_update(1, $thread['tid']);
	}
}

// Inline moderator tool
function ougc_showinportal_forumdisplay_end()
{
	global $fid, $showinportal, $settings;

	if(!$showinportal->can_moderate($fid))
	{
		return;
	}

	global $lang, $templates, $threadslist;
	$showinportal->lang_load();

	$value = 'multishowinportal';
	$message = $lang->ougc_showinportal_showinportal;
	eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_inlinemod').'";');

	$value = 'multiunshowinportal';
	$message = $lang->ougc_showinportal_unshowinportal;
	eval('$ougc_showinportal .= "'.$templates->get('ougcshowinportal_inlinemod').'";');

	$threadslist = str_replace('<!--OUGC_SHOWINPORTAL-->', $ougc_showinportal, $threadslist);
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

// Alter portal behavior
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

		if(!$this->is_member($settings['portal_announcementsfid'], $fid))
		{
			return false;
		}

		if($this->is_member($settings['ougc_showinportal_forums'], $fid))
		{
			return false;
		}

		if(!$this->is_member($settings['ougc_showinportal_groups']))
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

		global $db;

		$sip = (int)(bool)$sip;
		$where = implode('\',\'', array_filter(array_map('intval', $tids)));
		$db->update_query('threads', array('showinportal' => $sip), 'tid IN (\''.$where.'\')');

		$this->my_alerts($sip, $tids);

		return true;
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