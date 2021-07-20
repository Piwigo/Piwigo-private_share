{combine_css path="plugins/private_share/css/fontello/css/pshare-fontello.css"}

{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css id='colorbox' path='themes/default/js/plugins/colorbox/style2/colorbox.css'}
{combine_css path='plugins/private_share/css/style.css'}

{footer_script require='jquery'}
// popup
jQuery('.pshare-open').colorbox({
  inline:true,
  href:"#pshare_form"
});

jQuery(document).on('click', '.pshare-close',  function(e) {
  jQuery("#pshare_form .formInfo").css('visibility', 'hidden');
  jQuery("[name=pshare_form]").trigger("reset");
  jQuery('.pshare-open').colorbox.close();
  e.preventDefault();
});

jQuery('#pshare_form').submit(function(e){
  jQuery(".formActions .loading").css("visibility", "visible");

  jQuery.ajax({
    url: "ws.php?format=json&method=pshare.share.create",
    type:"POST",
    data: jQuery(this).serialize(),
    success:function(data) {
      var data = jQuery.parseJSON(data);
      if (data.stat == 'ok') {
        var html_message = '<span class="success">&#x2714; '+data.result.message+'</span>';
        html_message+= ' <a href="#" class="pshare-icon-cancel-circled pshare-close">{'Close'|translate}</a>';

        jQuery("#pshare_form .formInfo")
          .html(html_message)
          .css('visibility', 'visible')
          ;
      }
      else {
        jQuery("#pshare_form .formInfo")
          .html('<span class="error">&#x2718; '+data.message+'</span>')
          .css('visibility', 'visible')
          ;
      }

      jQuery(".formActions .loading").css("visibility", "hidden");
    },
    error:function(XMLHttpRequest, textStatus, errorThrows) {
      alert("error calling Piwigo API");
    }
  });

  e.preventDefault();
});
{/footer_script}

<div style="display:none;">
  <form id="pshare_form" name="pshare_form" action="{$F_ACTION}" method="post">
  <div class="formInfo"><span class="success">&#x2718; oups, problem occured</span> <a href="#pshare-close" class="icon-cancel-circled pshare-close">{'Close'|translate}</a></div>
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
          <option value="7">{'%d week'|translate:1}</option>
          <option value="15">{'%d weeks'|translate:2}</option>
          <option value="30">{'%d month'|translate:1}</option>
          <option value="90">{'%d months'|translate:3}</option>
        </select>
      </td>
    </tr>
  </table>

  <p class="formActions">
    <input type="submit" value="{'Send email'|translate}">
    <a href="#pshare-close" class="pshare-close">{'Cancel'|translate}</a>
    <img class="loading" src="themes/default/images/ajax-loader-small.gif">
  </p>

  </form>
</div>