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
		if (init('action') === 'event') {
			// Récupération du flux JSON (en tableau associatif : traccarEvent() accède aux données en mode tableau)
			$traccarEvent = json_decode(file_get_contents('php://input'), true);

			// Définition des variables
			$traccarUniqueId = $traccarEvent['device']['uniqueId'];
			$traccarEventType = $traccarEvent['event']['type'];

			// Récupération de l'équipement Traccar
			$traccar = traccar::getTraccarByUniqueId($traccarUniqueId);

			log::add('traccar', 'info', 'Réception d\'un événement (legacy) ' . $traccarEventType . ' - tracker ' . $traccarUniqueId.' - ' . $traccar->getName());
			log::add('traccar', 'debug', '  Trame JSON : ' . file_get_contents('php://input'));

			// Appel de la fonction d'événement Traccar
			traccar::traccarEvent($traccar, $traccarEvent);
		}
		// Réception d'une position
		else {
			// Récupération de l'équipement Traccar
			$traccar = traccar::getTraccarByUniqueId(init('id'));

			log::add('traccar', 'info', 'Réception d\'une position (legacy) - tracker ' . init('id') . ' - ' . $traccar->getName());
			log::add('traccar', 'debug', '  > speed --> ' . init('speed'));
			log::add('traccar', 'debug', '  > attributes --> ' . init('attributes'));

			// Appel de la fonction de position Traccar
			traccar::traccarPosition($traccar, init('latitude'), init('longitude'), init('speed'), json_decode(init('attributes')));
		}
	}

	// Actions sur réception d'une position
	/**
	 * @param traccar $traccar: l'objet traccar associé à la position reçue de l'application traccar
	 * @param string $latitude: la latitude reçue dans la notification
	 * @param string $longitude: la longitude reçue dans la notification
	 * @param string $speed: la vitesse reçue dans la notification
	 * @param array $attributes: un tableau contenant des informations supplementaires
	 */
	public static function traccarPosition($traccar, $latitude, $longitude, $speed, $attributes) {
		// Récupération de l'identifiant de l'équipement Geoloc associé
		$geolocId = $traccar->getConfiguration('geoloc');

		// Vérification de l'identifiant de l'équipement Geoloc associé
		if (null == $geolocId) {
			log::add('traccar', 'error', 'Cet équipement n\'est pas lié à un objet Geoloc - tracker '.$traccar->getLogicalId().' - '.$traccar->getName());
			throw new Exception(__('Traccar - cet équipement n\'est pas lié à un objet Geoloc : ', __FILE__) . $traccar->getLogicalId().' - '.$traccar->getName());
		}

		// Récupération de la commande Geoloc
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
				$geoloc->updateGeocodingReverse($latitude . "," . $longitude);
			}
		}
		else {
			// Envoi de l'événement à la commande geoloc
			$geoloc->event($latitude . "," . $longitude);
			// Rafraichissement du widget
			$geoloc->getEqLogic()->refreshWidget();
		}

		$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Vitesse ', 'numeric');
		$traccarCmd->event(round($speed));

		// Récupération des paramètres 'attributes' (cast en tableau : évite un warning si null/objet vide)
		foreach((array) $attributes as $attribute => $value) {
			switch ($attribute) {
				case 'batteryLevel':
					$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'batteryLevel', 'numeric');
					$traccarCmd->event($value);
					break;
				case 'ignition':
					$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'ignition', 'binary');
					$traccarCmd->event($value);
					break;
				case 'alarm':
					$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'alarm', 'string');
					$traccarCmd->event($value);
					break;
			}
		}

		// Réinitialisation des attributs vides
		$traccarCmdAlarm = traccar::getTraccarCmd($traccar->getId(), 'alarm', 'string', false);
		$attributesArray = (array) $attributes;
		if (is_object($traccarCmdAlarm) && !isset($attributesArray['alarm'])) {
			$traccarCmdAlarm->event('');
		}
	}

	// Actions sur réception d'un événement
	/**
	 * @param traccar $traccar: l'objet traccar associé à l'evenement reçu de l'application traccar
	 * @param array $traccarEvent: un tableau contenant les informations associées à l'événement reçu de l'application traccar
	 */
	public static function traccarEvent($traccar, $traccarEvent) {
		switch ($traccarEvent['event']['type']) {
			case 'geofenceEnter':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), $traccarEvent['geofence']['name'], 'binary');
				$traccarCmd->event(true);
				break;
			case 'geofenceExit':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), $traccarEvent['geofence']['name'], 'binary');
				$traccarCmd->event(false);
				break;
			case 'deviceOnline':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Online', 'binary');
				$traccarCmd->event(true);
				break;
			case 'deviceOffline':
			case 'deviceUnknown':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Online', 'binary');
				$traccarCmd->event(false);

				// Le tracker offline n'est plus en mouvement
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Moving', 'binary', false);
				if (is_object($traccarCmd)) {
					$traccarCmd->event(false);
				}
				break;
			case 'deviceMoving':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Moving', 'binary');
				$traccarCmd->event(true);
				break;
			case 'deviceStopped':
				$traccarCmd = traccar::getTraccarCmd($traccar->getId(), 'Moving', 'binary');
				$traccarCmd->event(false);
				break;
			default:
				log::add('traccar', 'info', 'L\'événement '.$traccarEvent['event']['type'] . ' n\'est pas implémenté');
		}
	}

	/**
	 * @param int $uniqueId: un identifier associé à l'objet dans l'application traccar
	 */
	public static function getTraccarByUniqueId($uniqueId) {
		$traccar = traccar::byLogicalId($uniqueId, 'traccar');

		if (!is_object($traccar) && null != $uniqueId) {
			log::add('traccar', 'error', 'Tracker inconnu - tracker ' . $uniqueId . ' -> création automatique');

			log::add('traccar', 'debug', 'Création de l\'équipement - tracker ' . $uniqueId);
			$traccar = new eqLogic();
			$traccar->setEqType_name('traccar');
			$traccar->setIsEnable(0);
			$traccar->setIsVisible(0);
			$traccar->setLogicalId($uniqueId);
			$traccar->setName('Tracker ' . $uniqueId);
			$traccar->save();

			log::add('traccar', 'debug', 'Tracker Id = ' . $uniqueId . ' - ' . $traccar->getName() . ' créé');
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
	/**
	 * @param int $traccarId: identifiant unique de l'objet traccar du plugin
	 * @param string $traccarCmdName: nom de la commande à rechercher
	 * @param string $type: type de la commande si non trouvée, et qu'on doit la créer
	 * @param bool $forceCreation: indique si on doit créer la commande si elle n'existe pas.
	 */
	public static function getTraccarCmd($traccarId, $traccarCmdName, $type, $forceCreation = true) {
		$traccarCmd = traccarCmd::byEqLogicIdCmdName($traccarId, $traccarCmdName);
		if (!is_object($traccarCmd) && $forceCreation) {
			log::add('traccar', 'debug', 'Le nom de commande '.$traccarCmdName.' n\'existe pas pour l\'équipement '.$traccarId.' : création');
			$traccarCmd = traccar::createTraccarCmd($traccarId, $traccarCmdName, $type);
		}
		return $traccarCmd;
	}

	// Crée une commande TraccarCmd
	/**
	 * @param int $traccarId: identifiant unique de l'objet traccar du plugin
	 * @param string $traccarCmdName: nom de la commande à rechercher
	 * @param string $type: type de la commande si non trouvée, et qu'on doit la créer
	 */
	public static function createTraccarCmd($traccarId, $traccarCmdName, $type) {
		$traccarCmd = new traccarCmd();
		$traccarCmd->setName($traccarCmdName);
		$traccarCmd->setEqLogic_id($traccarId);
		$traccarCmd->setEqType('traccar');
		$traccarCmd->setType('info');
		$traccarCmd->setSubType($type);
		$traccarCmd->save();

		return $traccarCmd;
	}

	public static function postConfig_mqtt_topic($_value = null) {
		if (!class_exists('mqtt2')) {
			return;
		}
		if (method_exists('mqtt2', 'removePluginTopicByPlugin')) {
			mqtt2::removePluginTopicByPlugin(__CLASS__);
		}
		if ('mqtt' === config::byKey('notif_mode', 'traccar', 'legacy')) {
			log::add('traccar', 'debug', 'Inscription au plugin mqtt2');
			$root_topic = config::byKey('mqtt_topic', 'traccar', __CLASS__);
			$root_topic = trim($root_topic, '/');
			mqtt2::addPluginTopic(__CLASS__, $root_topic);
		}
	}

	/**
	 * @param string $_datas: les informations du message MQTT reçu, au format json
	 */
	public static function handleMqttMessage($_datas) {

		if ('mqtt' !== config::byKey('notif_mode', 'traccar', 'legacy')) {
			log::add('traccar', 'error', 'Réception d\'une notification http en mode MQTT. Vous devez configurer le plugin en mode "MQTT"');
			return;
		}

		try {
			// If $_datas is already an array, do not decode it again.
			$data = is_string($_datas)
				? json_decode($_datas, true, 512, JSON_THROW_ON_ERROR)
				: $_datas;

			log::add('traccar', 'debug', 'MQTT message reçu: ' . json_encode($data));

			$rootTopic = config::byKey('mqtt_topic', 'traccar', __CLASS__);
			$rootTopic = trim($rootTopic, '/');

			// 1. On vérifie qu'on est bien dans notre base topic.
			if (!isset($data[$rootTopic]) || !is_array($data[$rootTopic])) {
				log::add('traccar', 'debug', 'MQTT message reçu, mais le root topic n\'est pas pour traccar');
				return;
			}

			// 2. On parcours les messages.
			foreach ($data[$rootTopic] as $mqttMessageType => $mqttPayload) {

				$logMsgData = json_encode($mqttPayload);
				if (!is_array($mqttPayload)) {
					log::add('traccar', 'warning', 'Message MQTT invalide (' . $mqttMessageType . '(' . $logMsgData . ')');
					continue;
				}
				$traccarUniqueId = $mqttPayload['device']['uniqueId'];

				// 3. On process les types de message ("events", et/ou "positions")
				switch ($mqttMessageType) {
					case 'events':

						if (!isset($mqttPayload['event']) or !isset($mqttPayload['event']['type'])) {
							log::add('traccar', 'warning', 'Message MQTT invalide (il manque la section event ou le type) : ' . $mqttMessageType . '(' . $logMsgData . ')');
							continue;
						}
						if (!isset($mqttPayload['device']) or !isset($mqttPayload['device']['uniqueId'])) {
							log::add('traccar', 'warning', 'Message MQTT invalide (il manque la section device) : ' . $mqttMessageType . '(' . $logMsgData . ')');
							continue;
						}
						$traccarUniqueId = $mqttPayload['device']['uniqueId'];
						$traccarEventType = $mqttPayload['event']['type'];

						// Récupère l'objet traccar du plugin
						$traccar = traccar::getTraccarByUniqueId($traccarUniqueId);
						log::add('traccar', 'info', 'Réception d\'un événement MQTT ' . $traccarEventType . ' - tracker ' . $traccarUniqueId . ' - ' . $traccar->getName());
						log::add('traccar', 'debug', '  Trame JSON : ' . $logMsgData);

						// Appel de la fonction d'événement Traccar
						traccar::traccarEvent($traccar, $mqttPayload);
						break;

					case 'positions':

						if (!isset($mqttPayload['position'])) {
							log::add('traccar', 'warning', 'Message MQTT invalide (il manque la section position) : ' . $mqttMessageType . '(' . $logMsgData . ')');
							continue;
						}
						if (!isset($mqttPayload['device']) or !isset($mqttPayload['device']['uniqueId'])) {
							log::add('traccar', 'warning', 'Message MQTT invalide (il manque la section device) : ' . $mqttMessageType . '(' . $logMsgData . ')');
							continue;
						}
						if (!isset($mqttPayload['position']['latitude']) or !isset($mqttPayload['position']['longitude']) or !isset($mqttPayload['position']['speed']) or !isset($mqttPayload['position']['attributes'])) {
							log::add('traccar', 'warning', 'Message MQTT invalide (il manque la section attributes ou les champs latitude, longitude ou speed) : ' . $mqttMessageType . '(' . $logMsgData . ')');
							continue;
						}
						// Récupère le traccar
						$traccarUniqueId = $mqttPayload['device']['uniqueId'];
						$traccar = traccar::getTraccarByUniqueId($traccarUniqueId);

						// Appel de la fonction d'événement Traccar
						$latitude = $mqttPayload['position']['latitude'];
						$longitude = $mqttPayload['position']['longitude'];
						$speed = $mqttPayload['position']['speed'];
						$attributes = $mqttPayload['position']['attributes'];

						log::add('traccar', 'info', 'Réception d\'une position MQTT - tracker ' . $traccarUniqueId . ' - ' . $traccar->getName());
						log::add('traccar', 'debug', '  > speed --> ' . $speed);
						log::add('traccar', 'debug', '  > attributes --> ' . json_encode($attributes));

						traccar::traccarPosition($traccar, $latitude, $longitude, $speed, $attributes);
						break;

					default:
						log::add('traccar', 'warning', 'MQTT message type : ' . $mqttMessageType . ', non supporté dans traccar.');
						break;
				}
			}
		} catch (JsonException $e) {
			log::add('traccar', 'error', 'Invalid MQTT JSON: ' . $e->getMessage());
		}
	}
}

class traccarCmd extends cmd {
}
