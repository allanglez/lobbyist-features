var waitForEl = function(selector, callback) {
	if (jQuery(selector).length) {
	  callback();
	} else {
	  setTimeout(function() {
		waitForEl(selector, callback);
	  }, 100);
	}
};
var waitForInhouseEl = function(tabId, callback) {
  if (jQuery("a.use-ajax").attr("href","/quicktabs/nojs/account_home/" + tabId)) {
      callback();
  } else {
      setTimeout(function() {
         waitForEl(selector, callback);
      }, 100);
  }
};

var waitForConsultantEl = function(tabId, callback) {
  if (jQuery("a.use-ajax").attr("href","/quicktabs/nojs/consultant_account_home/" + tabId)) {
      callback();
  } else {
      setTimeout(function() {
         waitForEl(selector, callback);
      }, 100);
  }
};

(function ($, Drupal) {
    Drupal.behaviors.modalDesktop = {
        attach: function (context, settings) {
            var selector = 'div:contains("You do not appear to be a lobbyist"):last';
            var trigger_selector = '.ui-front.ui-dialog-content.ui-widget-content';
            var page_selector = '.webform-submission-form';
            var next_selector = '.webform-button--next';
            var back_selector = '.webform-button--previous';
            var previous_selector = '.webform-button--previous';
            
            $('body', context).once('select2-group').each(function () {
                //Collapse the group when is clicked on element
    		    $("body").on('click', '.select2-results__group', function() {
                    $(this).siblings().slideToggle();
                });
                
                //Collapse the groups the first time select2 is opened
                waitForEl("body .select2-results__group", function() {
                    $("body .select2-results__group").siblings().toggle();
                });

                //Check if need change the tab positions in lobbyist dashboard, this depends on query params
                var urlParams = new URLSearchParams(window.location.search);
                if(urlParams.has('qt-account_home')) {
                    var tabId = urlParams.get('qt-account_home');
                    waitForInhouseEl(tabId, function() {
                            setTimeout(function() {
                                 (jQuery("a.use-ajax").attr("href","/quicktabs/nojs/account_home/"+tabId)).click();
                            }, 50);
                    });
                }else if (urlParams.has('qt-consultant_account_home')) {
                    var tabId = urlParams.get('qt-consultant_account_home');
                    waitForConsultantEl(tabId, function() {
                            setTimeout(function() {
                                 (jQuery("a.use-ajax").attr("href","/quicktabs/nojs/consultant_account_home/"+tabId)).click();
                            }, 50);
                    });
                }
            });

            //Add the href attribute if the element exists
            $('.modal-link-desktop', context).once('modalDesktop').each(function () {
    		    $('.modal-link-desktop'). attr("href", '/form/lobbyist-finder-modal');
            });

            //Show/hide buttons in modal if exist an option to selected  
            $('fieldset.js-webform-buttons input', context).once('modalOptions').click(function(){
		        if(
    			    event.currentTarget.value == 'No' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_5_1' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_6' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_6_1' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_6_2' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_6_3' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_6_4' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_6_5' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_6_6' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_7' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_9' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_10' &&
    			    $(page_selector).children().first().attr('data-webform-key') != 'page_11'
                ) {
    			    $('.js-form-wrapper .js-webform-webform-buttons').hide();
    			    $('.webform-button--previous').hide();
    			    $(back_selector).hide();
    			    $(next_selector).hide();
    		    } else {
    			    $(next_selector).click();
    		    }
            });

            //Close modal when clicked on the close icon
            $('.ui-button.ui-corner-all.ui-widget.ui-button-icon-only.ui-dialog-titlebar-close', context).once('button-titlebar').each(function () {
    		    $('.ui-button.ui-corner-all.ui-widget.ui-button-icon-only.ui-dialog-titlebar-close').on('click', function(event) {
                    location.reload();
	            });
            });

            //Canada will be select as a default country in the address 
            $("#edit-field-street-address-0-address-country-code--2", context).once('address').each(function () {
                if($('#edit-field-street-address-0-address-address-line1').val().length == 0 ){
                    $('#edit-field-street-address-0-address-country-code--2').val('CA').trigger('change');
                }
            });
        } 
    };
})(jQuery, Drupal);