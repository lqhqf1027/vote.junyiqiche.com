define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'activity/user/index',
                    // add_url: 'activity/user/add',
                    // edit_url: 'activity/user/edit',
                    del_url: 'activity/user/del',
                    multi_url: 'activity/user/multi',
                    table: 'wechat_user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'nickname', title: __('nickname')},
                        {field: 'headimgurl', title: __('Headimgurl'), formatter: Table.api.formatter.image},
                        {field: 'sex', title: __('Sex'), formatter: Controller.api.formatter.sex},
                        {field: 'city', title: __('City')},
                        {field: 'province', title: __('Province')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {

                sex: function (value, row, index) {
                    if (row.sex == 1) {
                        return "男";
                    }
                    if (row.sex == 2) {
                        return "女";
                    }
                },
            }
        }
    };
    return Controller;
});