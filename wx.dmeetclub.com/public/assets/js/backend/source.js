define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'source/index',
                    add_url: 'source/add',
                    // edit_url: 'notice/edit',
                    del_url: 'source/del',
                    down_url: 'source/down',
                    // multi_url: 'notice/multi',
                    table: 'source'
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
                        {field: 'name', title: __('渠道名称')},
                        {field: 'area_id', title: __('地区'), formatter: Controller.api.formatter.area},
                        {field: 'num1', title: __('授权注册')},
                        {field: 'num', title: __('实名认证')},
                        {field: 'create_time', title: __('创建日期'), formatter: Table.api.formatter.datetime, operate: false},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'del',
                                    title: __("删除"),
                                    text: __('删除'),
                                    classname: 'btn btn-xs btn-danger btn-delone',
                                    // icon: 'fa fa-leaf',
                                    // extend: 'data-area=\'["100%", "100%"]\'',
                                },
                                {
                                    name: 'del',
                                    title: __("下载二维码"),
                                    text: __('下载二维码'),
                                    classname: 'btn btn-xs btn-info btn-click',
                                    // icon: 'fa fa-leaf',
                                    // extend: 'data-area=\'["50%", "40%"]\'',
                                    click: function (data, row) {
                                       parent.window.open($.fn.bootstrapTable.defaults.extend.down_url+'/ids/'+row.id);
                                    },
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
