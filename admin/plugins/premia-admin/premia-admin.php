<?php

/**
 * Plugin Name: Premia Admin
 */

namespace Premia_Admin;

require 'vendor/autoload.php';

new ACF_Fields();
new Environments();
new Rest();
new Settings();
