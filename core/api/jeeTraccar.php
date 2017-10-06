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

// Réception d'une action événement
if (init('action') != null && init('action') === 'event') {
	// Récupération du flux JSON
	$traccarEvent = json_decode(file_get_contents('php://input'));
	
	// Définition des variables
	$traccarUniqueId = $traccarEvent->device->uniqueId;
	$traccarEventType = $traccarEvent->event->type;
	
	// Récupération de l'équipement Traccar
	$traccar = traccar::getTraccarByUniqueId($traccarUniqueId);
	
	log::add('traccar', 'info', 'Reception d\'un événement '.$traccarEventType.' - tracker '.$traccarUniqueId.' - '.$traccar->getName());
	log::add('traccar', 'debug', 'Trame JSON : '.file_get_contents('php://input'));
	
	// Appel de la fonction d'événement Traccar
	traccar::traccarEvent($traccar, $traccarEvent);
}
// Réception d'une position
else {
	// Récupération de l'équipement Traccar
	$traccar = traccar::getTraccarByUniqueId(init('id'));
	
	log::add('traccar', 'info', 'Reception d\'une position - tracker '.init('id').' - '.$traccar->getName());
	
	// Appel de la fonction de position Traccar
	traccar::traccarPosition($traccar, init('latitude'), init('longitude'));
}

return true;
?>
