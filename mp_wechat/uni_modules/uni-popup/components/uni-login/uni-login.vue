<template>
  <view class="oneLogin">
    <image
      class="oneLogin-back"
      :src="
        app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "
    ></image>
    <view class="oneLogin-base">
      <view :style="{ marginTop: '64rpx' }">
        <text class="oneLogin-text1">欢迎加入</text>
        <text class="oneLogin-text2">DMeet直面</text>
      </view>
      <view class="oneLogin-btnParent">
        <button
          open-type="getPhoneNumber"
          @getphonenumber="oneLogin"
          class="oneLogin-btn"
        >
          <text>手机号快捷登录</text>
          <!-- 微信账号一键登录 -->
        </button>
        <view @click="warning" v-if="!agree" class="oneLogin-agreeMask"></view>
      </view>
      <view
        :style="{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          marginTop: '100rpx',
        }"
      >
        <image
          @click="toggleAgree"
          :src="`/static/complete/${agree ? '' : 'un'}selected.png`"
          class="oneLogin-check_icon"
        ></image>
        <text class="oneLogin-txt1" :style="{ marginLeft: '15rpx' }"
          >我已阅读并同意</text
        >
        <text @click="openProtocol(0)" class="oneLogin-txt2">《用户协议》</text>
        <text class="oneLogin-txt1">和</text>
        <text @click="openProtocol(1)" class="oneLogin-txt2">《隐私政策》</text>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import popup from "../uni-popup/popup.js";
import { getCurrentInstance, ref, computed } from "vue";
import { useStore } from "vuex";
const app = getCurrentInstance().appContext.app;
const agree = ref(false);
const { proxy } = getCurrentInstance();
const store = useStore();
const source = computed(() => store.state.user.source);
const defaultCity = computed(() => store.state.user.defaultCity);
const emit = defineEmits(["success"]);
defineOptions({
  mixins: [popup],
});
const toggleAgree = () => {
  agree.value = !agree.value;
};
const warning = () => {
  uni.showToast({
    icon: "none",
    title: "请先同意协议",
  });
};
const oneLogin = (e: any) => {
  if (e.detail.code == null) {
    uni.showToast({
      icon: "none",
      title: e.detail.errMsg,
    });
    return;
  }
  uni.login({
    onlyAuthorize: true,
    success: async (res) => {
       // 打印 defaultCity.value 的值
      console.log('defaultCity.value 的值为:', defaultCity.value);
      const result: any = await api.post("user/login", {
        code: res.code, // res.code,
        phone_code: e.detail.code,
        source_id: source.value,
        login_area: defaultCity.value.showName,  //by Richard
      });
      if (result.data != null) {
        store.dispatch("setToken", result.data.token); // 更新token
        store.dispatch("refreshInfo"); // 更新token
      }
      emit("success");
      uni.$emit("refreshRecommendations"); // 刷新推荐
      proxy.popup.close();
    },
  });
};

const openProtocol = (type) => {
  uni.navigateTo({
    url: `/pages/protocol/protocol?type=${type}`,
  });
};

</script>

<style lang="scss" scoped>
.oneLogin {
  width: 750rpx;
  height: 524rpx;
  position: relative;
  border-radius: 32rpx 32rpx 0px 0px;
  background-color: #fff;
  &-back {
    width: 750rpx;
    height: 500rpx;
    z-index: 9;
    position: absolute;
    top: 0;
    left: 0;
  }
  &-base {
    width: 750rpx;
    height: 524rpx;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 10;
  }
  &-text1 {
    font-size: 48rpx;
    color: #1d2129;
    font-weight: 600;
  }
  &-text2 {
    font-size: 48rpx;
    font-weight: 600;
    background: linear-gradient(106deg, #4a97e7, #b57aff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  &-btnParent {
    margin-top: 64rpx;
    width: 558rpx;
    height: 96rpx;
    position: relative;
  }
  &-agreeMask {
    width: 558rpx;
    height: 96rpx;
    position: absolute;
    left: 0;
    top: 0;
    z-index: 10;
  }
  &-btn {
    width: 558rpx;
    height: 96rpx;
    background: linear-gradient(98deg, #4a97e7, #b57aff 100%);
    border-radius: 48rpx;
    text-align: center;
    line-height: 96rpx;
    font-size: 32rpx;
    color: #fff;
    font-weight: 500;
    z-index: 9;
  }
  &-check_icon {
    width: 34rpx;
    height: 32rpx;
  }
  &-txt1 {
    font-size: 24rpx;
    color: #868d9c;
  }
  &-txt2 {
    font-size: 24rpx;
    color: #2c94ff;
  }
}
</style>