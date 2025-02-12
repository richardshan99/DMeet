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
        title="我的积分"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view class="balance">
        <text class="balance-txt1">当前积分</text>
        <text class="balance-txt2">{{ balance }}</text>
      </view>
      <scroll-view
        type="list"
        scroll-y
        class="scroll"
        :style="{ marginTop: '24rpx' }"
        @scrolltolower="loadMore"
      >
        <view class="scroll-base">
          <view
            class="item"
            v-for="(item, ind) in dataList"
            :key="'balance' + ind"
          >
            <view :style="{ display: 'flex', flexDirection: 'column' }">
              <text class="item-txt1">{{ item.type_text }}</text>
              <text class="item-txt2">{{ item.create_time_text }}</text>
            </view>
            <text class="item-txt3">{{ item.price }}</text>
          </view>
          <text v-if="finish" class="scroll-status">{{
            dataList.length == 0 ? "没数据了" : "数据加载完毕"
          }}</text>
          <text v-else class="scroll-status">{{
            loading ? "正在加载" : "加载完毕"
          }}</text>
        </view>
      </scroll-view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, ref } from "vue";
import { onLoad } from "@dcloudio/uni-app";
const app = getCurrentInstance().appContext.app;
const dataList = ref([]);
let pageNo = 1;
const finish = ref(false);
const loading = ref(false);
const balance = ref(0);

onLoad(() => {
  getBalanceList(true);
});
const loadMore = () => {
  pageNo++;
  getBalanceList(false);
};

const getBalanceList = async (refresh: boolean) => {
  if (refresh) {
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
    const res: any = await api.post("my/balance/list", {
      page: pageNo,
    });
    loading.value = false;
    if (res.code == 1) {
      if (refresh) {
        dataList.value = res.data.list.data;
        balance.value = res.data.balance;
      } else {
        dataList.value = dataList.value.concat(res.data.list.data);
      }
      if (dataList.value.length == res.data.list.total) {
        finish.value = true;
      }
    }
  } catch (e) {
    loading.value = false;
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
  background-color: #fff;
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
    .balance {
      width: 686rpx;
      height: 196rpx;
      background: linear-gradient(104deg, #4a97e7, #b57aff 100%);
      border-radius: 24rpx;
      margin-top: 24rpx;
      display: flex;
      flex-direction: column;
      padding-left: 40rpx;
      box-sizing: border-box;
      &-txt1 {
        margin-top: 40rpx;
        line-height: 44rpx;
        font-size: 28rpx;
        color: #fff;
      }
      &-txt2 {
        margin-top: 8rpx;
        font-size: 64rpx;
        font-weight: 600;
        color: #fff;
      }
    }
    .scroll {
      flex: 1;
      flex-shrink: 0;
      width: 100%;
      &-status {
        font-size: 26rpx;
        color: #999;
        text-align: center;
        margin-top: 20rpx;
        display: block;
      }
      &-base {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        .item {
          width: 686rpx;
          height: 156rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
          border-bottom: 1px solid #eee;
          &-txt1 {
            font-size: 28rpx;
            color: #1d2129;
            line-height: 44rpx;
          }
          &-txt2 {
            margin-top: 8rpx;
            font-size: 24rpx;
            color: #868d9c;
            line-height: 40rpx;
          }
          &-txt3 {
            font-size: 32rpx;
            font-weight: 500;
            color: #1d2129;
            line-height: 48rpx;
          }
        }
      }
    }
  }
}
</style>
