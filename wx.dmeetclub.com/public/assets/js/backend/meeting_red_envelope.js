define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'meeting_red_envelope/index',
                    add_url: 'meeting_red_envelope/add',
                    edit_url: 'meeting_red_envelope/edit',
                    del_url: 'meeting_red_envelope/del',
                    // down_url: 'source/down',
                    // multi_url: 'notice/multi',
                    table: 'meeting_red_envelope'
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
                        {field: 'price', title: __('金额')},
                        {field: 'create_time', title: __('创建日期'), formatter: Table.api.formatter.datetime, operate: false},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                area: function(key, item,index){
                    return item["area"];
                }
            }
        }
    };
    return Controller;
});
