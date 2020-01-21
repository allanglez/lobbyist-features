jQuery(document).ready(function($){
  if ($(".webform-submission-data--view-mode-preview")[0]){
    $("body.page-node-type-webform").addClass("previewing-webform");
    $(".previewing-webform #wb-cont").append("<span class='preview-steps-span'>Step 2 of 2</span>");
      //$( "#edit-actions-preview-prev" ).prepend( "<p>Test</p>");
    var div =  document.createElement("div");
    div.id = "checkbox-container-preview";
    var input = document.createElement("input");
    input.type = "checkbox";
    input.id = "confirm-checkbox";
    var label = document.createElement('label')
    label.htmlFor = "confirm-checkbox";
    label.appendChild(document.createTextNode('I confirm that the information I’m about to submit is accurate and I am legally permitted to do so.'));
    input.appendChild(label);
    div.appendChild(input);
    div.appendChild(label);
    var a2 = document.getElementById('wb-cont');
    $("<div id='preview-header-container'><label>These are all the details of your submission. If you want to make changes click the edit details button below. If you are happy with what’s here, chose finish and go to payment.</label></div>").insertAfter(a2);
    var y = document.getElementsByClassName('webform-button--submit');
    var a = y[0];
    a.parentNode.insertBefore(div,a);
  $('#confirm-checkbox:checkbox').click(function() {
    var $checkbox = $(this), checked = $checkbox.is(':checked');
    $checkbox.closest('#confirm-checkbox').find(':checkbox').prop('disabled', checked);
    if(checked){
        $('.webform-button--submit').removeClass('hidden');
    }else{
        $('.webform-button--submit').addClass('hidden');
    }  
    $checkbox.prop('disabled', false);
    }); 
  } else {
    $("body.page-node-type-webform").removeClass("previewing-webform");
  } 	
  if ($("#edit-submit-search-activity")[0]){
    $("#edit-submit-search-activity").removeClass('btn');   
  }
  //DashboardsSettingsForm
  //biz_block_plugin

  (function ($, Drupal, drupalSettings) {
      /**
       * @namespace
       */
       console.log( Drupal.behaviors.biz_block_plugin);
/*
      Drupal.behaviors.DashboardsSettingsForm = {
        attach: function (context) {
          var data = DashboardsSettingsForm;
        }
      };
*/
  })(jQuery, Drupal, drupalSettings);


});
