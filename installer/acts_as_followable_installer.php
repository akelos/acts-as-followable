<?php


define('AK_AACR_PLUGIN_FILES_DIR', AK_APP_PLUGINS_DIR.DS.'acts_as_followable'.DS.'installer'.DS.'files');


class ActsAsFollowableInstaller extends AkPluginInstaller
{

    function up_1()
    {
        $this->runMigration();
        echo "\n\nInstallation completed\n";
    }
    
    function runMigration()
    {
        include_once(AK_APP_INSTALLERS_DIR.DS.'acts_as_followable_plugin_installer.php');
        $Installer =& new ActsAsFollowablePluginInstaller();

        echo "Running the acts_as_follower plugin migration\n";
        $Installer->install();
    }

    function down_1()
    {
        include_once(AK_APP_INSTALLERS_DIR.DS.'acts_as_followable_plugin_installer.php');
        $Installer =& new ActsAsFollowablePluginInstaller();
        echo "Uninstalling the acts_as_follower plugin migration\n";
        $Installer->uninstall();
    }

}
?>