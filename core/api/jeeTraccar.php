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

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
    
if (!jeedom::apiAccess(init('apikey'), 'traccar')) {
    echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (geoloc)', __FILE__);
    die();
}
    
if (init('action') != null && init('action') === 'event') {
    // Récupération du flux JSON
    $content = file_get_contents('php://input');
    $traccarEvent = json_decode($content);
    log::add('traccar', 'debug', 'Evénement reçu, trame JSON : '. $content);

    // Définition des variables
    $traccarUniqueId = $traccarEvent->device->uniqueId;
    $traccarEventType = $traccarEvent->event->type;
    
    // Récupération de l'équipement Traccar
    $traccar = traccar::getTraccarByUniqueId($traccarUniqueId);
    
    // Appel de la fonction d'événement Traccar
    if ($traccar != '') {
        log::add('traccar', 'info', 'Reception d\'un événement '.$traccarEventType.' - tracker '.$traccarUniqueId.' - '.$traccar->getName());
        traccar::traccarEvent($traccar, $traccarEvent);
    }   
}elseif (init('id') != '') { // Réception d'une position
    // Récupération de l'équipement Traccar
    $traccar = traccar::getTraccarByUniqueId(init('id'));    
    if ($traccar != '') {
        log::add('traccar', 'info', 'Reception d\'une position - tracker '.init('id').' - '.$traccar->getName());
        // Appel de la fonction de position Traccar
        traccar::traccarPosition($traccar, init('latitude'), init('longitude'));    
    }

}else{
    log::add('traccar', 'error', 'Oups, verifier la configuration du serveur traccar');
    return false;
}

return true;
?>

