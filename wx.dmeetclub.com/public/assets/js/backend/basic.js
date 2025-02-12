define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'form'], function ($, undefined, Backend, Datatable, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init();

            Form.api.bindevent($("form"));


            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    var _id = panel.attr("id");
                    if(_id == 'virt' || _id == 'message')
                    {
                        Controller.table[_id].call(this);
                    }
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });
        },
        addvirt: function () {
            Controller.api.bindevent();
        },
        editvirt: function () {
            Controller.api.bindevent();
        },
        addmessage: function () {
            Controller.api.bindevent();
        },
        editmessage: function () {
            Controller.api.bindevent();
        },
        table: {
            virt: function () {
                // 表格1
                var table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'basic/virt',
                    extend: {
                        index_url: 'basic/virtlist',
                        add_url: 'basic/addvirt',
                        edit_url: 'basic/editvirt',
                        del_url: 'basic/delvirt',
                        table: 'virt',
                    },
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    search: false,
                    commonSearch: false,
                    showToggle: false,
                    showColumns: false,
                    showExport: false,
                    columns: [
                        [
                            {field: 'content', title: '内容'},
                            {field: 'operate', title: __('Operate'), table: table2, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table2);
            },
            message: function () {
                // 表格2
                var table3 = $("#table3");
                table3.bootstrapTable({
                    url: 'basic/message',
                    extend: {
                        index_url: 'basic/message',
                        add_url: 'basic/addmessage',
                        edit_url: 'basic/editmessage',
                        del_url: 'basic/delmessage',
                        table: 'message',
                    },
                    toolbar: '#toolbar3',
                    sortName: 'id',
                    search: false,
                    commonSearch: false,
                    showToggle: false,
                    showColumns: false,
                    showExport: false,
                    columns: [
                        [
                            {field: 'content', title: '内容'},
                            {field: 'operate', title: __('Operate'), table: table3, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table3);
            }
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
            }
        }

    };

    return Controller;
});
