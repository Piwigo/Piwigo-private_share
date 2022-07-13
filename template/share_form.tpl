<form id="pshare_form" action="{$F_ACTION}" method="post">
  {include file='infos_errors.tpl' errors=$share.errors infos=$share.infos}

  <input type="hidden" name="image_id" id="image_id" value="{$PSHARE_IMAGE_ID}">

  <div class="pshare_input_group">
    <label for="email">{'Email address'|translate}</label>  
    <input type="text" name="email" id="email" size="40">
  </div>

  <div class="pshare_input_group">
    <label for="expires_in">{'Expires in'|translate}</label> 
    <select name="expires_in" id="expires_in">
          <option value="7">{'%d week'|translate:1}</option>
          <option value="15">{'%d weeks'|translate:2}</option>
          <option value="30">{'%d month'|translate:1}</option>
          <option value="90">{'%d months'|translate:3}</option>
      </select>
    
  </div>

</form>