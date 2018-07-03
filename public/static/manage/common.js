/**
 * 哈哈哈，后面果断要模块化
 * 嗯，先凑合用着
 */

// common_alert
window.alert = window.commonAlert = function(text, code) {
	if(code == 1) {
		class_name = 'am-text-success';
		icon_name = 'am-icon-check-square';
	} else {
		class_name = 'am-text-danger';
		icon_name = 'am-icon-times-circle';
	}

	message = '<p class="alert-icon"><span class="' + icon_name + ' am-icon-lg ' + class_name + '"></span></p>';
	message += '<p class="' + class_name + '">' + text + '</p>';
	alertify.alert(message).setting({
		basic: true,
		transition: 'fade',
	});
};
// commonAlert('登录名称不能为空');

// confirm_alert
window.confirmAlert = function(text, success, error) {
	var onSuccess = function() {
		success && success();
	};
	var onError = function() {
		error && error();
	};
	alertify.confirm(text).setHeader('温馨提醒').setting({
		labels: {
			ok: '确定',
			cancel: '取消',
		},
		transition: 'fade',
		onok: onSuccess,
		oncancel: onError,
		onclose: onError
	});
};
// confirmAlert('确定要删除吗?');

// prompt_alert
window.promptAlert = function(text, value, success, error) {
	var onSuccess = function(event, value) {
		success && success(value);
	};
	var onError = function() {
		error && error();
	};
	alertify.prompt('', value).setHeader(text).setting({
		labels: {
			ok: '确定',
			cancel: '取消',
		},
		transition: 'fade',
		onok: onSuccess,
		oncancel: onError,
		onclose: onError
	});
};
// promptAlert('请输入姓名', '张三');

// jump_alert
window.jumpAlert = function(text, code, url, wait) {

	wait || (wait = 30);
	url || (url = '');
	var class_name, icon_name;
	if(code == 1) {
		class_name = 'am-text-success';
		icon_name = 'am-icon-check-square';
	} else {
		class_name = 'am-text-danger';
		icon_name = 'am-icon-times-circle';
	}

	var onJump = function() {
		if(url.lastIndexOf('history.go(') >= 0) {
			eval(url);
		} else {
			location.href = url;
		}
	};

	var url_text = '';
	if(url.lastIndexOf('history.go(') >= 0) {
		url_text = 'javascript:' + url;
	} else {
		url_text = url;
	}

	message = '<p class="alert-icon"><span class="' + icon_name + ' am-icon-lg ' + class_name + '"></span></p>';
	message += '<p class="' + class_name + '">' + text + '</p>';
	message += '<p class="alert-link"><a href="' + url_text + '">' + wait + ' 秒后自动跳转链接</a></p>';
	alertify.alert(message).setting({
		basic: true,
		transition: 'fade',
		oncancel: onJump,
		onclose: onJump
	});

	setTimeout(function() {
		onJump();
	}, wait * 1000);
};
// jumpAlert('添加记录成功!', 1);

// ajax_success
window.ajaxSuccess = function(data) {

	ajaxDone();

	if(data.url) {
		jumpAlert(data.msg, data.code, data.url, data.wait);
	} else {
		commonAlert(data.msg, data.code);
	}
};

// ajax_error
window.ajaxError = function() {

	ajaxDone();

	commonAlert('网络链接错误');
};

// ajax_done
window.ajaxDone = function() {
	// remove ajax-disabled
	$('.ajax-disabled').removeClass('ajax-disabled');
};

// upload_file
window.uploadFile = function(option) {
	var form_data = new FormData();
	form_data.append('upload_option', option.option);
	form_data.append('upload_file', option.file);
	var ajax_option = {
		url: baseApi.upload,
		type: 'post',
		data: form_data,
		dataType: 'json',
		timeout: 0,
		processData: false,
		contentType: false,
		xhr: function() {
			var xhr = $.ajaxSettings.xhr();
			xhr.upload.onprogress = function(event) {
				var percent = 0,
					position = event.loaded || event.position,
					total = event.total;
				if(event.lengthComputable) {
					percent = position / total * 100;
				}
				if(percent > 100) {
					percent = 100;
				}
				option.progress && option.progress(position, total, percent.toFixed(2));
			};
			return xhr;
		},
		complete: function() {
			option.complete && option.complete();
		},
		success: function(res) {
			option.success && option.success(res);
		},
		error: function(xhr) {
			option.error && option.error(xhr, '网络链接错误');
		}
	};
	window.uploadObject = $.ajax(ajax_option);
};

// upload_cancel
window.uploadCancel = function() {
	window.uploadObject && window.uploadObject.abort();
};

$(function() {

	// nd_refresh
	$('.nd-refresh').on('click', function() {
		history.go(0);
	});

	// nd_backward
	$('.nd-backward').on('click', function() {
		history.go(-1);
	});

	// nd_jump
	$('.nd-jump').on('click', function() {
		var url = $(this).attr('url');
		url && (location.href = url);
	});

	// nd_color
	$('.nd-color').colorPicker && $('.nd-color').colorPicker();

	// nd_tag
	$('.nd-tag').tagEditor && $('.nd-tag').tagEditor({
		forceLowercase: false
	});

	// nd_input
	$('.nd-input').on('change', function() {
		var url = $(this).attr('url'),
			value = $(this).val();
		if(url) {
			url = url.replace('xxxxxx', value);
			$.ajax({
				url: url,
				type: 'get',
				dataType: 'json',
				success: ajaxSuccess,
				error: ajaxError
			});
		}
	});

	// nd_upload
	$('.nd-upload-file').on('change', function() {
		var $this = $(this),
			target = $this.attr('nd-target'),
			$target_input,
			preview = $this.attr('nd-preview'),
			$preview_div,
			upload_file = $this.get(0).files[0],
			upload_option = $this.attr('nd-option'),
			$upload_span = $($this.parent().find('span')[0]);

		if(typeof upload_file == 'undefined') {
			return false;
		}

		if(target) {
			$target_input = $('#' + target);
		}

		if(preview) {
			$preview_div = $('#' + preview);
		}

		var option = {
			file: upload_file,
			option: upload_option ? upload_option : '{}',
			progress: function(position, total, percent) {
				$upload_span && $upload_span.html('<span class="am-text-warning">' + percent + '%</span>');
			},
			complete: function() {
				setTimeout(function() {
					$upload_span && $upload_span.html('选择文件');
				}, 3000);
			},
			success: function(res) {
				if(res.code == 1) {
					$target_input && $target_input.val(res.data.url);
					$preview_div && $preview_div.css('background-image', 'url(' + res.data.url + ')');
				} else {
					$upload_span && $upload_span.html('<span class="am-text-danger">' + res.msg + '</span>');
				}
			},
			error: function(xhr, info) {
				$upload_span && $upload_span.html('<span class="am-text-danger">' + info + '</span>');
			}
		};
		uploadFile(option);
	});

	// nd_editor_ace
	$('.nd-editor-ace').each(function() {
		var $this = $(this),
			type = $this.attr('nd-type'),
			target = $this.attr('nd-target'),
			value = $this.html();

		// json
		if(type == 'json') {
			value = JSON.stringify(JSON.parse(value), null, 4);
		}

		var $pre = $('<pre id="' + target + '">' + value + '</pre>');
		$pre.insertAfter($this);

		require(['ace/ace'], function(ace) {
			var editor = ace.edit(target);
			editor.session.setMode('ace/mode/' + type);

			editor.getSession().setUseWrapMode(true);
			editor.setAutoScrollEditorIntoView(true);
			editor.setOption("minLines", 10);
			editor.setOption("maxLines", 15);

			$this.attr('readonly') && editor.setReadOnly(true);

			editor.getSession().on('change', function(e) {
				$this.html(editor.getValue());
			});
		});
	});

	// nd_editor_wang
	$('.nd-editor-wang').each(function() {
		var $this = $(this),
			target = $this.attr('nd-target');
		require(['beautify-html', 'ace/ace', 'ace/mode/html', 'wangEditor'], function(html_beautify, ace) {

			// close log
			wangEditor.config.printLog = false;

			var editor = new wangEditor($this.get(0));

			// upload
			editor.config.uploadImgUrl = baseApi.upload_wang;
			editor.config.uploadImgFileName = 'upload_file';

			// filter
			editor.config.jsFilter = false;

			// create
			editor.create();

			// image
			editor.config.uploadImgFns.onload = function(resultText, xhr) {
				editor.command(null, 'insertHtml', '<img src="' + resultText + '" />');
			};

			// source
			var source = editor.menus.source;
			source.updateSelectedEvent = function() {
				if(source.isShowCode == true) {
					if(!source.isShow) {
						source.isShow = true;

						var textarea = source.$codeTextarea[0];
						textarea.value = html_beautify.html_beautify(textarea.value);

						// add target
						$(textarea).parent().append('<pre id="' + target + '" style="min-height:' + $(textarea).height() + 'px;"></pre>');
						$(textarea).addClass('am-hide');

						var ed = ace.edit(target);
						ed.setValue(textarea.value);
						ed.session.setMode('ace/mode/html');
						ed.setOption("minLines", 20);

						ed.getSession().setUseWrapMode(true);
						ed.setAutoScrollEditorIntoView(true);

						ed.getSession().on('change', function(e) {
							textarea.value = ed.getValue();
						});

					}
				} else {
					$this.parent().find('.ace_editor').remove();
					source.isShow = false;
				}
				return source.isShowCode;
			}

			// fullscreen
			var fullscreen = editor.menus.fullscreen;
			fullscreen.updateSelectedEvent = function() {
				if(editor.isFullScreen == true) {
					$('body').addClass('editor-fullscreen');
				} else {
					$('body').removeClass('editor-fullscreen');
				}
				return editor.isFullScreen;
			}

		});
	});

	// nd_search
	$('.nd-search').on('click', function() {
		var target = $(this).attr('target-form'),
			url = $(this).attr('url');
		if(!url && target) {
			url = $('.' + target).attr('action');
		}
		if(url) {
			url = url.replace('.html', '');
			$('.nd-search-field').each(function() {
				var name = $(this).attr('name');
				var value = $(this).val();
				if(name && value && value != '**') {
					url += '/' + name + '/' + value;
				}
			});
			url += '.html';
			location.href = url;
		}
	});

	// ajax_get
	$('.ajax-get').click(function() {

		// add ajax-disabled
		if($(this).hasClass('ajax-disabled')) {
			return false;
		}
		$(this).addClass('ajax-disabled');

		var that = this;
		var target = $(that).attr('href') || $(that).attr('url');
		if(target) {
			var ajax_func = function() {
				$.ajax({
					url: target,
					type: 'get',
					dataType: 'json',
					success: ajaxSuccess,
					error: ajaxError
				});
			};
			if($(that).hasClass('ajax-confirm')) {
				confirmAlert('确认要执行该操作吗?', function() {
					ajax_func();
				}, function() {
					ajaxDone();
				});
			} else {
				ajax_func();
			}
		}
		return false;
	});

	// ajax_post
	$('.ajax-post').click(function() {

		// add ajax-disabled
		if($(this).hasClass('ajax-disabled')) {
			return false;
		}
		$(this).addClass('ajax-disabled');

		var that = this;
		var target_form = $(this).attr('target-form');
		var target = $(this).attr('href') || $(this).attr('url');
		if(target_form && !target) {
			target = $('.' + target_form).attr('action');
		}
		if(target_form && target) {
			var ajax_func = function() {
				$.ajax({
					url: target,
					type: 'post',
					dataType: 'json',
					data: $('.' + target_form).serialize(),
					success: ajaxSuccess,
					error: ajaxError
				});
			};
			if($(that).hasClass('ajax-confirm')) {
				confirmAlert('确认要执行该操作吗?', function() {
					ajax_func();
				}, function() {
					ajaxDone();
				});
			} else {
				ajax_func();
			}
		}
		return false;
	});

});