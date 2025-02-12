define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/change/index',
                    audit_url: 'user/change/audit',
                    table: 'user_change'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search: false,
                commonSearch: false,
                showToggle: false,
                showColumns: true,
                showExport: false,
                columns: [
                    [
                        {field: 'user.nickname', title: __('昵称'),operate:"like"},
                        {field: 'user.gender', title: __('性别'),operate:false, formatter:Controller.api.formatter.gender},
                        {field: 'avatar', title: __('新头像'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'nickname', title: __('新昵称'),operate:false},
                        {field: 'intro', width: "200px", title: __('新介绍'),operate:false},
                        {field: 'create_time', title: __("提交时间"), formatter: Table.api.formatter.datetime, operate: false},
                        {field: 'status', width: "100px", title: __('状态'),operate:false,formatter:Controller.api.formatter.status},
                        {
                            field: 'operate',
                            width: "120px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'audit',
                                    title: __("通过"),
                                    text: __('通过'),
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    confirm: "确认审核通过该条信息吗？",
                                    refresh: true,
                                    // icon: 'fa fa-leaf',
                                    // extend: 'data-area=\'["70%", "60%"]\'',
                                    url: "user/change/audit/type/approve",
                                    visible: function(row) {
                                        return row["status"] == 1;
                                    }
                                },
                                {
                                    name: 'audit',
                                    title: __("驳回"),
                                    text: __('驳回'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    // extend: 'data-area=\'["70%", "60%"]\'',
                                    extend: 'data-area=\'["70%", "60%"]\'',
                                    url: "user/change/audit/type/reject",
                                    visible: function(row) {
                                        return row["status"] == 1;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        audit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                gender: function(value, row, index)
                {
                    return row['user']['gender'] == 1 ? '男': "女";
                },
                status: function(value, row, index)
                {
                    return row['status_text']+(row['status'] == 3 ? "("+row['reject_reason']+")" : "") ;
                },
            }
        }

    };
    return Controller;
});
