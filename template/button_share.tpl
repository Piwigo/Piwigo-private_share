

<div class="nav-item pwg-state-default pwg-button"> 
<a href="#pshare_form" title="{'Share'|translate}" id="pshare_form_button" class="pshare_form_popin nav-link" rel="nofollow">
  <i class="pwg-icon pshare-icon-share"></i>
  <span class="pwg-button-text d-lg-none ml-2">{'Share'|translate}</span>
</a>
</div>

{combine_css path=$PSHARE_PATH|cat:'css/fontello/css/pshare-fontello.css'}
{combine_css path=$PSHARE_PATH|cat:'/css/style.css'}

{footer_script require='jquery'}

{* Pass HTML form *}
var pshare_form = `{$PSHARE_FORM}`;

{* Language variable *}
var str_share_title = '{'Share this picture'|translate|escape:javascript}';
var str_send = '{'Send'|translate|escape:javascript}';
var str_cancel = '{'Cancel'|translate|escape:javascript}';
var str_close = '{'Close'|translate|escape:javascript}';
var str_private_share = '{'Share link'|translate|escape:javascript}'
var str_private_share_sent = '{'Your email has been sent'|translate|escape:javascript}'
var str_private_share_error = '{'Your email could not be sent'|translate|escape:javascript}'

{/footer_script}

{combine_script id='jquery.confirm' load='footer' require='jquery' path='themes/default/js/plugins/jquery-confirm.min.js'}
{combine_css path="themes/default/js/plugins/jquery-confirm.min.css"}
{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css id='colorbox' path='themes/default/js/plugins/colorbox/style2/colorbox.css'}

{combine_script id='pshare_common' require='jquery' load='footer' path=$PSHARE_PATH|cat:'template/js/pshareCommon.js'}

{combine_script id='shareForm' require='jquery' load='footer' path=$PSHARE_PATH|cat:'template/js/shareForm.js'}
