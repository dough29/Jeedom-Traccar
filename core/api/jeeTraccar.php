<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'traccar')) {
	echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (traccar)', __FILE__);
	die();
}

if ('mqtt' === config::byKey('notif_mode', 'traccar', 'legacy')) {
	log::add('traccar', 'error', 'Reception d\'une notification http en mode MQTT. Vous devez configurer le plugin en mode "legacy"');
} else {
	traccar::event();
}
return true;
?>
