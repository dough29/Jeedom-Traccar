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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
	<fieldset>
    <!-- BR: 2026/08/03: Add MQTT -->
	<?php if (class_exists('jMQTT')) {
			echo '<div class="alert alert-warning">{{Le plugin jMQTT est installé, veuillez vérifier la configuration du broker dans le plugin jMQTT et la reporter, si nécessaire, dans le plugin MQTT Manager.}}</div>';
		}
	?>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Mode}}</label>
			<div class="col-md-3">
				<select class="configKey form-control"
						data-l1key="notif_mode"
						id="sel_notificationMode">
					<option value="">{{A configurer}}</option>
					<option value="legacy">{{Legacy}}</option>
					<option value="mqtt">{{MQTT}}</option>
				</select>
			</div>
		</div>

		<div class="form-group notificationMode mqtt">
			<label class="col-md-4 control-label">{{Topic racine}}</label>
			<div class="col-md-3">
				<input class="configKey form-control"
					data-l1key="mqtt_topic" value="traccar"/>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<div class="form-group notificationMode legacy">
			<div class="form-group">
				<label class="col-lg-4 control-label"></label>
				<div class="col-lg-3"><b><a href="https://www.jeedom.com/forum/viewtopic.php?f=59&t=20329" target="_blank">Tuto installation Traccar sur le forum Jeedom</a></b></div>
				</div>
			<div class="form-group">
				<label class="col-lg-4 control-label">Configuration Traccar 'traccar.xml' avec serveur Traccar sur le même réseau que Jeedom</label>
				<div class="col-lg-3">
<?php
					echo '<textarea class="eqLogicAttr form-control" wrap="off" rows="6" style="width: 750px">';
					echo htmlentities('<!--- jeedom direct connector --->
<entry key=\'forward.enable\'>true</entry>
<entry key=\'forward.url\'>'.network::getNetworkAccess('internal').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;id={uniqueId}&amp;latitude={latitude}&amp;longitude={longitude}&amp;speed={speed}&amp;attributes={attributes}</entry>

<entry key=\'event.status.enable\'>true</entry>					
<entry key=\'event.forward.enable\'>true</entry>
<entry key=\'event.forward.url\'>'.network::getNetworkAccess('internal').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;action=event</entry>');
					echo '</textarea>'
					?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-4 control-label">Configuration Traccar 'traccar.xml' avec serveur Traccar externe</label>
				<div class="col-lg-3">
<?php
					echo '<textarea class="eqLogicAttr form-control" wrap="off" rows="6" style="width: 750px">';
					echo htmlentities('<entry key=\'forward.enable\'>true</entry>
<entry key=\'forward.url\'>'.network::getNetworkAccess('external').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;id={uniqueId}&amp;latitude={latitude}&amp;longitude={longitude}&amp;speed={speed}&amp;attributes={attributes}</entry>

<entry key=\'event.status.enable\'>true</entry>					
<entry key=\'event.forward.enable\'>true</entry>
<entry key=\'event.forward.url\'>'.network::getNetworkAccess('external').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;action=event</entry>');
					echo '</textarea>';
					?>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<div class="form-group notificationMode mqtt">
			<?php if (!class_exists('mqtt2')) {
				echo '<div class="alert alert-warning">{{Le plugin MQTTManager n\'est pas installé, veuillez l\'installer avant de configurer le plugin traccar en MQTT.}}</div>';
			}
			?>
			<div class="form-group">
				<label class="col-lg-4 control-label"></label>
				<div class="col-lg-3"><b><a href="https://www.jeedom.com/forum/viewtopic.php?f=59&t=20329" target="_blank">Tuto installation Traccar sur le forum Jeedom</a></b></div>
				</div>
			<div class="form-group">
			<label class="col-lg-4 control-label">Configuration Traccar 'traccar.xml' avec serveur MQTT</label>
			<div class="col-lg-3">
<?php
				if (class_exists('mqtt2')) {
					$mqtt = mqtt2::getFormatedInfos();
					$mqtt_url = $mqtt['protocol'] . '://';
					if (isset($mqtt['user']) && isset($mqtt['password'])) {
						$mqtt_url .= $mqtt['user'] . ':' . $mqtt['password'] . '@';
					}
					$mqtt_url .= $mqtt['ip'];
					if (isset($mqtt['port'])) {
						$mqtt_url .= ':' . $mqtt['port'];
					}
					$mqtt_topic = config::byKey('mqtt_topic', 'traccar', 'traccar');
    				$mqtt_topic = trim($mqtt_topic, '/');
					echo '<textarea class="eqLogicAttr form-control" wrap="off" rows="6" style="width: 750px">';
					echo htmlentities('<!-- Événements via MQTT -->
<entry key=\'event.status.enable\'>true</entry>
<entry key=\'event.forward.enable\'>true</entry>
<entry key=\'event.forward.type\'>mqtt</entry>
<entry key=\'event.forward.url\'>' . $mqtt_url. '</entry>
<entry key=\'event.forward.topic\'>'. $mqtt_topic . '/events</entry>

<!-- Positions via MQTT -->
<entry key=\'forward.enable\'>true</entry>
<entry key=\'forward.type\'>mqtt</entry>
<entry key=\'forward.url\'>' . $mqtt_url . '</entry>
<entry key=\'forward.topic\'>' . $mqtt_topic . '/positions</entry>');
					echo '</textarea>';
				}
				?>
			</div>
		</div>
	</fielset>
</form>

<script>
	<!-- BR: 2026/08/03: MQTT addon -->
  $('#sel_notificationMode').off('change').on('change', function() {
    $('.notificationMode').hide();
    if ($(this).value() != '') {
      $('.notificationMode.' + $(this).value()).show();
    }
  })
</script>
