<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('/auth/reset-password', 'Auth::resetPassword');
$routes->get('/auth/contact-admin', 'Auth::contactAdministrator');
