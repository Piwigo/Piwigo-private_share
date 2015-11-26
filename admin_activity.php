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
// | prepare data for template                                             |
// +-----------------------------------------------------------------------+

$users = array();
$images = array();

$query = '
SELECT *
  FROM '.PSHARE_KEYS_TABLE.'
  ORDER BY pshare_key_id DESC 
;';
$shares = query2array($query);

foreach ($shares as $share)
{
  $users[ $share['user_id'] ] = 1;
  $images[ $share['image_id'] ] = 1;
}

// find details about sharing users
if (count($users) > 0)
{
  $query = '
SELECT
    '.$conf['user_fields']['id'].' AS id,
    '.$conf['user_fields']['username'].' AS username
  FROM '.USERS_TABLE.'
  WHERE id IN ('.implode(',', array_keys($users)).')
;';
  $username_of = query2array($query, 'id', 'username');
}

// find details about shared images
if (count($images) > 0)
{
  $query = '
SELECT
    id,
    name
  FROM '.IMAGES_TABLE.'
  WHERE id IN ('.implode(',', array_keys($images)).')
;';
  $image_name_of = query2array($query, 'id', 'name');

  // echo '<pre>'; print_r($image_name_of); echo '</pre>';
}

// log details
$stats_of = array();

$query = '
SELECT
    pshare_key_idx,
    type,
    COUNT(*) AS counter
  FROM '.PSHARE_LOG_TABLE.'
  GROUP BY pshare_key_idx, type
;';
$result = pwg_query($query);
while ($row = pwg_db_fetch_assoc($result))
{
  if (!isset($stats_of[ $row['pshare_key_idx'] ]))
  {
    $stats_of[ $row['pshare_key_idx'] ] = array('visit'=>0, 'download'=>0);
  }

  $stats_of[ $row['pshare_key_idx'] ][ $row['type'] ] = $row['counter'];
}

// fill user/image details into shares (activity lines)
foreach ($shares as &$share)
{
  $share['user'] = 'deleted';
  if (isset($username_of[ $share['user_id'] ]))
  {
    $share['user'] = $username_of[ $share['user_id'] ];
  }

  $share['photo'] = 'deleted';
  if (isset($image_name_of[ $share['image_id'] ]))
  {
    $share['photo'] = $image_name_of[ $share['image_id'] ];
  }

  foreach (array('visit', 'download') as $type)
  {
    $share[$type] = 0;
    if (isset($stats_of[ $share['pshare_key_id'] ]))
    {
      $share[$type] = $stats_of[ $share['pshare_key_id'] ][$type];
    }
  }

  // we take current datetime from database because this is what we used to
  // set all dates in table
  list($dbnow) = pwg_db_fetch_row(pwg_query('SELECT NOW()'));
  if (strtotime($share['expire_on']) < strtotime($dbnow))
  {
    $share['expired'] = true;
  }  
}

$template->assign('activity_lines', $shares);

// +-----------------------------------------------------------------------+
// | sending html code                                                     |
// +-----------------------------------------------------------------------+

$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
?>