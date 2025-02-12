define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/message/index',
                    table: 'user_message'
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
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {field: 'fromuser.nickname', title: __('发送人'), formatter:Controller.api.formatter.fromuser,width:'20%'},
                        {field: 'touser.nickname', title: __('接收人'), formatter:Controller.api.formatter.touser,width:'20%'},
                        {field: 'message', title: __('内容'),width:'40%'},
                        {field: 'create_time', title: __("发送时间"), formatter: Table.api.formatter.datetime, operate: false,width:'10%'},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            formatter: {
                fromuser: function(value, item, index) {
                    return item['fromuser']['nickname']+" "+item['fromuser']['mobile'];
                },
                touser: function(value, item, index) {
                    return item['fromuser']['nickname']+" "+item['fromuser']['mobile'];
                }
            }
        }
    };
    return Controller;
});
