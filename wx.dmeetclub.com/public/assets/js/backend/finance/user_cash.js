define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/user_cash/index' + location.search,
                    add_url: 'finance/user_cash/add',
                    multi_url: 'finance/user_cash/multi',
                    import_url: 'finance/user_cash/import',
                    pass_through_url: 'finance/user_cash/pass_through',
                    reject_url: 'finance/user_cash/reject',
                    table: 'user_cash',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                search: false,
                commonSearch: true,
                showToggle: false,
                showColumns: true,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'user.mobile', title: __('用户手机号'), operate: 'LIKE'},
                        {field: 'money', title: __('提现金额'), operate:false},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {1: '待处理', 2:'已转账', 3:'已驳回'}},
                        {field: 'reject_reason', title: __('驳回原因'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'create_time', title: __('提现时间'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'),
                            buttons: [
                                {
                                    name: 'pass_through',
                                    text: '通过',
                                    confirm: '确定操作吗？',
                                    icon: '',
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    url: 'finance/user_cash/pass_through',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                },
                                {
                                    name: 'reject',
                                    text: '驳回',
                                    icon: '',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'finance/user_cash/reject',
                                },
                            ],
                            table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            formatter: function (value, row, index) { //隐藏自定义的视频按钮
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                //权限判断
                                if(row.status !== 1){ //通过Config.chapter 获取后台存的chapter
                                    $(table).data("operate-pass_through", null);
                                    $(table).data("operate-reject", null);
                                }
                                that.table = table;
                                return Table.api.formatter.operate.call(that, value, row, index);
                            }
                        }
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        pass_through: function () {
            Controller.api.bindevent();
        },
        reject: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
