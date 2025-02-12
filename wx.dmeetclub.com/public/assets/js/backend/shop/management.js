define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, Template, Echarts) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/management/index',
                    add_url: 'shop/management/add',
                    edit_url: 'shop/management/edit',
                    multi_url: 'shop/management/multi',
                    ratio_url: 'shop/management/ratio',
                    users_url: 'shop/management/users',
                    data_url: 'shop/management/data',
                    table: 'shop_management'
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
                        {field: 'name', title: __('门店名称'),operate:"like"},
                        {field: 'mobile', title: __('手机号'),operate:"like"},
                        {field: 'shop_category_id', title: __('类别'), searchList: Config.shopCategory, formatter: Controller.api.formatter.shop_category},
                        {field: 'address', title: __('地址'),operate:false},
                        {field: 'cash_type', title: __('提现类型'),operate:false, formatter: Controller.api.formatter.cash_type},
                        {field: 'cash_account', title: __('提现账号'), align:'left',operate:false, formatter: Controller.api.formatter.cash_account},
                        {field: 'business_time', title: __('营业时间段'),operate:false},
                        {field: 'status', title: __('冻结/解冻'),operate:false, formatter: Table.api.formatter.toggle, table: table ,yes: 'normal', no: 'hidden'},
                        {field: 'status', title: __('状态'), formatter: Table.api.formatter.status, searchList: {normal: __('正常'), hidden: __('冻结')}},
                        {field: 'commission_ratio', title: __('分成比例(%)'),operate:false},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'users',
                                    title: __("查看门店数据"),
                                    text: __('查看门店数据'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    url:  $.fn.bootstrapTable.defaults.extend.data_url,
                                },
                                {
                                    name: 'users',
                                    title: __("查看门店店员"),
                                    text: __('查看门店店员'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    url:  $.fn.bootstrapTable.defaults.extend.users_url,
                                },
                                {
                                    name: 'edit',
                                    title: __("编辑信息"),
                                    text: __('编辑信息'),
                                    classname: 'btn btn-xs btn-success btn-editone',
                                    // icon: 'fa fa-leaf',
                                    // extend: 'data-area=\'["100%", "100%"]\'',
                                },
                                {
                                    name: 'ratio',
                                    title: __("分成比例设置"),
                                    text: __('分成比例设置'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    // extend: 'data-area=\'["50%", "40%"]\'',
                                    url: $.fn.bootstrapTable.defaults.extend.ratio_url,
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
        ratio: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));

            $("[data-toggle='addresspicker']").data("callback", function(res){
                //其中res则是包含了address/lat/lng/zoom等信息的JSON对象
                $('#c-point').val(res.lng+","+res.lat);
                $('#c-address').val(res.address);
            });

            require(['layui'], function (Layui) {
                Layui.use('laydate', function () {
                    var laydate = Layui.laydate;
                    laydate.render({
                        elem: '.timeclicknew'
                        , type: 'time'
                        , range: true
                        , format: 'HH:mm'
                    });
                });
            })
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));

            $("[data-toggle='addresspicker']").data("callback", function(res){
                //其中res则是包含了address/lat/lng/zoom等信息的JSON对象
                $('#c-point').val(res.lng+","+res.lat);
                $('#c-address').val(res.address);
            });

            require(['layui'], function (Layui) {
                Layui.use('laydate', function () {
                    var laydate = Layui.laydate;
                    laydate.render({
                        elem: '.timeclicknew'
                        , type: 'time'
                        , range: true
                        , format: 'HH:mm'
                    });
                });
            })
        },
        users: function () {
            Table.api.init();
            // 初始化表格参数配置
            var table = $("#table");
            var tableOptions = {
                url: 'shop/management/users/ids/'+Config.shop_id,
                pk: 'id',
                sortName: 'id',
                search: false,
                commonSearch: false,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {field: 'name', title: __("姓名")},
                        {field: 'mobile', title: __("手机号")},
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
        data: function() {

            Table.api.init();
            // 初始化表格参数配置
            var table = $("#table");
            var tableOptions = {
                url: 'shop/management/data/ids/'+Config.shop_id,
                pk: 'id',
                sortName: 'id',
                search: false,
                commonSearch: true,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {field: 'user.nickname', title: __("用户昵称"), operate:false},
                        {field: 'package', title: __("套餐内容"), operate:false, align:'left', width: '300px', formatter:Controller.api.formatter.package},
                        {field: 'price', title: __("支付金额"), operate:false},
                        {field: 'create_time', title: __("支付时间"), operate:false, formatter: Table.api.formatter.datetime, addclass: 'datetimerange'},
                        {field: 'meet_time', title: __("到店时间"), operate:'RANGE', formatter: Table.api.formatter.datetime, addclass: 'datetimerange'},
                        {field: 'invite_status', title: __("状态"), operate:false, formatter:Controller.api.formatter.invite_status},
                    ]
                ]
            };
            // 初始化表格
            table.bootstrapTable(tableOptions);

            // 为表格绑定事件
            Table.api.bindevent(table);

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里我们手动设置底部的值
                Controller.api.echart(data.extend.chart);
            });
        },
        api: {
            formatter: {
                shop_category: function(valeu, row, index) {
                    return row["category"]["name"];
                },
                cash_type: function(value, row, index) {
                    return row["cash_type_text"];
                },
                cash_account: function(value, row, index)
                {
                    return "姓名：" + value["name"] +"<br>"
                        +  "账号：" + value["account"] +"<br>"
                        +  (row["cash_type"] == 3 ? ("开户行：" + value["deposit"]) : "");
                },
                status: function(value, row, index)
                {
                    return row['status_text'];
                },
                invite_status: function(value, row, index)
                {
                    return row['invite_status_text'];
                },
                user_status: function(value, row, index)
                {
                    return row['status_text'];
                },
                package: function(value, item, index)
                {
                    var $str = "";
                    //套餐
                    var package1 = JSON.parse(item['package']);
                    $str += "套餐 名称："+ package1['name'] + "<br>";
                    $str += "介绍：" + package1['intro'] + "<br>";
                    $str += "服务：<br>";
                    var service1 = package1['service'];
                    console.log(service1);
                    for(var s1 in service1) {
                        $str += (parseInt(s1)+1)+"、名称："+service1[s1]['name']+", 价格：" +service1[s1]['price'] + "<br>";
                    }

                    return $str;
                }
            },
            echart: function (data){
                var _keys = [],
                    _vals = [];
                for(var i in data) {
                    var _item = data[i];
                    _keys.push(_item['name']);
                    _vals.push(_item['value']);
                }
                var barChart = Echarts.init(document.getElementById('invite-chart'));
                var option = {
                    title: {
                        text: '数据统计',
                        left: 'center',
                        top: 20,
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'cross',
                            crossStyle: {
                                color: '#999'
                            }
                        }
                    },
                    xAxis: {
                        type: 'category',
                        axisPointer: {
                            type: 'shadow'
                        },
                        axisLabel: {
                            interval:0,
                        },
                        data: _keys
                    },
                    yAxis: {
                        type: 'value',
                        axisLine: {
                            lineStyle: {
                                color: "#58aa4d"
                            }
                        }
                    },
                    series: [{
                        data: _vals,
                        type: 'bar',
                        itemStyle: {
                            color: "#58aa4d",
                            opacity: 0.6
                        }
                    }]
                };
                // 使用刚指定的配置项和数据显示图表。
                barChart.setOption(option);
            }
        }

    };
    return Controller;
});
