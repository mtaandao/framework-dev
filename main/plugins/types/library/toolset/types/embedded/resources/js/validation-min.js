var typesValidation=(function(f){function g(){f.each(types.validation,function(){c(this.selector);b(this.elements)})}function a(){f.each(types.validation,function(){b(this.elements)})}function c(h){f(h).validate({ignore:'input[type="hidden"], .mncf-form-groups-support-post-type, .mncf-form-groups-support-tax, .mncf-form-groups-support-templates',errorPlacement:function(i,j){i.insertBefore(j)},highlight:function(k,i,j){f("#publishing-action .spinner").css("visibility","hidden");f("#publish").bind("click",function(){f("#publishing-action .spinner").css("visibility","visible")});f(k).parents(".collapsible").slideDown();if(h=="#post"){var l=jQuery(k).parents("postbox");if(l.hasClass("closed")){l.find(".handlediv").trigger("click")}}f(k).parents(".collapsible").slideDown();f("input#publish").addClass("button-primary-disabled");f("input#save-post").addClass("button-disabled");f("#save-action .ajax-loading").css("visibility","hidden");f("#publishing-action #ajax-loading").css("visibility","hidden")},unhighlight:function(k,i,j){f("input#publish, input#save-post").removeClass("button-primary-disabled").removeClass("button-disabled")},invalidHandler:function(m,k){var n=new Array(),m=f(h),o=false;for(var j=0;j<k.errorList.length;j++){var l=k.errorList[j].element;n.push(f(l).attr("id"))}if(h=="#post"&&d(h,n,m,k)){o=true}if(o){f(h).validate().cancelSubmit=true;f(h).submit()}mncfLoadingButtonStop()},errorClass:"mncf-form-error"})}function d(h,p,o,n){var m,j=new Array(),k=new Array();for(var l=0;l<p.length;l++){h=p[l];m=jQuery("#"+h);if(m.length>0){if(e(m)){k.push(h)}else{if(m.parents(".inside").is(":hidden")){m.parents(".postbox").find(".handlediv").trigger("click")}j.push(h)}}}if(j.length>0){return false}else{if(k.length>0){return true}}return false}function e(h){if(h.parents(".mncf-conditional").length>0&&h.parents(".inside").is(":hidden")){if(h.parents(".mncf-conditional").css("display")=="none"){return true}return false}else{return h.parents(".mncf-conditional").length>0&&h.is(":hidden")}}function b(h){f.each(h,function(){element=this;if(f(element.selector).length>0){f.each(element.rules,function(){if(e(f(element.selector))){f(element.selector).rules("remove",this.method)}else{var i={messages:{}};i[this.method]=this.value=="true"?true:this.value;i.messages[this.method]=this.message;f(element.selector).rules("add",i)}})}})}return{init:g,setRules:a,conditionalIsHidden:e}})(jQuery);jQuery(document).ready(function(a){typesValidation.init()});