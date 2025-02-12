define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/apply/index',
                    audit_url: 'shop/apply/audit',
                    table: 'shop_apply'
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
                        {field: 'name', title: __('申请人姓名'),operate:false},
                        {field: 'mobile', title: __('手机号码')},
                        {field: 'area', title: __('地区')},
                        {field: 'shop_name', title: __('门店名称'),operate:false},
                        {field: 'address', title: __('地址'),operate:false},
                        {field: 'category.name', title: __('门店类别'),operate:false},
                        {field: 'business_license_image', title: __('营业执照'),operate:false, table: table,formatter:Table.api.formatter.image, events: Table.api.events.image},
                        {field: 'other_images', title: __('其他图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        // {field: 'thumb', title: __('门店缩略图'),operate:false, table: table,formatter:Table.api.formatter.image, events: Table.api.events.image},
                        // {field: 'images', title: __('门店轮播图'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                      
                        {field: 'remark', title: __('备注'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},

                        // {field: 'business_time', title: __('营业时间段'),operate:false},
                        // {field: 'content', title: __('门店文本'),operate:false},
                        // {field: 'content_images', title: __('门店图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
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
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    extend: 'data-area=\'["70%", "60%"]\'',
                                    url: "shop/apply/audit/type/approve",
                                },
                                {
                                    name: 'audit',
                                    title: __("驳回"),
                                    text: __('驳回'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    // icon: 'fa fa-leaf',
                                    extend: 'data-area=\'["70%", "60%"]\'',
                                    url: "shop/apply/audit/type/reject",
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
        api: {
            formatter: {

            }
        }

    };
    return Controller;
});
