(function ($, Drupal) {
  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {
      console.log(settings);
      // Using once() with more complexity.
      $('#btn-confirm', context).once('bottonConfirm').each(function () {
        var varsToPatch;
    	 	var sid, webformId,flagToModify;
    	 	var base_url = "http://pr-14-lobbyist-frontend.pantheonsite.io/"
    		$(".delete-action, .accept-action").on("click", function(e){
    		  varsToPatch = $(this).children("div").attr("data");
    		  [sid, webformId,flagToModify] = varsToPatch.split('&');
    		  $("#confirmModal").modal('show');
    		});
  
    		$("#modal-btn-yes").on("click", function(){
      		switch(flagToModify){
        		case "active":
        		  getCsrfToken(function (csrfToken) {
        			  patchNode(csrfToken, { "status": flagToModify });
        			});
        		break;
        		default:
        		  getCsrfToken(function (csrfToken) {
        			  patchNode(csrfToken, { "deleted": true });
        			});
        		break;
      		}
    		});
  		  
    		$("#modal-btn-no").on("click", function(){
    		  $("#confirmModal").modal('hide');
    		});
      });
    } 
  };
  function getCsrfToken(callback) {
  	$.ajax({
  	    url: base_url + 'rest/session/token',
  	    method: 'GET',
  	    cors: true,
  	    headers: {
  	      'Content-Type': 'application/json',
  	    },
  	    success: function (data) {
  		    var csrfToken = data;
  			callback(csrfToken);
  	    }
    });
  }
  
  function patchNode(csrfToken, data) {
    $.ajax({
      url: base_url + 'webform_rest/' + webformId + '/submission/' + sid,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      data: JSON.stringify(data),
      success: function (data) {
  	    $("#confirmModal").modal('hide');
  	    location.reload(true);
      }
    });
  }
})(jQuery, Drupal);