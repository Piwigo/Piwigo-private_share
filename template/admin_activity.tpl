{combine_css path="plugins/private_share/css/fontello/css/pshare-fontello.css"}

{combine_script id='jquery.dataTables' load='footer' path='themes/default/js/plugins/jquery.dataTables.js'}

{html_style}
.sorting { background: url({$ROOT_URL}themes/default/js/plugins/datatables/images/sort_both.png) no-repeat center right; cursor:pointer; }
.sorting_asc { background: url({$ROOT_URL}themes/default/js/plugins/datatables/images/sort_asc.png) no-repeat center right; }
.sorting_desc { background: url({$ROOT_URL}themes/default/js/plugins/datatables/images/sort_desc.png) no-repeat center right; }

.sorting, .sorting_asc, .sorting_desc { 
	padding: 3px 18px 3px 10px;
}
.sorting_asc_disabled { background: url({$ROOT_URL}themes/default/js/plugins/datatables/images/sort_asc_disabled.png) no-repeat center right; }
.sorting_desc_disabled { background: url({$ROOT_URL}themes/default/js/plugins/datatables/images/sort_desc_disabled.png) no-repeat center right; }

.dtBar {
	text-align:left;
	padding: 10px 0 10px 20px
}
.dtBar DIV{
	display:inline;
	padding-right: 5px;
}

.dataTables_paginate A {
	padding-left: 3px;
}

.activityActions {
  text-align:left;
}

.activityActions .loading {
  visibility:hidden;
}
{/html_style}

<h2>{'Activity'|@translate} - Private Share</h2>

{footer_script}
var oTable = jQuery('#activityTable').dataTable({});

jQuery(document).ready(function() {
  jQuery(document).on('click', '.expire',  function(e) {
    var $this = jQuery(this);

    $this.closest("td").find(".loading").css("visibility", "visible");

    jQuery.ajax({
      url: "ws.php?format=json&method=pshare.share.expire",
      type:"POST",
      data: {
        id: $this.data("id")
      },
      success:function(data) {
        var data = jQuery.parseJSON(data);
        if (data.stat == 'ok') {
          $this.closest("td").html("{'expired'|translate}");
        }
        else {
          alert("error on pshare.share.expire");
        }
      },
      error:function(XMLHttpRequest, textStatus, errorThrows) {
        alert("serious error on pshare.share.expire");
      }
    });

    e.preventDefault();
  });
});
{/footer_script}

<table id="activityTable">
<thead>
<tr class="throw">
	<th class="dtc_date">{'Creation date'|@translate}</th>
	<th class="dtc_user">{'User'|@translate}</th>
	<th class="dtc_user">{'Photo'|@translate}</th>
	<th class="dtc_user">{'Recipient'|@translate}</th>
	<th class="dtc_date">{'Expiration'|@translate}</th>
  <th><span class="icon-eye"></span></th>
  <th><span class="pshare-icon-down-circled2"></span></th>
  <th></th>
</tr>
</thead>

{foreach from=$activity_lines item=activity}
{strip}
<tr>
<td>{$activity.created_on}</td>
<td>{$activity.user}</td>
<td>{$activity.photo}</td>
<td>{$activity.sent_to}</td>
<td>{$activity.expire_on}</td>
<td>{$activity.visit}</td>
<td>{$activity.download}</td>
<td class="activityActions">
  {if $activity.expired}
  {'expired'|translate}
  {else}
  <a href="#expire" class="icon-cancel-circled expire" data-id="{$activity.pshare_key_id}">expire</a> <img class="loading" src="themes/default/images/ajax-loader-small.gif">
  {/if}
</td>
</tr>
{/strip}
{/foreach}
</table>