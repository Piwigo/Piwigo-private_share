jQuery(document).ready(function () {

  jQuery('#pshare_form_button').on('click',function(){
    sharePopin();
  })

  function sharePopin() {
    $.confirm({
        ...jconfirmConfigPshare,
        boxWidth: '560px',
        title: str_share_title,
        content: pshare_form,
        buttons: {
            formSubmit: {
                text: str_send,
                btnClass: '',
                action: function () {
                  $('#request_form').submit(
                    sendRequest()
                  );
                }
            },
            cancel: {
                text: str_cancel,
            },
        }
    })
  }

  function sendRequest(data) {
    console.log(data);
    jQuery.ajax({
      url: "ws.php?format=json&method=pshare.share.create",
      type:"POST",
      data: {
        image_id : jQuery('#image_id').val(),
        email : jQuery('#email').val(),
        expires_in : jQuery('#expires_in').val()
      },
      success:function(data) {
        var data = jQuery.parseJSON(data);
        if (data.stat == 'ok') {
          jQuery.alert({
            theme: 'modern',
            useBootstrap: false,
            title: str_private_share,
            content: str_private_share_sent
          });
        }
        else {
          jQuery.alert({
            theme: 'modern',
            useBootstrap: false,
            title: str_private_share_error,
            content:data.message
          });
        }
      },
      error: function (e) {
        jQuery.alert({
          theme: 'modern',
          useBootstrap: false,
          title: str_private_share_error,
          content:data.message
        });
      }
    });
  }

});