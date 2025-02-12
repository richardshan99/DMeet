define([], function () {
    require([], function () {
    //绑定data-toggle=addresspicker属性点击事件

    $(document).on('click', "[data-toggle='addresspicker']", function () {
        var that = this;
        var callback = $(that).data('callback');
        var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
        var lat_id = $(that).data("lat-id") ? $(that).data("lat-id") : "";
        var lng_id = $(that).data("lng-id") ? $(that).data("lng-id") : "";
        var zoom_id = $(that).data("zoom-id") ? $(that).data("zoom-id") : "";
        var lat = lat_id ? $("#" + lat_id).val() : '';
        var lng = lng_id ? $("#" + lng_id).val() : '';
        var zoom = zoom_id ? $("#" + zoom_id).val() : '';
        var url = "/addons/address/index/select";
        url += (lat && lng) ? '?lat=' + lat + '&lng=' + lng + (input_id ? "&address=" + $("#" + input_id).val() : "") + (zoom ? "&zoom=" + zoom : "") : '';
        Fast.api.open(url, '位置选择', {
            callback: function (res) {
                input_id && $("#" + input_id).val(res.address).trigger("change");
                lat_id && $("#" + lat_id).val(res.lat).trigger("change");
                lng_id && $("#" + lng_id).val(res.lng).trigger("change");
                zoom_id && $("#" + zoom_id).val(res.zoom).trigger("change");

                try {
                    //执行回调函数
                    if (typeof callback === 'function') {
                        callback.call(that, res);
                    }
                } catch (e) {

                }
            }
        });
    });
});

//修改上传的接口调用
require(['upload'], function (Upload) {

    var _onInit = Upload.events.onInit;
    //初始化中完成判断
    Upload.events.onInit = function () {
        _onInit.apply(this, Array.prototype.slice.apply(arguments));
        //如果上传接口不是七牛云，则不处理
        if (this.options.url !== Config.upload.uploadurl) {
            return;
        }
        var _success = this.options.success;

        $.extend(this.options, {
            //关闭自动处理队列功能
            autoQueue: false,
            chunkSuccess: function (chunk, file, response) {
                this.contexts = this.contexts ? this.contexts : [];
                this.contexts.push(typeof response.ctx !== 'undefined' ? response.ctx : response.data.ctx);
            },
            chunksUploaded: function (file, done) {
                var that = this;
                var params = $(that.element).data("params") || {};
                var category = typeof params.category !== 'undefined' ? params.category : ($(that.element).data("category") || '');
                category = typeof category === 'function' ? category.call(this, file) : category;
                Fast.api.ajax({
                    url: "/addons/qiniu/index/upload",
                    data: {
                        action: 'merge',
                        filesize: file.size,
                        filename: file.name,
                        chunkid: file.upload.uuid,
                        chunkcount: file.upload.totalChunkCount,
                        width: file.width || 0,
                        height: file.height || 0,
                        type: file.type,
                        category: category,
                        qiniutoken: Config.upload.multipart.qiniutoken,
                        contexts: this.contexts
                    },
                }, function (data, ret) {
                    done(JSON.stringify(ret));
                    return false;
                }, function (data, ret) {
                    file.accepted = false;
                    that._errorProcessing([file], ret.msg);
                    return false;
                });

            },
        });

        //先移除已有的事件
        this.off("success", _success).on("success", function (file, response) {
            var that = this;
            var ret = {code: 0, msg: response};
            try {
                ret = typeof response === 'string' ? JSON.parse(response) : response;
                if (file.xhr.status === 200) {
                    if (Config.upload.uploadmode === 'client') {
                        if (typeof ret.key !== 'undefined') {
                            ret = {code: 1, msg: "", data: {url: '/' + ret.key, hash: ret.hash}};
                        }
                        var url = ret.data.url || '';
                        var params = $(that.element).data("params") || {};
                        var category = typeof params.category !== 'undefined' ? params.category : ($(that.element).data("category") || '');
                        category = typeof category === 'function' ? category.call(that, file) : category;
                        Fast.api.ajax({
                            url: "/addons/qiniu/index/notify",
                            data: {name: file.name, url: ret.data.url, hash: ret.data.hash, size: file.size, width: file.width || 0, height: file.height || 0, type: file.type, category: category, qiniutoken: Config.upload.multipart.qiniutoken}
                        }, function () {
                            return false;
                        }, function () {
                            return false;
                        });
                    } else {
                        console.error(ret);
                    }
                } else {
                    console.error(file.xhr);
                }
            } catch (e) {
                console.error(e);
            }
            _success.call(this, file, ret);
        });

        this.on("addedfile", function (file) {
            var that = this;
            setTimeout(function () {
                if (file.status === 'error') {
                    return;
                }

                var md5 = ''; //七牛云无需本地获取文件MD5
                var chunk = that.options.chunking && file.size > that.options.chunkSize ? 1 : 0;
                var params = $(that.element).data("params") || {};
                var category = typeof params.category !== 'undefined' ? params.category : ($(that.element).data("category") || '');
                category = typeof category === 'function' ? category.call(that, file) : category;
                Fast.api.ajax({
                    url: "/addons/qiniu/index/params",
                    data: {method: 'POST', category: category, md5: md5, name: file.name, type: file.type, size: file.size, chunk: chunk, chunksize: that.options.chunkSize, qiniutoken: Config.upload.multipart.qiniutoken},
                }, function (data) {
                    file.qiniutoken = data.qiniutoken;
                    file.params = data;
                    file.category = category;

                    if (file.status != 'error') {
                        //开始上传
                        that.enqueueFile(file);
                    } else {
                        that.removeFile(file);
                    }
                    return false;
                }, function () {
                    that.removeFile(file);
                });
            }, 0);
        });

        //如果是直传模式
        if (Config.upload.uploadmode === 'client') {
            var _url = this.options.url;

            //分片上传时URL链接不同
            this.options.url = function (files) {
                this.options.headers = {"Authorization": "UpToken " + Config.upload.multipart.qiniutoken};
                if (files[0].upload.chunked) {
                    var chunk = null;
                    files[0].upload.chunks.forEach(function (item) {
                        if (item.status === 'uploading') {
                            chunk = item;
                        }
                    });
                    if (!chunk) {
                        return Config.upload.uploadurl + '/mkfile/' + files[0].size;
                    } else {
                        return Config.upload.uploadurl + '/mkblk/' + chunk.dataBlock.data.size;
                    }
                }
                return _url;
            };

            this.options.params = function (files, xhr, chunk) {
                var params = Config.upload.multipart;
                if (chunk) {
                    return $.extend({}, params, {
                        filesize: chunk.file.size,
                        filename: chunk.file.name,
                        chunkid: chunk.file.upload.uuid,
                        chunkindex: chunk.index,
                        chunkcount: chunk.file.upload.totalChunkCount,
                        chunkfilesize: chunk.dataBlock.data.size,
                        width: chunk.file.width || 0,
                        height: chunk.file.height || 0,
                        type: chunk.file.type,
                    });
                } else {
                    var retParams = $.extend({}, params, files[0].params || {});
                    //七牛云直传使用的是token参数
                    retParams.token = retParams.qiniutoken;
                    delete retParams.qiniutoken;
                    return retParams;
                }
            };

            //分片上传时需要变更提交的内容
            this.on("sending", function (file, xhr, formData) {
                if (file.upload.chunked) {
                    var _send = xhr.send;
                    xhr.send = function () {
                        var chunk = null;
                        file.upload.chunks.forEach(function (item) {
                            if (item.status == 'uploading') {
                                chunk = item;
                            }
                        });
                        if (chunk) {
                            _send.call(xhr, chunk.dataBlock.data);
                        }
                    };
                }
            });
        }
    };

});

require.config({
    paths: {
        'summernote': '../addons/summernote/lang/summernote-zh-CN.min'
    },
    shim: {
        'summernote': ['../addons/summernote/js/summernote.min', 'css!../addons/summernote/css/summernote.min.css'],
    }
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定summernote事件
            if ($(Config.summernote.classname || '.editor', form).length > 0) {
                var selectUrl = typeof Config !== 'undefined' && Config.modulename === 'index' ? 'user/attachment' : 'general/attachment/select';
                require(['summernote'], function () {
                    var imageButton = function (context) {
                        var ui = $.summernote.ui;
                        var button = ui.button({
                            contents: '<i class="fa fa-file-image-o"/>',
                            tooltip: __('Choose'),
                            click: function () {
                                parent.Fast.api.open(selectUrl + "?element_id=&multiple=true&mimetype=image/", __('Choose'), {
                                    callback: function (data) {
                                        var urlArr = data.url.split(/\,/);
                                        $.each(urlArr, function () {
                                            var url = Fast.api.cdnurl(this, true);
                                            context.invoke('editor.insertImage', url);
                                        });
                                    }
                                });
                                return false;
                            }
                        });
                        return button.render();
                    };
                    var attachmentButton = function (context) {
                        var ui = $.summernote.ui;
                        var button = ui.button({
                            contents: '<i class="fa fa-file"/>',
                            tooltip: __('Choose'),
                            click: function () {
                                parent.Fast.api.open(selectUrl + "?element_id=&multiple=true&mimetype=*", __('Choose'), {
                                    callback: function (data) {
                                        var urlArr = data.url.split(/\,/);
                                        $.each(urlArr, function () {
                                            var url = Fast.api.cdnurl(this, true);
                                            var node = $("<a href='" + url + "'>" + url + "</a>");
                                            context.invoke('insertNode', node[0]);
                                        });
                                    }
                                });
                                return false;
                            }
                        });
                        return button.render();
                    };

                    $(Config.summernote.classname || '.editor', form).each(function () {
                        $(this).summernote($.extend(true, {}, {
                            // height: 250,
                            minHeight: 250,
                            lang: 'zh-CN',
                            fontNames: [
                                'Arial', 'Arial Black', 'Serif', 'Sans', 'Courier',
                                'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande',
                                "Open Sans", "Hiragino Sans GB", "Microsoft YaHei",
                                '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆',
                            ],
                            fontNamesIgnoreCheck: [
                                "Open Sans", "Microsoft YaHei",
                                '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆'
                            ],
                            toolbar: [
                                ['style', ['style', 'undo', 'redo']],
                                ['font', ['bold', 'underline', 'strikethrough', 'clear']],
                                ['fontname', ['color', 'fontname', 'fontsize']],
                                ['para', ['ul', 'ol', 'paragraph', 'height']],
                                ['table', ['table', 'hr']],
                                ['insert', ['link', 'picture']],
                                ['select', ['image']],
                                ['view', ['fullscreen', 'codeview']],
                            ],
                            buttons: {
                                image: imageButton,
                                attachment: attachmentButton,
                            },
                            dialogsInBody: true,
                            followingToolbar: false,
                            callbacks: {
                                onChange: function (contents) {
                                    $(this).val(contents);
                                    $(this).trigger('change');
                                },
                                onInit: function () {
                                },
                                onImageUpload: function (files) {
                                    var that = this;
                                    //依次上传图片
                                    for (var i = 0; i < files.length; i++) {
                                        Upload.api.send(files[i], function (data) {
                                            var url = Fast.api.cdnurl(data.url, true);
                                            $(that).summernote("insertImage", url, 'filename');
                                        });
                                    }
                                }
                            }
                        }, $(this).data("summernote-options") || {}));
                    });
                });
            }
        } catch (e) {

        }

    };
});

});