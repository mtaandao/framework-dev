(function($) {
	var id;
    mn.media.mnUserAvatar = {
		
        get: function() {
            return mn.media.view.settings.post.mnUserAvatarId
        },
        set: function(a) {
            var b = mn.media.view.settings;
            b.post.mnUserAvatarId = a;
            b.post.mnUserAvatarSrc = $('div.attachment-info').find('img').attr('src');
            if (b.post.mnUserAvatarId && b.post.mnUserAvatarSrc) {
                $('#mn-user-avatar'+id).val(b.post.mnUserAvatarId);
                $('#mnua-images'+id+', #mnua-undo-button'+id).show();
                $('#mnua-preview'+id).find('img').attr('src', b.post.mnUserAvatarSrc).removeAttr('height', "");
                $('#mnua-remove-button'+id+', #mnua-thumbnail'+id).hide();
                $('#mn_user_avatar_radio').trigger('click')
            }
            mn.media.mnUserAvatar.frame().close()
        },
        frame: function() {
            if (this._frame) {
                return this._frame
            }
            this._frame = mn.media({
                library: {
                    type: 'image'
                },
                multiple: false,
                title: $('#mnua-add'+id).data('title')
            });
            this._frame.on('open', function() {
                var a = $('#mn-user-avatar'+id).val();
                if (a == "") {
                    $('div.media-router').find('a:first').trigger('click')
                } else {
                    var b = this.state().get('selection');
                    attachment = mn.media.attachment(a);
                    attachment.fetch();
                    b.add(attachment ? [attachment] : [])
                }
            }, this._frame);
            this._frame.state('library').on('select', this.select);
            return this._frame
        },
        select: function(a) {
            selection = this.get('selection').single();
            mn.media.mnUserAvatar.set(selection ? selection.id : -1)
        },
        init: function() {
            $('body').on('click', '#mnua-add', function(e) {
                e.preventDefault();
                e.stopPropagation();
				id='';
                mn.media.mnUserAvatar.frame().open()
            })
            $('body').on('click', '#mnua-add-existing', function(e) {
                e.preventDefault();
                e.stopPropagation();
				id='-existing';
                mn.media.mnUserAvatar.frame().open()
            })
        }
    }
})(jQuery);
jQuery(function($) {
    if (typeof(mn) != 'undefined') {
        mn.media.mnUserAvatar.init()
    }
    $('#your-profile').attr('enctype', 'multipart/form-data');
    var a = $('#mn-user-avatar').val();
    var b = $('#mnua-preview').find('img').attr('src');
    $('body').on('click', '#mnua-remove', function(e) {
        e.preventDefault();
        $('#mnua-original').remove();
        $('#mnua-remove-button, #mnua-thumbnail').hide();
        $('#mnua-preview').find('img:first').hide();
        $('#mnua-preview').prepend('<img id="mnua-original" />');
        $('#mnua-original').attr('src', mnua_custom.avatar_thumb);
        $('#mn-user-avatar').val("");
        $('#mnua-original, #mnua-undo-button').show();
        $('#mn_user_avatar_radio').trigger('click')
    });
    $('body').on('click', '#mnua-undo', function(e) {
        e.preventDefault();
        $('#mnua-original').remove();
        $('#mnua-images').removeAttr('style');
        $('#mnua-undo-button').hide();
        $('#mnua-remove-button, #mnua-thumbnail').show();
        $('#mnua-preview').find('img:first').attr('src', b).show();
        $('#mn-user-avatar').val(a);
        $('#mn_user_avatar_radio').trigger('click')
    })
});
jQuery(function($) {
    if (typeof(mn) != 'undefined') {
        mn.media.mnUserAvatar.init()
    }
    $('#your-profile').attr('enctype', 'multipart/form-data');
    var a = $('#mn-user-avatar-existing').val();
    var b = $('#mnua-preview-existing').find('img').attr('src');
    $('body').on('click', '#mnua-remove-existing', function(e) {
        e.preventDefault();
        $('#mnua-original-existing').remove();
        $('#mnua-remove-button-existing, #mnua-thumbnail-existing').hide();
        $('#mnua-preview-existing').find('img:first').hide();
        $('#mnua-preview-existing').prepend('<img id="mnua-original-existing" />');
        $('#mnua-original-existing').attr('src', mnua_custom.avatar_thumb);
        $('#mn-user-avatar-existing').val("");
        $('#mnua-original-existing, #mnua-undo-button-existing').show();
        $('#mn_user_avatar_radio').trigger('click')
    });
    $('body').on('click', '#mnua-undo-existing', function(e) {
        e.preventDefault();
        $('#mnua-original-existing').remove();
        $('#mnua-images-existing').removeAttr('style');
        $('#mnua-undo-button-existing').hide();
        $('#mnua-remove-button-existing, #mnua-thumbnail-existing').show();
        $('#mnua-preview-existing').find('img:first').attr('src', b).show();
        $('#mn-user-avatar-existing').val(a);
        $('#mn_user_avatar_radio').trigger('click')
    })
});