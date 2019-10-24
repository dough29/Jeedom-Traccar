## Configuration du plugin

Les équipements Traccar se créent automatiquement dès lors que votre serveur Traccar est bien paramétré (voir chapitre suivant).

Une fois que votre tracker apparaît il faut l'éditer pour l'activer et lui associer un équipement Geoloc existant.

Les commandes Traccar permettant de défnir si un équipement est présent ou non dans une zone (à définir via l'interface WEB de votre serveur Traccar) se créent de façon automatique. Les commandes sont de type binaire, voici quelques exemples pour vos scénarios :

    Test si le tracker 'AZERTY1234' est dans la zone 'Domicile' --> #[Aucun][Tracker AZERTY1234][Domicile]# == 1
    Test si le tracker 'AZERTY1234' n'est pas dans la zone 'Domicile' --> #[Aucun][Tracker AZERTY1234][Domicile]# == 0

## Configuration du serveur Traccar pour l'envoi des positions à Jedom

Du côté du serveur Traccar il faut éditer le fichier de configuration traccar.xml et ajouter les lignes :

    <entry key='forward.enable'>true</entry>
    <entry key='forward.url'>http://<IP Jeedom>:<port Jeedom>/core/api/jeeApi.php?api=<clé API>&amp;type=traccar&amp;id={uniqueId}&amp;latitude={latitude}&amp;longitude={longitude}</entry>

Bien veiller à ce que les "&" soient représentés par leur code HTML "&amp;" !

Remplacer :
  - <IP Jeedom> : par l'IP de la machine hébergant votre Jeedom
  - <port Jeedom> : par le port HTTP de votre Jeedom
  - <clé API> : par la clé API de votre Jeedom disponible dans l'écran de configuration générale

Relancer ensuite le serveur Traccar pour prendre en compte les changements.

## Configuration du serveur Traccar pour l'envoi des événements à Jedom

Editer le fichier de configuration traccar.xml et ajouter les lignes :

    <entry key='event.forward.enable'>true</entry>
    <entry key='event.forward.url'>http://<IP Jeedom>:<port Jeedom>/core/api/jeeApi.php?api=<clé API>&amp;type=traccar&amp;action=event</entry>

Bien veiller à ce que les "&" soient représentés par leur code HTML "&amp;" !

Remplacer :
  - <IP Jeedom> : par l'IP de la machine hébergant votre Jeedom
  - <port Jeedom> : par le port HTTP de votre Jeedom
  - <clé API> : par la clé API de votre Jeedom disponible dans l'écran de configuration générale

Traccar propose un événement "deviceMoving" qui peu envoyer trop de notifications en fonction du tracker utilisé.

Il est possible de paramétrer dans Traccar un seuil de détection de mouvement, par exemple sur ceux que j'utilise j'ai paramétré le seuil à 0.4 km/h comme suit :

    <entry key='event.motion.speedThreshold'>0.4</entry>

Modifier ce paramètre à  votre guise selon le comportement de vos trackers.

Relancer ensuite le serveur Traccar pour prendre en compte les changements.