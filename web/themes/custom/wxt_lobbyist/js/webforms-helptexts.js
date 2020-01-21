jQuery(document).ready(function($){
  
  

  var myEle = document.getElementById('edit-mail');
    if(myEle){
        $('#edit-mail').attr('data-original-title', 'This should be your business email address. An email address can only be used once to register. This is the email address our system uses to correspond with you.');
    }
  
  var myEle2 = document.getElementById('edit-field-legal-organization-0-value');
    if(myEle2){
        $('#edit-field-legal-organization-0-value').attr('data-original-title', 'In Yukon, this is your legal business name.');
  }
  var myEle6 = document.getElementById('edit-field-operating-organization-0-value');
    if(myEle6){
        $('#edit-field-operating-organization-0-value').attr('data-original-title', 'This is the name your business is commonly known as.');
  }	
  var myEle3 = document.getElementById('edit-field-position-0-value');
    if(myEle3){
        $('#edit-field-position-0-value').attr('data-original-title', 'This is your current job title related to your role as an in-house lobbyist.');
  }	
    
  
  if($('body > div.dialog-off-canvas-main-canvas > div.region.region-header > nav.tabs.mrgn-tp-md.mrgn-bttm-md > ul > li.active:nth-child(3)').length){
    var myEle4 = document.getElementById('edit-field-first-name-consultant-0-value');
    if(myEle4){
      $('#edit-field-first-name-consultant-0-value').attr('data-original-title', 'This is the first name of the consultant');
    }	
    var myEle5 = document.getElementById('edit-field-last-name-consultant-0-value');
    if(myEle5){
      $('#edit-field-last-name-consultant-0-value').attr('data-original-title', 'This is the last name of the consultant');
    }	
  }
  
  	
});