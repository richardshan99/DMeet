define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, Template, Echarts) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                search: false,
                commonSearch: true,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'avatar', title: __('头像'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'nickname', title: __('昵称'), operate: 'LIKE'},
                        {field: 'mobile', title: __('手机号'), operate: 'LIKE'},
                        {field: 'name', title: __('姓名'), operate: false},
                        {field: 'idcard', title: __('身份证号'), operate: false},
                        {field: 'birth', title: __('出生年月'), operate: false},
                        {field: 'height', title: __('身高'), operate: false},
                        {field: 'school', title: __('学校'), operate: false},
                        {field: 'education_type', title: __('学历'), operate: false, formatter:Controller.api.formatter.education_type},
                        {field: 'area_id', title: __('所在地'), formatter: Controller.api.formatter.area, searchList: function (column) {
                            return Template('areatpl', {});
                        }},
                        {field: 'hometown', title: __('家乡')},
                        {field: 'gender', title: __('性别'), visible: false, searchList:{1:"男", 2:"女"}},
                        {field: 'age', title: __('年龄'), visible: false, searchList: function (column) {
                            return Template('agetpl', {});
                        }},
                        {field: 'work_type', title: __('工作情况'), operate: false, formatter: Controller.api.formatter.work_type},
                        {field: 'source_id', title: __('渠道来源'), operate: false, formatter: Controller.api.formatter.source},
                        {field: 'is_cert_realname', title: __('实名认证'), operate: false, formatter:Controller.api.formatter.is_cert_realname},
                        {field: 'is_member', title: __('是否会员'), operate: false, formatter:Controller.api.formatter.is_member},
                        {field: 'is_cert_work', title: __('学历认证'), operate: false, formatter:Controller.api.formatter.is_cert_work},
                        {field: 'status', title: __('冻结/解冻'),operate:false, formatter: Table.api.formatter.toggle, table: table ,yes: 'normal', no: 'hidden'},
                        {field: 'new_status', title: __('用户状态'), searchList:{1:"授权用户", 2:"普通用户", 3:"认证用户", 4:"会员用户"}, formatter:Controller.api.formatter.new_status},
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'detail',
                                    title: __("用户详情"),
                                    text: __('用户详情'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    url: "user/user/detail",
                                },
                                {
                                    name: 'sendmessage',
                                    title: __("发送系统消息"),
                                    text: __('发送系统消息'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    extend: 'data-area=\'["100%", "100%"]\'',
                                    url: "user/user/sendmessage",
                                }
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
                Controller.api.echart(data.extend.chart);
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        sendmessage: function () {
            Controller.api.bindevent();
        },
        detail: function() {
            Table.api.init({
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'user/user/message/ids/'+Config.user_id,
                extend: {

                },
                pk: 'id',
                sortName: 'id',
                search: false,
                commonSearch: false,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'fromuser.nickname', title: __('留言用户昵称'), operate: false},
                        {field: 'fromuser.avatar', title: __('头像'), operate: false, table: table,formatter:Table.api.formatter.image, events: Table.api.events.image},
                        {field: 'fromuser.mobile', title: __('手机号码'), operate: false},
                        {field: 'message', title: __('留言内容'), operate: false},
                        {field: 'create_time', title: __('留言时间'), operate: false, formatter: Table.api.formatter.datetime},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                area: function(value, item, key)
                {
                    return item['area'];
                },
                work_type: function(value, item, key)
                {
                    return item['work_type_text'];
                },
                source: function(value, item, key)
                {
                    return item['source_text'];
                },
                education_type: function(value, item, key)
                {
                    return item['education_type_text'];
                },
                is_cert_realname: function(value, item, key)
                {
                    return item['is_cert_realname'] == 1 ? "是" : "否";
                },
                is_member: function(value, item, key)
                {
                    return item['is_member'] == 1 ? "是" : "否";
                },
                is_cert_work: function(value, item, key)
                {
                    return item['is_cert_work'] == 1 ? "是" : "否";
                },
                new_status: function(value, item, key)
                {
                    if(item['is_improve'] == -1) {
                        return '授权用户';
                    }
                    if(item['is_improve'] == 1) {
                        return '普通用户';
                    }
                    if(item['is_cert_realname'] == 1) {
                        return '认证用户';
                    }
                    if(item['is_member'] == 1) {
                        return '会员用户';
                    }
                },
            },
            echart: function (data){
                var _keys = [],
                    _vals = [],
                    _ratio = [];
                for(var i in data) {
                    var _item = data[i];
                    _keys.push(_item['name']);
                    _vals.push(_item['value']);
                    _ratio.push(_item['ratio']);
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
                    legend: {
                        data: ['数量', '占比']
                    },
                    xAxis: [
                        {
                            type: 'category',
                            axisTick: {
                                alignWithLabel: true
                            },
                            // prettier-ignore
                            data: _keys
                        }
                    ],
                    yAxis: [
                        {
                            type: 'value',
                            name: '数量',
                            position: 'left',
                            alignTicks: true,
                            axisLine: {
                                show: true,
                            },
                            axisLabel: {
                                formatter: '{value}'
                            }
                        },
                        {
                            type: 'value',
                            name: '占比',
                            position: 'right',
                            alignTicks: true,
                            axisLine: {
                                show: true,
                            },
                            axisLabel: {
                                formatter: '{value}%'
                            }
                        }
                    ],
                    series: [
                        {
                            name: '数量',
                            type: 'bar',
                            data: _vals
                        },
                        {
                            name: '占比',
                            type: 'bar',
                            yAxisIndex: 1,
                            data: _ratio
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                barChart.setOption(option);
            }
        }
    };
    return Controller;
});