## Version 1.8 - 19/05/2019

Ajout de l'information batteryLevel
Ajout de l'information ignition

## Version 1.7 - 08/10/2017

Ajout de la compatibilité avec le plugin Localisation et Trajet (geotrav), la compatibilité avec le plugin Geoloc reste assurée
Prise en compte de l'état "deviceUnknown", état Traccar entre "online" et "offline". Côté Jeedom il est considéré comme "offline" ("Online" à faux + "Moving" à faux)
Récupération de l'information "batteryLevel" dans les données "attributes" de Traccar : nécessite l'ajout du paramètre à la configuration Traccar
Récupération de l'information vitesse "speed"
Possibilité de d'historiser les commandes
Ajout sur l'écran de configuration d'un lien vers le tuto installation Traccar sur le forum Jeedom

## Version 1.6 - 07/10/2017

Migration vers une clé d'API propre au plugin (l'ancienne méthode reste pour le moment fonctionnelle)
Amélioration de l'écran de gestion du plugin avec accès direct à l'écran "Configuration"
Ajout sur l'écran "Configuration" des configurations Traccar "traccar.xml" pour utilisation interne (serveur Traccar sur le même réseau que Jeedom) ou externe

## Version 1.5 - 06/10/2017

Compatibilité Jeedom 3.1

## Version 1.4 - 12/11/2016

Ajout de la gestion des événements 'deviceMoving' et 'deviceStopped'. Si les notifications sont intempestives penser à augmenter le paramètre 'event.motion.speedThreshold' du service Traccar

## Version 1.3 - 07/11/2016

Ajout de la possibilité de recevoir les événements Traccar 'deviceOnline' et 'deviceOffline'

## Version 1.2 - 06/11/2016

Ajout de la possibilité de recevoir les événements Traccar 'geofenceEnter' et 'geofenceExit' pour l'entrée et la sortie d'une zone

## Version 1.1 - 06/11/2016

Les équipements se créent maintenant de façons automatisée, il faut tout de même les activer et les associer à leur équipement Geoloc

## Version 1 - 11/08/2016

Création du plugin