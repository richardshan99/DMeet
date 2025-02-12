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
        :shadow="false"
        title="提现"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>

    <view class="box">

      <view class="money">
        <view class="num">
          <input
            placeholder="请输入内容"
            border="surround"
            v-model="value"
            type="number"
          ></input>
        </view>
        <view class="info">
          <view>可提现 ¥{{money}}</view>
          <view>提现金额不少于¥100.00</view>
        </view>
      </view>

      <view class="withdraw">
        <view class="lable">提现账户</view>
        <view class="value">微信零钱包</view>
      </view>

      <view class="btn" @click="withdraw">确认提现</view>
    </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, ref } from "vue";
import { onLoad } from "@dcloudio/uni-app";
const app = getCurrentInstance().appContext.app;
const money = ref("");

const value = ref("");

onLoad((options) => {
  money.value = options.money;
});

const withdraw = async () => {
  console.log(value.value);
  if (Number(value.value) < 100) {
    uni.showToast({
      title: "提现金额不少于100",
      icon: "none",
    });
    return;
  }

  const res = await api.post("my_red_envelope_balance/add", {
    money: value.value,
  });

  if (res.code == 1) {
    uni.showToast({
      title: "提现成功",
      icon: "none",
    });
  } else {
    uni.showToast({
      title: res.msg,
      icon: "none",
    });
  }
};

const navBack = () => {
  uni.navigateBack();
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
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .box {
    width: 686rpx;
    padding: 0 32rpx;

    .money {
      width: 100%;
      height: 248rpx;
      background: #ffffff;
      border-radius: 24rpx;
      margin-top: 24rpx;
      display: flex;
      flex-direction: column;

      .num {
        flex: 1;
        padding: 0 32rpx;
        input {
          width: 100%;
          height: 100%;
        }
      }
      .info {
        width: calc(100% - 64rpx);
        margin-left: 32rpx;
        height: 104rpx;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: #dadce0 solid 1rpx;

        font-size: 24rpx;
        font-family: PingFang SC, PingFang SC-400;
        font-weight: 400;
        color: #868d9c;
      }
    }

    .withdraw {
      width: 686rpx;
      height: 108rpx;
      background: #ffffff;
      border-radius: 24rpx;
      margin-top: 24rpx;
      display: flex;
      align-items: center;
      justify-content: space-between;
      //   width: calc(100% - 64rpx);
      //   margin-left: 32rpx;
      .lable {
        font-size: 28rpx;
        font-family: PingFang SC, PingFang SC-400;
        font-weight: 400;

        color: #1d2129;
        padding-left: 32rpx;
      }
      .value {
        font-size: 28rpx;
        font-family: PingFang SC, PingFang SC-400;
        font-weight: 400;
        color: #868d9c;
        padding-right: 32rpx;
      }
    }

    .btn {
      width: 686rpx;
      height: 88rpx;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;

      font-size: 28rpx;
      font-family: PingFang SC, PingFang SC-500;
      font-weight: 500;

      color: #ffffff;

      display: flex;
      justify-content: space-around;
      align-items: center;

      margin-top: 64rpx;
    }
  }
}
</style>
