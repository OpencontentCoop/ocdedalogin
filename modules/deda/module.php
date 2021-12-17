<?php

$Module = array('name' => 'Deda Login');

$ViewList = array();
$ViewList['login'] = array(
    'functions' => array('login'),
    'script' => 'login.php',
    'params' => array('IDP'),
    'unordered_params' => array()
);
$ViewList['logout'] = array(
    'functions' => array('login'),
    'script' => 'logout.php',
    'params' => array(),
    'unordered_params' => array()
);
$ViewList['metadata'] = array(
    'functions' => array('login'),
    'script' => 'metadata.php',
    'params' => array('IDP'),
    'unordered_params' => array()
);
$ViewList['acs'] = array(
    'functions' => array('login'),
    'script' => 'acs.php',
    'params' => array(),
    'unordered_params' => array()
);

$FunctionList = array();
$FunctionList['login'] = array();


