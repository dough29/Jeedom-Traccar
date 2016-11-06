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
		// Réception d'un événement
		if (init('action') != null) {
			log::add('traccar', 'debug', 'Reception d\'un événement');
			
			$traccarEvent = json_decode(file_get_contents('php://input'));
			
			log::add('traccar', 'debug', 'Trame JSON : '.file_get_contents('php://input'));
			
			$traccarUniqueId = $traccarEvent->device->uniqueId;
			$traccarEventType = $traccarEvent->event->type;
			
			$traccar = traccar::byLogicalId($traccarUniqueId, 'traccar');
			
			if ($traccar->getIsEnable() != 1) {
				log::add('traccar', 'debug', 'Cet équipement n\'est pas activé - tracker Id = '.$traccarUniqueId);
				throw new Exception(__('Traccar - cet équipement n\'est pas activé : ', __FILE__) . $traccarUniqueId);
			}
			
			switch ($traccarEventType) {
				//byTypeEqLogicNameCmdName
				case 'geofenceEnter':
					$traccarCmd = traccarCmd::byEqLogicIdCmdName($traccar->getId(), $traccarEvent->geofence->name);
					if (!is_object($traccarCmd)) {
						log::add('traccar', 'debug', 'Le nom de commande '.$traccarEvent->geofence->name.' n\'existe pas pour l\'équipement '.$traccarUniqueId);
						$traccarCmd = traccar::createTraccarCmd($traccar->getId(), $traccarEvent->geofence->name);
					}
					$traccarCmd->event(true);
					break;
				case 'geofenceExit':
					$traccarCmd = traccarCmd::byEqLogicIdCmdName($traccar->getId(), $traccarEvent->geofence->name);
					if (!is_object($traccarCmd)) {
						log::add('traccar', 'debug', 'Le nom de commande '.$traccarEvent->geofence->name.' n\'existe pas pour l\'équipement '.$traccarUniqueId);
						$traccarCmd = traccar::createTraccarCmd($traccar->getId(), $traccarEvent->geofence->name);
					}
					$traccarCmd->event(false);
					break;
				default:
					log::add('traccar', 'debug', 'L\'événement '.$traccarEventType.' n\'est pas implémenté');
			}
		}
		// Réception d'une position
		else {
			log::add('traccar', 'debug', 'Reception d\'une position tracker Id = '.init('id'));
			$traccar = traccar::byLogicalId(init('id'), 'traccar');
			
			if (!is_object($traccar) && null != init('id')) {
				log::add('traccar', 'debug', 'Tracker inconnu - tracker Id = '.init('id'));
				
				log::add('traccar', 'debug', 'Création de l\'équipement - tracker Id = '.init('id'));
				$traccar = new eqLogic();
				$traccar->setEqType_name('traccar');
				$traccar->setIsEnable(0);
				$traccar->setIsVisible(0);
				$traccar->setLogicalId(init('id'));
				$traccar->setName('Tracker '.init('id'));
				$traccar->save();
				
				log::add('traccar', 'debug', 'Tracker Id = '.init('id').' créé');
			}
			
			if ($traccar->getEqType_name() != 'traccar') {
				log::add('traccar', 'debug', 'Cet équipement n\'est pas de type traccar - tracker Id = '.init('id'));
				throw new Exception(__('Traccar - cet équipement n\'est pas de type traccar : ', __FILE__) . init('id'));
			}
			if ($traccar->getIsEnable() != 1) {
				log::add('traccar', 'debug', 'Cet équipement n\'est pas activé - tracker Id = '.init('id'));
				throw new Exception(__('Traccar - cet équipement n\'est pas activé : ', __FILE__) . init('id'));
			}
			
			$geolocId = $traccar->getConfiguration('geoloc');
			
			if (null == $geolocId) {
				log::add('traccar', 'debug', 'Cet équipement n\'est pas lié à un objet Geoloc - tracker Id = '.init('id'));
				throw new Exception(__('Traccar - cet équipement n\'est pas lié à un objet Geoloc : ', __FILE__) . init('id'));
			}
			
			$geoloc = geolocCmd::byId($geolocId);
			
			$geoloc->event(init('latitude').",".init('longitude'));
			$geoloc->getEqLogic()->refreshWidget();
		}
	}
	
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
	public function execute($_options = null) {
	}
}
?>
