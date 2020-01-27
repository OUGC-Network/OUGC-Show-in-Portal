<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/plugins/ougc_showinportal.php)
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

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

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
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.22',
		'versioncode'	=> 1822,
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_showinportal',
		'myalerts'		=> '2.0.4',
		'pl'			=> array(
			'version'	=> 13,
			'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
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
		'pm'	=> array(
		   'title'			=> $lang->setting_ougc_showinportal_sendpm,
		   'description'	=> $lang->setting_ougc_showinportal_sendpm_desc,
		   'optionscode'	=> 'yesno',
			'value'			=>	0,
		),
		'myalerts'	=> array(
		   'title'			=> $lang->setting_ougc_showinportal_myalerts,
		   'description'	=> $lang->setting_ougc_showinportal_myalerts_desc,
		   'optionscode'	=> 'yesno',
			'value'			=>	0,
		)
	));

	// Add template group
	$PL->templates('ougcshowinportal', 'OUGC Show in Portal', array(
		'input'		=> '{$br_quickreply}<label><input type="checkbox" class="checkbox" name="{$name}" value="1"{$checked} />&nbsp;{$message}</label>{$br}'
	));

	// Modify templates
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('editpost_postoptions', '#'.preg_quote('{$disablesmilies}').'#', '{$disablesmilies}<!--OUGC_SHOWINPORTAL-->');
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('{$closeoption}').'#', '{$closeoption}<!--OUGC_SHOWINPORTAL-->');
	find_replace_templatesets('showthread_quickreply', '#'.preg_quote('{$closeoption}').'#', '{$closeoption}<!--OUGC_SHOWINPORTAL-->');

	// MyAlerts
	if(class_exists('MybbStuff_MyAlerts_AlertTypeManager'))
	{
		global $db;

		$alertTypeManager or $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);

		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		$alertType = new MybbStuff_MyAlerts_Entity_AlertType();

		$alertType->setCode('ougc_showinportal');
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);
	}

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
	find_replace_templatesets('editpost_postoptions', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
	find_replace_templatesets('newreply_modoptions', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
	find_replace_templatesets('showthread_quickreply', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
	find_replace_templatesets('forumdisplay_inlinemoderation', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);
	find_replace_templatesets('showthread_moderationoptions', '#'.preg_quote('<!--OUGC_SHOWINPORTAL-->').'#', '', 0);

	// MyAlerts
	if(class_exists('MybbStuff_MyAlerts_AlertTypeManager'))
	{
		global $db, $cache;

		$alertTypeManager or $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);

		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		$alertTypeManager->deleteByCode('ougc_showinportal');
	}
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
		function execute_thread_moderation($thread_options=array(), $tids=array())
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

// Validate reply input
function ougc_showinportal_post_insert_post(&$args)
{
	global $mybb, $showinportal, $plugins;

	if(!$showinportal->can_moderate($thread['fid']))
	{
		return;
	}

	$thread = get_thread($args->data['tid']);

	if($plugins->current_hook == 'datahandler_post_update')
	{
		if(THIS_SCRIPT == 'xmlhttp.php')
		{
			return false;
		}

		$options = $mybb->get_input('modoptions', MYBB::INPUT_ARRAY);
	}
	else
	{
		$options = $args->data['modoptions'];
	}

	if($thread['showinportal'] && empty($options['showinportal']))
	{
		$showinportal->thread_update(0, $thread['tid']);
	}
	if(!$thread['showinportal'] && !empty($options['showinportal']))
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

// Magicfull
class OUGC_ShowInPortal
{
	public $status_display = 1;

	public $status_remove = 0;

	public $myalerts = false;

	// Build class
	function __construct()
	{
		global $mybb, $plugins;

		// Run/Add Hooks
		if(defined('IN_ADMINCP'))
		{
			$plugins->add_hook('admin_config_settings_start', array($this, 'lang_load'));
			$plugins->add_hook('admin_style_templates_set', array($this, 'lang_load'));
			$plugins->add_hook('admin_config_settings_change', array($this, 'lang_load'));
			$plugins->add_hook('admin_formcontainer_end', 'ougc_showinportal_modtools');
			$plugins->add_hook('admin_config_mod_tools_add_thread_tool_commit', 'ougc_showinportal_modtools_commit');
			$plugins->add_hook('admin_config_mod_tools_edit_thread_tool_commit', 'ougc_showinportal_modtools_commit');
		}
		else
		{
			$plugins->add_hook('newthread_end', array($this, 'input_display'));
			$plugins->add_hook('showthread_end', array($this, 'input_display'));
			$plugins->add_hook('newreply_end', array($this, 'input_display'));
			$plugins->add_hook('editpost_end', array($this, 'input_display'));
			$plugins->add_hook('moderation_start', 'ougc_showinportal_moderation');
			$plugins->add_hook('datahandler_post_insert_thread', 'ougc_showinportal_insert_thread');
			$plugins->add_hook('datahandler_post_insert_post', 'ougc_showinportal_post_insert_post');
			//datahandler_post_insert_merge
			$plugins->add_hook('datahandler_post_update', 'ougc_showinportal_post_insert_post');
			$plugins->add_hook('postbit', 'ougc_showinportal_postbit');
			$plugins->add_hook('portal_start', 'ougc_showinportal_portal');
			$plugins->add_hook('syndication_start', 'ougc_showinportal_syndication');
			$plugins->add_hook('xmlhttp_update_post', 'ougc_showinportal_xmlhttp');

			// My Alerts
			$plugins->add_hook('global_start', array($this, 'hook_global_start'));

			if(in_array(THIS_SCRIPT, array('forumdisplay.php', 'showthread.php', 'newthread.php', 'newreply.php', 'editpost.php')))
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

		$this->myalerts = $mybb->settings['ougc_showinportal_myalerts'] && $mybb->cache->cache['plugins']['active']['myalerts'];
	}

	// Loads language strings
	function lang_load()
	{
		global $lang;

		isset($lang->setting_group_ougc_showinportal) or $lang->load('ougc_showinportal');
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

		if(!$mybb->settings['enablepms'] || !$mybb->settings['ougc_showinportal_pm'])
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
	function my_alerts($status, $tids)
	{
		global $lang, $mybb, $alertType, $db;
		$this->lang_load(true);

		if(!$this->myalerts)
		{
			return false;
		}

		$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('ougc_showinportal');

		if(!$alertType)
		{
			return false;
		}

		$tids = implode("','", $tids);

		$threads_cache = array();

		$query = $db->simple_select('threads', 'uid, tid', "tid IN ('{$tids}') AND visible='1'"); // visibility checks?
		while($thread = $db->fetch_array($query))
		{
			$threads_cache[(int)$thread['tid']] = (int)$thread['uid'];
		}

		if($alertType == null || !$alertType->getEnabled())
		{
			return false;
		}

		foreach($threads_cache as $tid => $uid)
		{
			// Check if already alerted
			$query = $db->simple_select('alerts', '*', "object_id='{$tid}' AND uid='{$uid}' AND unread=1 AND alert_type_id='{$alertType->getId()}'");

			if($db->fetch_field($query, 'id'))
			{
				return false;
			}

			$alert = new MybbStuff_MyAlerts_Entity_Alert($uid, $alertType, $tid);

			$alert->setExtraDetails(
				array(
					'type'	=> $status ? 'display' : 'remove'
				)
			);

			MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
		}
	}

	// Insert plugin variable at new thread
	function input_display()
	{
		global $plugins, $templates, $lang, $thread, $post, $mybb, $fid;

		$modoptions = &$GLOBALS['modoptions'];
		$lang_var = 'ougc_showinportal_input_newthread';

		$br = '<br />';
		$br_quickreply = '';

		if($plugins->current_hook == 'editpost_end' && $thread['firstpost'] == $mybb->get_input('pid', MYBB::INPUT_INT))
		{
			$modoptions = &$GLOBALS['postoptions'];

			$br = '';
			$br_quickreply = '<br />';
		}

		if($plugins->current_hook == 'showthread_end')
		{
			$modoptions = &$GLOBALS['quickreply'];
			$lang_var = 'ougc_showinportal_input_quickreply';

			$br = '';
			$br_quickreply = '<br />';
		}

		if(!isset($modoptions) || my_strpos($modoptions, '<!--OUGC_SHOWINPORTAL-->') === false)
		{
			return;
		}

		if(!$this->can_moderate($fid))
		{
			return;
		}

		$this->lang_load();

		// Figure out if checked
		$display_status = $this->status_remove; // newthread_end

		$options = $mybb->get_input('modoptions', MYBB::INPUT_ARRAY);

		if($mybb->request_method == 'post')
		{
			$display_status = $options['showinportal'];
		}
		else
		{
			switch($plugins->current_hook)
			{
				case 'showthread_end':
				case 'editpost_end':
				case 'newreply_end':
					$display_status = (int)$thread['showinportal'];
					break;
			}
		}

		$checked = '';

		if(!empty($display_status))
		{
			$checked = ' checked="checked"';
		}

		// Show the option
		$name = 'modoptions[showinportal]';
		$message = $lang->{$lang_var};
		eval('$ougc_showinportal = "'.$templates->get('ougcshowinportal_input').'";');

		$modoptions = str_replace('<!--OUGC_SHOWINPORTAL-->', $ougc_showinportal, $modoptions);
	}

	// Hook: global_start
	function hook_global_start()
	{
		if(class_exists('MybbStuff_MyAlerts_AlertFormatterManager'))
		{
			global $mybb, $lang, $showinportal;
			$showinportal->lang_load();

			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

			$formatterManager or $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);

			$formatterManager->registerFormatter(new OUGC_ShowInPortal_MyAlerts_Formatter($mybb, $lang, 'ougc_showinportal'));
		}
	}
}

if(class_exists('MybbStuff_MyAlerts_Formatter_AbstractFormatter'))
{
	class OUGC_ShowInPortal_MyAlerts_Formatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
	{
		public function init()
		{
			global $showinportal;
			$showinportal->lang_load();
		}

		/**
		 * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
		 *
		 * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
		 *
		 * @return string The formatted alert string.
		 */
		public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
		{
			global $showinportal, $templates, $parser, $mybb;

			$Details = $alert->toArray();
			$ExtraDetails = $alert->getExtraDetails();

			$FromUser = $alert->getFromUser();

			$lang_var = 'ougc_showinportal_myalerts_display';
			if($ExtraDetails['type'] == 'remove')
			{
				$lang_var = 'ougc_showinportal_myalerts_removed';
			}

			$subject = '';
			if($thread = get_thread($Details['object_id']))
			{
				require_once MYBB_ROOT.'inc/class_parser.php';
				($parser instanceof PostDataHandler) or $parser = new postParser;

				$thread['threadprefix'] = $thread['displayprefix'] = '';
				if($thread['prefix'])
				{
					$threadprefix = build_prefixes($thread['prefix']);

					if(!empty($threadprefix['prefix']))
					{
						$thread['threadprefix'] = htmlspecialchars_uni($threadprefix['prefix']).'&nbsp;';
						$thread['displayprefix'] = $threadprefix['displaystyle'].'&nbsp;';
					}
				}

				$subject = $parser->parse_badwords($thread['subject']);
				$subject = $thread['displayprefix'].htmlspecialchars_uni($thread['subject']);
			}

			$username = format_name($mybb->user['username'], $mybb->user['usergroup'], $mybb->user['displaygroup']);

			return $this->lang->sprintf($this->lang->{$lang_var}, $username, $outputAlert['from_user'], $subject);
		}

		/**
		 * Build a link to an alert's content so that the system can redirect to it.
		 *
		 * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
		 *
		 * @return string The built alert, preferably an absolute link.
		 */
		public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
		{
			global $settings;

			$Details = $alert->toArray();
			$ExtraDetails = $alert->getExtraDetails();

			return $settings['bburl'].'/'.get_thread_link($Details['object_id']);
		}
	}
}

$GLOBALS['showinportal'] = new OUGC_ShowInPortal;

