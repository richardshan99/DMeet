define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/change/index',
                    audit_url: 'shop/change/audit',
                    table: 'shop_change'
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
                        {field: 'shop_name', title: __('门店名称'),operate:"like"},
                        {field: 'thumb', title: __('门店缩略图'),operate:false, table: table,formatter:Table.api.formatter.image, events: Table.api.events.image},
                        {field: 'images', title: __('门店轮播图'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'address', title: __('地址'),operate:false},
                        {field: 'business_time', title: __('营业时间段'),operate:false},
                        {field: 'shop_name', title: __('门店套餐内容介绍'), align:'left', width: '300px',operate:false, formatter: Controller.api.formatter.package},
                        {field: 'content.content', title: __('门店文本'),operate:false},
                        {field: 'content.content_images', title: __('门店图片'),operate:false, table: table,formatter:Table.api.formatter.images, events: Table.api.events.image},
                        {field: 'status', title: __('状态'),operate:false, formatter: Controller.api.formatter.status},
                        {field: 'cash_type', title: __('提现类型'),operate:false, formatter: Controller.api.formatter.cash_type},
                        {field: 'cash_account', title: __('提现账号'), align:'left',operate:false, formatter: Controller.api.formatter.cash_account},
                        {field: 'create_time', title: __('提交时间'), formatter: Table.api.formatter.datetime, operate:false, addclass: 'datetimerange'},
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
                                    confirm: "确认审核通过该条信息吗？",
                                    refresh: true,
                                    // icon: 'fa fa-leaf',
                                    // extend: 'data-area=\'["70%", "60%"]\'',
                                    url: "shop/change/audit/type/approve",
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
                                    url: "shop/change/audit/type/reject",
                                    visible: function(row) {
                                        return row["status"] == 1;
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
        api: {
            formatter: {
                cash_type: function(value, row, index) {
                    return row["cash_type_text"];
                },
                cash_account: function(value, row, index)
                {
                    return "姓名:" + value["name"] +"<br>"
                        +  "账号:" + value["account"] +"<br>"
                        +  (row["cash_type"] == 3 ? ("开户行:" + value["deposit"]) : "");
                },
                status: function(value, row, index)
                {
                    return row['status_text'] + (row['status'] == 3 ?  "（"+row['reject_reason'] + "）": '');
                },
                package: function (value, row, index)
                {
                    var $str = "";
                    //套餐1
                    var package1 = row['package1'];
                    $str += "套餐1 名称："+ package1['name'] + "<br>";
                    $str += "价格："+ package1['price'] + "<br>";
                    $str += "介绍：" + package1['intro'] + "<br>";
                    $str += "服务：<br>";
                    var service1 = package1['service'];
                    for(var s1 in service1) {
                        $str += (parseInt(s1)+1)+"、名称："+service1[s1]['name']+", 价格：" +service1[s1]['price'] + "<br>";
                    }

                    $str += "<hr>";

                    //套餐2
                    var package2 = row['package2'];
                    $str += "套餐2 名称："+ package2['name'] + "<br>";
                    $str += "价格："+ package2['price'] + "<br>";
                    $str += "介绍：" + package2['intro'] + "<br>";
                    $str += "服务：<br>";
                    var service2 = package2['service'];
                    for(var s2 in service2) {
                        $str += (parseInt(s2)+1)+"、名称："+service2[s2]['name']+", 价格：" +service2[s2]['price'] + "<br>";
                    }

                    return $str;
                }
            }
        }

    };
    return Controller;
});
