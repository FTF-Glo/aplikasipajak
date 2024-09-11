<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$autoload['packages'] = array();
$autoload['libraries'] = array('database','session','fun','format');
$autoload['drivers'] = array();
$autoload['helper'] = array('url','form','html','security', 'config');
$autoload['config'] = array('config_dev');
$autoload['language'] = array();
$autoload['model'] = array('m_db');