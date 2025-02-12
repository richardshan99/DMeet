define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/label/index',
                    add_url: 'user/label/add',
                    edit_url: 'user/label/edit',
                    del_url: 'user/label/del',
                    multi_url: 'user/label/multi',
                    table: 'user_label'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: '',
                escape: false,
                dblClickToEdit: false,
                pagination: false,
                search: false,
                commonSearch: false,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {field: 'name',  cellStyle: function (value, row, index) {
                                return {css: {"padding-left": "30%", 'text-align': 'left!important'}};
                            },title: __('标签'), formatter: Controller.api.formatter.name},
                        {
                            field: 'is_filter',
                            title: __('是否为首页筛选项'),
                            align: 'center',
                            table: table,
                            width:'180px',
                            yes: 1,
                            no: -1,
                            formatter: Controller.api.formatter.filter
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            width:'250px',
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                rowAttributes: function (row, index) {
                    if (this.totalRows > 500) {
                        return row.pid == 0 ? {} : {style: "display:none"};
                    }
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //显示隐藏子节点
            $(document).on("click", ".btn-node-sub", function (e) {
                var status = $(this).data("shown") ? true : false;
                $("a.btn[data-pid='" + $(this).data("id") + "']").each(function () {
                    $(this).closest("tr").toggle(!status);
                });
                $(this).data("shown", !status);
                return false;
            });


            //展开隐藏全部
            $(document.body).on("click", ".btn-toggle-all", function (e) {
                var that = this;
                var show = $("i", that).hasClass("fa-plus");
                $("i", that).toggleClass("fa-plus", !show);
                $("i", that).toggleClass("fa-minus", show);
                $(".btn-node-sub.disabled").closest("tr").toggle(show);
                $(".btn-node-sub").data("shown", show);
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                subnode: function (value, row, index) {
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Toggle sub menu') + '" data-id="' + row.id + '" data-pid="' + row.pid + '" class="btn btn-xs '
                        + (row.haschild == 1 ? 'btn-success' : 'btn-default disabled') + ' btn-node-sub"><i class="fa fa-sitemap"></i></a>';
                },
                filter: function(value, row, index) {
                    return row['pid'] != 0 ? Table.api.formatter.toggle.call(this, value, row, index) : "";
                },
            },
            bindevent: function () {
                $(document).on('click', "input[name='row[ismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    var ismenu = $(this).val() == 1;
                    name.prop("placeholder", ismenu ? name.data("placeholder-menu") : name.data("placeholder-node"));
                    $('div[data-type="menu"]').toggleClass("hidden", !ismenu);
                });
                $("input[name='row[ismenu]']:checked").trigger("click");

                var iconlist = [];
                var iconfunc = function () {
                    Layer.open({
                        type: 1,
                        area: ['99%', '98%'], //宽高
                        content: Template('chooseicontpl', {iconlist: iconlist})
                    });
                };
                Form.api.bindevent($("form[role=form]"), function (data) {
                    Fast.api.refreshmenu();
                });
                $(document).on('change keyup', "#icon", function () {
                    $(this).prev().find("i").prop("class", $(this).val());
                });
                $(document).on('click', ".btn-search-icon", function () {
                    if (iconlist.length == 0) {
                        $.get(Config.site.cdnurl + "/assets/libs/font-awesome/less/variables.less", function (ret) {
                            var exp = /fa-var-(.*):/ig;
                            var result;
                            while ((result = exp.exec(ret)) != null) {
                                iconlist.push(result[1]);
                            }
                            iconfunc();
                        });
                    } else {
                        iconfunc();
                    }
                });
                $(document).on('click', '#chooseicon ul li', function () {
                    $("input[name='row[icon]']").val('fa fa-' + $(this).data("font")).trigger("change");
                    Layer.closeAll();
                });
                $(document).on('keyup', 'input.js-icon-search', function () {
                    $("#chooseicon ul li").show();
                    if ($(this).val() != '') {
                        $("#chooseicon ul li:not([data-font*='" + $(this).val() + "'])").hide();
                    }
                });
            }
        }
    };
    return Controller;
});
