<?php
/*
Plugin Name: Private Share
Version: auto
Description: Share a private photo, with a key instead of authentication
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=
Author: plg
Author URI: http://le-gall.net/pierrick
*/

global $conf, $prefixeTable;

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

if (mobile_theme()) return;

define('PSHARE_PATH' , PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)) . '/');
define('PSHARE_KEYS_TABLE', $prefixeTable.'pshare_keys');
define('PSHARE_LOG_TABLE', $prefixeTable.'pshare_log');
define('PSHARE_KEY_PATTERN', '/^[a-zA-Z0-9]{30}$/');
define('PSHARE_ADMIN_BASE_URL', get_root_url().'admin.php?page=plugin-private_share');

add_event_handler('init', 'pshare_init');
function pshare_init()
{
  global $conf;

  load_language('plugin.lang', PSHARE_PATH);
}

add_event_handler('get_admin_plugin_menu_links', 'pshare_admin_menu');
function pshare_admin_menu($menu)
{
  global $page;

  array_push(
    $menu,
    array(
      'NAME' => 'Private Share',
      'URL'  => get_root_url().'admin.php?page=plugin-private_share'
      )
    );

  return $menu;
}

// +-----------------------------------------------------------------------+
// | SECTION INIT
// +-----------------------------------------------------------------------+

add_event_handler('loc_end_section_init', 'pshare_section_init');

/* define page section from url */
function pshare_section_init()
{
  global $tokens, $page, $conf, $user, $template;

  if ($tokens[0] == 'pshare')
  {
    $page['section'] = 'pshare';
    $page['title'] = l10n('Shared Picture');

    if (!isset($tokens[1]))
    {
      die("missing key");
    }

    if (!preg_match(PSHARE_KEY_PATTERN, $tokens[1]))
    {
      die("invalid key");
    }

    $page['pshare_key'] = $tokens[1];
    
    $query = '
SELECT
    *,
    NOW() AS dbnow
  FROM '.PSHARE_KEYS_TABLE.'
  WHERE uuid = \''.$page['pshare_key'].'\'
;';
    $shares = query2array($query);
    if (count($shares) == 0)
    {
      die('unknown key');
    }

    $share = $shares[0];

    pshare_log($share['pshare_key_id'], 'visit');

    // is the key still valid?
    if (strtotime($share['expire_on']) < strtotime($share['dbnow']))
    {
      die('expired key');
    }

    // if the user is permitted for this photo, let's redirect to
    // picture.php (with full details and actions)
    if (!is_a_guest() and pshare_is_photo_visible($share['image_id']))
    {
      // find the first reachable category linked to the photo
      $query = '
SELECT category_id
  FROM '.IMAGE_CATEGORY_TABLE.'
  WHERE image_id = '.$share['image_id'].'
;';

      $authorizeds = array_diff(
        array_from_query($query, 'category_id'),
        explode(',', calculate_permissions($user['id'], $user['status']))
        );

      foreach ($authorizeds as $category_id)
      {
        $url = make_picture_url(
          array(
            'image_id' => $share['image_id'],
            'category' => get_cat_info($category_id),
            )
          );

        if (function_exists('Fotorama_is_replace_picture') and Fotorama_is_replace_picture())
        {
          $url.= '&slidestop';
        }

        redirect($url);
      }
      
      redirect(
        make_picture_url(
          array(
            'image_id' => $share['image_id'],
            )
          )
        );
    }

    $query = '
SELECT *
  FROM '.IMAGES_TABLE.'
  WHERE id = '.$share['image_id'].'
;';
    $rows = query2array($query);
    $image = $rows[0];
    $src_image = new SrcImage($image);

    if (isset($tokens[2]) && 'download' == $tokens[2])
    {
      $format_id = null;

      if (isset($tokens[3]) && preg_match('/^f(\d+)$/', $tokens[3], $matches))
      {
        $format_id = $matches[1];

        $query = '
SELECT
    *
  FROM '.IMAGE_FORMAT_TABLE.'
  WHERE format_id = '.$format_id.'
    AND image_id = '.$image['id'].'
;';
        $formats = query2array($query);

        if (count($formats) == 0)
        {
          do_error(400, 'Invalid request - format');
        }

        $format = $formats[0];

        $file = original_to_format(get_element_path($image), $format['ext']);
        $image['file'] = get_filename_wo_extension($image['file']).'.'.$format['ext'];
      }
      else
      {
        $file = $image['path'];
      }
      
      $gmt_mtime = gmdate('D, d M Y H:i:s', filemtime($file)).' GMT';

      $http_headers = array(
        'Content-Length: '.@filesize($file),
        'Last-Modified: '.$gmt_mtime,
        'Content-Type: '.mime_content_type($file),
        'Content-Disposition: attachment; filename="'.$image['file'].'";',
        'Content-Transfer-Encoding: binary',
        );

      foreach ($http_headers as $header) {
        header($header);
      }
      
      readfile($file);

      pshare_log($share['pshare_key_id'], 'download', $format_id);
        
      exit();
    }

    $template->set_filename('shared_picture', realpath(PSHARE_PATH.'template/shared_picture.tpl'));

    $derivative = new DerivativeImage(ImageStdParams::get_by_type(IMG_MEDIUM), $src_image);

    $derivative_size = $derivative->get_size();
    
    // a random string to avoid browser cache
    $rand = '&amp;download='.substr(md5(time()), 0, 6);
    
    $template->assign(
      array(
        'SRC' => $derivative->get_url(),
        'IMG_WIDTH' => $derivative_size[0],
        'IMG_HEIGHT' => $derivative_size[1],
        'DOWNLOAD_URL' => duplicate_index_url().'/'.$page['pshare_key'].'/download'.$rand,
        )
      );

    // formats
    if (defined('IMAGE_FORMAT_TABLE'))
    {
      $query = '
SELECT *
  FROM '.IMAGE_FORMAT_TABLE.'
  WHERE image_id = '.$share['image_id'].'
;';
      $formats = query2array($query);
  
      if (!empty($formats))
      {
        foreach ($formats as &$format)
        {
          $format['download_url'] = duplicate_index_url().'/'.$page['pshare_key'].'/download';
          $format['download_url'].= '/f'.$format['format_id'].$rand;
          
        $format['filesize'] = sprintf('%.1fMB', $format['filesize']/1024);
        }
      }

      $template->assign('formats', $formats);
    }
  
    $template->parse('shared_picture');
    $template->p();
    
    exit();
  }
}

add_event_handler('loc_end_picture', 'pshare_end_picture');
function pshare_end_picture()
{
  global $conf, $template, $picture, $user;

  if (!pshare_is_active())
  {
    return;
  }
  
  $template->set_prefilter('picture', 'pshare_end_picture_prefilter');
  $template->assign(
    array(
      'PSHARE_IMAGE_ID' => $picture['current']['id'],
      )
    );

  if (!isset($conf['use_pshare_picture_template']) or $conf['use_pshare_picture_template'])
  {
    $template->set_filename('pshare_picture', realpath(PSHARE_PATH.'template/picture.tpl'));
    $template->assign_var_from_handle('PSHARE_CONTENT', 'pshare_picture');
  }
}

function pshare_end_picture_prefilter($content, &$smarty)
{
  $search = '<dl id="standard"';
  
  $replace = '{$PSHARE_CONTENT}'.$search;
  
  $content = str_replace($search, $replace, $content);
  return $content;
}

add_event_handler('ws_add_methods', 'pshare_add_methods');
function pshare_add_methods($arr)
{
  $service = &$arr[0];

  $service->addMethod(
    'pshare.share.create',
    'ws_pshare_share_create',
    array(
      'image_id' => array('type'=>WS_TYPE_ID),
      'email' => array(),
      'expires_in' => array('type'=>WS_TYPE_INT|WS_TYPE_POSITIVE),
      ),
    'Create a new share'
    );

  $service->addMethod(
    'pshare.share.expire',
    'ws_pshare_share_expire',
    array(
      'id' => array('type'=>WS_TYPE_ID),
      ),
    'Expire a share now',
    '',
    array('admin_only'=>true)
    );
}

function ws_pshare_share_create($params, &$service)
{
  global $conf, $user;

  if (!pshare_is_active())
  {
    return new PwgError(401, "permission denied");
  }

  $query = '
SELECT *
  FROM '.IMAGES_TABLE.'
  WHERE id = '.$params['image_id'].'
;';
  $images = query2array($query);

  if (count($images) == 0)
  {
    return new PwgError(404, "image not found");
  }

  $image = $images[0];

  if (!pshare_is_photo_visible($params['image_id']))
  {
    return new PwgError(401, "permissions denied");
  }
  
  if (!email_check_format($params['email']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, l10n('Invalid email address'));
  }
  
  // TODO check the expires_in is in the defined list

  $query = '
SELECT
    NOW(),
    ADDDATE(NOW(), INTERVAL '.$params['expires_in'].' DAY)
;';
  list($now, $expire) = pwg_db_fetch_row(pwg_query($query));

  $key_uuid = pshare_get_key();

  single_insert(
    PSHARE_KEYS_TABLE,
    array(
      'uuid' => $key_uuid,
      'user_id' => $user['id'],
      'image_id' => $params['image_id'],
      'sent_to' => $params['email'],
      'created_on' => $now,
      'duration' => $params['expires_in'],
      'expire_on' => $expire,
      )
    );

  $query = '
SELECT *
  FROM '.PSHARE_KEYS_TABLE.'
  WHERE uuid = \''.$key_uuid.'\'
;';
  $shares = query2array($query);

  if (count($shares) == 0)
  {
    return new PwgError(500, "share not created");
  }

  $share = $shares[0];

  //
  // Send the email
  //
  include_once(PHPWG_ROOT_PATH.'include/functions_mail.inc.php');

  // force $conf['derivative_url_style'] to 2 (script) to make sure we
  // will use i.php?/upload and not _data/i/upload because you don't
  // know when the cache will be flushed
  $previous_derivative_url_style = $conf['derivative_url_style'];
  $conf['derivative_url_style'] = 2;

  $thumb_url = DerivativeImage::thumb_url(
    array(
      'id' => $image['id'],
      'path' => $image['path'],
      )
    );

  // restore configuration setting
  $conf['derivative_url_style'] = $previous_derivative_url_style;

  $link = get_absolute_root_url().'index.php?/pshare/'.$share['uuid'];
  
  $content = '<p style="text-align:center">';
  $content.= l10n('%s has shared a photo with you', $user['username']);
  $content.= '<br><br><a href="'.$link.'"><img src="'.$thumb_url.'"></a>';
  $content.= '<br><br><a href="'.$link.'">'.l10n('click to view').'</a>';
  $content.= '</p>';

  $subject = l10n('Photo shared');

  pwg_mail(
    $params['email'],
    array(
      'subject' => '['. $conf['gallery_title'] .'] '. $subject,
      'mail_title' => $conf['gallery_title'],
      'mail_subtitle' => $subject,
      'content' => $content,
      'content_format' => 'text/html',
      )
    );

  return array(
    'message' => l10n('Email sent to %s', $share['sent_to']),
    );
}

function ws_pshare_share_expire($params, &$service)
{
  global $conf, $user;

  $query = '
SELECT *
  FROM '.PSHARE_KEYS_TABLE.'
  WHERE pshare_key_id = '.$params['id'].'
;';
  $shares = query2array($query);

  if (count($shares) == 0)
  {
    return new PwgError(404, "not found");
  }

  $share = $shares[0];

  list($dbnow) = pwg_db_fetch_row(pwg_query('SELECT NOW()'));

  single_update(
    PSHARE_KEYS_TABLE,
    array('expire_on' => $dbnow),
    array('pshare_key_id' => $params['id'])
    );

  return true;
}

function pshare_get_key()
{
  $candidate = generate_key(30);

  // in very rare cases, with Piwigo <2.8, generate_key may return some "="
  // at the end
  if (!preg_match(PSHARE_KEY_PATTERN, $candidate))
  {
    return pshare_get_key();
  }

  $query = '
SELECT
    COUNT(*)
  FROM '.PSHARE_KEYS_TABLE.'
  WHERE uuid = \''.$candidate.'\'
;';
  list($counter) = pwg_db_fetch_row(pwg_query($query));

  if (0 == $counter)
  {
    return $candidate;
  }
  else
  {
    return pshare_get_key();
  }

}

function pshare_log($key_id, $type='visit', $format_id=null)
{
  global $user;
  
  list($dbnow) = pwg_db_fetch_row(pwg_query('SELECT NOW();'));

  single_insert(
    PSHARE_LOG_TABLE,
    array(
      'pshare_key_idx' => $key_id,
      'occured_on' => $dbnow,
      'type' => $type,
      'ip_address' => $_SERVER['REMOTE_ADDR'],
      'user_id' => $user['id'],
      'format_id' => empty($format_id) ? '' : $format_id,
      )
    );
}

function pshare_is_photo_visible($image_id)
{
  $query='
SELECT id
  FROM '.CATEGORIES_TABLE.'
    INNER JOIN '.IMAGE_CATEGORY_TABLE.' ON category_id = id
  WHERE image_id = '.$image_id.'
'.get_sql_condition_FandF(
  array(
      'forbidden_categories' => 'category_id',
      'forbidden_images' => 'image_id',
    ),
  '    AND'
  ).'
  LIMIT 1
;';

  if (pwg_db_num_rows(pwg_query($query)) < 1)
  {
    return false;
  }

  return true;
}

function pshare_is_active()
{
  global $user;

  $query = '
SELECT
    COUNT(*)
  FROM '.GROUPS_TABLE.'
    JOIN '.USER_GROUP_TABLE.' ON group_id = id
  WHERE user_id = '.$user['id'].'
    AND pshare_enabled = \'true\'
;';
  list($counter) = pwg_db_fetch_row(pwg_query($query));

  if ($counter == 0)
  {
    return false;
  }

  return true;
}
?>
