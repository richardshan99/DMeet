define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, Template, Echarts) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/invitation/index',
                    table: 'finance_invitation'
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
                        {field: 'id', title: __('ID')},
                        {field: 'shop.name', title: __('门店名称'),visible:false,operate:'like'},
                        {field: 'user.avatar', title: __('邀约人头像'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'user.nickname', title: __('邀约人昵称'),operate:false},
                        {field: 'inviteuser.avatar', title: __('被邀约人头像'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'inviteuser.nickname', title: __('被邀约人昵称'),operate:false},
                        {field: 'address', title: __('见面地址'),operate:false},
                        {field: 'meet_time', title: __('见面时间'),formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', datetimeFormat: 'YYYY-MM-DD HH:mm'},
                        {field: 'inviter_is_verify', title: __('邀约人签到状态'), formatter: Table.api.formatter.status, searchList: {1:"已签到", '-1':"未签到"}},
                        {field: 'inviter_sign_image', title: __('邀约人签到图'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'invitee_is_verify', title: __('被邀约人签到状态'), formatter: Table.api.formatter.status, searchList: {1:"已签到", '-1':"未签到"}},
                        {field: 'invitee_sign_image', title: __('被邀约人签到图'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'package', title: __('套餐内容'),align:'left',operate:false, formatter: Controller.api.formatter.package},
                        {field: 'price', title: __('套餐价格'),operate:false},
                        {field: 'pay_mode', title: __('付款方式'),operate:false, formatter: Controller.api.formatter.pay_mode},
                        {field: 'deposit', title: __('履约保证金'),operate:false},
                        {field: 'status', title: __('状态'), formatter: Controller.api.formatter.status, searchList: {2:"待见面", 3:"已完成",4:"已取消"}},
                        {field: 'commission', title: __('门店分成金额'),operate:false},
                        {field: 'inviter_cancel', title: __('邀约人取消扣除金额'),operate:false},
                        {field: 'invitee_cancel', title: __('被邀约人取消扣除金额'),operate:false},
                        {field: 'refund_salary', title: __('退单收入'),operate:false},
                    ]
                ]
            });

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
                status: function(value, item, key)
                {
                    return item['status_text'];
                },
                package: function(value, row, key)
                {
                    var $str = "";
                    //套餐1
                    var package1 = row['package'];
                    if(package1){
                        $str += "套餐名称："+ package1['name'] + "<br>";
                        $str += "介绍：" + package1['intro'] + "<br>";
                        $str += "服务：<br>";
                        var service1 = package1['service'];
                        for(var s1 in service1) {
                            $str += (parseInt(s1)+1)+"、名称："+service1[s1]['name']+", 价格：" +service1[s1]['price'] + "<br>";
                        }
                    }
                    

                    return $str;

                },
                pay_mode: function(value, item, key)
                {
                    return item['pay_mode_text'];
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
