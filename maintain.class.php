<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class private_share_maintain extends PluginMaintain
{
  private $installed = false;

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id);
  }

  function install($plugin_version, &$errors=array())
  {
    global $conf, $prefixeTable;

    $query = '
CREATE TABLE IF NOT EXISTS `'.$prefixeTable.'pshare_keys` (
  `pshare_key_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `image_id` mediumint(8) unsigned NOT NULL,
  `sent_to` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `duration` int(10) unsigned DEFAULT NULL,
  `expire_on` datetime NOT NULL,
  `is_valid` enum(\'true\',\'false\') NOT NULL DEFAULT \'true\',
  PRIMARY KEY (`pshare_key_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;';
    pwg_query($query);

    $query = '
CREATE TABLE IF NOT EXISTS `'.$prefixeTable.'pshare_log` (
  `pshare_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pshare_key_idx` int(10) unsigned NOT NULL,
  `occured_on` datetime NOT NULL,
  `type` enum(\'download\',\'visit\') NOT NULL DEFAULT \'visit\',
  `ip_address` varchar(15) NOT NULL DEFAULT \'\',
  `user_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`pshare_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;';
    pwg_query($query);

    $result = pwg_query('SHOW COLUMNS FROM `'.GROUPS_TABLE.'` LIKE "pshare_enabled";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE '.GROUPS_TABLE.' ADD pshare_enabled enum(\'true\', \'false\') DEFAULT \'false\';');
    }
    
    $this->installed = true;
  }

  function activate($plugin_version, &$errors=array())
  {
    global $prefixeTable;
    
    if (!$this->installed)
    {
      $this->install($plugin_version, $errors);
    }
  }

  function update($old_version, $new_version, &$errors=array())
  {
    $this->install($new_version, $errors);
  }
  
  function deactivate()
  {
  }

  function uninstall()
  {
    global $prefixeTable;

    pwg_query('DROP TABLE '.$prefixeTable.'pshare_keys;');
    pwg_query('DROP TABLE '.$prefixeTable.'pshare_log;');

    $query = 'DROP TABLE '.GROUPS_TABLE.' DROP pshare_enabled;';
    pwg_query($query);
  }
}
?>
