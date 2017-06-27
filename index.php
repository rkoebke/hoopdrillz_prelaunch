<?php
/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/3/17
 * Time: 9:34 PM
 */

$f3 = require('includes/f3/base.php');
require('includes/PHPMailer/PHPMailerAutoload.php');
require('includes/random_compat/lib/random.php');

$f3->config("app.ini");

//new Session();
new Session(NULL,'CSRF');

$f3->set('FALLBACK','en');

$f3->run();