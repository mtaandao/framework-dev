jQuery(function($) {
  // Add enctype to form with JavaScript as backup
  $('#your-profile').attr('enctype', 'multipart/form-data');
  // Store User Avatar ID
  var mnuaID = $('#mn-user-avatar').val();
  // Store User Avatar src
  var mnuaSrc = $('#mnua-preview').find('img').attr('src');
  // Remove User Avatar
  $('body').on('click', '#mnua-remove', function(e) {
    e.preventDefault();
    $('#mnua-original').remove();
    $('#mnua-remove-button, #mnua-thumbnail').hide();
    $('#mnua-preview').find('img:first').hide();
    $('#mnua-preview').prepend('<img id="mnua-original" />');
    $('#mnua-original').attr('src', mnua_custom.avatar_thumb);
    $('#mn-user-avatar').val("");
    $('#mnua-original, #mnua-undo-button').show();
    $('#mn_user_avatar_radio').trigger('click');
  });
  // Undo User Avatar
  $('body').on('click', '#mnua-undo', function(e) {
    e.preventDefault();
    $('#mnua-original').remove();
    $('#mnua-images').removeAttr('style');
    $('#mnua-undo-button').hide();
    $('#mnua-remove-button, #mnua-thumbnail').show();
    $('#mnua-preview').find('img:first').attr('src', mnuaSrc).show();
    $('#mn-user-avatar').val(mnuaID);
    $('#mn_user_avatar_radio').trigger('click');
  });
  
  // Store MN Existing User Avatar ID
  var mnuaEID = $('#mn-user-avatar-existing').val();
  // Store MN Existing User Avatar src
  var mnuaESrc = $('#mnua-preview-existing').find('img').attr('src');
  // Remove MN Existing User Avatar
  $('body').on('click', '#mnua-remove-existing', function(e) {
    e.preventDefault();
    $('#mnua-original-existing').remove();
    $('#mnua-remove-button-existing, #mnua-thumbnail-existing').hide();
    $('#mnua-preview-existing').find('img:first').hide();
    $('#mnua-preview-existing').prepend('<img id="mnua-original-existing" />');
    $('#mnua-original-existing').attr('src', mnua_custom.avatar_thumb);
    $('#mn-user-avatar-existing').val("");
    $('#mnua-original-existing, #mnua-undo-button-existing').show();
    $('#mn_user_avatar_radio-existing').trigger('click');
  });
  // Undo MN Existing User Avatar
  $('body').on('click', '#mnua-undo-existing', function(e) {
    e.preventDefault();
    $('#mnua-original-existing').remove();
    $('#mnua-images-existing').removeAttr('style');
    $('#mnua-undo-button-existing').hide();
    $('#mnua-remove-button-existing, #mnua-thumbnail-existing').show();
    $('#mnua-preview-existing').find('img:first').attr('src', mnuaSrc).show();
    $('#mn-user-avatar-existing').val(mnuaID);
    $('#mn_user_avatar_radio-existing').trigger('click');
  });
});
