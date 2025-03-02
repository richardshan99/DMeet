<template>
  <view class="content">
    <image class="content-top" :src="app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'"></image>
    <view class="content-base">
      <view class="nav" :style="{ marginTop: statusBarHeight + 'px' }">
        <view class="nav-item">
          <image @click="openSearch" class="icon" src="/static/index/search.png" :style="{ marginLeft: '32rpx' }"></image>
          <image @click="openFilter" class="icon" src="/static/index/filter.png" :style="{ marginLeft: '40rpx' }"></image>
        </view>
        <view class="nav-item" :style="{ justifyContent: 'center' }">
          <view @click="changeTab(0)" class="bar">
            <text class="bar-txt" :class="tabIndex == 0 ? 'active' : 'common'">推荐</text>
            <image :style="{ visibility: tabIndex == 0 ? 'visible' : 'hidden' }" class="bar-line"
			  src="/static/index/current_line.png"></image>
          </view>
          <view class="divide"></view>      
<!--      <view @click="changeTab(1)" class="bar">
            <text class="bar-txt" :class="tabIndex == 1 ? 'active' : 'common'">动态</text>-->             
            <!--暂时将动态替换成简介，by Richard-->
        <view @click="navigateToWechatPage" class="bar"> 
          <text class="bar-txt" :class="tabIndex == 1? 'active' : 'common'">简介</text>
            <image :style="{ visibility: tabIndex == 1 ? 'visible' : 'hidden' }" class="bar-line"
              src="/static/index/current_line.png"></image>
          </view>
        </view>
        <view class="nav-item"></view>
      </view>
	  
      <recommendation @login="toLogin" class="content-bottom" v-if="tabIndex == 0"></recommendation>
      <trends @login="loginOrComplete" class="content-bottom" v-else></trends>
    </view>
    <view :style="{ height: '96px' }"></view>

    <uni-popup :style="{ zIndex: '99999' }" ref="warnPopup" type="center">
      <meet-popup
        @confirm="toRealName"
        msg="你需先完成实名认证后才能使用筛选功能"
        confirmText="实名认证"
        cancelText="暂不认证"
      ></meet-popup>
    </uni-popup>
    <uni-popup :style="{ zIndex: '99999' }" ref="searchPopup" type="center">
      <meet-popup
        @confirm="toBuyMember"
        msg="只有会员才能使用搜索"
        confirmText="购买会员"
        cancelText="知道了"
      ></meet-popup>
    </uni-popup>
    <uni-popup :style="{ zIndex: '99999' }" type="center" ref="completePopup">
      <meet-popup
        @confirm="toComplete"
        msg="你还没有完善资料，无法查看更多用户"
        confirmText="立即完善"
        cancelText="取消"
      ></meet-popup>
    </uni-popup>
    <uni-popup
      :style="{ zIndex: '99999' }"
      ref="advertisePopup"
      type="center"
      :maskClick="false"
    >
      <view
        :style="{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
        }"
      >
        <image class="advertiseImg" :src="advertiseImgPath"></image>
        <image
          @click="closePopup(advertisePopup)"
          class="closeImg"
          src="/static/index/closeDia.png"
        ></image>
      </view>
    </uni-popup>
    <uni-transition
      :show="showFocus && !errorFlag"
      mode-class="fade"
      custom-class="content-focus"
    >
      <view class="left">
        <image
          @click="showFocus = false"
          class="left-icon"
          src="/static/index/focus_close.png"
        ></image>
        <text class="left-txt">关注公众号，接受消息更及时</text>
      </view>
      <view @click="focusAccount" class="right">
        <text class="right-txt">立即关注</text>
      </view>
    </uni-transition>
    <uni-popup
      @change="loginClose"
      :style="{ zIndex: '99999' }"
      ref="loginPopup"
      type="bottom"
    >
      <uni-login></uni-login>
    </uni-popup>
    <uni-popup
      type="top"
      ref="officalAccount"
      :style="{ zIndex: '99999' }"
      :mask-click="false"
    >
      <view :style="{ width: '750rpx' }">
        <view
          :style="{
            width: '100%',
            height: officalHeight + 'px',
            backgroundColor: '#fff',
          }"
        ></view>
        <official-account
          @error="showErrorInfo"
          style="width: 90%"
        ></official-account>
        <view
          @click="closePopup(officalAccount)"
          :style="{
            width: '100%',
            height: '40px',
            backgroundColor: '#fff',
            display: 'flex',
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'center',
          }"
        >
          <text :style="{ fontSize: '26rpx', color: '#666' }">关闭弹窗</text>
        </view>
      </view>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import {
  onLoad,
  onShow,
  onShareAppMessage,
  onShareTimeline,
  onHide,
} from "@dcloudio/uni-app";
import {
  ref,
  getCurrentInstance,
  nextTick,
  computed,
  watch,
  reactive,
} from "vue";
import { useStore } from "vuex";
const advertisePopup = ref();
const store = useStore();
const curPages = getCurrentPages()[0]; // 获取当前页面实例
const token = computed(() => store.state.user.token);
const userInfo = computed(() => store.state.user.userInfo);
const isFollow = computed(() => store.state.user.is_follow_wechat);
const isImprove = computed(() => store.state.user.is_improve);
const advertiseImgPath = ref(null);
const searchInfo = computed(() => store.state.user.searchData); // 默认查询参数
const officalAccount = ref();

const app = getCurrentInstance().appContext.app;

const swiperCurrent = ref(0); // 当前swiper真正的索引

const recommendations = ref([]); // 推荐列表

const showFocus = ref(false);
const errorFlag = ref(false);

const tabIndex = ref(0);
const warnPopup = ref();
const searchPopup = ref();
const completePopup = ref();
const loginPopup = ref(); //登录弹窗

watch(
  () => isFollow.value,
  (n, o) => {
    if (n == -1) {
      // 登录了就弹出关注公众号
      showFocus.value = true;
    }
  },
  {
    immediate: true,
  }
);

onShareAppMessage(() => {
  return {
    title: " ",
    path: `/pages/index/index`,
  };
});

onShareTimeline(() => {
  return {
    title: " ",
    query: ``,
  };
});

onShow(() => {
  if (token.value != null) {
    store.dispatch("refreshInfo");
    store.dispatch("refreshInvitationNum");
  }
  if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
    curPages.getTabBar().setData({
      selected: 0, // 表示当前菜单的索引，该值在不同的页面表示不同
    });
  }
});

const officalHeight = ref(0);
const statusBarHeight = ref(20);
// 定义全局变量,by Richard
const loginArea = ref('');

onLoad((options: any) => {
  store.commit("setSource", options.source);
  store.dispatch("setToken");    //在这里获取token
  statusBarHeight.value = uni.getSystemInfoSync().statusBarHeight;
  officalHeight.value =
    uni.getSystemInfoSync().statusBarHeight +
    uni.getMenuButtonBoundingClientRect().height +
    4;

  uni.getLocation({
    type: "wgs84",
    isHighAccuracy: true,
    highAccuracyExpireTime: 3000,
    success: function (res) {
      console.log("当前位置的经度：" + res.longitude);
      console.log("当前位置的纬度：" + res.latitude);

      api.post("index/info", {
          point: [res.longitude, res.latitude].toString(),
        })
        .then(async (res: any) => {
          if (res.code == 1 && res.data != null) {
            advertiseImgPath.value = res.data.screen;
            let defaultLocation = res.data.default_position.split("-");
            console.log(defaultLocation);

            if (defaultLocation.indexOf("中国") >= 0) {
              const xres: any = await api.post("common/china_list");
              if (xres.code == 1) {
                let item = xres.data.find(
                  (item) => item.name == defaultLocation[1]
                );
                console.log(item, "item--");
                if (item != null) {
                  const vres: any = await api.post("common/area_list", {
                    pid: item.id,
                  });
                  if (vres.code == 1) {
                    let lastItem = vres.data.find(
                      (item) => item.name == defaultLocation[2]
                    );

                    if (lastItem != null) {
                      store.commit("setCity", {
                        china: 1,
                        all: res.data.default_position,
                        indexList: [
                          xres.data.indexOf(item),
                          vres.data.indexOf(lastItem),
                        ],
                        parentId: item.id,
                        cityInd: lastItem.id,
                        parent: defaultLocation[1],
                        child: defaultLocation[2],
                        showName: defaultLocation[1] + defaultLocation[2],
                      });
                    }
                  }
                }
              }
            } else {
              const xres: any = await api.post("common/oversea_list");
              if (xres.code == 1) {
                let item = xres.data.find(
                  (item) => item.name == defaultLocation[0]
                );
                if (item != null) {
                  const vres: any = await api.post("common/area_list", {
                    pid: item.id,
                  });
                  if (vres.code == 1) {
                    let lastItem = vres.data.find(
                      (item) => item.name == defaultLocation[1]
                    );
                    if (lastItem != null) {
                      store.commit("setCity", {
                        china: 0,
                        all: res.data.default_position,
                        indexList: [
                          xres.data.indexOf(item),
                          vres.data.indexOf(lastItem),
                        ],
                        parentId: item.id,
                        cityInd: lastItem.id,
                        parent: defaultLocation[0],
                        child: defaultLocation[1],
                        showName: defaultLocation[0] + defaultLocation[1],
                      });
                    }
                  }
                }
              }
            }
        
            nextTick(async () => {
              advertisePopup.value.open();
            });
          }
        });
    },
  });
});

const showErrorInfo = (e) => {
  // return
  if (e.detail.status != 0) {
    errorFlag.value = true;
  }
  if (e.type == "error") {
    errorFlag.value = true;
  }
};

const toBuyMember = () => {
  // 去购买会员
  uni.navigateTo({
    url: "/pages/member_purchase/member_purchase",
  });
};

const toRealName = () => {
  // 去实名认证
  uni.navigateTo({
    url: `/pages/attestation/attestation?type=1`,
  });
};

const focusAccount = async () => {
  officalAccount.value.open();
  // return;
  // 关注公众号
  const res: any = await api.post("index/follow_wechat");
  if (res.code == 1) {
    showFocus.value = false;
     uni.showToast({
     	icon:'none',
     	title: "关注公众号成功",
     })
  }
};

// 关闭弹窗
const closePopup = (popup: any) => {
  popup.close();
};

// 登录弹窗关闭的时候，显示tabbar
const loginClose = () => {
  if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
    curPages.getTabBar().setData({
      showTabbar: true, // 显示tabbar
    });
  }
};
const toLogin = () => {
  if (token.value == null) {
    if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
      curPages.getTabBar().setData({
        showTabbar: false, // 隐藏tabbar防止遮挡
      });
    }
    loginPopup.value.open();
    return;
  }
  if (isImprove.value == -1) {
    // -1未完善
    completePopup.value.open();
  }
  // completePopup.value.open();
};

const loginOrComplete = () => {
  if (token.value == null) {
    if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
      curPages.getTabBar().setData({
        showTabbar: false, // 隐藏tabbar防止遮挡
      });
    }
    loginPopup.value.open();
    return;
  }
  if (isImprove.value == -1) {
    // -1未完善
    completePopup.value.open();
  }
  /* 暂时关闭此功能，by Richard
  if (isImprove.value == -2) {
    uni.showToast({
      icon: "none",
      title: "您的信息正在审核，请先等待审核结果。",
    });
  }*/
};

const toComplete = () => {
  // 去完善资料
  uni.navigateTo({url: "/pages/complete_infomation/complete_infomation",});
};

const openSearch = () => {
  if (userInfo.value == null) {
    loginPopup.value.open();
    return;
  }
  if (isImprove.value == -1) {
    completePopup.value.open();
    return;
  }
  /* 暂时关闭此功能，by Richard
  if (isImprove.value == -2) {
    uni.showToast({
      icon: "none",
      title: "您的信息正在审核，请先等待审核结果。",
    });
    return;
  }*/
  if (userInfo.value.is_member != 1) {
    searchPopup.value.open();
    return;
  }
  uni.navigateTo({
    url: "/pages/search/search",
  });
};

const openFilter = () => {
  if (userInfo.value == null) {
    loginPopup.value.open();
    return;
  }
  if (isImprove.value == -1) {
    completePopup.value.open();
    return;
  }
  
  /* 暂时关闭此功能，by Richard
  if (isImprove.value == -2) {
    uni.showToast({
      icon: "none",
      title: "您的信息正在审核，请先等待审核结果。",
    });
    return;
  }*/
  if (userInfo.value != null) {
    console.log(userInfo.value);

    if (userInfo.value.cert.realname_status == -1) {
      warnPopup.value.open(); // 去认证
    } else if (userInfo.value.cert.realname_status == 1) {
      uni.showToast({
        icon: "none",
        title: "实名认证正在审核中，请等待审核结果",
      });
    } else if (userInfo.value.cert.realname_status == 2) {
      uni.navigateTo({
        url: "/pages/filter/filter",
      });
    }
  }

  // 下面的ios来使用
  // uni.showModal({
  // 	title:'你需先完成实名认证后才能使用筛选功能',
  // 	content:'',
  // 	confirmText: '立即完善',
  // 	confirmColor: '#2C94FF',
  // 	cancelText: '取消',
  // 	cancelColor: '#868D9C'
  // })
};

const changeTab = function (index: number) {
  if (tabIndex.value == index) {
    return;
  }
  tabIndex.value = index;
};

// 跳转到外部公众号网页，by Richard
const navigateToWechatPage = () => {    
   // 使用 uni.navigateTo 跳转到一个包含 web-view 的页面
   uni.navigateTo({
      url: '/pages/webviewPage/webviewPage?url=https://mp.weixin.qq.com/s/XxJbLWiAqmKTx_poYHTJYA'    
    });
  };

</script>

<style lang="scss">
.content {
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
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    .nav {
      width: 750rpx;
      height: 44px;
      display: flex;
      flex-direction: row;
      align-items: center;
      &-item {
        flex: 1;
        flex-shrink: 0;
        display: flex;
        flex-direction: row;
        align-items: center;
        .icon {
          width: 20px;
          height: 20px;
        }
        .divide {
          width: 1px;
          height: 12px;
          background-color: rgba(0, 0, 0, 0.15);
          margin: 0 40rpx;
        }
        .bar {
          display: flex;
          flex-direction: column;
          align-items: center;
          &-txt {
            font-size: 16px;
            font-weight: 600;
          }
          .common {
            color: #1d2129;
          }
          .active {
            background: linear-gradient(123deg, #4a97e7, #b57aff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          }
          &-line {
            width: 18px;
            height: 5px;
            margin-top: 2px;
          }
        }
      }
    }
  }
  &-bottom {
    flex: 1;
    flex-shrink: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
  }
  &-focus {
    width: 602rpx;
    height: 72rpx;
    background-color: #f2efff;
    border: 1px solid #ffffff;
    border-radius: 38rpx;
    box-shadow: 0px 16rpx 48rpx 0px rgba(0, 0, 0, 0.05);
    position: fixed;
    bottom: 80px;
    left: 0;
    right: 0;
    margin: 0 auto;
    display: flex;
    flex-direction: row;
    align-items: center;
    z-index: 999;
    .left {
      flex: 1;
      flex-shrink: 0;
      display: flex;
      flex-direction: row;
      align-items: center;
      &-icon {
        width: 34rpx;
        height: 32rpx;
        margin-left: 22rpx;
      }
      &-txt {
        margin-left: 14rpx;
        color: #1d2129;
        font-size: 26rpx;
      }
    }
    .right {
      width: 144rpx;
      height: 56rpx;
      margin-left: 40rpx;
      border-radius: 32rpx;
      background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      &-txt {
        font-size: 24rpx;
        color: #fff;
      }
    }
  }
  .advertiseImg {
    width: 654rpx;
    height: 860rpx;
    border-radius: 30rpx;
  }
  .closeImg {
    margin-top: 24rpx;
    width: 74rpx;
    height: 72rpx;
  }
}
</style>
