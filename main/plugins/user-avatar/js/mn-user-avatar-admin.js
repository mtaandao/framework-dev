jQuery(function($) {
  // Show size info only if allow uploads is checked
  $('#mn_user_avatar_allow_upload').change(function() {
    $('#mnua-contributors-subscribers').slideToggle($('#mn_user_avatar_allow_upload').is(':checked'));
  });
  // Show resize info only if resize uploads is checked
  $('#mn_user_avatar_resize_upload').change(function() {
     $('#mnua-resize-sizes').slideToggle($('#mn_user_avatar_resize_upload').is(':checked'));
  });
  // Hide Gravatars if disable Gravatars is checked
  $('#mn_user_avatar_disable_gravatar').change(function() {
    if($('#mn-avatars').length) {
      $('#mn-avatars, #avatar-rating').slideToggle(!$('#mn_user_avatar_disable_gravatar').is(':checked'));
      $('#mn_user_avatar_radio').trigger('click');
    }
  });
  // Add size slider
  $('#mnua-slider').slider({
    value: parseInt(mnua_admin.upload_size_limit),
    min: 0,
    max: parseInt(mnua_admin.max_upload_size),
    step: 1,
    slide: function(event, ui) {
      $('#mn_user_avatar_upload_size_limit').val(ui.value);
      $('#mnua-readable-size').html(Math.floor(ui.value / 1024) + 'KB');
      $('#mnua-readable-size-error').hide();
      $('#mnua-readable-size').removeClass('mnua-error');
    }
  });
  // Update readable size on keyup
  $('#mn_user_avatar_upload_size_limit').keyup(function() {
    var mnuaUploadSizeLimit = $(this).val();
    mnuaUploadSizeLimit = mnuaUploadSizeLimit.replace(/\D/g, "");
    $(this).val(mnuaUploadSizeLimit);
    $('#mnua-readable-size').html(Math.floor(mnuaUploadSizeLimit / 1024) + 'KB');
    $('#mnua-readable-size-error').toggle(mnuaUploadSizeLimit > parseInt(mnua_admin.max_upload_size));
    $('#mnua-readable-size').toggleClass('mnua-error', mnuaUploadSizeLimit > parseInt(mnua_admin.max_upload_size));
  });
  $('#mn_user_avatar_upload_size_limit').val($('#mnua-slider').slider('value'));
});
