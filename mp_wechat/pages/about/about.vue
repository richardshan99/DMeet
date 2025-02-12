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
        title="关于"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view class="options">
        <view class="options-item" @click="openPage(2)">
          <text class="txt">关于我们</text>
          <image src="/static/mine_center/arrow_left.png" class="left"></image>
        </view>
        <view class="options-item" @click="openPage(0)">
          <text class="txt">用户协议</text>
          <image src="/static/mine_center/arrow_left.png" class="left"></image>
        </view>
        <view class="options-item" @click="openPage(1)">
          <text class="txt">隐私政策</text>
          <image src="/static/mine_center/arrow_left.png" class="left"></image>
        </view>
        <view class="options-item" :style="{ borderBottom: 'none' }" @click="openPage(3)">
          <text class="txt">退出登录</text>
          <image src="/static/mine_center/arrow_left.png" class="left"></image>
        </view>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { useStore } from "vuex";
const store = useStore();
import { getCurrentInstance, ref } from "vue";
const app = getCurrentInstance().appContext.app;
const navBack = () => {
  uni.navigateBack();
};

const openPage = (type: number) => {
  if (type === 2) {
        uni.navigateTo({  //跳到公众号页面
            url: '/pages/webviewPage/webviewPage?url=https://mp.weixin.qq.com/s/XxJbLWiAqmKTx_poYHTJYA'    
        });
    } else if (type === 3) {
      // 清除token，退出登录，by Richard
      store.dispatch('clearTokenAction').then(() => {
      // 显示提示框
      uni.showToast({
        title: '已退出登录',
        icon: 'none',
        duration: 2000 // 提示框显示时长，单位为毫秒
      });
       // 在提示框显示时长结束后返回上级页面
       setTimeout(() => {
                uni.navigateBack({
                    delta: 1 // 返回的页面数，1 表示返回上一级页面
                });
            }, 2000); 
        }).catch((error) => {
            console.error('清除 token 出错:', error);
        });
  }
   else {  
    uni.navigateTo({url: `/pages/protocol/protocol?type=${type}`,});
  }
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
    .options {
      width: 686rpx;
      background-color: #fff;
      border-radius: 24rpx;
      margin: 24rpx auto 0 auto;
      padding: 0 32rpx;
      box-sizing: border-box;
      &-item {
        height: 108rpx;
        box-sizing: border-box;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e8eaef;
        .txt {
          font-size: 28rpx;
          color: #1d2129;
        }
        .left {
          width: 16rpx;
          height: 24rpx;
        }
      }
    }
  }
}
</style>
