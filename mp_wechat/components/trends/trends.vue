<template>
  <view class="trends">
    <scroll-view
      class="scroll"
      type="list"
      scroll-y
      @scrolltolower="loadDynamic"
    >
      <view class="scroll-base">
        <dynamic-item
          v-for="(item, ind) in dataList"
          :key="ind + 'dynamic'"
          :dynamicItem="item"
        ></dynamic-item>
      </view>
      <text v-if="finish" class="scroll-status">{{
        dataList.length == 0 ? "没数据了" : "数据加载完毕"
      }}</text>
      <text v-else class="scroll-status">{{
        loading ? "正在加载" : "加载完毕"
      }}</text>
      <view :style="{ height: '96px', width: '100%' }"></view>
    </scroll-view>
    <image
      @click="publishTrends"
      src="/static/dynamic/add.png"
      class="trends-add"
    ></image>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { onMounted, ref, computed } from "vue";
import {
  onLoad,
  onShow,
  onShareAppMessage,
  onShareTimeline,
} from "@dcloudio/uni-app";
import { useStore } from "vuex";
const store = useStore();
const dataList = ref([]);
let pageNo = 1;
const loading = ref(false);
const finish = ref(false);
const token = computed(() => store.state.user.token);
const isImprove = computed(() => store.state.user.is_improve);

const emit = defineEmits(["login"]);
onMounted(() => {
  // 挂载
  getDynamics(true);

  uni.$on("updataDynamic", updata);
});

const updata = () => {
  getDynamics(true);
};

const loadDynamic = () => {
  pageNo++;
  getDynamics(false);
};

const getDynamics = async (refresh: boolean) => {
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
    const res: any = await api.post("blog/list", {
      page: pageNo,
    });
    loading.value = false;
    if (res.code == 1) {
      if (refresh) {
        dataList.value = res.data.data;
      } else {
        dataList.value = dataList.value.concat(res.data.data);
      }
      if (dataList.value.length == res.data.total) {
        finish.value = true;
      }
    }
  } catch (e) {
    loading.value = false;
  }
};

const publishTrends = () => {
  if (token.value == null || isImprove.value != 1) {
    emit("login");
    return;
  }
  uni.navigateTo({
    url: "/pages/publish_trends/publish_trends",
  });
};
</script>

<style lang="scss" scoped>
.trends {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  &-add {
    width: 88rpx;
    height: 88rpx;
    position: fixed;
    right: 32rpx;
    bottom: 96px;
    z-index: 999;
  }
  .scroll {
    margin-top: 36rpx;
    width: 100%;
    flex: 1;
    flex-shrink: 0;
    min-height: 0;
    &-base {
      width: 686rpx;
      display: flex;
      flex-direction: column;
      margin: 0 auto;
    }
    &-status {
      font-size: 26rpx;
      color: #999;
      text-align: center;
      margin: 20rpx auto;
      display: block;
    }
    .item {
      width: 100%;
      border-bottom: 1px solid #f0f2f5;
      display: flex;
      flex-direction: row;
      // margin-top: 36rpx;
      &-avatar {
        width: 80rpx;
        height: 80rpx;
        border-radius: 40rpx;
      }
      &-content {
        flex: 1;
        flex-shrink: 0;
        min-width: 0;
        margin-left: 16rpx;
        display: flex;
        flex-direction: column;
        .userName {
          font-size: 28rpx;
          color: #1d2129;
          font-weight: 500;
          line-height: 44rpx;
        }
        .gender {
          margin-left: 8rpx;
          width: 28rpx;
          height: 28rpx;
        }
        .options {
          width: 40rpx;
          height: 40rpx;
        }
        .desc {
          font-size: 22rpx;
          color: #868d9c;
          line-height: 34rpx;
        }
        .substance {
          margin: 16rpx 0;
          width: 100%;
          font-size: 28rpx;
          color: #1d2129;
          line-height: 44rpx;
        }
        .oneImg {
          width: 406rpx;
          height: 542rpx;
          border-radius: 16rpx;
        }
      }
    }
  }
}
</style>