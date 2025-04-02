<template>
  <view class="main">
    <view
      v-if="showTitle"
      class="navbar"
      :style="{ paddingTop: statusBarHeight + 'px', opacity: opacity }"
    >
      <view class="navbar-base">
        <view
          class="space"
          :style="{
            display: 'flex',
            flexDirection: 'row',
            alignItems: 'center',
          }"
        >
          <uni-icons
            @click="navBack"
            :style="{ marginLeft: '32rpx' }"
            type="left"
            size="20"
            color="#000"
          ></uni-icons>
        </view>
        <text class="title">{{ userInfo.nickname }}</text>
        <view class="space"></view>
      </view>
    </view>
    <view
      v-else
      class="navbar"
      :style="{
        backgroundColor: 'transparent',
        paddingTop: statusBarHeight + 'px',
      }"
    >
      <view class="navbar-base">
        <uni-icons
          @click="navBack"
          :style="{ marginLeft: '32rpx' }"
          type="left"
          size="20"
          color="#fff"
        ></uni-icons>
      </view>
    </view>
    <uni-list :style="{ backgroundColor: '#f7f8fa' }">
      <uni-list-item
        :customStyle="{ backgroundColor: 'transparent', padding: 0 }"
        :border="false"
      >
        <template #body>
          <view class="sticky_bar">
            <!-- <image mode="aspectFill" id="avatarImg" class="main-head" :src="userInfo.avatar_text"></image> -->
            <view id="avatarImg" class="main-head">
              <swiper
                class="base_avatar"
                :current="currentAlbum"
                @change="changeCurrentAlbum"
              >
                <swiper-item
                  v-for="(item, index) in userInfo.albums_text"
                  :key="'album' + index"
                >
                  <image
                    class="base_avatar"
                    mode="aspectFill"
                    :src="item"
                  ></image>
                </swiper-item>
              </swiper>
              <view class="progress_line">
                <view
                  v-for="(item, index) in userInfo.albums_text"
                  :key="'dot' + index"
                  class="term"
                  :class="{ active: currentAlbum == index }"
                  :style="{
                    marginRight:
                      index < userInfo.albums_text.length - 1 ? '8rpx' : '0',
                  }"
                ></view>
              </view>
            </view>
            <view
              id="myTabbar"
              class="tabbar"
              :style="{
                position: fixed ? 'fixed' : 'absolute',
                top: fixed ? statusBarHeight + 36 + 'px' : 'none',
              }"
            >
              <view @click="changeTab(0)" class="tabbar-item">
                <text :class="currentTab == 0 ? 'choice' : 'common'"
                  >个人资料</text
                >
                <image
                  v-if="currentTab == 0"
                  class="line"
                  src="/static/index/current_line.png"
                ></image>
                <view v-else class="line"></view>
              </view>
              <view class="tabbar-divide"></view>
              <view @click="changeTab(1)" class="tabbar-item">
                <text :class="currentTab == 1 ? 'choice' : 'common'"
                  >近期动态</text
                >
                <image
                  v-if="currentTab == 1"
                  class="line"
                  src="/static/index/current_line.png"
                ></image>
                <view v-else class="line"></view>
              </view>
            </view>
          </view>
        </template>
      </uni-list-item>
      <uni-list-item
        :customStyle="{ backgroundColor: 'transparent', padding: 0 }"
        :border="false"
      >
        <template #body :style="{ width: '100%' }">
          <personal-data
            :userDetail="userInfo"
            :style="{ width: '100%' }"
            v-if="currentTab == 0"
          ></personal-data>
          <view
            v-else
            :style="{
              width: '100%',
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
            }"
          >
            <view class="scroll-base">
              <dynamic-item
                v-for="(item, ind) in dataList"
                :key="ind + 'dynamic'"
                :dynamicItem="item"
              ></dynamic-item>
            </view>
          </view>
        </template>
      </uni-list-item>
    </uni-list>
  </view>
</template>



<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { onPageScroll, onLoad, onReachBottom } from "@dcloudio/uni-app";
import { getCurrentInstance, nextTick, reactive, ref } from "vue";
const statusBarHeight = ref(20);
const currentTab = ref(0);

const currentAlbum = ref(0);
const fixed = ref(false);

const userInfo = reactive({});

const opacity = ref(0);

const showTitle = ref(false);
let imgHt = 0,
  tabTop = 0;

const dataList = ref([]);
let pageNo = 1;
const loading = ref(false);
const finish = ref(false);
let userId = null;
onLoad((options) => {
  const query = uni.createSelectorQuery().in(getCurrentInstance());
  nextTick(() => {
    query
      .select("#avatarImg")
      .boundingClientRect((data) => {
        imgHt = data.height;
      })
      .exec();
    query
      .select("#myTabbar")
      .boundingClientRect((data) => {
        tabTop = data.top;
      })
      .exec();
    statusBarHeight.value = uni.getSystemInfoSync().statusBarHeight;
  });
  userId = options.id;
  uni.showLoading({
    title: "加载中",
  });
  uni.getLocation({
    type: "wgs84",
    highAccuracyExpireTime: 3000,
    isHighAccuracy: true,
    success: (resp) => {
   
      api
        .post("/index/user_info", {
          id: options.id,
          position: [resp.longitude, resp.latitude].toString(),
        })
        .then((res: any) => {
          uni.hideLoading();
          if (res.code == 1) {
            Object.assign(userInfo, res.data);
          }
        });
      getDynamics(true);
    },
  });
});

onReachBottom(() => {
  loadDynamic();
});

const loadDynamic = () => {
  pageNo++;
  getDynamics(false);
};

const changeCurrentAlbum = (e) => {
  if (e.detail.current != currentAlbum.value) {
    currentAlbum.value = e.detail.current;
  }
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
      user_id: userId,
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

const changeTab = (ind: number) => {
  if (currentTab.value != ind) {
    currentTab.value = ind;
  }
};

onPageScroll((e) => {
  if (e.scrollTop >= 40 && showTitle.value == false) {
    showTitle.value = true;
  }
  if (e.scrollTop < 40 && showTitle.value) {
    showTitle.value = false;
  }
  if (e.scrollTop >= 40 && e.scrollTop + statusBarHeight.value + 44 <= imgHt) {
    opacity.value = (e.scrollTop + statusBarHeight.value + 44) / imgHt;
  }
  if (
    e.scrollTop >= tabTop - statusBarHeight.value - 40 &&
    fixed.value == false
  ) {
    fixed.value = true;
  }
  if (
    e.scrollTop < tabTop - statusBarHeight.value - 40 &&
    fixed.value == true
  ) {
    fixed.value = false;
  }
});

const navBack = () => {
  uni.navigateBack();
};
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  min-height: 100%;
  background-color: #f7f8fa;
  display: flex;
  flex-direction: column;

  .navbar {
    width: 100%;
    background-color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 999;
    box-sizing: border-box;
    &-base {
      width: 100%;
      height: 44px;
      display: flex;
      flex-direction: row;
      align-items: center;
      .space {
        flex: 1;
        flex-shrink: 0;
        min-width: 0;
      }
      .title {
        flex: 1;
        flex-shrink: 0;
        min-width: 0;
        font-size: 13px;
        color: #1d2129;
        font-weight: 500;
        text-align: center;
      }
    }
  }
  &-head {
    width: 750rpx;
    height: 750rpx;
    z-index: 9;
    position: relative;
    .base_avatar {
      width: 750rpx;
      height: 750rpx;
      position: relative;
    }
    .progress_line {
      width: 686rpx;
      position: absolute;
      left: 32rpx;
      bottom: 56rpx;
      z-index: 11;
      display: flex;
      flex-direction: row;
      justify-content: center;
      .term {
        width: 68rpx;
        height: 6rpx;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4rpx;
      }
      .active {
        background: #fff;
      }
    }
  }
  .tabbar {
    width: 750rpx;
    height: 104rpx;
    z-index: 99;
    border-radius: 32rpx 32rpx 0px 0px;
    background-color: #f7f8fa;
    position: absolute;
    bottom: 0;
    left: 0;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    &-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      .choice {
        font-size: 32rpx;
        font-weight: 600;
        line-height: 48rpx;
        background: linear-gradient(121deg, #4a97e7, #b57aff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
      .common {
        font-size: 32rpx;
        color: #1d2129;
        font-weight: 600;
        line-height: 48rpx;
      }
      .line {
        margin-top: 4rpx;
        width: 36rpx;
        height: 10rpx;
      }
    }
    &-divide {
      width: 1px;
      height: 24rpx;
      background-color: rgba(0, 0, 0, 0.15);
      margin: 0 72rpx;
    }
  }
  .sticky_bar {
    width: 750rpx;
    height: 826rpx;
    position: relative;
  }
  .scroll-base {
    width: 686rpx;
    display: flex;
    flex-direction: column;
    margin: 0 auto;
  }
}
</style>
