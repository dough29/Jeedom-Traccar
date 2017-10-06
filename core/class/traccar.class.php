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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class traccar extends eqLogic {
	public static function event() {
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
	}
	
	// Actions sur réception d'une position
	function traccarPosition($traccar, $latitude, $longitude) {
		// Récupèration de l'identifiant de l'équipement Geoloc associé
		$geolocId = $traccar->getConfiguration('geoloc');
		
		// Vérification de l'identifiant de l'équipement Geoloc associé
		if (null == $geolocId) {
			log::add('traccar', 'error', 'Cet équipement n\'est pas lié à un objet Geoloc - tracker '.$traccar->getLogicalId().' - '.$traccar->getName());
			throw new Exception(__('Traccar - cet équipement n\'est pas lié à un objet Geoloc : ', __FILE__) . $traccar->getLogicalId().' - '.$traccar->getName());
		}
		
		// Récupèration de la commande Geoloc
		$geoloc = geolocCmd::byId($geolocId);
		
		// Si on n'a pas récupéré de commande Geoloc
		if (!is_object($geoloc)) {
			log::add('traccar', 'debug', 'Impossible de récupérer l\'objet geolocCmd, tentative de récupération de l\'objet geotrav');
			
			// Récupération de l'objet geotrav
			$geoloc = geotrav::byId($geolocId);
			if (!is_object($geoloc)) {
				log::add('traccar', 'error', 'Impossible de récupérer l\'objet geolocCmd ou geotrav !');
			}
			else {
				// Envoi de l'événement à la l'objet geotrav
				$geoloc->updateGeocodingReverse($latitude.",".$longitude);
			}
		}
		else {
			// Envoi de l'événement à la commande geoloc
			$geoloc->event($latitude.",".$longitude);
			// Rafraichissement du widget
			$geoloc->getEqLogic()->refreshWidget();
		}
	}
	
	// Actions sur réception d'un événement
	function traccarEvent($traccar, $traccarEvent) {
		switch ($traccarEvent->event->type) {
			case 'geofenceEnter':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), $traccarEvent->geofence->name);
				$traccarCmd->event(true);
				break;
			case 'geofenceExit':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), $traccarEvent->geofence->name);
				$traccarCmd->event(false);
				break;
			case 'deviceOnline':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Online');
				$traccarCmd->event(true);
				break;
			case 'deviceOffline':
			case 'deviceUnknown':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Online');
				$traccarCmd->event(false);
				
				// Le tracker offline n'est plus en mouvement
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Moving', false);
				if (is_object($traccarCmd)) {
					$traccarCmd->event(false);
				}
				break;
			case 'deviceMoving':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Moving');
				$traccarCmd->event(true);
				break;
			case 'deviceStopped':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Moving');
				$traccarCmd->event(false);
				break;
			default:
				log::add('traccar', 'info', 'L\'événement '.$traccarEvent->event->type.' n\'est pas implémenté');
		}
	}
	
	function getTraccarByUniqueId($uniqueId) {
		$traccar = traccar::byLogicalId($uniqueId, 'traccar');
		
		if (!is_object($traccar) && null != $uniqueId) {
			log::add('traccar', 'error', 'Tracker inconnu - tracker '.$uniqueId.' -> création automatique');
			
			log::add('traccar', 'debug', 'Création de l\'équipement - tracker '.$uniqueId);
			$traccar = new eqLogic();
			$traccar->setEqType_name('traccar');
			$traccar->setIsEnable(0);
			$traccar->setIsVisible(0);
			$traccar->setLogicalId(init('id'));
			$traccar->setName('Tracker '.$uniqueId);
			$traccar->save();
			
			log::add('traccar', 'debug', 'Tracker Id = '.$uniqueId.' - '.$traccar->getName().' créé');
		}
		
		// Vérification de l'équipement de type Traccar
		if ($traccar->getEqType_name() != 'traccar') {
			log::add('traccar', 'error', 'Cet équipement n\'est pas de type traccar - tracker '.$uniqueId.' - '.$traccar->getName());
			throw new Exception(__('Traccar - cet équipement n\'est pas de type traccar : ', __FILE__) . $uniqueId.' - '.$traccar->getName());
		}
		// Vérification de l'équipement actif
		if ($traccar->getIsEnable() != 1) {
			log::add('traccar', 'error', 'Cet équipement n\'est pas activé - tracker '.$uniqueId.' - '.$traccar->getName());
			throw new Exception(__('Traccar - cet équipement n\'est pas activé : ', __FILE__) . $uniqueId.' - '.$traccar->getName());
		}
		
		return $traccar;
	}
	
	// Récupère la commande TraccarCmd et demande sa création si elle n'existe pas
	function getTraccarCmd($traccarId, $traccarCmdName, $forceCreation = true) {
		$traccarCmd = traccarCmd::byEqLogicIdCmdName($traccarId, $traccarCmdName);
		if (!is_object($traccarCmd) && $forceCreation) {
			log::add('traccar', 'debug', 'Le nom de commande '.$traccarCmdName.' n\'existe pas pour l\'équipement '.$traccarId);
			$traccarCmd = traccar::createTraccarCmd($traccarId, $traccarCmdName);
		}
		return $traccarCmd;
	}
	
	// Crée une commande TraccarCmd
	function createTraccarCmd($traccarId, $traccarCmdName) {
		$traccarCmd = new traccarCmd();
		$traccarCmd->setName($traccarCmdName);
		$traccarCmd->setEqLogic_id($traccarId);
		$traccarCmd->setEqType('traccar');
		$traccarCmd->setType('info');
		$traccarCmd->setSubType('binary');
		$traccarCmd->save();
		
		return $traccarCmd;
	}
}

class traccarCmd extends cmd {
}
?>
