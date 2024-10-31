function gswish_check_payment_status(rpt) {
  //   jQuery.ajax({
  //     url: giveswish.ajaxurl,
  //     dataType: 'json',
  //     type: 'post',
  //     data: {
  //       action: 'giveswish_check_payment_status',
  //       nonce: giveswish.nonce,
  //       payment_id: giveswish.payment_id,
  //     },
  //     success: function (response) {
  //       console.log(response);
  //       //   if (response.status == 'success') {
  //       //     // close popup
  //       //     jQuery.magnificPopup.close();
  //       //     //redirect to response.uri
  //       //     window.location.href = response.uri;
  //       //   } else {
  //       //   }
  //     },
  //   });

  var data = {
    action: 'giveswish_check_payment_status',
    nonce: giveswish.nonce,
    payment_id: giveswish.payment_id,
  };
  if (!rpt) {
    jQuery.post(giveswish.ajax_url, data, function (response) {
      // console.log('RPT False:' + response);
      response = JSON.parse(response);
      if (response.status == 'success') {
        jQuery.magnificPopup.close();
        window.location.replace(response.uri);
      } else {
        jQuery('.giveswish-payment-popup-heading').html(`${response.message}. Please try again`);
        setTimeout(function () {
          jQuery.magnificPopup.close();
          window.location.replace(response.uri);
        }, 1500);
      }
    });
  } else {
    jQuery.post(giveswish.ajax_url, data, function (response) {
      //console.log('RPT true: :: ' + response);
      response = JSON.parse(response);
      if (response.status == 'success') {
        jQuery.magnificPopup.close();
        window.location.replace(response.uri);
      } else {
        return;
      }
    });
  }
}

// ajax call to post payment status

function giveswish_update_payment_status(status) {
  // if status is empty then set it to 'cancelled'
  if (status == '' || status == null || status == undefined) {
    status = 'cancelled';
  }

  jQuery.ajax({
    url: giveswish.ajax_url,
    dataType: 'json',
    type: 'post',
    data: {
      action: 'giveswish_update_payment_status',
      nonce: giveswish.nonce,
      payment_id: giveswish.payment_id,
      status: status,
    },
    success: function (response) {
      if (response.status == 'success') {
        jQuery.magnificPopup.close();
        window.location.replace(response.uri);
      }
    },
  });
}
