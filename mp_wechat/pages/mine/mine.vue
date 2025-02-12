<template>
  <view class="content">
    <image
      class="content-top"
      :src="
        app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "
    ></image>
    <view class="content-base">
      <uni-nav-bar
        :border="false"
        title=" "
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view class="userInfo" @click="toLogin">
        <image
          mode="aspectFill"
          v-if="token != null && isImprove == 1"
          class="userInfo-real"
          :src="userInfo.avatar_text"
        ></image>
        <image
          v-else
          src="/static/mine_center/non_login.png"
          class="userInfo-icon"
        />
        <view v-if="token != null && isImprove == 1" class="userInfo-desc">
          <view class="line1">
            <text class="line1-txt">{{ userInfo.nickname }}</text>
            <image
              v-if="userInfo.gender == 1"
              src="/static/sex_man.png"
              class="line1-sex"
            ></image>
            <image
              v-if="userInfo.gender == 2"
              src="/static/sex_woman.png"
              class="line1-sex"
            ></image>
            <image
              v-if="userInfo?.is_member == 1"
              src="/static/vip_icon.png"
              class="line1-vip"
            ></image>
            <image
              v-if="userInfo.is_cert_realname == 1"
              class="line1-vip"
              src="/static/person/confirm_name.png"
            ></image>
            <image
              v-if="userInfo.is_cert_education == 1"
              class="line1-vip"
              src="/static/person/edu.png"
            ></image>
          </view>
          <text class="line2"
            >{{ userInfo.birth_year }}年 · {{ userInfo.height }}cm ·
            {{ userInfo.area }}</text
          >
        </view>
        <text v-else-if="token == null" class="userInfo-warn">账号未登录</text>
        <text v-else-if="isImprove == -1" class="userInfo-warn"
          >未完善资料</text
        >
        <text v-else-if="isImprove == -2" class="userInfo-warn"
          >资料正在审核</text
        >
      </view>
      <view class="numsInfo">
        <view
          class="numsInfo-item"
          @click="openPage('/pages/my_following/my_following')"
        >
          <text class="txt1">{{
            userInfo == null ? 0 : userInfo.follow_num
          }}</text>
          <text class="txt2">我的关注</text>
        </view>
        <view
          @click="openPage('/pages/follow_me/follow_me')"
          class="numsInfo-item"
        >
          <text class="txt1">{{
            userInfo == null ? 0 : userInfo.fans_num
          }}</text>
          <text class="txt2">我的粉丝</text>
          <text
            v-if="userInfo != null && userInfo.new_fans_num > 0"
            class="notice"
            >+{{ userInfo.new_fans_num }}</text
          >
        </view>
        <view @click="openPage('/pages/news/news')" class="numsInfo-item">
          <text class="txt1">{{
            userInfo == null ? 0 : userInfo.new_message_num
          }}</text>
          <text class="txt2">新消息</text>
          <text
            v-if="userInfo != null && userInfo.new_message_num > 0"
            class="notice"
            >+{{ userInfo.new_message_num }}</text
          >
        </view>
      </view>
      <view class="vipInfo">
        <image
          class="vipInfo-back"
          :src="
            app.config.globalProperties.$imgBase +
            '/xlyl_meet/mine_center/vip_info.png'
          "
        ></image>
        <view class="vipInfo-base">
          <view
            :style="{
              flex: 1,
              flexShrink: 0,
              minWidth: 0,
              display: 'flex',
              flexDirection: 'column',
            }"
          >
            <text class="txt1">VIP会员</text>
            <text v-if="userInfo?.is_member != 1" class="txt2"
              >开通VIP会员，尊享多种会员权益</text
            >
            <text v-else class="txt2"
              >会员到期：{{ userInfo.member_expire_text }}</text
            >
          </view>
          <text
            @click="openPage('/pages/member_purchase/member_purchase')"
            class="buy_btn"
            >立即购买</text
          >
        </view>
      </view>
      <scroll-view class="options" scroll-y>
        <view v-if="shopInfo?.has_shop" class="options-store">
          <view class="head">
            <text class="head-title">门店管理</text>
            <text class="head-time">{{ shopInfo.create_date }}入驻</text>
          </view>
          <view class="manage">
            <view
              @click="openPage('/pages/store_information/store_information')"
              class="manage-item"
            >
              <image
                class="icon"
                :src="`/static/mine_center/information${
                  shopInfo.user_role == 1 ? '_p' : ''
                }.png`"
              ></image>
              <text class="txt1" :class="{ txt2: shopInfo.user_role == 1 }"
                >信息管理</text
              >
              <image
                v-if="shopInfo.shop_info_status == 1"
                class="audit_flag"
                src="/static/mine_center/audit_flag.png"
              ></image>
            </view>
            <view
              @click="openPage('/pages/order_manage/order_manage')"
              class="manage-item"
            >
              <image class="icon" src="/static/mine_center/order.png"></image>
              <text class="txt1 txt2">订单管理</text>
            </view>
            <view
              @click="openPage('/pages/store_revenue/store_revenue')"
              class="manage-item"
            >
              <image
                class="icon"
                :src="`/static/mine_center/income${
                  shopInfo.user_role == 1 ? '_p' : ''
                }.png`"
              ></image>
              <text class="txt1" :class="{ txt2: shopInfo.user_role == 1 }"
                >门店收入</text
              >
            </view>
            <view
              @click="
                openPage('/pages/employee_management/employee_management')
              "
              class="manage-item"
            >
              <image
                class="icon"
                :src="`/static/mine_center/staff${
                  shopInfo.user_role == 1 ? '_p' : ''
                }.png`"
              ></image>
              <text class="txt1" :class="{ txt2: shopInfo.user_role == 1 }"
                >员工管理</text
              >
            </view>
          </view>
        </view>
        <view class="options-top">
          <view
            v-for="(item, ind) in optionsTop"
            :key="'topOptions' + ind"
            class="item"
            @click="openPage(item.pagePath)"
            :style="{
              borderBottom:
                ind < optionsTop.length - 1 ? '1px solid #F0F2F5' : 'none',
            }"
          >
            <view class="item-left">
              <image :src="item.iconPath" class="icon"></image>
              <text class="title">{{ item.title }}</text>
            </view>
            <view class="item-right">
              <text v-if="ind == 0" class="info">
                {{
                  userInfo == null
                    ? ""
                    : "已完成" + (userInfo.complete_ratio + "%")
                }}</text
              >
              <image
                src="/static/mine_center/arrow_left.png"
                class="arrow_right"
              ></image>
            </view>
          </view>
        </view>

        <view class="options-top" :style="{ marginTop: '24rpx' }">
          <template
            v-for="(item, ind) in optionsBottom"
            :key="'bottomOptions' + ind"
          >
            <button
              v-if="item.pagePath == 'service'"
              class="item"
              open-type="contact"
              @contact="contractService"
              :style="{
                borderBottom:
                  ind < optionsBottom.length - 1 ? '1px solid #F0F2F5' : 'none',
              }"
            >
              <view class="item-left">
                <image :src="item.iconPath" class="icon"></image>
                <text class="title">{{ item.title }}</text>
              </view>
              <view class="item-right">
                <image
                  src="/static/mine_center/arrow_left.png"
                  class="arrow_right"
                ></image>
              </view>
            </button>
            <view
              v-else
              class="item"
              @click="openPage(item.pagePath)"
              :style="{
                borderBottom:
                  ind < optionsBottom.length - 1 ? '1px solid #F0F2F5' : 'none',
              }"
            >
              <view class="item-left">
                <image :src="item.iconPath" class="icon"></image>
                <text class="title">{{ item.title }}</text>
              </view>
              <view class="item-right">
                <image
                  src="/static/mine_center/arrow_left.png"
                  class="arrow_right"
                ></image>
              </view>
            </view>
          </template>
        </view>
        <view :style="{ width: '100%', height: '96px' }"></view>
      </scroll-view>
    </view>
    <uni-popup
      @change="loginClose"
      :style="{ zIndex: '99999' }"
      ref="loginPopup"
      type="bottom"
    >
      <uni-login></uni-login>
    </uni-popup>
    <uni-popup :style="{ zIndex: '99999' }" type="center" ref="completePopup">
      <meet-popup
        @confirm="toComplete"
        msg="你还没有完善资料，无法使用更多功能"
        confirmText="立即完善"
        cancelText="取消"
      ></meet-popup>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import { onShow } from "@dcloudio/uni-app";
import { getCurrentInstance, ref, computed } from "vue";
import { useStore } from "vuex";
const curPages: any = getCurrentPages()[0]; // 获取当前页面实例
const app = getCurrentInstance().appContext.app;
const store = useStore();
const loginPopup = ref();
const completePopup = ref();
const userInfo = computed(() => store.state.user.userInfo);
const token = computed(() => store.state.user.token);
const isImprove = computed(() => store.state.user.is_improve);
const shopInfo = computed(() => store.state.user.shopInfo);

const openPage = (path: string) => {
  if (path == "service") {
    return;
  }
  if (token.value == null) {
    toLogin();
    return;
  }
  if (
    path == "/pages/member_purchase/member_purchase" ||
    path == "/pages/data_editing/data_editing" ||
    path == "/static/mine_center/identity.png" ||
    path == "/pages/authentication/authentication"
  ) {
    // 未完善资料，提示下
    if (isImprove.value == -1) {
      completePopup.value.open();
      return;
    }   /* 暂时关闭此功能，by Richard
    else if (isImprove.value == -2) {
      uni.showToast({
        icon: "none",
        title: "您的信息正在审核，请等待审核完成！",
      });
      return;
    }*/
  }
  if (
    path == "/pages/settleds_apply/settleds_apply" &&
    shopInfo.value.has_shop == true
  ) {
    uni.showToast({
      icon: "none",
      title: "您已经入驻",
    });
    return;
  }
  if (path == "/pages/store_information/store_information") {
    if (shopInfo.value.shop_info_status == 1) {
      uni.showToast({
        icon: "none",
        title: "正在审核中，请等待审核完成后再编辑",
      });
      return;
    }
  }
  if (
    path == "/pages/store_information/store_information" ||
    path == "/pages/store_revenue/store_revenue" ||
    path == "/pages/employee_management/employee_management"
  ) {
    if (shopInfo.value.user_role == 2) {
      // 店员不允许进入
      return;
    }
  }
  uni.navigateTo({
    url: path,
  });
};

const optionsTop = [
  {
    iconPath: "/static/mine_center/perfect_info.png",
    title: "完善信息",
    pagePath: "/pages/data_editing/data_editing",
  },
  {
    iconPath: "/static/mine_center/identity.png",
    title: "身份认证",
    pagePath: "/pages/authentication/authentication",
  },
  {
    iconPath: "/static/mine_center/my_activity.png",
    title: "我的活动",
    pagePath: "/pages/my_activity/my_activity",
  },
  {
    iconPath: "/static/mine_center/my_dynamic.png",
    title: "我的动态",
    pagePath: "/pages/my_dynamic/my_dynamic",
  },
  {
    iconPath: "/static/mine_center/my_balance.png",
    title: "我的积分",
    pagePath: "/pages/my_balance/my_balance",
  },
  {
    iconPath: "/static/mine_center/red_packet.png",
    title: "红包余额",
    pagePath: "/pages/red_packet/red_packet",
  },
];
const optionsBottom = [
  {
    iconPath: "/static/mine_center/service.png",
    pagePath: "service",
    title: "联系客服",
  },
  {
    iconPath: "/static/mine_center/store_entry.png",
    title: "商家入驻",
    pagePath: "/pages/settleds_apply/settleds_apply",
  },
  {
    iconPath: "/static/mine_center/about.png",
    pagePath: "/pages/about/about",
    // pagePath: "/pages/complete_infomation/complete_infomation",
    title: "关于",
  },

];

onShow(() => {
  if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
    curPages.getTabBar().setData({
      selected: 4, // 表示当前菜单的索引，该值在不同的页面表示不同
    });
  }
  if (token.value != null) {
    store.dispatch("refreshInfo");
    store.dispatch("refreshInvitationNum");
  }
});

const contractService = (e) => {
  console.error(e);
};

// 登录弹窗关闭的时候，显示tabbar
const loginClose = () => {
  if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
    curPages.getTabBar().setData({
      showTabbar: true, // 显示tabbar
    });
  }
};

const toComplete = () => {
  // 去完善资料
  uni.navigateTo({
    url: "/pages/complete_infomation/complete_infomation",
  });
};

// 点击头像判断登录
const toLogin = () => {
  if (token.value == null) {
    if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
      curPages.getTabBar().setData({
        showTabbar: false, // 隐藏tabbar防止遮挡
      });
    }
    loginPopup.value.open();
    return;
  }
  if (isImprove.value == -1) {
    // 未完善
    completePopup.value.open();
  }
  /* 暂时关闭此功能，by Richard
  if (isImprove.value == -2) {
    uni.showToast({
      icon: "none",
      title: "您的信息正在审核，请等待审核完成！",
    });
    return;
  }*/
};
</script>

<style lang="scss" scoped>
.content {
  width: 100%;
  height: 100%;
  background-color: #f7f8fa;
  display: flex;
  flex-direction: column;
  &-top {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9;
    width: 750rpx;
    height: 500rpx;
  }
  &-base {
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    .userInfo {
      display: flex;
      flex-direction: row;
      align-items: center;
      // margin: 40rpx 40rpx 0 40rpx;
      width: 100%;
      padding: 0 40rpx;
      box-sizing: border-box;
      margin-top: 40rpx;
      &-icon {
        width: 120rpx;
        height: 120rpx;
        margin-left: 16rpx;
      }
      &-real {
        width: 120rpx;
        height: 120rpx;
        margin-left: 16rpx;
        border-radius: 60rpx;
      }
      &-warn {
        margin-left: 32rpx;
        font-size: 40rpx;
        color: #1d2129;
        font-weight: 600;
      }
      &-desc {
        display: flex;
        flex-direction: column;
        margin-left: 32rpx;
        .line1 {
          display: flex;
          flex-direction: row;
          align-items: center;
          &-txt {
            font-size: 40rpx;
            line-height: 56rpx;
            color: #1d2129;
            font-weight: 600;
          }
          &-sex {
            width: 40rpx;
            height: 42rpx;
            margin-left: 16rpx;
          }
          &-vip {
            margin-left: 12rpx;
            width: 40rpx;
            height: 40rpx;
          }
        }
        .line2 {
          margin-top: 12rpx;
          font-size: 26rpx;
          color: #868d9c;
        }
      }
    }
    .numsInfo {
      width: 100%;
      padding: 0 40rpx;
      box-sizing: border-box;
      margin-top: 32rpx;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      &-item {
        width: 180rpx;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        .txt1 {
          font-size: 40rpx;
          color: #1d2129;
          font-weight: 700;
          line-height: 60rpx;
        }
        .txt2 {
          font-size: 26rpx;
          color: #868d9c;
          line-height: 38rpx;
        }
        .notice {
          width: 42rpx;
          height: 32rpx;

          display: flex;
          align-items: center;
          justify-content: space-around;

          // padding: 6rpx 8rpx;
          font-size: 20rpx;
          font-weight: 500;
          color: #fff;
          background-color: #ff546f;
          // border-radius: 50%;
          border-radius: 18rpx;
          position: absolute;
          top: 0;
          right: 0;
        }
      }
    }
    .vipInfo {
      width: 670rpx;
      height: 120rpx;
      position: relative;
      margin-top: 32rpx;
      &-back {
        position: absolute;
        top: 0;
        left: 0;
        width: 670rpx;
        height: 120rpx;
        z-index: 8;
        border-radius: 20rpx;
      }
      &-base {
        z-index: 10;
        width: 670rpx;
        height: 120rpx;
        position: relative;
        box-sizing: border-box;
        padding: 0 32rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        .txt1 {
          font-size: 32rpx;
          font-weight: 500;
          color: #fff;
          line-height: 48rpx;
        }
        .txt2 {
          font-size: 24rpx;
          color: rgba(255, 255, 255, 0.5);
          line-height: 40rpx;
        }
        .buy_btn {
          width: 144rpx;
          height: 52rpx;
          background: linear-gradient(180deg, #edddff, #ffffff 100%);
          border-radius: 26rpx;
          font-size: 24rpx;
          font-weight: 500;
          color: #af6eff;
          line-height: 52rpx;
          text-align: center;
        }
      }
    }
    .options {
      width: 100%;
      margin-top: 24rpx;
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      &-store {
        width: 670rpx;
        background-color: #fff;
        border-radius: 32rpx;
        display: flex;
        flex-direction: column;
        margin: 0 auto 24rpx auto;
        .head {
          margin-top: 32rpx;
          width: 100%;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
          padding: 0 32rpx;
          box-sizing: border-box;
          &-title {
            font-size: 28rpx;
            font-weight: 500;
            color: #1d2129;
          }
          &-time {
            font-size: 26rpx;
            color: #868d9c;
          }
        }
        .manage {
          width: 100%;
          box-sizing: border-box;
          display: flex;
          flex-direction: row;
          justify-content: space-between;
          &-item {
            display: flex;
            flex: 1;
            flex-shrink: 0;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            padding: 32rpx 0;
            position: relative;
            z-index: 9;
            .icon {
              width: 50rpx;
              height: 48rpx;
            }
            .txt1 {
              font-size: 24rpx;
              color: #1d2129;
              margin-top: 16rpx;
            }
            .txt2 {
              color: #c2c5cc;
            }
            .audit_flag {
              width: 94rpx;
              height: 56rpx;
              position: absolute;
              right: 0;
              top: 0;
              z-index: 100;
            }
          }
        }
      }
      &-top {
        width: 670rpx;
        border-radius: 24rpx;
        background-color: #fff;
        margin: 0 auto;
        padding: 0 40rpx;
        box-sizing: border-box;
        .item {
          display: flex;
          flex-direction: row;
          align-items: center;
          height: 112rpx;
          padding: 0;
          background-color: #fff;
          border-bottom: 1px solid #f0f2f5;
          &::after {
            border: none;
          }
          &-left {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex: 1;
            flex-shrink: 0;
            min-width: 0;
            .icon {
              width: 48rpx;
              height: 48rpx;
            }
            .title {
              font-size: 28rpx;
              color: #1d2129;
              font-weight: 500;
              margin-left: 24rpx;
            }
          }
          &-right {
            display: flex;
            flex-direction: row;
            align-items: center;
            .info {
              font-size: 24rpx;
              color: #868d9c;
            }
            .arrow_right {
              width: 16rpx;
              height: 24rpx;
              margin-left: 16rpx;
            }
          }
        }
      }
    }
  }
}
</style>
