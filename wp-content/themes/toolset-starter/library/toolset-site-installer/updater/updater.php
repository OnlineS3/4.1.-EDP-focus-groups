<?php

// resources
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once dirname(__FILE__) . '/plugin.php';
require_once dirname(__FILE__) . '/theme.php';

// check for plugin update
$all_plugins = get_plugins();

foreach ($settings->getPlugins() as $plugin) {
    if (strpos($plugin->getSrc(), 'wordpress.org') !== false             // skip if plugin is hosted on wordpress.org
        || ! array_key_exists($plugin->getEntryPoint(), $all_plugins)   // skip if plugin is not installed / active
    ) {
        continue;
    }

    $update_plugin = new TT_Updater_Plugin( $settings, $plugin->getId() );
    $update_plugin->setName($all_plugins[$plugin->getEntryPoint()]['Name']);
    $update_plugin->setVersion($all_plugins[$plugin->getEntryPoint()]['Version']);
    $update_plugin->setEntryPoint($plugin->getEntryPoint());
	$update_plugin->setUrlLatestVersion($plugin->getUpdateUrl());
}

// check for theme update
$theme_name   = basename(get_template_directory());
$theme_update = new TT_Updater_Theme(
    $theme_name,
    $settings->getThemeUpdateUrl()
);
