<template>
  <view class="main">
    <view v-if="detail != null" class="main-content" v-html="detail"></view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { onLoad } from "@dcloudio/uni-app";
import { ref } from "vue";
const detail = ref(null);
onLoad((options) => {
  let title = "用户协议";
  if (options.type == 0) {
    // 用户协议
    title = "用户协议";
  } else if (options.type == 1) {
    title = "隐私政策";
  } else if (options.type == 2) {
    title = "关于我们";
  } else if (options.type == 3) {
    title = "帮助中心";
  }
  uni.setNavigationBarTitle({
    title: title,
  });
  getProtocolInfo(options.type);
});

const getProtocolInfo = async (type: number) => {
  const res: any = await api.post("/common/info");
  if (res.code == 1) {
    if (type == 0) {
      detail.value = res.data.agreement;
    } else if (type == 1) {
      detail.value = res.data.privacy;
    } else if (type == 2) {
      detail.value = res.data.aboutus;
    } else if (type == 3) {
      detail.value = res.data.help_center;
    }
  }
};
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  background-color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  &-content {
    width: 686rpx;
  }
}
</style>
