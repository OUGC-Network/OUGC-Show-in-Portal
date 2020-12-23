<?php

/***************************************************************************
 *
 *	OUGC Show in Portal plugin (/inc/languages/espanol/ougc_showinportal.lang.php)
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

// Newthread/Newreply
$l['ougc_showinportal_input_newthread'] = '<strong>Mostrar en portal:</strong> Mostrar este tema en el portal.';
$l['ougc_showinportal_input_quickreply'] = '<strong>Mostrar en portal</strong>';

// MyCode lang
$l['ougc_showinportal_readmore'] = '

Read More: {1}/{2}';
$l['ougc_showinportal_readmore_html'] = '

<a href=\"{1}/{2}\" title=\"Leer tema completo\">Leer más...</a>';
$l['ougc_showinportal_readmore_mycode'] = '

[url={1}/{2}]Leer más...[/url]';

// Send PM
$l['ougc_showinportal_pm_subject'] = 'Tu tema ha sido añadido al portal.';
$l['ougc_showinportal_pm_message'] = 'Hola! Este es un mensaje privado automático de notificación, ya que uno de tus temas ha sido añadido al portal de este sitio por un moderador.

Saludos, {1}.';
$l['ougc_showinportal_pm_subject_removed'] = 'Tu tema ha sido removido del portal.';
$l['ougc_showinportal_pm_message_removed'] = 'Hola! Este es un mensaje privado automático de notificación, ya que uno de tus temas ha sido removido del portal de este sitio por un moderador.

Saludos, {1}.';

// MyAlerts
$l['ougc_showinportal_myalerts_display'] = '{1}, tu tema "{3}" ha sido añadido al portal por {2}.';
$l['ougc_showinportal_myalerts_removed'] = '{1}, tu tema "{3}" ha sido removido del portal por {2}.';
$l['myalerts_setting_ougc_showinportal'] = 'Recibir alerta cuando mis temas son añadidos o removidos del portal.';