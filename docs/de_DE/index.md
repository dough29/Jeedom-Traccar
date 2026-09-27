## Configuration du plugin

Depuis la version 2.0, il faut choisir le mode de communication entre le serveur Traccar et Jeedom. Dans la page de configuration du plugin, choisissez le mode « legacy » si vous voulez utiliser la communication directe entre Traccar et Jeedom. C'est la communication qui était utilisée jusqu'à la version 2.0. Si vous choisissez d'utiliser MQTT, vous pouvez définir le préfixe racine utilisé (défaut : traccar) ; il vous faut surtout le plugin MQTTManager installé.
En fonction du choix (« legacy » ou MQTT), la fenêtre de configuration de l'application Traccar contient les informations de configuration à copier-coller dans le fichier `traccar.xml`.

Attention, un nouveau paramètre existe dans les dernières versions de Traccar pour recevoir les événements. Par défaut, l'envoi des événements est désactivé.

    <entry key='event.status.enable'>true</entry>

Une fois le plugin configuré, les équipements Traccar se créent automatiquement dès lors que votre serveur Traccar est bien paramétré (voir chapitre suivant).

Une fois que votre traqueur apparaît, modifiez-le pour l'activer et lui associer un équipement Localisation et Trajet ou Géoloc existant.

Les commandes Traccar permettant de définir si un équipement est présent ou non dans une zone (à définir via l'interface web de votre serveur Traccar) se créent de façon automatique. Les commandes sont de type binaire, voici quelques exemples pour vos scénarios :

    Test si le traqueur 'AZERTY1234' est dans la zone 'Domicile' --> #[Aucun][Tracker AZERTY1234][Domicile]# == 1
    Test si le traqueur 'AZERTY1234' n'est pas dans la zone 'Domicile' --> #[Aucun][Tracker AZERTY1234][Domicile]# == 0

## Configuration du serveur Traccar pour l'envoi des positions à Jeedom

Du côté du serveur Traccar, éditez le fichier de configuration `traccar.xml` et ajoutez les lignes proposées dans le panneau de configuration du plugin. Il faut être en mode « legacy ».

Exemple :

    <entry key='forward.enable'>true</entry>
    <entry key='forward.url'>http://<IP Jeedom>:<port Jeedom>/plugins/traccar/core/api/jeeTraccar.php?apikey=<clé API>&amp;type=traccar&amp;id={uniqueId}&amp;latitude={latitude}&amp;longitude={longitude}&amp;speed={speed}&amp;attributes={attributes}</entry>

Veillez bien à ce que les « & » soient représentés par leur code HTML `&amp;` !

Remplacez :

- `<IP Jeedom>` : par l'IP de la machine hébergeant votre Jeedom ;
- `<port Jeedom>` : par le port HTTP de votre Jeedom ;
- `<clé API>` : par la clé API de votre Jeedom disponible dans l'écran de configuration générale.

Relancez ensuite le serveur Traccar pour prendre en compte les changements :

  systemctl stop traccar
  systemctl start traccar

## Configuration du serveur Traccar pour l'envoi des événements à Jeedom

Assurez-vous d'avoir configuré le plugin en mode « legacy ».

Éditez le fichier de configuration `traccar.xml` et ajoutez les lignes :

    <entry key='event.status.enable'>true</entry>
    <entry key='event.forward.enable'>true</entry>
    <entry key='event.forward.url'>http://<IP Jeedom>:<port Jeedom>/plugins/traccar/core/api/jeeTraccar.php?apikey=<clé API>&amp;type=traccar&amp;action=event</entry>

Veillez bien à ce que les « & » soient représentés par leur code HTML `&amp;` !

Remplacez :

- `<IP Jeedom>` : par l'IP de la machine hébergeant votre Jeedom ;
- `<port Jeedom>` : par le port HTTP de votre Jeedom ;
- `<clé API>` : par la clé API de votre Jeedom disponible dans l'écran de configuration générale.

Traccar propose un événement « deviceMoving » qui peut envoyer trop de notifications en fonction du traqueur utilisé.

Il est possible de paramétrer dans Traccar un seuil de détection de mouvement. Par exemple, sur ceux que j'utilise, j'ai paramétré le seuil à 0,4 km/h comme suit :

    <entry key='event.motion.speedThreshold'>0.4</entry>

Modifiez ce paramètre à votre guise selon le comportement de vos traqueurs.

Relancez ensuite le serveur Traccar pour prendre en compte les changements :

  systemctl stop traccar
  systemctl start traccar

## Configuration du serveur Traccar pour l'envoi des positions à un broker MQTT

Du côté du serveur Traccar, éditez le fichier de configuration `traccar.xml` et ajoutez les lignes proposées dans le panneau de configuration du plugin. Assurez-vous d'avoir configuré le plugin en mode MQTT. La solution la plus simple est de copier-coller la fenêtre de configuration dans la page de configuration du plugin.

Exemple :

    <!-- Positions via MQTT -->
    <entry key='forward.enable'>true</entry>
    <entry key='forward.type'>mqtt</entry>
    <entry key='forward.url'>mqtt://<user>:<password>@<ip broker mqtt>:<port broker mqtt></entry>
    <entry key='forward.topic'><root topic>/positions</entry>

Remplacez :

- `<ip broker mqtt>` : par l'IP de la machine hébergeant votre broker MQTT (Mosquitto) ;
- `<port broker mqtt>` : par le port du broker MQTT (défaut : 1883) ;
- `<user>` : l'utilisateur utilisé pour vous identifier sur le broker MQTT ;
- `<password>` : le mot de passe de l'utilisateur du broker MQTT ;
- `<root topic>` : la racine utilisée pour identifier les événements envoyés par Traccar au broker MQTT (défaut : traccar).

Relancez ensuite le serveur Traccar pour prendre en compte les changements :

    systemctl stop traccar
    systemctl start traccar

## Configuration du serveur Traccar pour l'envoi des événements à un broker MQTT

Assurez-vous d'avoir configuré le plugin en mode MQTT. La solution la plus simple est de copier-coller la fenêtre de configuration dans la page de configuration du plugin.

Éditez le fichier de configuration `traccar.xml` et ajoutez les lignes :

    <!-- Événements via MQTT -->
    <entry key='event.status.enable'>true</entry>
    <entry key='event.forward.enable'>true</entry>
    <entry key='event.forward.type'>mqtt</entry>
    <entry key='event.forward.url'>mqtt://<user>:<password>@<ip broker mqtt>:<port broker mqtt></entry>
    <entry key='event.forward.topic'><root topic>/events</entry>

Remplacez :

- `<ip broker mqtt>` : par l'IP de la machine hébergeant votre broker MQTT (Mosquitto) ;
- `<port broker mqtt>` : par le port du broker MQTT (défaut : 1883) ;
- `<user>` : l'utilisateur utilisé pour vous identifier sur le broker MQTT ;
- `<password>` : le mot de passe de l'utilisateur du broker MQTT ;
- `<root topic>` : la racine utilisée pour identifier les événements envoyés par Traccar au broker MQTT (défaut : traccar).

Traccar propose un événement « deviceMoving » qui peut envoyer trop de notifications en fonction du traqueur utilisé.

Il est possible de paramétrer dans Traccar un seuil de détection de mouvement. Par exemple, sur ceux que j'utilise, j'ai paramétré le seuil à 0,4 km/h comme suit :

    <entry key='event.motion.speedThreshold'>0.4</entry>

Modifiez ce paramètre à votre guise selon le comportement de vos traqueurs.

Relancez ensuite le serveur Traccar pour prendre en compte les changements :

    systemctl stop traccar
    systemctl start traccar

