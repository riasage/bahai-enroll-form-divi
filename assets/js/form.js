(function($){
  function updateButton(){
    var agreed = $('#befd-agree').is(':checked');
    var $btn = $('.befd-send');
    if(agreed){
      $btn.prop('disabled', false).addClass('enabled');
    } else {
      $btn.prop('disabled', true).removeClass('enabled');
    }
  }
  $(document).on('change','#befd-agree', updateButton);
  $(document).ready(function(){
    updateButton();
    // reCAPTCHA v3
    if(window.BEFD && BEFD.recaptchaSiteKey){
      var s = document.createElement('script');
      s.src = 'https://www.google.com/recaptcha/api.js?render=' + BEFD.recaptchaSiteKey;
      s.onload = function(){
        grecaptcha.ready(function() {
          grecaptcha.execute(BEFD.recaptchaSiteKey, {action: 'submit'}).then(function(token) {
            $('#g-recaptcha-response').val(token);
          });
        });
      };
      document.head.appendChild(s);
    }
  });
})(jQuery);