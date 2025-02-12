define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/form/index',
                    add_url: 'user/form/add',
                    edit_url: 'user/form/edit',
                    del_url: 'user/form/del',
                    multi_url: 'user/form/multi',
                    table: "user_form"
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                dblClickToEdit: false,
                search: false,
                commonSearch: false,
                showToggle: false,
                showColumns: false,
                showExport: false,
                pagination: false,
                columns: [
                    [
                        {field: 'id', title: __('序号'),operate:false, formatter:function (value,row,index)
                            {
                                var options = table.bootstrapTable('getOptions');
                                var pageNumber = options.pageNumber;
                                var pageSize = options.pageSize;
                                if(pageSize == "All") {
                                    pageSize = 0;
                                }
                                return (pageNumber - 1) * pageSize + 1 + index;
                            }
                        },
                        {field: 'type', title: __('字段类型'), width:'150px', formatter: Controller.api.formatter.type},
                        {field: 'key', title: __('字段名'), width:'150px'},
                        {field: 'name', title: __('字段标题'), width:'150px'},
                        {field: 'value', title: __('默认填充'), formatter: Controller.api.formatter.values},
                        {field: 'is_require', title: __('是否必填'), operate:false, formatter: Table.api.formatter.toggle, table: table ,yes: '1', no: "-1"},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'edit',
                                    title: __('默认填充'),
                                    text: __('默认填充'),
                                    classname: 'btn btn-xs btn-info btn-editone',
                                    // icon: 'fa fa-leaf',
                                    visible: function(row)
                                    {
                                        if(row['is_regular'] == 1) {
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'edit',
                                    title: __('编辑'),
                                    text: __('编辑'),
                                    classname: 'btn btn-xs btn-success btn-editone',
                                    // icon: 'fa fa-leaf',
                                    visible: function(row)
                                    {
                                        if(row['is_regular'] != 1) {
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'del',
                                    title: __('删除'),
                                    text: __('删除'),
                                    classname: 'btn btn-xs btn-danger btn-delone',
                                    // icon: 'fa fa-leaf',
                                    visible: function(row)
                                    {
                                        if(row['is_regular'] != 1) {
                                            return true;
                                        }
                                        return false;
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
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                type: function(value, item, key)
                {
                    return item['type_text'];
                },
                values: function(value, item, key)
                {
                    return item['value_text'];
                },
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }

    };
    return Controller;
});
