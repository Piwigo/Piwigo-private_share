{combine_css path="plugins/private_share/css/fontello/css/pshare-fontello.css"}

{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css id='colorbox' path='themes/default/js/plugins/colorbox/style2/colorbox.css'}

<div class="pshare"><a href="#pshare" title="Share" class="pshare-open pshare-icon-share">{'Partager'|translate}</a></div>

{footer_script require='jquery'}
// popup
jQuery('.pshare-open').colorbox({
  inline:true,
  href:"#pshare_form"
});

jQuery('.pshare-close').click(function(e) {
  console.log('salut les truffes');
  jQuery('.pshare-open').colorbox.close();
  e.preventDefault();
});

jQuery('#pshare_form').submit(function(e){
  jQuery.ajax({
    url: "ws.php?format=json&method=pshare.share.create",
    type:"POST",
    data: jQuery(this).serialize(),
    success:function(data) {
      var data = jQuery.parseJSON(data);
      if (data.stat == 'ok') {
        alert("yeah baby");
      }
      else {
        alert("error on buying photo");
      }
    },
    error:function(XMLHttpRequest, textStatus, errorThrows) {
      alert("error while buying photo");
    }
  });

  e.preventDefault();
});
{/footer_script}

{html_style}
.pshare {
  padding: 10px 0 10px 7px;
}

.pshare a {
  text-decoration:none;
}

#pshare_form {
  width:400px;
}

#pshare_form table {
  margin:10px auto;
}

#pshare_form td {
  padding:3px;
}

#pshare_form th {
  text-align:right;
  padding-right:5px;
}

#pshare_form .formActions {
  text-align:center;
}
{/html_style}

<div style="display:none;">
  <form id="pshare_form" action="{$F_ACTION}" method="post">
  <input type="hidden" name="image_id" value="{$PSHARE_IMAGE_ID}">
  <table>
    <tr>
      <th>{'Email address'|@translate}</th>
      <td><input type="text" name="email"></td>
    </tr>
    <tr>
      <th>{'Expires in'|@translate}</th>
      <td>
        <select name="expires_in">
          <option value="7">1 week</option>
          <option value="30">1 month</option>
        </select>
      </td>
    </tr>
  </table>

  <p class="formActions">
    <input type="submit" value="Send email">
    <a href="#pshare-close" class="pshare-close">{'Cancel'|translate}</a>
  </p>

  </form>
</div>