define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/cash/index',
                    table: 'finance_cash'
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
                        {field: 'user.mobile', title: __('提现手机号'),operate:false},
                        {field: 'shop.name', title: __('门店名称'),operate:'like'},
                        {field: 'price', title: __('提现金额'),operate:false},
                        {field: 'shop.cash_account', title: __('提现账号'),operate:false,formatter: Controller.api.formatter.cash_account},
                        {field: 'remark', title: __('备注文本'),operate:false},
                        {field: 'create_time', title: __('提现时间'),formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange'},
                        {field: 'status', title: __('支付状态'),operate:false,formatter: Controller.api.formatter.status},
                        {field: 'is_pay', title: __('打款状态'),operate:false,formatter: Controller.api.formatter.is_pay},
                        {field: 'pay_time', title: __('打款时间'),formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange'},
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
                                    confirm: "确认通过该信息吗？",
                                    refresh:true,
                                    // icon: 'fa fa-leaf',
                                    url: "finance/cash/audit/type/approve",
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
                                    extend: 'data-area=\'["70%", "60%"]\'',
                                    url: "finance/cash/audit/type/reject",
                                    visible: function(row) {
                                        return row["status"] == 1;
                                    }
                                },
                                {
                                    name: 'pay',
                                    title: __("打款"),
                                    text: __('打款'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    extend: 'data-area=\'["70%", "60%"]\'',
                                    url: "finance/cash/pay",
                                    visible: function(row) {
                                        return row["status"] == 2 && row["is_pay"] == -1;
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
        pay: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                status: function(value, item, key)
                {
                    return item['status_text'] + (item['status']== 3 ? "("+item['reject_reason']+")" : "");
                },
                is_pay: function(value, item, key)
                {
                    return item['is_pay_text'] ;
                },
                cash_account: function(value, item, key)
                {
                    var $str = "";
                    $str += "提现方式：" + item['shop']['cash_type_text']+"<br>";
                    var _account = item['shop']['cash_account'];
                    for(var i in _account) {
                        if(i == 'name') {
                            $str += '姓名';
                        }

                        if(i == 'account') {
                            $str += '账号';
                        }

                        if(i == 'deposit') {
                            $str += '开户行';
                        }

                        $str += _account[i] + "<br>";
                    }

                    return $str;
                },
            }
        }

    };
    return Controller;
});
