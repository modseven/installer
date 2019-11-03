<?php

use Modseven\Route;

Route::set('default', '(<controller>(/<action>(/<id>)))')
    ->defaults([
        'namespace' => '<AppName>',
        'controller' => 'Welcome',
        'action' => 'index',
    ]);
