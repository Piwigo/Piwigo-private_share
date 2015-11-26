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
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);

  text-align:center;
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
</style>
</head>
<body>
<div class="image">
<img src="{$SRC}">
<div class="downloadLinks"><a class="pshare-icon-down-circled2" href="{$DOWNLOAD_URL}">download</a></div>
</div>
</body>
</html>