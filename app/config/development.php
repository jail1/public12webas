<?php

// Configuration specific to a development environment
$app->config(array(
    // Database configuration
    'database' => array(
        'dbname' => 'coupons',
        'user' => 'root',
        'password' => 'root',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
	'log' => true,
    ),

    // image upload url
    'image_url' => '', 

    // Api keys and their api secrets
    'api.noauth' => true,

    'email' => array(
        'from' => '',
        'from_name' => '',
        'smtp' => array(
            'host' => '',
            'port' => 25,
            'username' => '',
            'password' => '',
            'security' => 'tls'
        )
    )
));
