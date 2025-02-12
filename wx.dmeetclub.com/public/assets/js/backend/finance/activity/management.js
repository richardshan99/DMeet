define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/activity/management/index',
                    add_url: 'finance/activity/management/add',
                    edit_url: 'finance/activity/management/edit',
                    del_url: 'finance/activity/management/del',
                    cancel_url: 'finance/activity/management/cancel',
                    user_url: 'finance/activity/management/user',
                    table: 'finance_activity_management'
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
                        {field: 'thumb', title: __('缩略图'),operate:false, table: table,formatter:Table.api.formatter.image, events: Table.api.events.image},
                        {field: 'activity_type.name', title: __('活动类型'),operate:false},
                        {field: 'name', title: __('活动名称'),operate:'like'},
                        {field: 'area', title: __('活动地区'),operate: 'like'},
                        {field: 'begin_time', title: __('活动日期'),operate:false, formatter: Controller.api.formatter.activity_time},
                        {field: 'num', title: __('已报名人数/活动最小人数'),operate:false, formatter: Controller.api.formatter.num},
                        {field: 'sign_status', title: __('报名状态'), formatter: Controller.api.formatter.sign_status, searchList: {"-1": "人数不够", "1":"人数已够"}},
                        {field: 'status', title: __('状态'), formatter: Controller.api.formatter.status, searchList: {1: "进行中", 2:"已结束", 3: "已取消"}},
                        {field: 'total_price', title: __('报名收入'),operate:false},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'edit',
                                    title: __("编辑"),
                                    text: __('编辑'),
                                    classname: 'btn btn-xs btn-success btn-editone',
                                    visible: function(row) {
                                        return row["status"] != 1 && row["status"] != 3;
                                    }
                                },
                                {
                                    name: 'del',
                                    title: __("删除"),
                                    text: __('删除'),
                                    classname: 'btn btn-xs btn-primary btn-delone',
                                    visible: function(row) {
                                        return row["status"] != 1;
                                    }
                                },
                                {
                                    name: 'cancel',
                                    title: __("取消活动"),
                                    text: __('取消活动'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    confirm: '确认取消该活动吗？',
                                    refresh: true,
                                    // icon: 'fa fa-leaf',
                                    extend: 'data-area=\'["70%", "60%"]\'',
                                    url: $.fn.bootstrapTable.defaults.extend.cancel_url,
                                    visible: function(row) {
                                        return row["status"] == 1;
                                    }
                                },
                                {
                                    name: 'user',
                                    title: __("查看活动报名人员"),
                                    text: __('查看活动报名人员'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    url: $.fn.bootstrapTable.defaults.extend.user_url,
                                },
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里我们手动设置底部的值
                $("#total_num").text(data.extend.total_num);
                $("#total_price").text(data.extend.total_price);
                $("#total_price_wechat").text(data.extend.total_price_wechat);
                $("#total_price_balance").text(data.extend.total_price_balance);
                $("#total_user").text(data.extend.total_user);
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        user: function () {
            Table.api.init();
            // 初始化表格参数配置
            var table = $("#table");
            var tableOptions = {
                url: 'finance/activity/management/user/ids/'+Config.activity_id,
                pk: 'id',
                sortName: 'id',
                search: false,
                commonSearch: false,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {field: 'user.mobile', title: __("报名人手机号")},
                        {field: 'user.nickname', title: __("昵称")},
                        {field: 'price', title: __("支付金额")},
                        {field: 'create_time', title: __("支付时间"), formatter: Table.api.formatter.datetime, operate: false},
                        {field: 'status', title: __("状态"), formatter: Controller.api.formatter.user_status},
                    ]
                ]
            };
            // 初始化表格
            table.bootstrapTable(tableOptions);

            // 为表格绑定事件
            Table.api.bindevent(table);

            //必须默认触发shown.bs.tab事件
            // $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

        },
        api: {
            formatter: {
                num: function(value, row, index) {
                    return row['user_count'] + '/' + row['min_num'];
                },
                sign_status: function(value, row, index) {
                    return row['user_count'] < row['min_num'] ? "人数不够" : "人数已够";
                },
                status: function(value, row, index) {
                    return row['status_text'];
                },
                activity_time: function(value, row, index) {
                    return "开始时间："+ row['begin_time_text'] + "<br />结束时间：" + row['end_time_text'];
                },
                user_status: function(value, row, index) {
                    return row['status_text'];
                },
            }
        }

    };
    return Controller;
});
