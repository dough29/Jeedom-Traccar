## Configuration du plugin

Depuis la version 2.0, il faut choisir le mode de communication entre le serveur Traccar et jeedom. Dans la page de configuration du plugin, il faut choisir le mode "legacy" si vous voulez utilez la communication directe entre traccar et jeedom. C'est la communication qui etait utilise jusqu'a la version 2.0. Si vous choisissez d'utiliser MQTT, alors vous pouvez choisir le root prefix utilise (defaut: traccar), et surtout il vous faut le plugin MQTTManager installe.
En fonction du choix (legacy ou mqtt), la fenetre de configuration de l'application traccar contient les informations de configuration a copier & coller dans le fichier traccar.xml.
Attention, un nouveau parametre existe dans les derniers versions de traccar pour recevoir les evenements. Par defaut, l'envoie des evenements est desactive.

    <entry key='event.status.enable'>true</entry>

Une fois le plugin configure, les équipements Traccar se créent automatiquement dès lors que votre serveur Traccar est bien paramétré (voir chapitre suivant).

Une fois que votre tracker apparaît il faut l'éditer pour l'activer et lui associer un équipement Localisation et Trajet ou Geoloc existant.

Les commandes Traccar permettant de défnir si un équipement est présent ou non dans une zone (à définir via l'interface WEB de votre serveur Traccar) se créent de façon automatique. Les commandes sont de type binaire, voici quelques exemples pour vos scénarios :

    Test si le tracker 'AZERTY1234' est dans la zone 'Domicile' --> #[Aucun][Tracker AZERTY1234][Domicile]# == 1
    Test si le tracker 'AZERTY1234' n'est pas dans la zone 'Domicile' --> #[Aucun][Tracker AZERTY1234][Domicile]# == 0

## Configuration du serveur Traccar pour l'envoi des positions à Jeedom

Du côté du serveur Traccar il faut éditer le fichier de configuration traccar.xml et ajouter les lignes proposees dans le panneau de configuration du plugin. Il faut etre en mode "legacy".

Example:

    <entry key='forward.enable'>true</entry>
    <entry key='forward.url'>http://<IP Jeedom>:<port Jeedom>/plugins/traccar/core/api/jeeTraccar.php?apikey=<clé API>&amp;type=traccar&amp;id={uniqueId}&amp;latitude={latitude}&amp;longitude={longitude}&amp;speed={speed}&amp;attributes={attributes}</entry>

Bien veiller à ce que les "&" soient représentés par leur code HTML "&amp;" !

Remplacer :

- \<IP Jeedom\> : par l'IP de la machine hébergant votre Jeedom
- \<port Jeedom\> : par le port HTTP de votre Jeedom
- \<clé API\> : par la clé API de votre Jeedom disponible dans l'écran de configuration générale

Relancer ensuite le serveur Traccar pour prendre en compte les changements.

  systemctl stop traccar
  systemctl start traccar

## Configuration du serveur Traccar pour l'envoi des événements à Jeedom

Assurer vous d'avoir configurer le plugin en mode "legacy".

Editer le fichier de configuration traccar.xml et ajouter les lignes :

    <entry key='event.status.enable'>true</entry>
    <entry key='event.forward.enable'>true</entry>
    <entry key='event.forward.url'>http://<IP Jeedom>:<port Jeedom>/plugins/traccar/core/api/jeeTraccar.php?apikey=<clé API>&amp;type=traccar&amp;action=event</entry>

Bien veiller à ce que les "&" soient représentés par leur code HTML "&amp;" !

Remplacer :

- \<IP Jeedom\> : par l'IP de la machine hébergant votre Jeedom
- \<port Jeedom\> : par le port HTTP de votre Jeedom
- \<clé API\> : par la clé API de votre Jeedom disponible dans l'écran de configuration générale

Traccar propose un événement "deviceMoving" qui peu envoyer trop de notifications en fonction du tracker utilisé.

Il est possible de paramétrer dans Traccar un seuil de détection de mouvement, par exemple sur ceux que j'utilise j'ai paramétré le seuil à 0.4 km/h comme suit :

    <entry key='event.motion.speedThreshold'>0.4</entry>

Modifier ce paramètre à  votre guise selon le comportement de vos trackers.

Relancer ensuite le serveur Traccar pour prendre en compte les changements.

  systemctl stop traccar
  systemctl start traccar

## Configuration du serveur Traccar pour l'envoi des positions à un MQTT broker

Du côté du serveur Traccar il faut éditer le fichier de configuration traccar.xml et ajouter les lignes proposees dans le panneau de configuration du plugin. Assurez vous d'avoir configurer le plugin en mode mqtt. La solution la plus simple est de copier & coller la fenetre de configuration dans la page configuration du plugin.

Example:

    <!-- Positions via MQTT -->
    <entry key='forward.enable'>true</entry>
    <entry key='forward.type'>mqtt</entry>
    <entry key='forward.url'>mqtt://<user>:<password>@<ip broker mqtt>:<port broker mqtt></entry>
    <entry key='forward.topic'><root topic>/positions</entry>

Remplacer :

- \<ip broker mqtt\> : par l'IP de la machine hébergant votre broker MQTT (mosquitto?)
- \<port broker mqtt\> : par le port broker mqtt (defaut 1883)
- \<user\> : l'utilisateur utlise pour vous identifez sur le broker MQTT
- \<password\>: le password de l'utilisateur du broker MQTT
- \<root topic>: la racine utilisee pour identifier les evements envoye par traccar au MQTT broker (defaut: traccar)

Relancer ensuite le serveur Traccar pour prendre en compte les changements.

    systemctl stop traccar
    systemctl start traccar

## Configuration du serveur Traccar pour l'envoi des événements à un MQTT broker

Assurez vous d'avoir configurer le plugin en mode mqtt. La solution la plus simple est de copier & coller la fenetre de configuration dans la page configuration du plugin.

Editer le fichier de configuration traccar.xml et ajouter les lignes :

<!-- Événements via MQTT -->
    <entry key='event.status.enable'>true</entry>
    <entry key='event.forward.enable'>true</entry>
    <entry key='event.forward.type'>mqtt</entry>
    <entry key='forward.url'>mqtt://<user>:<password>@<ip broker mqtt>:<port broker mqtt></entry>
    <entry key='forward.topic'><root topic>/positions</entry>

Remplacer :

- \<ip broker mqtt\> : par l'IP de la machine hébergant votre broker MQTT (mosquitto?)
- \<port broker mqtt\> : par le port broker mqtt (defaut 1883)
- \<user\> : l'utilisateur utlise pour vous identifez sur le broker MQTT
- \<password\>: le password de l'utilisateur du broker MQTT
- \<root topic>: la racine utilisee pour identifier les evements envoye par traccar au MQTT broker (defaut: traccar)

Traccar propose un événement "deviceMoving" qui peu envoyer trop de notifications en fonction du tracker utilisé.

Il est possible de paramétrer dans Traccar un seuil de détection de mouvement, par exemple sur ceux que j'utilise j'ai paramétré le seuil à 0.4 km/h comme suit :

    <entry key='event.motion.speedThreshold'>0.4</entry>

Modifier ce paramètre à  votre guise selon le comportement de vos trackers.

Relancer ensuite le serveur Traccar pour prendre en compte les changements.

    systemctl stop traccar
    systemctl start traccar
    