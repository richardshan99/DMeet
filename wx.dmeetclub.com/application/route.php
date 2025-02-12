<?php

use think\Route;


Route::group('api', function() { // API
    Route::group('common', function() { // 公共
        Route::post([
            'meet_announce' => 'api/common/meetAnnounce', //见面公示栏
            'meet_share'    => 'api/common/meetShare', //见面分享动态
            'area_list'     => 'api/common/areaList', //世界区域， 国家 - 省 -市
            'china_list'    => 'api/common/chinaList', //国内， 国家 - 省 -市
            'oversea_list'  => 'api/common/overseaList', //海外， 国家 - 省 -市
            'education_type_list'     => 'api/common/educationTypeList', //学历选项列表
            'constellation_type_list' => 'api/common/constellationTypeList', //星座选项列表
            'work_type_list'          => 'api/common/workTypeList', //工作情况选项列表
            'salary_list'          => 'api/common/salaryList', //工资列表
        ]);
    });

    Route::group('user', function() { // 用户
        Route::post([
            'improve_field_list' => 'api/user/improveFieldList', //完善信息字段 - 列表
            'edit_avatar'        => 'api/user/editAvatar', //我的 -个人名片 -编辑头像
            'edit_intro'         => 'api/user/editIntro', //我的 -个人名片 -编辑关于我
            'edit_myExpect'      => 'api/user/editmyExpect', //我的 -个人名片 -编辑我的期望
            'edit'               => 'api/user/edit', //我的 -个人名片 -编辑资料
            'label_list'         => 'api/user/labelList', //我的 -个人名片 - 标签列表
            'edit_label'         => 'api/user/editLabel', //我的 -个人名片 - 编辑标签
            'get_improve_info'   => 'api/user/getImproveInfo', //获取完善信息历史数据
        ]);
    });

    Route::group('index', function() { // 首页
        Route::post([
            'notice_list'   => 'api/index/noticeList', //平台公告 - 列表
            'notice_detail' => 'api/index/noticeDetail',//平台公告 - 详情
            'label_list'    => 'api/index/labelList',//筛选标签 - 列表 - 仅2级
            'all_label_list'    => 'api/index/allLabelList',//筛选标签 - 列表 - 1\2级
            'recommend_list' => 'api/index/recommendList',//推荐 - 列表
            'user_info'     => 'api/index/userInfo',//个人详情页
            'user_blog'     => 'api/index/userBlog',//近期动态
            'info'          => 'api/index/info',//首页公共
            'follow_wechat'  => 'api/index/followWechat',//关注公众号
        ]);
    });

    Route::group('invitation', function() { // 首页 - 发起邀约
        Route::post([
            'check'               => 'api/invitation/check',//前置检测
            'suggest_new_address' => 'api/invitation/suggestNewAddress',//新地点建议
            'shop_list'           => 'api/invitation/shopList',//门店列表
            'shop_detail'         => 'api/invitation/shopDetail',//门店详情
            'disable_meet_date'   => 'api/invitation/disableMeetDate',//不可选的日期
        ]);
    });

    Route::group('blog', function() { // 首页 - 动态
        Route::post([
            'list'      => 'api/blog/list',//列表
            'detail'    => 'api/blog/detail',//详情
            'publish'   => 'api/blog/publish',//发布
            'like'      => 'api/blog/like',//点赞
            'report'    => 'api/blog/report',//举报
        ]);
    });

    Route::group('invite', function() { // 邀约
        Route::post([
            'todo_list'         => 'api/invite/todoList',//待处理列表
            'my_invitation_list' => 'api/invite/myInvitationList',//历史记录 - 我的邀约
            'invite_me_list'    => 'api/invite/inviteMeList',//历史记录 - 邀约我的
            'badge'            => 'api/invite/badge', //我的邀约 / 邀约我的 - 角标

            'revoke' => 'api/invite/revoke',//历史记录 - 我的邀约/待确认 - 撤回邀约
            'cancel' => 'api/invite/cancel',//历史记录 - 待见面  - 取消邀约
            'qrcode' => 'api/invite/qrcode',//历史记录 - 待见面  - 核销码
            'approve' => 'api/invite/approve',//历史记录 - 邀约我的/待确认  - 同意
            'reject' => 'api/invite/reject',//历史记录 - 邀约我的/待确认  - 驳回
        ]);
    });

    Route::group('call', function() { // 见面
        Route::post([
            'initiate'      => 'api/call/initiate', //发起召集
            'hall'          => 'api/call/hall',     //召集大厅
            'concern'       => 'api/call/concern',   //感兴趣/不感兴趣
            'concern_hall'  => 'api/call/concernHall', //兴趣大厅
            'mine'          => 'api/call/mine',   //我发起的
            'concern_list'  => 'api/call/concernList',   //我发起的 - 感兴趣的用户
        ]);
    });

    Route::group('activity', function() { // 活动
        Route::post([
            'category' => 'api/activity/category', //活动类型 -- 列表
            'list'     => 'api/activity/list',     //活动 -- 列表
            'detail'   => 'api/activity/detail',   //活动 -- 详情
        ]);
    });

    Route::group('message', function() { // 我的 - 个人名片  - 消息
        Route::post([
            'list'        => 'api/message/list', //消息列表
            'template'        => 'api/message/template', //消息模版列表
            'likes_list'        => 'api/message/likesList', //点赞列表
            'invitation_list'   => 'api/message/invitationList', //邀约列表
            'chat_list'   => 'api/message/chatList', //消息 -打招呼 - 对话列表
            'chat_user_list'   => 'api/message/chatUserList', //消息 -打招呼列表
            'system_list'   => 'api/message/systemList', //消息 -系统列表
        ]);
    });

    Route::group('my', function() { // 我的
        Route::post([
            'follow_list' => 'api/my/followList', //我的关注
            'fans_list'   => 'api/my/fansList', //关注我的（我的粉丝）
        ]);

        Route::group('cert', function() { //身份认证
            Route::post([
                'realname'  => 'api/mycert/realname', //实名认证
                'education' => 'api/mycert/education',//教育认证
                'work'      => 'api/mycert/work',//工作认证
                'work_trade_list'      => 'api/mycert/workTradeList',//工作认证 - 行业列表
            ]);
        });

        Route::group('member', function() { //会员购买
            Route::post([
                'list'  => 'api/mymember/list', //会员列表
                'pay'   => 'api/mymember/pay', //支付
            ]);
        });

        Route::group('balance', function() { //我的余额
            Route::post([
                'list' => 'api/mybalance/list', //余额列表
            ]);
        });

        Route::group('dynamic', function() { //我的动态
            Route::post([
                'list'      => 'api/mydynamic/list', //列表
                'detail'    => 'api/mydynamic/detail', //详情
                'like_user' => 'api/mydynamic/likeUser', //点赞用户列表
                'del'       => 'api/mydynamic/del', //删除
            ]);
        });

        Route::group('activity', function() { //我的活动
            Route::post([
                'list'      => 'api/myactivity/list', //列表
                'refund'    => 'api/myactivity/refund', //退款
            ]);
        });

        Route::group('shop', function() { //申请入驻/门店管理
            Route::post([
                'check_apply' => 'api/myshop/checkApply', //查询入驻状态
                'apply'       => 'api/myshop/apply', //申请入驻
                'category'    => 'api/myshop/category', //门店类别列表
                'info'        => 'api/myshop/info', //门店信息管理 - 门店情况
                'change'      => 'api/myshop/change', //门店信息管理 - 门店信息维护 - 信息
                'submit_change' => 'api/myshop/submitChange', //门店信息管理 - 门店信息维护 - 提交

                'cash'         => 'api/myshop/cash', //门店管理 - 提现 - 提交
                'balance_list' => 'api/myshop/balanceList', //门店管理 - 金额明列表
                'cash_list'    => 'api/myshop/cashList', //门店管理 - 提现明细列表

                'order_list'   => 'api/myshop/orderList', //门店管理 - 订单管理 - 列表
                'order_verify' => 'api/myshop/orderVerify', //门店管理 - 订单管理 - 核销

                'clerk_list'   => 'api/myshop/ClerkList', //门店管理 - 店员管理 - 店员列表
                'add_clerk'    => 'api/myshop/addClerk', //门店管理 - 店员管理  - 添加店员
                'edit_clerk'   => 'api/myshop/editClerk', //门店管理 - 店员管理  - 编辑店员
                'del_clerk'    => 'api/myshop/delClerk', //门店管理 - 店员管理  - 删除店员
                'operate_clerk' => 'api/myshop/operateClerk', //门店管理 - 店员管理  - 冻结 / 恢复
            ]);
        });
    });
});