define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, Template, Echarts) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/no_restaurant/index',
                    add_url: 'shop/no_restaurant/add',
                    edit_url: 'shop/no_restaurant/edit',
                    del_url: 'shop/no_restaurant/del',
                    claim_url: 'shop/no_restaurant/claim',
                    multi_url: 'shop/no_restaurant/multi',
                    table: 'shop'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                search: false,
                commonSearch: true,
                showToggle: false,
                showColumns: true,
                showExport: false,
                columns: [
                    [
                        {field: 'name', title: __('门店名称'),operate:"like"},
                        // {field: 'mobile', title: __('手机号'),operate:"like"},
                        {field: 'shop_category_id', title: __('类别'), searchList: Config.shopCategory, formatter: Controller.api.formatter.shop_category},
                        {field: 'area', title: __('地区'),operate:false},
                        {field: 'point_text', title: __('经纬度'),operate:false},
                        {field: 'address', title: __('地址'),operate:false},
                        {field: 'business_time', title: __('营业时间段'),operate:false},
                        {field: 'operate', title: __('Operate'),
                            buttons: [
                              
                                {
                                    name: 'claim',
                                    text: '关联用户',
                                    icon: '',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'shop/no_restaurant/claim',
                                },
                            ],
                            table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            formatter: function (value, row, index) { //隐藏自定义的视频按钮
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                //权限判断
                                if(row.mobile){ //通过Config.chapter 获取后台存的chapter
                                    $(table).data("operate-claim", null);
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
        claim: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                shop_category: function(valeu, row, index) {
                    return row["category"]["name"];
                },
            },
            
        }

    };
    return Controller;
});
