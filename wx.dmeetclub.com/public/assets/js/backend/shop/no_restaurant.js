// 定义变量来存储异步获取的数据
var areaList = {};
var cityList = {};
var provinceList = {};

// 创建一个 Deferred 对象
var areaListDeferred = $.Deferred();
//李川 2025-03-09 10:00:00
// 异步获取数据并存储到变量中
$.getJSON('ajax/getShopArea', function (data) {
    console.log(data);
    if (data.data) {
        $.each(data.data, function (index, item) {
            areaList[item.value] = item.name;
        });
    }
    // 当数据准备好时，解决 Deferred 对象
    areaListDeferred.resolve();
});

define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, Template, Echarts) {

    var Controller = {
        index: function () {
            // 等待 areaListDeferred 解决后再初始化表格
            $.when(areaListDeferred).done(function () {
               

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
                    showSearch: true,
                    commonSearch: true,
                    showToggle: false,
                    showColumns: true,
                    showExport: false,
                    pageSize: 50, // 设置每页显示50行
                    
                    columns: [
                        [
                            { field: 'areaName', title: __('区域'), operate: false },
                            { field: 'province', title: __('城市'), operate: "select", searchList: areaList, visible: false },
                            { field: 'city', title: __('地区'), operate: "select", searchList: provinceList, visible: false },
                            { field: 'name', title: __('门店名称'), operate: "like" },
                            { field: 'shop_category_id', title: __('类别'), searchList: Config.shopCategory, formatter: Controller.api.formatter.shop_category },

                            { field: 'point_text', title: __('经纬度'), operate: false },
                            { field: 'address', title: __('地址'), operate: false },
                            { field: 'business_time', title: __('营业时间段'), operate: false },
                            { field: 'recommend', title: __('推荐等级'), operate: false },
                            {
                                field: 'operate', title: __('Operate'),
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
                                formatter: function (value, row, index) {
                                    var that = $.extend({}, this);
                                    var table = $(that.table).clone(true);
                                    if (row.mobile) {
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

                $('.commonsearch-table').removeClass('hidden');

                // 监听城市下拉框变化事件
                $(document).on('change', 'select[name="province"]', function () {
                    var provinceId = $(this).val();
                    console.log(provinceId);
                    localStorage.setItem('provinceId', provinceId);
                    if (provinceId) {
                        // 根据选择的国家动态加载城市数据
                        $.getJSON('ajax/getShopArea', { pid: provinceId }, function (data) {
                            var cityOptions = {};
                            if (data.data) {
                                $.each(data.data, function (index, item) {
                                    cityOptions[item.value] = item.name;
                                });
                            }
                            // 更新城市下拉框的内容
                            var provinceSelect = $('select[name="city"]');
                            provinceSelect.html('<option value="">' + __('选择') + '</option>');
                            $.each(cityOptions, function (key, value) {
                                provinceSelect.append('<option value="' + key + '">' + value + '</option>');
                            });

                            // 更新表格数据
                            table.bootstrapTable('refresh', {
                                query: { province: provinceId }
                            });
                        });
                    } else {
                        $('select[name="city"]').html('<option value="">' + __('选择') + '</option>');
                        // 清空表格数据
                        table.bootstrapTable('refresh', {
                            query: { province: '' }
                        });
                    }
                });

                // 监听地区下拉框变化事件
                $(document).on('change', 'select[name="city"]', function () {
                    var cityId = $(this).val();
                    localStorage.setItem('cityId', cityId);
                    console.log(cityId);
                    if (cityId) {
                        // 更新表格数据
                        table.bootstrapTable('refresh', {
                            query: { city: cityId }
                        });
                    } else {
                        // 清空表格数据
                        table.bootstrapTable('refresh', {
                            query: { city: '' }
                        });
                    }
                });

                // 监听类型下拉框变化事件
                $(document).on('change', 'select[name="shop_category_id"]', function () {
                    var shop_category_id = $(this).val();
                    console.log(shop_category_id);
                    localStorage.setItem('shop_category_id', shop_category_id);
                    if (shop_category_id) {
                        // 更新表格数据
                        table.bootstrapTable('refresh', {
                            query: { shop_category_id: shop_category_id }
                        });
                    } else {
                        // 清空表格数据
                        table.bootstrapTable('refresh', {
                            query: { shop_category_id: '' }
                        });
                    }
                });
            });
        },
        ratio: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));

            $("[data-toggle='addresspicker']").data("callback", function (res) {
                $('#c-point').val(res.lng + "," + res.lat);
                $('#c-address').val(res.address);
            });

            require(['layui'], function (Layui) {
                Layui.use('laydate', function () {
                    var laydate = Layui.laydate;
                    laydate.render({
                        elem: '.timeclicknew',
                        type: 'time',
                        range: true,
                        format: 'HH:mm'
                    });
                });
            });
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));

            $("[data-toggle='addresspicker']").data("callback", function (res) {
                $('#c-point').val(res.lng + "," + res.lat);
                $('#c-address').val(res.address);
            });

            require(['layui'], function (Layui) {
                Layui.use('laydate', function () {
                    var laydate = Layui.laydate;
                    laydate.render({
                        elem: '.timeclicknew',
                        type: 'time',
                        range: true,
                        format: 'HH:mm'
                    });
                });
            });
        },
        claim: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                shop_category: function (value, row, index) {
                    return row["category"]["name"];
                },
            },
        }
    };
    return Controller;
});