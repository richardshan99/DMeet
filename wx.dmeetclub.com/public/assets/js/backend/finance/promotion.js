define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/promotion/index',
                    table: 'finance_promotion'
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
                        {field: 'user.avatar', title: __('头像'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'user.nickname', title: __('昵称'),operate:false},
                        {field: 'user.name', title: __('用户姓名')},
                        {field: 'user.mobile', title: __('手机号码'),operate:false},
                        {field: 'price', title: __('支付金额'),operate:false},
                        {field: 'pay_type', title: __('支付方式'),operate:false,formatter: Controller.api.formatter.pay_type},
                        {field: 'create_time', title: __('支付时间'),formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange'},

                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里我们手动设置底部的值
                $("#total_price_wechat").text(data.extend.total_price_wechat);
                $("#total_price_balance").text(data.extend.total_price_balance);
            });
        },
        api: {
            formatter: {
                pay_type: function(value, item, key)
                {
                    return item['pay_type_text'];
                }
            }
        }

    };
    return Controller;
});
