<?php

$this->set('version', '1.0');
$this->set('links', '{}');
$this->set('meta', '{}');

$this->set('entities', $this->each($routes, function ($section, $route) {
    $section->set('id', $route->id);
    $section->set('route_method', $route->route_method);
    $section->set('route_name', $route->route_name);
}));

$this->set('linked', '{}');
