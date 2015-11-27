<?php
// +-----------------------------------------------------------------------+
// | Piwigo - a PHP based picture gallery                                  |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2008-2011 Piwigo Team                  http://piwigo.org |
// | Copyright(C) 2003-2008 PhpWebGallery Team    http://phpwebgallery.net |
// | Copyright(C) 2002-2003 Pierrick LE GALL   http://le-gall.net/pierrick |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

if( !defined("PHPWG_ROOT_PATH") )
{
  die ("Hacking attempt!");
}

// +-----------------------------------------------------------------------+
// |                                actions                                |
// +-----------------------------------------------------------------------+

if (!empty($_POST))
{
  check_input_parameter('groups', $_POST, true, PATTERN_ID);

  // first we must reset all groups to false
  $query = '
UPDATE '.GROUPS_TABLE.'
  SET pshare_enabled = \'false\'
;';
  pwg_query($query);

  // then we set submitted groups to true
  $query = '
UPDATE '.GROUPS_TABLE.'
  SET pshare_enabled = \'true\'
  WHERE id IN ('.implode(',', $_POST['groups']).')
;';
  pwg_query($query);
  
  array_push($page['infos'], l10n('Information data registered in database'));
}

// +-----------------------------------------------------------------------+
// | form options                                                          |
// +-----------------------------------------------------------------------+

$query = '
SELECT id
  FROM '.GROUPS_TABLE.'
;';
$group_ids = query2array($query, null, 'id');

$query = '
SELECT id
  FROM '.GROUPS_TABLE.'
  WHERE pshare_enabled = \'true\'
;';
$groups_selected = query2array($query, null, 'id');

$template->assign(
  array(
    'CACHE_KEYS' => get_admin_client_cache_keys(array('groups')),
    'groups' => $group_ids,
    'groups_selected' => $groups_selected,
    )
  );

// +-----------------------------------------------------------------------+
// | sending html code                                                     |
// +-----------------------------------------------------------------------+

$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
?>