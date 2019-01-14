define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'activity/votingrecord/index',
                    add_url: 'activity/votingrecord/add',
                    // edit_url: 'activity/votingrecord/edit',
                    del_url: 'activity/votingrecord/del',
                    multi_url: 'activity/votingrecord/multi',
                    table: 'voting_record',
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
                        {field: 'application.name', title: __('参选人姓名')},
                        {field: 'application.model', title: __('参选人车型')},
                        {field: 'application.daily_running_water', title: __('参选人日均流水'), operate:'BETWEEN'},
                        {field: 'application.service_points', title: __('参选人服务分')},
                        {field: 'application.applicationimages', title: __('参选人图片'), formatter: Table.api.formatter.images},
                        {field: 'application.votes', title: __('参选人得票数')},
                        {field: 'user.nickname', title: __('投票人昵称')},
                        {field: 'user.headimgurl', title: __('投票人头像'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'user.sex', title: __('投票人性别'), formatter: Controller.api.formatter.sex},
                        {field: 'user.city', title: __('投票人城市')},
                        {field: 'user.province', title: __('投票人省份')},
                        {field: 'votetime', title: __('Votetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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