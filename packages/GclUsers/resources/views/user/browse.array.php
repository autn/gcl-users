<?php
$this->set('version', '1.0');
$this->set('links', $this->helper('gcl.gclusers::helpers.links', $users['data']));
$this->set('meta', function ($section) use ($users) {

    $section->set('offset', $users['offset']);
    $section->set('limit', $users['limit']);
    $section->set('total', $users['total']);
});

$this->set('entities', $this->each($users['data'], function ($section, $user) {

    $section->set($section->partial('gcl.gclusers::partials/user', [ 'user' => $user ]));
}));

$this->set('linked', '{}');
