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
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function event() {
		$traccar = traccar::byLogicalId(init('id'), 'traccar');
		
		if (!is_object($traccar)) {
			throw new Exception(__('Traccar - tracker inconnu : ', __FILE__) . init('id'));
		}
		if ($traccar->getEqType_name() != 'traccar') {
			throw new Exception(__('Traccar - cet équipement n\'est pas de type traccar : ', __FILE__) . init('id'));
		}
		
		$geolocId = $traccar->getConfiguration('geoloc');
		
		if (null == $geolocId) {
			throw new Exception(__('Traccar - cet équipement n\'est pas lié à un objet Geoloc : ', __FILE__) . init('id'));
		}
		
		$geoloc = geolocCmd::byId($geolocId);
		
		$geoloc->event(init('latitude').",".init('longitude'));
		$geoloc->getEqLogic()->refreshWidget();
	}

	/*     * *********************Methode d'instance************************* */

}

class traccarCmd extends cmd {
	/*     * *************************Attributs****************************** */
	
	/*     * ***********************Methode static*************************** */
	
	/*     * *********************Methode d'instance************************* */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */
}

?>
