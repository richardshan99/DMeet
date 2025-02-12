<template>
  <view class="main">
    <image
      class="main-top"
      :src="app.config.globalProperties.$imgBase + '/xlyl_meet/member.png'"
    ></image>
    <view class="main-base">
      <uni-nav-bar
        left-icon="left"
        @clickLeft="navBack"
        color="#1D2129"
        :border="false"
        background-color="transparent"
        title="会员购买"
        :statusBar="true"
      ></uni-nav-bar>
      <view class="person">
        <image
          mode="aspectFill"
          :src="userInfo.avatar_text"
          class="person-avatar"
        ></image>
        <view class="person-intro">
          <text class="username">{{ userInfo.nickname }}</text>
          <text v-if="userInfo.is_member != 1" class="status"
            >暂未开通会员</text
          >
          <text v-else class="status"
            >会员到期：{{ userInfo.member_expire_text }}</text
          >
        </view>
      </view>
      <view class="content">
        <text class="content-choice">选择会员时长</text>
        <scroll-view class="content-packages" scroll-y :show-scrollbar="false">
          <view
            :style="{ width: '100%', display: 'flex', flexDirection: 'column' }"
          >
            <view class="child">
              <view
                v-for="(item, index) in packageInfo.dataList"
                @click="choosePrice(item)"
                class="child-base"
                :key="'pay' + index"
                :class="choice?.id == item.id ? 'active' : ''"
              >
                <view class="child-secondView">
                  <view
                    class="child-item common"
                    :class="choice?.id == item.id ? 'selected' : 'common'"
                  >
                    <text class="txt1">{{ item.name }}</text>
                    <text class="txt2">￥{{ item.price }}</text>
                    <text class="txt3">￥{{ item.price }}</text>
                  </view>
                </view>
              </view>
            </view>
          </view>
          <text class="content-title2">选择支付方式</text>
          <view class="content-methods">
            <view
              @click="changePayMethod(1)"
              class="parent"
              :class="payMethod == 1 ? 'parent_select' : ''"
            >
              <view class="second">
                <view class="item" :class="payMethod == 1 ? 'selected' : ''">
                  <image
                    class="item-wechat"
                    src="/static/wechat_pay.png"
                  ></image>
                  <text class="item-txt">微信支付</text>
                </view>
              </view>
            </view>
            <view
              @click="changePayMethod(2)"
              class="parent"
              :class="payMethod == 2 ? 'parent_select' : ''"
            >
              <view class="second">
                <view class="item" :class="payMethod == 2 ? 'selected' : ''">
                  <image
                    class="item-balance"
                    src="/static/balance_pay.png"
                  ></image>
                  <text class="item-txt">积分支付</text>
                </view>
              </view>
            </view>
          </view>
          <text class="content-title2">会员说明</text>
          <view
            v-if="packageInfo.detail != null"
            class="content-desc"
            v-html="packageInfo.detail"
          ></view>
        </scroll-view>
      </view>
    </view>
    <view @click="payNow" class="main-payNow">
      <text class="txt">立即支付</text>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { ref, getCurrentInstance, computed, reactive } from "vue";
import { useStore } from "vuex";
import { onLoad } from "@dcloudio/uni-app";
const store = useStore();
const userInfo = computed(() => store.state.user.userInfo);
const app = getCurrentInstance().appContext.app;
const packageInfo = reactive({
  dataList: [],
  detail: null,
});

const choice = ref(null);

const payMethod = ref(1);

onLoad(() => {
  getPackages();
});
const navBack = () => {
  uni.navigateBack();
};
const changePayMethod = (type: number) => {
  if (payMethod.value != type) {
    payMethod.value = type;
  }
};

const choosePrice = (item: any) => {
  if (choice.value.id == item.id) {
    return;
  }
  choice.value = item;
};

const getPackages = async () => {
  const res: any = await api.post("my/member/list");
  if (res.code == 1) {
    packageInfo.dataList = res.data.list;
    packageInfo.detail = res.data.desc;
    choice.value = res.data.list[0];
  }
};
const payNow = async () => {
  if (choice.value == null) {
    uni.showToast({
      icon: "none",
      title: "请选择套餐",
    });
    return;
  }
  const res: any = await api.post("/my/member/pay", {
    id: choice.value.id,
    pay_type: payMethod.value,
  });
  if (res.code == 1) {
    if (payMethod.value == 2) {
      uni.showToast({
        icon: "none",
        title: res.data.message,
      });
      return;
    } else if (payMethod.value == 1) {
      uni.requestPayment({
        provider: "wxpay",
        orderInfo: null,
        timeStamp: res.data.payment.timeStamp,
        nonceStr: res.data.payment.nonceStr,
        package: res.data.payment.package,
        signType: res.data.payment.signType,
        paySign: res.data.payment.paySign,
        success: (result) => {
          uni.showToast({
            icon: "none",
            title: "支付成功",
          });
          store.dispatch("refreshInfo");
        },
        fail: () => {
          uni.showToast({
            icon: "none",
            title: "支付失败",
          });
        },
      });
    }
  }
};
</script>

<style lang="scss">
.main {
  width: 100%;
  height: 100%;
  background-color: #f7f8fa;
  display: flex;
  flex-direction: column;
  position: relative;
  &-top {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9;
    width: 750rpx;
    height: 400rpx;
  }
  &-base {
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    .person {
      margin-top: 40rpx;
      // margin-left: 40rpx;
      width: 100%;
      padding: 0 40rpx;
      box-sizing: border-box;
      display: flex;
      flex-direction: row;
      align-items: center;
      &-avatar {
        width: 96rpx;
        height: 96rpx;
        border-radius: 48rpx;
        border: 3rpx solid #ffffff;
      }
      &-intro {
        margin-left: 24rpx;
        display: flex;
        flex-direction: column;
        .username {
          font-size: 32rpx;
          color: #1d2129;
          font-weight: 500;
          line-height: 48rpx;
        }
        .status {
          font-size: 24rpx;
          color: #4e5769;
          margin-top: 4rpx;
          line-height: 40rpx;
        }
      }
    }
    .content {
      width: 100%;
      flex: 1;
      min-height: 0;
      background-color: #fff;
      border-radius: 32rpx 32rpx 0px 0px;
      margin-top: 40rpx;
      display: flex;
      flex-direction: column;
      &-choice {
        margin-top: 32rpx;
        margin-left: 40rpx;
        font-size: 28rpx;
        color: #868d9c;
      }
      &-packages {
        flex: 1;
        min-height: 0;
        margin-top: 24rpx;
        width: 100%;
        .child {
          width: 670rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
          flex-wrap: wrap;
          margin: 0 auto;
          &-base {
            width: 324rpx;
            height: 196rpx;
            display: flex;
            flex-direction: column;
            background: #f0f2f5;
            margin-bottom: 24rpx;
            border-radius: 24rpx;
            padding: 3rpx;
            box-sizing: border-box;
          }
          .active {
            background: linear-gradient(118deg, #4a97e7, #b57aff 100%);
          }
          &-secondView {
            flex: 1;
            min-height: 0;
            border-radius: 24rpx;
            background-color: #fff;
          }
          &-item {
            width: 100%;
            height: 100%;
            border-radius: 24rpx;
            display: flex;
            flex-direction: column;
            align-items: center;

            .txt3 {
              font-size: 24rpx;
              color: #868d9c;
              line-height: 40rpx;
              text-decoration: line-through;
            }
          }
          .common {
            background: #ffffff;
            .txt1 {
              margin-top: 24rpx;
              font-size: 28rpx;
              color: #1d2129;
              line-height: 44rpx;
              font-weight: 500;
            }
            .txt2 {
              margin-top: 8rpx;
              font-size: 40rpx;
              color: #1d2129;
              font-weight: 600;
              line-height: 56rpx;
            }
          }
          .selected {
            background: linear-gradient(
              118deg,
              rgba(74, 151, 231, 0.1),
              rgba(181, 122, 255, 0.1) 100%
            );
            .txt1 {
              margin-top: 24rpx;
              font-size: 28rpx;
              background: linear-gradient(117deg, #4a97e7, #b57aff 100%);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
              line-height: 44rpx;
              font-weight: 500;
            }
            .txt2 {
              margin-top: 8rpx;
              font-size: 40rpx;
              background: linear-gradient(117deg, #4a97e7, #b57aff 100%);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
              font-weight: 600;
              line-height: 56rpx;
            }
          }
        }
      }
      &-title2 {
        margin-top: 48rpx;
        margin-left: 40rpx;
        font-size: 28rpx;
        color: #868d9c;
        line-height: 44rpx;
      }
      &-methods {
        width: 670rpx;
        margin: 24rpx auto 0 auto;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        .parent {
          width: 324rpx;
          height: 108rpx;
          padding: 3rpx;
          box-sizing: border-box;
          border-radius: 24rpx;
          background: #f0f2f5;
          display: flex;
          flex-direction: column;
          .second {
            flex: 1;
            min-height: 0;
            border-radius: 24rpx;
            background-color: #fff;
            .item {
              width: 100%;
              height: 100%;
              border-radius: 24rpx;
              background-color: #fff;
              display: flex;
              flex-direction: row;
              align-items: center;
              &-wechat {
                margin-left: 40rpx;
                width: 40rpx;
                height: 40rpx;
              }
              &-balance {
                margin-left: 40rpx;
                width: 42rpx;
                height: 40rpx;
              }
              &-txt {
                margin-left: 24rpx;
                font-size: 28rpx;
                color: #222222;
              }
            }
            .selected {
              position: relative;
              background: linear-gradient(
                106deg,
                rgba(74, 151, 231, 0.1),
                rgba(181, 122, 255, 0.1) 100%
              );
              // border-image: linear-gradient(106deg, #4a97e7, #b57aff 100%) 1.5 1.5;
            }
          }
        }
        .parent_select {
          background: linear-gradient(106deg, #4a97e7, #b57aff 100%);
        }
      }
      &-desc {
        margin: 24rpx auto 200rpx auto;
        width: 670rpx;
        font-size: 28rpx;
        color: #868d9c;
      }
    }
  }
  &-payNow {
    position: fixed;
    z-index: 99999;
    left: 0;
    right: 0;
    margin: 0 auto;
    bottom: 100rpx;
    width: 686rpx;
    height: 88rpx;
    background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
    border-radius: 44rpx;
    line-height: 88rpx;
    text-align: center;
    .txt {
      display: block;
      font-size: 28rpx;
      color: #ffffff;
      font-weight: 500;
    }
  }
}
</style>
