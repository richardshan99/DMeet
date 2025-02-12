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
        color="#1D2129"
        :border="false"
        background-color="transparent"
        title="消息"
        :statusBar="true"
      ></uni-nav-bar>
      <view class="scroll" type="list">
        <view
          class="item"
          v-for="(item, index) in dataList"
          :key="'new' + index"
          @click="toPage(item.key)"
        >
          <image
            v-if="item.key == 'chat'"
            src="/static/news/salutation_mes.png"
            class="item-icon"
          ></image>
          <image
            v-if="item.key == 'invitation'"
            src="/static/news/invitation_mes.png"
            class="item-icon"
          ></image>
          <image
            v-if="item.key == 'like'"
            src="/static/news/likes_mes.png"
            class="item-icon"
          ></image>
          <image
            v-if="item.key == 'system'"
            src="/static/news/system_mes.png"
            class="item-icon"
          ></image>
          <view class="item-content">
            <text class="title">{{ item.name }}</text>
            <text class="info">{{ item.content }}</text>
          </view>
          <view class="item-foot">
            <text class="time">{{ item.time }}</text>
            <view v-if="item.count > 0" class="num">
              <text>{{ item.count }}</text>
            </view>
            <view v-else class="space"></view>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { getCurrentInstance, ref } from "vue";
import { api } from "@/common/request/index.ts";
import { onLoad } from "@dcloudio/uni-app";
const app = getCurrentInstance().appContext.app;

const dataList = ref([]);

onLoad(() => {
  api.post("message/list").then((res: any) => {
    if (res.code == 1) {
      dataList.value = res.data;
    }
  });
});
const navBack = () => {
  uni.navigateBack();
};

const toPage = (type: string) => {
  switch (type) {
    case "system":
      uni.navigateTo({
        url: "/pages/system_news/system_news",
      });
      break;
    case "like":
      uni.navigateTo({
        url: "/pages/likes_notice/likes_notice",
      });
      break;
    case "chat":
      uni.navigateTo({
        url: "/pages/say_hello/say_hello",
      });
      break;
    case "invitation":
      uni.navigateTo({
        url: "/pages/invitation_notice/invitation_notice",
      });
      break;
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
  position: relative;
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
    .scroll {
      width: 100%;
      margin-top: 24rpx;
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      overflow: auto;
      .item {
        width: 686rpx;
        padding: 32rpx 0;
        box-sizing: border-box;
        background-color: #fff;
        border-radius: 24rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        // margin-top: 24rpx;
        margin: 24rpx auto 0 auto;
        &-icon {
          margin-left: 32rpx;
          width: 96rpx;
          height: 96rpx;
        }
        &-content {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          margin-left: 24rpx;
          display: flex;
          justify-content: center;
          flex-direction: column;
          .title {
            font-size: 28rpx;
            color: #1d2129;
            font-weight: 500;
            line-height: 44rpx;
          }
          .info {
            margin-top: 4rpx;
            font-size: 24rpx;
            color: #868d9c;
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
          }
        }
        &-foot {
          margin-right: 32rpx;
          display: flex;
          flex-direction: column;
          align-items: flex-end;
          margin-left: 26rpx;
          .time {
            font-size: 20rpx;
            color: #868d9c;
          }
          .num {
            width: 30rpx;
            height: 32rpx;
            flex-shrink: 0;
            margin-top: 16rpx;
            // padding: 6rpx 12rpx;
            border-radius: 50%;
            background-color: #ff546f;
            box-sizing: border-box;
            font-size: 20rpx;
            color: #fff;

            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
          }
          .space {
            margin-top: 16rpx;
            width: 32rpx;
            height: 32rpx;
          }
        }
      }
    }
  }
}
</style>
