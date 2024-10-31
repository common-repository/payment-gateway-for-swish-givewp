/**
 *  Giveswish JS
 *  @author:  Proloy Bhaduri <support@proloybhaduri.com>
 *  @version: 1.0.0
 *  @since:   1.0.0
 */

const $iframe = jQuery('iframe[name="give-embed-form"]');
$iframe.on('load', function () {
  if ($iframe.contents().find('body').html() == '') {
    trigger_magnific_popup();
    countdown('timer-countdown', 3, 0);
  }
});
function trigger_magnific_popup() {
  jQuery.magnificPopup.open({
    items: {
      //src: $phtml,
      src: `<div class="white-popup mfp-with-anim">
      <h4 class="giveswish-payment-popup-heading">Please Open Swish App and Complete payment within 3 minutes </h4>
         <div class="give-swish-timer-countdown" id="timer-countdown">03:00</div>
         <span class="giveswish-notice-info">Please wait! You'll be redirected once the payment is completed.</span>
         <a href="javascript:void(0);" class="giveswish-btn giveswish-error-btn giveswish-cancel-payment">Cancel</a>
      </div>`,
      type: 'inline',
    },
    closeOnBgClick: false,
    closeOnContentClick: false,
    closeBtnInside: false,
    showCloseBtn: false,
    enableEscapeKey: false,
    removalDelay: 500, //delay removal by X to allow out-animation
    callbacks: {
      beforeOpen: function () {
        this.st.mainClass = 'mfp-zoom-in';
      },
      open: function () {
        setInterval(function () {
          gswish_check_payment_status(true);
        }, 7000);
      },
    },
  });
}

// -------- coundown-timer-----

//if (jQuery('#timer-countdown').length) {
function countdown(elementName, minutes, seconds) {
  var element, endTime, hours, mins, msLeft, time;
  function twoDigits(n) {
    return n <= 9 ? '0' + n : n;
  }
  function updateTimer() {
    msLeft = endTime - +new Date();
    if (msLeft < 1000) {
      element.innerHTML = '00:00';
      // set background to red
      jQuery('.give-swish-timer-countdown').css('background-color', 'red');
      gswish_check_payment_status(false);
    } else {
      time = new Date(msLeft);
      hours = time.getUTCHours();
      mins = time.getUTCMinutes();
      element.innerHTML =
        (hours ? hours + ':' + twoDigits(mins) : mins) + ':' + twoDigits(time.getUTCSeconds());
      setTimeout(updateTimer, time.getUTCMilliseconds() + 500);
    }
  }
  element = document.getElementById(elementName);
  endTime = +new Date() + 1000 * (60 * minutes + seconds) + 500;
  updateTimer();
}
//}

// ajax call to check if payment is completed

// on clicking cancel button
jQuery(document).on('click', '.giveswish-cancel-payment', function () {
  giveswish_update_payment_status('cancelled');
});
