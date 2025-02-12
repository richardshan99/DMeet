define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dynamic/meet/index',
                    audit_url: 'dynamic/meet/audit',
                    table: 'dynamic_meet'
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
                columns: [
                    [
                        {field: 'user.nickname', title: __('发布人昵称'),operate:'like'},
                        {field: 'user.mobile', title: __('发布人手机号'),operate:'like'},
                        {field: 'user.avatar', title: __('头像'),operate:false, table: table,formatter:Table.api.formatter.image, events: Table.api.events.image},
                        {field: 'content', title: __('动态内容'),operate:false},
                        {field: 'images', title: __('动态图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'create_time', title: __('发布时间'),operate:false,formatter: Table.api.formatter.datetime, addclass: 'datetimerange'},
                        {field: 'style_level', title: __('动态等级'),operate:false,formatter: Controller.api.formatter.style_level},
                        {field: 'is_handle', title: __('状态'),operate:false, width:'100px',searchList: {"1":"待审核", 2:'通过',3:'驳回'}, formatter:Controller.api.formatter.status},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'audit',
                                    title: __("通过"),
                                    text: __('通过'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    refresh:true,
                                    visible: function(data) {
                                        return data['status'] == 1;
                                    },
                                    url: "dynamic/meet/audit/type/approve",
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
                                    url: "dynamic/meet/audit/type/reject",
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        approve: function(){
            Form.api.bindevent($("form[role=form]"));

        },
        audit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                status: function(value, item, index) {
                    return item['status_text'] + (item['status']== 3 ? "("+item['reject_reason']+")" : "");
                },
                style_level: function(value, item, index) {
                    if(item['style_level'] == 1) return '普通';
                    if(item['style_level'] == 2) return '精选';
                    return;
                }
            }
        }

    };
    return Controller;
});
