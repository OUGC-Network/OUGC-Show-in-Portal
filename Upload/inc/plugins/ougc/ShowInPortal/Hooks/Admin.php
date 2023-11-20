<?php

/***************************************************************************
 *
 *    OUGC Show in Portal plugin (/inc/plugins/ougc/ShowInPortal/Hooks/Admin.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow moderators to choose what threads to display in the portal.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\ShowInPortal\Hooks\Admin;

use function ougc\ShowInPortal\Core\loadLanguage;

function admin_config_plugins_deactivate(): bool
{
    global $mybb, $page;

    if (
        $mybb->get_input('action') != 'deactivate' ||
        $mybb->get_input('plugin') != 'ougc_showinportal' ||
        !$mybb->get_input('uninstall', \MyBB::INPUT_INT)
    ) {
        return false;
    }

    if ($mybb->request_method != 'post') {
        $page->output_confirm_action(
            'index.php?module=config-plugins&amp;action=deactivate&amp;uninstall=1&amp;plugin=ougc_showinportal'
        );
    }

    if ($mybb->get_input('no')) {
        \admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function admin_config_settings_start()
{
    loadLanguage();
}

function admin_style_templates_set()
{
    loadLanguage();
}

function admin_config_settings_change()
{
    loadLanguage();
}


// Moderator Tools
function admin_formcontainer_end()
{
    global $mybb, $run_module, $form_container, $lang;

    if (!($run_module == 'config' && !empty($form_container->_title) && !empty($lang->thread_moderation) && $form_container->_title == $lang->thread_moderation && $mybb->get_input(
            'action'
        ) != 'add_post_tool' && $mybb->get_input('action') != 'edit_post_tool')) {
        return;
    }

    global $form;

    loadLanguage();

    if ($mybb->get_input('action') != 'add_thread_tool' && !isset($mybb->input['showinportal'])) {
        global $thread_options;

        $mybb->input['showinportal'] = (int)$thread_options['showinportal'];
    }

    $form_container->output_row(
        $lang->ougc_showinportal_modtool . ' <em>*</em>',
        '',
        $form->generate_select_box('showinportal', [
            0 => $lang->no_change,
            1 => $lang->ougc_showinportal_modtool_show,
            2 => $lang->ougc_showinportal_modtool_remove,
            3 => $lang->toggle
        ], $mybb->get_input('showinportal', \MyBB::INPUT_INT), ['id' => 'showinportal']),
        'showinportal'
    );
}

// Save moderator tools input
function admin_config_mod_tools_add_thread_tool_commit()
{
    global $mybb;

    if ($mybb->request_method == 'post') {
        global $db, $thread_options, $update_tool, $new_tool;

        $showInPortal = $mybb->get_input('showinportal', \MyBB::INPUT_INT);

        $thread_options['showinportal'] = ($showInPortal > 3 || $showInPortal < 0 ? 0 : $showInPortal);

        $var = $mybb->get_input('action') == 'add_thread_tool' ? 'new_tool' : 'update_tool';

        ${$var}['threadoptions'] = $db->escape_string(serialize($thread_options));

        if ($mybb->get_input('action') == 'add_thread_tool') {
            global $tid;

            $db->update_query('modtools', $new_tool, 'tid=\'' . $tid . '\'');
        }
    }
}

function admin_config_mod_tools_edit_thread_tool_commit()
{
    admin_config_mod_tools_add_thread_tool_commit();
}