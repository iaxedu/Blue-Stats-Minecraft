<?php
/** @var module $this */
$this->loadPlugin("query");

if (!isset($this->plugins["query"]))
    return;

$plugin = $this->plugins["query"];

if (in_array($this->player->name, $plugin->onlinePlayers())) {
    echo '<span class="label label-success">Online</span>';
}
else {
    echo '<span class="label label-danger">Offline</span>';
}