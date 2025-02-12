define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cert/work/index',
                    audit_url: 'cert/work/audit',
                    table: 'cert_work'
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
                        {field: 'user.nickname', title: __('用户昵称'),operate:false},
                        {field: 'user.mobile', title: __('手机号'),operate:'like'},
                        {field: 'company', title: __('公司名称'),operate:false},
                        // {field: 'trade_id', title: __('行业'),operate:false, formatter: Controller.api.formatter.trade},
                        {field: 'position', title: __('职位'),operate:false},
                        {field: 'images', title: __('图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'status', title: __('状态'),searchList: {1:"待审核", 2:'通过', 3:'驳回'}, formatter:Controller.api.formatter.status},
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
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    // icon: 'fa fa-leaf',
                                    confirm: "确认审核通过该条信息吗？",
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    refresh:true,
                                    visible: function(data) {
                                        return data['status'] == 1;
                                    },
                                    url: "cert/work/audit/type/approve",
                                },
                                {
                                    name: 'audit',
                                    title: __("驳回"),
                                    text: __('驳回'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    visible: function(data) {
                                        return data['status'] == 1;
                                    },
                                    url: "cert/work/audit/type/reject",
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
                status: function(value, row, index) {
                    return row['status_text'] + (row['status']== 3 ? ('('+row['reject_reason']+')'): "");
                },
                trade: function(value, row, index) {
                    return row['trade_text'];
                }
            }
        }

    };
    return Controller;
});
