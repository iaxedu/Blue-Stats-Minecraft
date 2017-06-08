<?php
/** @var module $this */
$blocks_names = json_decode(file_get_contents($this->bluestats->appPath."/items.json"),true);

$render = function ($module, $plugin, $blocks_names) {
	$output = "";
	foreach ($plugin->database['stats'] as $stat => $info) {
		$statName = $plugin->database["stats"][$stat]["name"];
		$output .= "<h4>$statName</h4>";
		$table = New Table();
		// Loop through all values in database
		$data = $plugin->stats->player($module->player, $stat);
		foreach ($data as $key => $entry) {
			$values = [];
			$count = 0;
			$itemID = 0;
			foreach ($entry as $statt => $value) {
				switch ($plugin->database["stats"][$stat]["values"][$count]["dataType"]){
					case "item_id":
						// If the data collected was of type item_id, store it and wait until the data type is received.
						$itemID = $value;
						break;
					case "item_type":
						// If an item type value is recieved assume the item id has already been recieved. Thus, also print the name of the bock into the table
						$name = getBlockNameFromID($itemID, $value, $blocks_names)?: getBlockNameFromID($itemID, 0, $blocks_names)?: $itemID . '-' . $value;
						array_push($values, $name);
						break;
					case "player_name":
						if ($module->bluestats->url->useUUID) {
							$uuid = $module->bluestats->basePlugin->player->getUUIDfromName($value);
							$value = "<a href=\"" . $module->bluestats->url->player($uuid) . "\"><img src=\"https://minotar.net/helm/{$value}/32.png\" alt=\"\"> {$value}</a>";
						} else {
							$value = "<a href=\"" . $module->bluestats->url->player($value) . "\"><img src=\"https://minotar.net/helm/{$value}/32.png\" alt=\"\"> {$value}</a>";
						}
						array_push($values, $value);
						break;
					default:
						array_push($values, $value);
				}
				$count++;
			}
			call_user_func_array([$table, 'addRecord'], $values);
		}

		// Generate header for table
		$values = [];

		foreach ($plugin->database["stats"][$stat]["values"] as $entry) {
			switch ($entry["dataType"]) {
				case "item_id":
					break;
				case "item_type":
					array_push($values, "Block");
					break;
				default:
					array_push($values, $entry["name"]);
			}
		}
		call_user_func_array([$table, 'makeHeader'], $values);
		$output .= $table->tableToHTML();
	}
	return $output;
};

if (isset($this->args[0]))
	return print($render($this, $this->bluestats->plugins[$this->args[0]], $blocks_names));

$output = "";

/** @var \BlueStats\API\plugin $plugin */
foreach ($this->bluestats->plugins as $plugin) {
	if ($plugin::$isMySQLplugin)
		$output .= "<h3>$plugin->name</h3>" . $render($this, $plugin, $blocks_names);
}

echo $output;