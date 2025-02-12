<template>
  <view class="main">
    <image
      class="main-top"
      :src="
        app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "
    ></image>
    <view class="main-base">
      <uni-nav-bar
        left-icon="left"
        @clickLeft="navBack"
        :border="false"
        title="召集"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view class="main-notice">
        <image class="icon" src="/static/index/notice.png"></image>
        <swiper
          :interval="2000"
          :circular="true"
          class="swiper_notice"
          :autoplay="true"
          :vertical="true"
        >
          <swiper-item
            v-for="(item, ind) in noticeList"
            :key="'m_notice' + ind"
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'center',
            }"
          >
            <text class="info">{{ item }}</text>
          </swiper-item>
        </swiper>
      </view>
      <view v-if="firstMeetShare.content != null" class="main-first">
        <view class="child">
          <view class="child-left">
            <text class="info">{{ firstMeetShare.content }}</text>
            <view class="btns">
              <view class="btns-head">
                <image
                  :src="firstMeetShare.invitee_image_text"
                  class="icon1"
                ></image>
                <image
                  :src="firstMeetShare.inviter_image_text"
                  class="icon2"
                ></image>
              </view>
              <view @click="openMoreShare" class="btns-share">
                <text class="txt">更多分享</text>
                <image
                  class="arrow_left"
                  src="/static/invitation/arrow_left.png"
                ></image>
              </view>
            </view>
          </view>
          <image
            class="child-right"
            v-if="firstMeetShare.image_text != ''"
            :src="firstMeetShare.image_text"
          ></image>
        </view>
      </view>
      <view class="tab_bars">
        <view
          @click="changeMeetTab(0)"
          class="bar_item"
          :class="{ active_item: meetIndex == 0 }"
        >
          <text class="txt">召集大厅</text>
        </view>
        <view
          @click="changeMeetTab(1)"
          class="bar_item"
          :class="{ active_item: meetIndex == 1 }"
        >
          <text class="txt">感兴趣的</text>
        </view>
        <view
          @click="changeMeetTab(2)"
          class="bar_item"
          :class="{ active_item: meetIndex == 2 }"
        >
          <text class="txt">我发起的</text>
          <text v-if="badgeNum > 0" class="badge">{{ badgeNum }}</text>
        </view>
      </view>
      <scroll-view
        :scroll-y="true"
        type="list"
        class="scroll"
        @scrolltolower="loadMeetings"
      >
        <view
          v-if="meetIndex == 0 || meetIndex == 1"
          class="convene"
          v-for="(item, ind) in dataList"
          :key="'meet' + ind"
        >
          <view class="convene-user">
            <view @click="toUserDetail(item.user.id)" class="head">
              <image
                mode="aspectFill"
                :src="item.user.avatar_text"
                class="avatar"
              ></image>
              <view
                v-if="item.shop_type == 1"
                class="sign s_me"
                :class="{
                  s_me: item.pay_mode == 1,
                  s_you: item.pay_mode == 2,
                  s_aa: item.pay_mode == 3,
                }"
              >
                <text v-if="item.pay_mode == 1">我付</text>
                <text v-if="item.pay_mode == 2">你付</text>
                <text v-if="item.pay_mode == 3">AA</text>
              </view>
            </view>
            <view class="user_desc">
              <view class="line1">
                <text class="name">{{ item.user.nickname }}</text>
                <image
                  v-if="item.user.gender == 1"
                  class="sex"
                  src="/static/sex_man.png"
                ></image>
                <image
                  v-if="item.user.gender == 2"
                  class="sex"
                  src="/static/sex_woman.png"
                ></image>
                <image
                  v-if="item.user.is_member == 1"
                  src="/static/vip_icon.png"
                  class="other_icon"
                ></image>
                <image
                  v-if="item.user.is_cert_realname == 1"
                  src="/static/person/confirm_name.png"
                  class="other_icon"
                ></image>
                <image
                  v-if="item.user.is_cert_education == 1"
                  src="/static/person/edu.png"
                  class="other_icon"
                ></image>
              </view>
              <text class="line2"
                >{{ item.user.birth_year }}年 · {{ item.user.height }}cm ·
                {{ item.user.area }}</text
              >
            </view>
            <view
              @click="toggleInterested(item.id)"
              v-if="item.is_concern == -1"
              class="btn1"
            >
              <view class="child">
                <text class="txt">感兴趣</text>
              </view>
            </view>
            <view
              @click="toggleInterested(item.id)"
              v-if="item.is_concern == 1"
              class="btn2"
            >
              <text>取消兴趣</text>
            </view>
          </view>
          <image
            src="/static/invitation/invitation_line.png"
            class="convene-divide"
          ></image>
          <view
            @click="toShopDetail(item.shop.id, item.package.name)"
            class="convene-title"
            >[{{ item.shop.area_name }}] {{ item.shop.name }}
            <image
              :style="{ width: '14rpx', height: '20rpx', marginLeft: '8rpx' }"
              src="/static/invitation/arrow_right.png"
            ></image
          ></view>
          <view class="convene-row">
            <image class="icon" src="/static/invitation/location.png"></image>
            <text class="txt">{{ item.address }}</text>
          </view>
          <view class="convene-row">
            <image class="icon" src="/static/invitation/datetime.png"></image>
            <text class="txt">{{ item.meet_time_text }}</text>
          </view>
          <view class="convene-row" v-if="item.shop_type == 1">
            <image class="icon" src="/static/invitation/package.png"></image>
            <text class="txt"
              >{{ item.package.name }}（¥{{ item.price }}）</text
            >
          </view>
          <view class="convene-row" v-else>
            <image class="icon" src="/static/red_packet.png"></image>
            <text class="txt"
              >见面红包（¥{{ item.meeting_red_envelope_price }}）</text
            >
          </view>
        </view>
        <view
          class="mine"
          v-else
          v-for="(item, ind) in dataList"
          :key="'mine' + ind"
        >
          <view class="mine-user">
            <view
              :style="{
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'center',
                flex: 1,
                flexShrink: 0,
                minWidth: 0,
              }"
            >
              <image
                @click="toUserDetail(item.user.id)"
                mode="aspectFill"
                :src="item.user.avatar_text"
                class="avatar"
              ></image>
              <text class="name">{{ item.user.nickname }}</text>
              <view
                class="sign s_you"
                v-if="item.shop_type == 1"
                :class="{
                  s_me: item.pay_mode == 1,
                  s_you: item.pay_mode == 2,
                  s_aa: item.pay_mode == 3,
                }"
              >
                <text v-if="item.pay_mode == 1">你付</text>
                <text v-if="item.pay_mode == 2">我付</text>
                <text v-if="item.pay_mode == 3">AA</text>
              </view>
            </view>
            <view v-if="item.status == 1 || item.status == 3" class="status1"
              ><text>{{ item.status == 1 ? "进行中" : "邀约成功" }}</text></view
            >
          </view>
          <view
            @click="toShopDetail(item.shop.id, item.package.name)"
            class="mine-title"
            >[{{ item.shop.area_name }}] {{ item.shop.name
            }}<image
              :style="{ width: '14rpx', height: '20rpx', marginLeft: '8rpx' }"
              src="/static/invitation/arrow_right.png"
            ></image
          ></view>
          <view class="mine-row">
            <image class="icon" src="/static/invitation/location.png"></image>
            <text class="txt">{{ item.address }}</text>
          </view>
          <view class="mine-row">
            <image class="icon" src="/static/invitation/datetime.png"></image>
            <text class="txt">{{ item.meet_time_text }}</text>
          </view>
          <view class="mine-pack">
            <view class="row" v-if="item.shop_type == 1">
              <text class="top">{{ item.package.name }}</text>
              <text class="top">¥{{ item.price }}</text>
            </view>
            <view class="row" v-else>
              <text class="top">见面红包</text>
              <text class="top">¥{{ item.meeting_red_envelope_price }}</text>
            </view>
            <view class="row" v-if="item.shop_type == 1">
              <text class="bottom">实付金额</text>
              <text class="bottom">¥{{ item.price }}</text>
            </view>
            <view class="row" v-else>
              <text class="bottom">履行保证金</text>
              <text class="bottom">¥{{ item.deposit }}</text>
            </view>
          </view>
          <image
            src="/static/invitation/invitation_line.png"
            class="mine-divide"
          ></image>
          <view class="mine-end">
            <view
              v-if="item.invite_count > 0"
              :style="{
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'center',
              }"
            >
              <image
                v-for="(item, ind) in item.invite_avatars"
                :key="'invite' + ind"
                :class="'img' + (ind + 1)"
                :src="item"
              ></image>
              <text class="info1">已有{{ item.invite_count }}人感兴趣</text>
            </view>
            <view v-else></view>
            <view
              @click="toMoreUsers(item)"
              v-if="item.invite_count > 0"
              class="btn_now"
            >
              <view class="child">
                <text class="txt">查看用户</text>
              </view>
            </view>
          </view>
        </view>

        <text v-if="finish" class="scroll-status">{{
          dataList.length == 0 ? "没数据了" : "数据加载完毕"
        }}</text>
        <text v-else class="scroll-status">{{
          loading ? "正在加载" : "加载完毕"
        }}</text>
        <view :style="{ height: '32rpx', width: '100%' }"></view>
      </scroll-view>
    </view>
    <view @click="publishMeeting" class="main-gather">
      <text>发起召集</text>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { ref, reactive, getCurrentInstance, computed } from "vue";
import { onLoad, onUnload } from "@dcloudio/uni-app";
import { useStore } from "vuex";
const store = useStore();
const token = computed(() => store.state.user.token);
const isImprove = computed(() => store.state.user.is_improve);

const app = getCurrentInstance().appContext.app;
const meetIndex = ref(0);
const noticeList = ref([]);
const badgeNum = ref(0);

const dataList = ref([]);
let pageNo = 1;
const loading = ref(false);
const finish = ref(false);

const firstMeetShare = reactive({
  content: null,
  image_text: null,
  inviter_image_text: null,
  invitee_image_text: null,
});

const navBack = () => {
  uni.navigateBack();
};
onLoad(() => {
  api.post("/common/meet_share").then((vres: any) => {
    if (vres.code == 1 && vres.data.content != null) {
      Object.assign(firstMeetShare, vres.data);
    }
  });
  getMeetingList(true);
  uni.$on("meetingUpdate", refreshData);
  api.post("/call/mine").then((cres: any) => {
    if (cres.code == 1) {
      badgeNum.value = cres.data.badge;
    }
  });
  api.post("/common/meet_announce").then((res: any) => {
    if (res.code == 1) {
      noticeList.value = res.data.map((item) => item.content);
    }
  });
});

const toShopDetail = (shopId, serviceName: string) => {
  if (shopId == null || shopId.length <= 0) {
    uni.showToast({
      icon: "none",
      title: "无此店铺",
    });
    return;
  }
  uni.navigateTo({
    url: `/pages/shop_detail/shop_detail?id=${shopId}&serviceName=${serviceName}&type=watch`,
  });
};

onUnload(() => {
  uni.$off("meetingUpdate", refreshData);
});

const toggleInterested = async (id: string) => {
  const res: any = await api.post("call/concern", {
    call_id: id,
  });
  if (res.code == 1) {
    uni.showToast({
      icon: "none",
      title: res.msg,
    });
    getMeetingList(true);
  }
};

const refreshData = () => {
  if (meetIndex.value == 2) {
    getMeetingList(true);
  }
  api.post("/call/mine").then((cres: any) => {
    if (cres.code == 1) {
      badgeNum.value = cres.data.badge;
    }
  });
};

const loadMeetings = () => {
  pageNo++;
  getMeetingList(false);
};

const publishMeeting = async () => {
  if (token.value == null) {
    uni.showModal({
      title: "提示",
      content: "您还未登录，是否去我的页面登录？",
      success(xres) {
        if (xres.confirm) {
          uni.switchTab({
            url: "/pages/mine/mine",
          });
        }
      },
    });
    return;
  }
  if (isImprove.value == -1) {
    uni.showModal({
      title: "提示",
      content: "你还没有完善资料,无法发起召集",
      confirmText: "立即完善",
      success: (ures) => {
        if (ures.confirm) {
          uni.navigateTo({
            url: "/pages/complete_infomation/complete_infomation",
          });
        }
      },
    });
    return;
  }
  if (isImprove.value == -2) {
    uni.showToast({
      icon: "none",
      title: "您的信息正在审核，请先等待审核结果。",
    });
    return;
  }
  const res: any = await api.post("/invitation/check");
  if (res.code == 1 && res.data.can_invite == 1) {
    // 允许
    uni.navigateTo({
      url: "/pages/give_invitation/give_invitation?convene=T",
    });
  } else {
    if (res.data.code == -81) {
      uni.showModal({
        title: "提示",
        content: "只有会员才能使用邀约，是否购买会员",
        success: (bres) => {
          if (bres.confirm) {
            uni.navigateTo({
              url: "/pages/member_purchase/member_purchase",
            });
          }
        },
      });
    } else if (res.data.code == -80) {
      uni.showModal({
        title: "提示",
        content: "请先完成实名认证",
        success: (ures) => {
          if (ures.confirm) {
            uni.navigateTo({
              url: `/pages/attestation/attestation?type=1`,
            });
          }
        },
      });
    }
  }
};

const toMoreUsers = (item: any) => {
  uni.navigateTo({
    url: `/pages/interested_users/interested_users?id=${item.id}&count=${item.invite_count}`,
  });
};

const getMeetingList = async (refresh: boolean) => {
  let url = "call/hall";
  if (meetIndex.value == 0) {
    url = "call/hall";
  } else if (meetIndex.value == 1) {
    url = "/call/concern_hall";
  } else if (meetIndex.value == 2) {
    url = "call/mine";
  }
  if (refresh) {
    pageNo = 1;
    finish.value = false;
  }
  if (finish.value) {
    return;
  }
  if (loading.value) {
    return;
  }
  loading.value = true;
  try {
    const res: any = await api.post(url, {
      page: pageNo,
    });
    loading.value = false;
    if (res.code == 1) {
      if (url == "call/mine") {
        badgeNum.value = res.data.badge;
      }
      if (refresh) {
        if (meetIndex.value == 2) {
          dataList.value = res.data.list.data;
        } else {
          dataList.value = res.data.data;
        }
      } else {
        if (meetIndex.value == 2) {
          dataList.value = dataList.value.concat(res.data.list.data);
        } else {
          dataList.value = dataList.value.concat(res.data.data);
        }
      }
      if (dataList.value.length == res.data.total) {
        finish.value = true;
      }
    }
  } catch (e) {
    loading.value = false;
  }
};

const openMoreShare = () => {
  uni.navigateTo({
    url: "/pages/meeting_share/meeting_share",
  });
};

const changeMeetTab = (ind: number) => {
  if (meetIndex.value != ind) {
    meetIndex.value = ind;
    getMeetingList(true);
  }
};

const toUserDetail = (id: string) => {
  uni.navigateTo({
    url: `/pages/personal_details/personal_details?id=${id}`,
  });
};
</script>

<style lang="scss" scoped>
.main {
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
    .tab_bars {
      width: 686rpx;
      margin-top: 40rpx;
      margin-bottom: 24rpx;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      .bar_item {
        width: 212rpx;
        height: 64rpx;
        background-color: #ffffff;
        border-radius: 32rpx;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        position: relative;
        z-index: 7;
        .txt {
          font-size: 28rpx;
          color: #4e5769;
          position: relative;
          z-index: 8;
        }
        .badge {
          padding: 6rpx 15rpx;
          font-size: 20rpx;
          color: #fff;
          position: absolute;
          right: 0;
          top: 0;
          border-radius: 18rpx;
          background-color: #ff546f;
          z-index: 9;
        }
      }
      .active_item {
        background: linear-gradient(
          105deg,
          rgba(74, 151, 231, 0.15),
          rgba(181, 122, 255, 0.15) 100%
        );
        .txt {
          color: transparent;
          background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
      }
    }
    .scroll {
      width: 100%;
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      &-status {
        font-size: 26rpx;
        color: #999;
        text-align: center;
        margin: 20rpx auto;
        display: block;
      }
      .convene {
        width: 686rpx;
        background-color: #ffffff;
        border-radius: 24rpx;
        padding-bottom: 24rpx;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        margin: 0 auto 24rpx auto;
        &-user {
          width: 686rpx;
          margin-top: 32rpx;
          padding: 0 32rpx;
          box-sizing: border-box;
          display: flex;
          flex-direction: row;
          align-items: center;
          .head {
            width: 88rpx;
            height: 94rpx;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            .avatar {
              width: 88rpx;
              height: 88rpx;
              border-radius: 44rpx;
              z-index: 8;
              position: relative;
            }
            .sign {
              width: 64rpx;
              height: 32rpx;
              border: 1px solid #ffffff;
              border-radius: 18rpx;
              line-height: 32rpx;
              text-align: center;
              font-size: 20rpx;
              color: #fff;
              position: absolute;
              bottom: 0;
              z-index: 99;
            }
            .s_me {
              background: linear-gradient(114deg, #4a97e7, #b57aff 100%);
            }
            .s_you {
              background: linear-gradient(114deg, #ff66c2, #ff7ccb 100%);
            }
            .s_aa {
              background: linear-gradient(116deg, #00bc6d 0%, #2acf8a 100%);
            }
          }
          .user_desc {
            flex: 1;
            flex-shrink: 0;
            min-width: 0;
            margin-left: 24rpx;
            display: flex;
            flex-direction: column;
            .line1 {
              display: flex;
              flex-direction: row;
              .name {
                font-size: 28rpx;
                color: #1d2129;
                font-weight: 500;
              }
              .sex {
                width: 32rpx;
                height: 32rpx;
                margin-left: 16rpx;
              }
              .other_icon {
                width: 32rpx;
                height: 32rpx;
                margin-left: 8rpx;
              }
            }
            .line2 {
              margin-top: 2rpx;
              font-size: 22rpx;
              color: #868d9c;
              line-height: 38rpx;
            }
          }
          .btn1 {
            width: 160rpx;
            height: 64rpx;
            border-radius: 36rpx;
            padding: 1px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
            .child {
              flex: 1;
              border-radius: 36rpx;
              background: #fff;
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: center;
              .txt {
                font-size: 28rpx;
                background: linear-gradient(115deg, #4a97e7, #b57aff 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
              }
            }
          }
          .btn2 {
            width: 160rpx;
            height: 64rpx;
            border: 1px solid #cccccc;
            border-radius: 36rpx;
            line-height: 64rpx;
            text-align: center;
            font-size: 28rpx;
            color: #222222;
          }
        }
        &-divide {
          width: 686rpx;
          height: 24rpx;
          z-index: 16;
          margin-top: 10rpx;
        }
        &-title {
          width: 686rpx;
          padding: 0 32rpx;
          box-sizing: border-box;
          margin-top: 16rpx;
          font-size: 32rpx;
          font-weight: 500;
          color: #1d2129;
          margin-bottom: 8rpx;
        }
        &-row {
          width: 686rpx;
          padding: 0 32rpx;
          box-sizing: border-box;
          display: flex;
          flex-direction: row;
          align-items: center;
          margin-top: 8rpx;
          .icon {
            width: 32rpx;
            height: 32rpx;
          }
          .txt {
            margin-left: 16rpx;
            font-size: 24rpx;
            color: #4e5769;
            flex: 1;
            flex-shrink: 0;
            min-width: 0;
          }
        }
      }
      .mine {
        width: 686rpx;
        background-color: #ffffff;
        border-radius: 24rpx;
        padding-bottom: 24rpx;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        margin: 0 auto 24rpx auto;
        &-user {
          width: 686rpx;
          margin-top: 32rpx;
          padding: 0 32rpx;
          box-sizing: border-box;
          display: flex;
          flex-direction: row;
          align-items: center;
          .avatar {
            width: 48rpx;
            height: 48rpx;
            border-radius: 24rpx;
          }
          .name {
            margin-left: 24rpx;
            font-size: 28rpx;
            color: #1d2129;
          }
          .sign {
            margin-left: 18rpx;
            width: 64rpx;
            height: 32rpx;
            border-radius: 18rpx;
            line-height: 32rpx;
            text-align: center;
            font-size: 20rpx;
            color: #fff;
          }
          .s_me {
            background: linear-gradient(114deg, #4a97e7, #b57aff 100%);
          }
          .s_you {
            background: linear-gradient(114deg, #ff66c2, #ff7ccb 100%);
          }
          .s_aa {
            background: linear-gradient(116deg, #00bc6d 0%, #2acf8a 100%);
          }
          .status1 {
            font-size: 28rpx;
            font-weight: 500;
            background: linear-gradient(115deg, #4a97e7, #b57aff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          }
          .status2 {
            font-size: 28rpx;
            font-weight: 500;
            color: #b4b7bf;
          }
        }
        &-title {
          width: 686rpx;
          padding: 0 32rpx;
          box-sizing: border-box;
          margin-top: 32rpx;
          font-size: 32rpx;
          font-weight: 500;
          color: #1d2129;
          margin-bottom: 8rpx;
        }
        &-row {
          width: 686rpx;
          padding: 0 32rpx;
          box-sizing: border-box;
          display: flex;
          flex-direction: row;
          align-items: center;
          margin-top: 8rpx;
          .icon {
            width: 32rpx;
            height: 32rpx;
          }
          .txt {
            margin-left: 16rpx;
            font-size: 24rpx;
            color: #4e5769;
            flex: 1;
            flex-shrink: 0;
            min-width: 0;
          }
        }
        &-pack {
          width: 622rpx;
          padding: 12rpx 24rpx;
          margin: 24rpx auto 0 auto;
          box-sizing: border-box;
          background: #f7f8fa;
          border-radius: 16rpx;
          .row {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            .top {
              font-size: 24rpx;
              color: #1d2129;
              line-height: 40rpx;
            }
            .bottom {
              font-size: 24rpx;
              color: #868d9c;
              line-height: 40rpx;
            }
          }
        }
        &-divide {
          width: 686rpx;
          height: 24rpx;
          z-index: 16;
          margin-top: 16rpx;
        }
        &-end {
          width: 686rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
          padding: 0 32rpx;
          box-sizing: border-box;
          .img1 {
            width: 48rpx;
            height: 48rpx;
            border-radius: 24rpx;
            border: 3rpx solid #ffffff;
            z-index: 8;
          }
          .img2 {
            width: 48rpx;
            height: 48rpx;
            border-radius: 24rpx;
            border: 3rpx solid #ffffff;
            margin-left: -12rpx;
            z-index: 9;
          }
          .img3 {
            width: 48rpx;
            height: 48rpx;
            border-radius: 24rpx;
            border: 3rpx solid #ffffff;
            margin-left: -12rpx;
            z-index: 10;
          }
          .info1 {
            margin-left: 16rpx;
            font-size: 24rpx;
            color: #868d9c;
            // #1D2129
          }
          .btn_now {
            width: 176rpx;
            height: 64rpx;
            background: linear-gradient(107deg, #4a97e7, #b57aff 100%);
            border-radius: 36rpx;
            padding: 1px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            .child {
              flex: 1;
              flex-shrink: 0;
              min-height: 0;
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: center;
              background-color: #fff;
              border-radius: 36rpx;
              .txt {
                font-size: 28rpx;
                background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
              }
            }
          }
        }
      }
    }
  }
  &-notice {
    margin-top: 16rpx;
    width: 686rpx;
    height: 70rpx;
    background: linear-gradient(
      95deg,
      rgba(74, 151, 231, 0.12),
      rgba(181, 122, 255, 0.12) 100%
    );
    border-radius: 32rpx;
    display: flex;
    flex-direction: row;
    align-items: center;
    .icon {
      margin-left: 24rpx;
      width: 28rpx;
      height: 30rpx;
    }
    .swiper_notice {
      flex: 1;
      flex-shrink: 0;
      min-width: 0;
      height: 70rpx;
    }
    .info {
      margin-left: 12rpx;
      font-size: 26rpx;
      color: #1d2129;
    }
  }
  &-first {
    margin-top: 24rpx;
    width: 686rpx;
    border-radius: 24rpx;
    box-sizing: border-box;
    position: relative;
    &::before {
      content: "";
      display: block;
      width: 100%;
      height: 100%;
      border-radius: 24rpx;
      position: absolute;
      z-index: 9;
      background: linear-gradient(107deg, #ff66c2, #ff7ccb 100%);
      opacity: 0.16;
    }
    .child {
      position: relative;
      margin: 32rpx;
      z-index: 10;
      display: flex;
      flex-direction: row;
      &-left {
        flex: 1;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        min-width: 0;
        .info {
          font-size: 28rpx;
          color: #1d2129;
          line-height: 44rpx;
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        .btns {
          margin-top: 30rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
          &-head {
            display: flex;
            flex-direction: row;
            .icon1 {
              width: 48rpx;
              height: 48rpx;
              border-radius: 24rpx;
              border: 1px solid #ffffff;
              z-index: 9;
            }
            .icon2 {
              width: 48rpx;
              height: 48rpx;
              border-radius: 24rpx;
              border: 1px solid #ffffff;
              z-index: 10;
              margin-left: -12rpx;
            }
          }
          &-share {
            width: 132rpx;
            height: 44rpx;
            background: linear-gradient(106deg, #ff66c2, #ff7ccb 100%);
            border-radius: 24rpx;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            .txt {
              font-size: 20rpx;
              color: #fff;
              font-weight: 500;
            }
            .arrow_left {
              width: 8rpx;
              height: 12rpx;
              margin-left: 8rpx;
            }
          }
        }
      }
      &-right {
        margin-left: 40rpx;
        width: 176rpx;
        height: 176rpx;
        border-radius: 12rpx;
      }
    }
  }
  &-gather {
    width: 256rpx;
    height: 88rpx;
    background: linear-gradient(107deg, #4a97e7, #b57aff 100%);
    border-radius: 48rpx;
    box-shadow: 0px 16rpx 64rpx 0px rgba(129, 137, 244, 0.15);
    line-height: 88rpx;
    text-align: center;
    font-size: 32rpx;
    color: #fff;
    font-weight: 500;
    position: fixed;
    bottom: 92rpx;
    left: 0;
    right: 0;
    margin: 0 auto;
    z-index: 99;
  }
}
</style>
