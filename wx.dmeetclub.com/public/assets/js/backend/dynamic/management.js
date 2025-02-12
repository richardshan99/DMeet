define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dynamic/management/index',
                    audit_url: 'dynamic/management/audit',
                    table: 'dynamic_management'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search: false,
                commonSearch: true,
                showToggle: false,
                showColumns: true,
                showExport: false,
                maintainSelected:true,
                columns: [
                    [
                        {checkbox:true},
                        {field: 'user.nickname', title: __('发布人昵称'),operate:'like'},
                        {field: 'user.mobile', title: __('发布人手机号'),operate:'like'},
                        {field: 'user.avatar', title: __('头像'),operate:false, table: table,formatter:Table.api.formatter.image, events: Table.api.events.image},
                        {field: 'content', title: __('动态内容'),operate:false},
                        {field: 'images', title: __('动态图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'create_time', title: __('发布时间'),width:'150px',operate:false,formatter: Table.api.formatter.datetime, addclass: 'datetimerange'},
                        {field: 'is_handle', title: __('状态'),operate:false, width:'100px',searchList: {"1":"待审核", 2:'通过',3:'驳回'}, formatter:Controller.api.formatter.status},
                        {
                            field: 'operate',
                            width: "100px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'audit',
                                    title: __("通过"),
                                    text: __('通过'),
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    // icon: 'fa fa-leaf',
                                    confirm: "确认通过吗？",
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    refresh:true,
                                    visible: function(data) {
                                        return data['status'] == 1;
                                    },
                                    url: "dynamic/management/audit/type/approve",
                                },
                                {
                                    name: 'audit',
                                    title: __("驳回"),
                                    text: __('驳回'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    visible: function(data) {
                                        return data['status'] != 3;
                                    },
                                    url: "dynamic/management/audit/type/reject",
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 批量通过
            $(document).on("click", ".btn-approve", function () {
                Layer.confirm("确认批量审核通过选中项吗？", {
                    icon: 3,
                    title: '提示'
                }, function (index) {
                    //在table外不可以使用添加.btn-change的方法
                    //只能自己调用Table.api.multi实现
                    //如果操作全部则ids可以置为空
                    var ids = Table.api.selectedids(table);
                    Fast.api.ajax({
                        url: "dynamic/management/multiapprove",
                        data: {
                            ids: ids
                        },
                        type:"post",
                    }, function () {
                        Layer.closeAll();
                        table.trigger("uncheckbox");
                        table.bootstrapTable('refresh');
                    }, function () {
                        Layer.closeAll();
                    });
                });

            });

            // 批量驳回
            $(document).on("click", ".btn-reject", function () {
                var _html = '<p>驳回原因：<input type="text" name="reject_refuse" style="width:200px;height:30px"></p>';

                layer.confirm('审核驳回',{
                    title: '审核驳回',
                    content: _html
                }, function (index, data){
                    var _check_refuse = $.trim($(data).find('input[name=reject_refuse]').val());
                    if(_check_refuse =='') {
                        parent.Toastr.error('请输入驳回原因');return;
                    }

                    var ids = Table.api.selectedids(table);
                    $.ajax({
                        dataType: 'json',
                        type: 'POST',
                        url: "dynamic/management/multireject",
                        data: {ids: ids, reject_reason:_check_refuse},
                        success: function (json) {
                            Layer.closeAll();
                            table.trigger("uncheckbox");
                            table.bootstrapTable('refresh');
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            Layer.closeAll();
                        }
                    });
                });
                return false;
            });

            $(document).on("click", ".btn-reject", function () {
                Layer.open({
                    content: Template("userinfotpl", userinfo),
                    title: "批量驳回",
                    resize: false,
                    btn: [__('确认'), __('关闭')],
                    yes: function () {
                        Fast.api.ajax({
                            url: Config.api_url + '/user/logout',
                            data: {uid: userinfo.id, token: userinfo.token, version: Config.faversion}
                        }, function (data, ret) {
                            Controller.api.userinfo.set(null);
                            Layer.closeAll();
                            Layer.alert(ret.msg, {title: __('Warning'), icon: 0});
                        }, function (data, ret) {
                            Controller.api.userinfo.set(null);
                            Layer.closeAll();
                            Layer.alert(ret.msg, {title: __('Warning'), icon: 0});
                        });
                    }
                });

            });
        },
        audit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                status: function(value, item, index) {
                    return item['status_text'] + (item['status']== 3 ? "("+item['reject_reason']+")" : "");
                },
            }
        }

    };
    return Controller;
});
