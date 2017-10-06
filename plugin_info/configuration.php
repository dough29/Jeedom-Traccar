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
		<div class="form-group">
			<label class="col-lg-4 control-label">Configuration Traccar 'default.xml' avec serveur Traccar sur le même réseau que Jeedom</label>
			<div class="col-lg-2">
<?php
				echo '<textarea class="eqLogicAttr form-control" wrap="off" rows="6" style="width: 750px">';
				echo htmlentities('<entry key=\'forward.enable\'>true</entry>
<entry key=\'forward.url\'>'.network::getNetworkAccess('internal').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;id={uniqueId}&amp;latitude={latitude}&amp;longitude={longitude}&amp;attributes={attributes}</entry>

<entry key=\'event.forward.enable\'>true</entry>
<entry key=\'event.forward.url\'>'.network::getNetworkAccess('internal').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;action=event</entry>');
				echo '</textarea>';
				?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-4 control-label">Configuration Traccar 'default.xml' avec serveur Traccar externe</label>
			<div class="col-lg-2">
<?php
				echo '<textarea class="eqLogicAttr form-control" wrap="off" rows="6" style="width: 750px">';
				echo htmlentities('<entry key=\'forward.enable\'>true</entry>
<entry key=\'forward.url\'>'.network::getNetworkAccess('external').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;id={uniqueId}&amp;latitude={latitude}&amp;longitude={longitude}&amp;attributes={attributes}</entry>

<entry key=\'event.forward.enable\'>true</entry>
<entry key=\'event.forward.url\'>'.network::getNetworkAccess('external').'/plugins/traccar/core/api/jeeTraccar.php?apikey='.jeedom::getApiKey('traccar').'&amp;type=traccar&amp;action=event</entry>');
				echo '</textarea>';
				?>
			</div>
		</div>
	</fieldset>
</form>
