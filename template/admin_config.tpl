{combine_script id='LocalStorageCache' load='footer' path='admin/themes/default/js/LocalStorageCache.js'}

{combine_script id='jquery.selectize' load='footer' path='themes/default/js/plugins/selectize.min.js'}
{combine_css id='jquery.selectize' path="themes/default/js/plugins/selectize.{$themeconf.colorscheme}.css"}

{html_style}{literal}
form p {text-align:left;}
{/literal}{/html_style}

{footer_script}
jQuery(document).ready(function() {

  var groupsCache = new GroupsCache({
    serverKey: '{$CACHE_KEYS.groups}',
    serverId: '{$CACHE_KEYS._hash}',
    rootUrl: '{$ROOT_URL}'
  });

  groupsCache.selectize(jQuery('[data-selectize=groups]'));
});
{/footer_script}

<div class="titrePage">
  <h2>{'Configuration'|@translate} - Private Share</h2>
</div>

<form method="post">

  <p>
{if count($groups) > 0}
    <strong>{'Permission granted for groups'|@translate}</strong>
    <br>
    <select data-selectize="groups" data-value="{$groups_selected|@json_encode|escape:html}"
      placeholder="{'Type in a search term'|translate}"
      name="groups[]" multiple style="width:600px;"></select>
{else}
    {'There is no group in this gallery.'|@translate} <a href="admin.php?page=group_list" class="externalLink">{'Group management'|@translate}</a>
{/if}
  </p>

{if count($groups) > 0}
	<p class="formButtons">
		<input type="submit" name="submit" value="{'Save Settings'|@translate}">
	</p>
{/if}

</form>
