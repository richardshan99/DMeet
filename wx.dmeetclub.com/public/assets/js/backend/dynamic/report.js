define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dynamic/report/index',
                    audit_url: 'dynamic/report/audit',
                    table: 'dynamic_report'
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
                        {field: 'blog.content', title: __('举报动态内容'),operate:false},
                        {field: 'blog.images', title: __('举报动态图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'user.nickname', title: __('举报人昵称')},
                        {field: 'user.mobile', title: __('举报人手机号')},
                        {field: 'content', title: __('举报内容'),operate:false},
                        {field: 'images', title: __('举报图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'is_handle', title: __('状态'), width:'100px',searchList: {"-1":"待处理", 1:'已处理'}, formatter:Controller.api.formatter.is_handle},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'audit',
                                    title: __("已处理"),
                                    text: __('已处理'),
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    // icon: 'fa fa-leaf',
                                    confirm: "确认处理吗？",
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    refresh:true,
                                    visible: function(data) {
                                        return data['is_handle'] == -1;
                                    },
                                    url: "dynamic/report/audit",
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
        audit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                is_handle: function(value, row, index) {
                    return row['is_handle_text'];
                },
            }
        }

    };
    return Controller;
});
