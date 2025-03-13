<?php

/***************************************************************************
 *
 *    OUGC Show in Portal plugin (/inc/plugins/ougc_showinportal.php)
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

use function ougc\ShowInPortal\Admin\pluginActivate;
use function ougc\ShowInPortal\Admin\pluginInfo;
use function ougc\ShowInPortal\Admin\pluginIsInstalled;
use function ougc\ShowInPortal\Admin\pluginUninstall;
use function ougc\ShowInPortal\Core\addHooks;
use function ougc\ShowInPortal\Core\loadLanguage;

if (!defined('IN_MYBB')) {
    die('This file cannot be accessed directly.');
}

const OUGC_SHOWINPORTAL_ROOT = MYBB_ROOT . 'inc/plugins/ougc/ShowInPortal';

// Plugin Settings
define('ougc\ShowInPortal\Core\SETTINGS', [
    //'allowedGroups' => '-1'
]);

// PLUGINLIBRARY
if (!defined('PLUGINLIBRARY')) {
    define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');
}

require_once OUGC_SHOWINPORTAL_ROOT . '/Core.php';

if (defined('IN_ADMINCP')) {
    require_once OUGC_SHOWINPORTAL_ROOT . '/Admin.php';
    require_once OUGC_SHOWINPORTAL_ROOT . '/Hooks/Admin.php';

    addHooks('ougc\ShowInPortal\Hooks\Admin');
} else {
    require_once OUGC_SHOWINPORTAL_ROOT . '/Hooks/Forum.php';

    addHooks('ougc\ShowInPortal\Hooks\Forum');
}

require_once OUGC_SHOWINPORTAL_ROOT . '/Core.php';

// Plugin API
function ougc_showinportal_info(): array
{
    return pluginInfo();
}

function ougc_showinportal_activate(): bool
{
    return pluginActivate();
}

// _is_installed() routine
function ougc_showinportal_is_installed(): bool
{
    return pluginIsInstalled();
}

// _uninstall() routine
function ougc_showinportal_uninstall(): bool
{
    return pluginUninstall();
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com )
if (!function_exists('control_object')) {
    function control_object(&$obj, $code)
    {
        static $cnt = 0;
        $newname = '_objcont_' . (++$cnt);
        $objserial = serialize($obj);
        $classname = get_class($obj);
        $checkstr = 'O:' . strlen($classname) . ':"' . $classname . '":';
        $checkstr_len = strlen($checkstr);
        if (substr($objserial, 0, $checkstr_len) == $checkstr) {
            $vars = array();
            // grab resources/object etc, stripping scope info from keys
            foreach ((array)$obj as $k => $v) {
                if ($p = strrpos($k, "\0")) {
                    $k = substr($k, $p + 1);
                }
                $vars[$k] = $v;
            }
            if (!empty($vars)) {
                $code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
            }
            eval('class ' . $newname . ' extends ' . $classname . ' {' . $code . '}');
            $obj = unserialize('O:' . strlen($newname) . ':"' . $newname . '":' . substr($objserial, $checkstr_len));
            if (!empty($vars)) {
                $obj->___setvars($vars);
            }
        }
        // else not a valid object or PHP serialize has changed
    }
}

if (!function_exists('control_db')) {
    // explicit workaround for PDO, as trying to serialize it causes a fatal error (even though PHP doesn't complain over serializing other resources)
    if ($GLOBALS['db'] instanceof AbstractPdoDbDriver) {
        $GLOBALS['AbstractPdoDbDriver_lastResult_prop'] = new ReflectionProperty('AbstractPdoDbDriver', 'lastResult');
        $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->setAccessible(true);
        function control_db($code)
        {
            global $db;
            $linkvars = array(
                'read_link' => $db->read_link,
                'write_link' => $db->write_link,
                'current_link' => $db->current_link,
            );
            unset($db->read_link, $db->write_link, $db->current_link);
            $lastResult = $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->getValue($db);
            $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->setValue($db, null); // don't let this block serialization
            control_object($db, $code);
            foreach ($linkvars as $k => $v) {
                $db->$k = $v;
            }
            $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->setValue($db, $lastResult);
        }
    } elseif ($GLOBALS['db'] instanceof DB_SQLite) {
        function control_db($code)
        {
            global $db;
            $oldLink = $db->db;
            unset($db->db);
            control_object($db, $code);
            $db->db = $oldLink;
        }
    } else {
        function control_db($code)
        {
            control_object($GLOBALS['db'], $code);
        }
    }
}

if (class_exists('MybbStuff_MyAlerts_Formatter_AbstractFormatter')) {
    class OUGC_ShowInPortal_MyAlerts_Formatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        public function init()
        {
            loadLanguage();
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
            global $parser, $mybb;

            $Details = $alert->toArray();
            $ExtraDetails = $alert->getExtraDetails();

            $FromUser = $alert->getFromUser();

            $lang_var = 'ougc_showinportal_myalerts_display';
            if ($ExtraDetails['type'] == 'remove') {
                $lang_var = 'ougc_showinportal_myalerts_removed';
            }

            $subject = '';
            if ($thread = get_thread($Details['object_id'])) {
                require_once MYBB_ROOT . 'inc/class_parser.php';
                ($parser instanceof PostDataHandler) || $parser = new postParser();

                $thread['threadprefix'] = $thread['displayprefix'] = '';
                if ($thread['prefix']) {
                    $threadprefix = build_prefixes($thread['prefix']);

                    if (!empty($threadprefix['prefix'])) {
                        $thread['threadprefix'] = htmlspecialchars_uni($threadprefix['prefix']) . '&nbsp;';
                        $thread['displayprefix'] = $threadprefix['displaystyle'] . '&nbsp;';
                    }
                }

                $subject = $parser->parse_badwords($thread['subject']);
                $subject = $thread['displayprefix'] . htmlspecialchars_uni($thread['subject']);
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

            return $settings['bburl'] . '/' . get_thread_link($Details['object_id']);
        }
    }
}