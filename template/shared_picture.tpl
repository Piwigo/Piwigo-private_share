<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<title>{$TITLE}</title>
<link rel="stylesheet" type="text/css" href="plugins/private_share/css/fontello/css/pshare-fontello.css">
<style>
body {
  background-color:#191919;
  color:#c9c9c9;
  font-family:sans-serif;
}

.image {
  text-align:center;
  width: {$IMG_WIDTH}px;
  height: {$IMG_HEIGHT}px;
  position: absolute;
  left: 50%;
  top: 50%; 
  margin-left: -{$IMG_WIDTH/2}px;
  margin-top: -{$IMG_HEIGHT/2}px;
}

.downloadLinks {
  margin-top:20px;
}

.downloadLinks a {
  padding:5px 10px;
  text-decoration:none;
  background-color:#666;
  border-radius:5px;
  color:#ccc;
}

.downloadLinks a:hover {
  color:white;
}

.downloadformatDetails {
  display:none;
}
</style>
</head>
<body>
<div class="image">
<img src="{$SRC}">
<div class="downloadLinks">
  <a class="pshare-icon-down-circled2" href="{$DOWNLOAD_URL}">{'Original'|@translate}</a>
{foreach from=$formats item=format}
  <a class="pshare-icon-down-circled2" href="{$format.download_url}">{$format.ext|upper}<span class="downloadformatDetails"> ({$format.filesize})</span></a>
{/foreach}
</div>
</div>
</body>
</html>
