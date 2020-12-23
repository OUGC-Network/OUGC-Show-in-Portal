<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/languages/espanol/admin/ougc_showinportal.lang.php)
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
$l['setting_group_ougc_showinportal_desc'] = 'Permite a los moderadores elegir que temas serán mostrados en la página del portal.';

// Settings
$l['setting_ougc_showinportal_groups'] = 'Permitir grupos';
$l['setting_ougc_showinportal_groups_desc'] = 'Elige que grupos pueden usar esta característica.';
$l['setting_ougc_showinportal_forums'] = 'Ignorar foros';
$l['setting_ougc_showinportal_forums_desc'] = 'Elige en que foros no se podrá utilizar esta característica.';
$l['setting_ougc_showinportal_tag'] = 'Etiqueta para cortar';
$l['setting_ougc_showinportal_tag_desc'] = 'Quieres utilizar una etiqueta para cortar mensajes en el portal? Dejar en blanco para deshabilitar. Por defecto: "[!--more--]".';
$l['setting_ougc_showinportal_tag_rss'] = 'Cortar en Sindicación';
$l['setting_ougc_showinportal_tag_desc'] = 'Activa esto para cortar los mensajes en la sindicación (RSS).';
$l['setting_ougc_showinportal_sendpm'] = 'Enviar mensaje privado';
$l['setting_ougc_showinportal_sendpm_desc'] = 'Enviar un mensaje privado a los usuarios cuando uno de sus temas sea añadido o removido del portal.';
$l['setting_ougc_showinportal_myalerts'] = 'Integrar MyAlerts';
$l['setting_ougc_showinportal_myalerts_desc'] = 'Enviar una notificación por MyAlerts a los usuarios cuando uno de sus temas sea añadido o removido del portal.';

// Moderator Tools
$l['ougc_showinportal_modtool'] = 'Mostrar en portal';
$l['ougc_showinportal_modtool_show'] = 'Mostrar';
$l['ougc_showinportal_modtool_remove'] = 'Remover';

// PluginLibrary
$l['ougc_showinportal_pluginlibrary_required'] = 'Este plugin necesita <a href="{1}">PluginLibrary</a> versión {2} o posterior para ser instalado en tu foro.';
$l['ougc_showinportal_pluginlibrary_update'] = 'Este plugin necesita <a href="{1}">PluginLibrary</a> versión {2} o posterior, actualmente tu versión es {3}.';