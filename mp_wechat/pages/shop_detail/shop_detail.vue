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
        <text class="title">{{ pageTitle }}</text>
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
    <view class="main-shopImg">
      <swiper
        id="mainShopImg"
        class="main-shopImg"
        :current="currentVal"
        @change="changeCurrent"
      >
        <swiper-item
          v-for="(item, ind) in shopDetail.images_text"
          :key="'shopd' + ind"
        >
          <image class="main-shopImg" :src="item"></image>
        </swiper-item>
      </swiper>
      <text class="position"
        >{{ currentVal + 1 }}/{{ shopDetail?.images_text?.length }}</text
      >
    </view>

    <view class="main-head">
      <text class="name">{{ shopDetail.name }}</text>
      <text class="class">咖啡厅</text>
      <view class="address">
        <view class="address-left">
          <text class="label">门店地址：</text>
          <text class="value">{{ shopDetail.address }}</text>
        </view>
        <view class="address-icon" @click="openMap">
          <image
            class="icon"
            src="/static/addres_ponit.png"
            mode="scaleToFill"
          />
        </view>
      </view>
      <text class="business_time"
        >营业时间：{{ shopDetail.business_time }}</text
      >
    </view>
    <view class="main-desc" :style="{ marginTop: '24rpx' }">
      <text class="title">门店简介</text>
      <text class="detail"
        >{{ textContent(shopDetail.contents) }}...<text
          v-if="shopDetail.contents.length > 200"
          @click="toMore"
          :style="{ fontSize: '26rpx', color: '#2C94FF' }"
          >全部</text
        ></text
      >
    </view>

    <template v-if="!isCanteenType">
      <view class="main-select">
        <text class="txt">选择套餐</text>
      </view>
      <view
        v-if="
          shopDetail?.package1?.name != null &&
          shopDetail?.package1?.name.length > 0
        "
        @click="changePackage(1)"
        class="main-package"
        :style="{ marginBottom: '24rpx' }"
        :class="{ 'main-choice': currentPackage == 1 }"
      >
        <view class="child">
          <text class="name">{{ shopDetail?.package1?.name }}</text>
          <text class="intro" :style="{ marginBottom: '16rpx' }">{{
            shopDetail?.package1?.intro
          }}</text>
          <view
            v-for="(item, index) in shopDetail?.package1?.service"
            :key="'service' + index"
            class="item"
            :style="{ marginTop: '8rpx' }"
          >
            <text class="item-txt">{{ item.name }}</text>
            <text class="item-price">¥{{ item.price }}</text>
          </view>
          <view class="line"></view>
          <view class="amount">
            <text class="txt">合计价值</text>
            <text class="txt">¥{{ total1 }}</text>
          </view>
          <view
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'flex-end',
              justifyContent: 'space-between',
              marginTop: '24rpx',
            }"
          >
            <view
              :style="{
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'flex-end',
              }"
            >
              <text class="p_label">套餐价格</text>
              <text class="p_price">¥{{ shopDetail?.package1?.price }}</text>
            </view>
            <view v-if="showNext">
              <image
                v-if="currentPackage == 1"
                src="/static/checked.png"
                class="check_icon"
              ></image>
              <image
                v-else
                src="/static/un_checked.png"
                class="check_icon"
              ></image>
            </view>
          </view>
        </view>
      </view>
      <view
        v-if="
          shopDetail?.package2?.name != null &&
          shopDetail?.package2?.name.length > 0
        "
        @click="changePackage(2)"
        class="main-package"
        :style="{ marginBottom: '24rpx' }"
        :class="{ 'main-choice': currentPackage == 2 }"
      >
        <view class="child">
          <text class="name">{{ shopDetail?.package2?.name }}</text>
          <text class="intro" :style="{ marginBottom: '16rpx' }">{{
            shopDetail?.package2?.intro
          }}</text>
          <view
            v-for="(item, index) in shopDetail?.package2?.service"
            :key="'service' + index"
            class="item"
            :style="{ marginTop: '8rpx' }"
          >
            <text class="item-txt">{{ item.name }}</text>
            <text class="item-price">¥{{ item.price }}</text>
          </view>
          <view class="line"></view>
          <view class="amount">
            <text class="txt">合计价值</text>
            <text class="txt">¥{{ total1 }}</text>
          </view>
          <view
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'flex-end',
              justifyContent: 'space-between',
              marginTop: '24rpx',
            }"
          >
            <view
              :style="{
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'flex-end',
              }"
            >
              <text class="p_label">套餐价格</text>
              <text class="p_price">¥{{ shopDetail?.package2?.price }}</text>
            </view>
            <view v-if="showNext">
              <image
                v-if="currentPackage == 2"
                src="/static/checked.png"
                class="check_icon"
              ></image>
              <image
                v-else
                src="/static/un_checked.png"
                class="check_icon"
              ></image>
            </view>
          </view>
        </view>
      </view>
    </template>
    <view v-if="showNext" class="main-step" :style="{ marginTop: '8rpx' }">
      <view @click="nextStep" class="next">
        <text>下一步</text>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { onPageScroll, onLoad, onReachBottom } from "@dcloudio/uni-app";
import { computed, getCurrentInstance, nextTick, reactive, ref } from "vue";

// 是否是  非餐厅类型
const isCanteenType = ref(false);

const showTitle = ref(false);
const statusBarHeight = ref(20);
const opacity = ref(0);
const currentVal = ref(0);
const pageTitle = ref(null);

const currentPackage = ref(1);

const shopDetail = reactive({});

const showNext = ref(true);
const showService = ref(null);

const textContent = (text) => {
  console.log(text.length);

  return text.substring(0, 200);
};

const total1 = computed(() => {
  let total = 0;
  if (shopDetail.package1 != null && shopDetail.package1.service.length > 0) {
    shopDetail.package1.service.forEach((item) => {
      total += parseFloat(item.price);
    });
  }
  return total;
});

const total2 = computed(() => {
  let total = 0;
  if (shopDetail.package2 != null && shopDetail.package2.service.length > 0) {
    shopDetail.package2.service.forEach((item) => {
      total += parseFloat(item.price);
    });
  }
  return total;
});

const openMap = () => {
  console.log(shopDetail);
  if (shopDetail.latitude && shopDetail.latitude != "") {
    uni.openLocation({
      latitude: Number(shopDetail.latitude),
      longitude: Number(shopDetail.longitude),
    });
  } else {
    uni.showToast({
      title: "当前门店暂时未填写经纬度",
      icon: "none",
    });
  }
};

let imgHt = 0;
onLoad((options) => {
  if (options.type == "watch") {
    showNext.value = false;
    showService.value = options.serviceName;
  }
  const query = uni.createSelectorQuery().in(getCurrentInstance());
  nextTick(() => {
    query
      .select("#mainShopImg")
      .boundingClientRect((data: any) => {
        imgHt = data.height;
      })
      .exec();
    statusBarHeight.value = uni.getSystemInfoSync().statusBarHeight;
  });

  api
    .post("invitation/shop_detail", {
      shop_id: options.id,
    })
    .then((res: any) => {
      if (res.code == 1) {
        isCanteenType.value = res.data.type == 2 ? true : false;
        console.log(res.data.type);

        Object.assign(shopDetail, res.data);
        if (!showNext.value) {
          if (shopDetail.package1.name == showService.value) {
            shopDetail.package2.name = null;
          }
          if (shopDetail.package2.name == showService.value) {
            shopDetail.package1.name = null;
          }
        }
        pageTitle.value = res.data.name;
      }
    });
});

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
});

const toMore = () => {
  uni.navigateTo({
    url: "/pages/store_introduction/store_introduction",
    success: (res) => {
      res.eventChannel.emit("acceptDataFromOpenerPage", {
        html: shopDetail.contents,
        imgs: shopDetail.content_images,
      });
    },
  });
};

const changeCurrent = (e) => {
  if (currentVal.value != e.detail.current) {
    currentVal.value = e.detail.current;
  }
};

//李川
const nextStep = () => {
  uni.navigateTo({
    url: "/pages/give_invitation/give_invitation?convene=T",
    success: () => {
      uni.$emit("updateMeetLocation", {
        address: isCanteenType.value ? shopDetail.address : "",
        isCanteenType: isCanteenType.value,
        shopId: shopDetail.id,
        shopName: shopDetail.name,
        shopImg: shopDetail.thumb_text, //假的
        packagePrice:
          currentPackage.value == 1
            ? shopDetail.package1.price
            : shopDetail.package2.price,
        packageName:
          currentPackage.value == 1
            ? shopDetail.package1.name
            : shopDetail.package2.name,
        packageType: currentPackage.value,
      });
    }
  });
};

const navBack = () => {
  uni.navigateBack();
};

const changePackage = (val) => {
  if (currentPackage.value != val) {
    currentPackage.value = val;
  }
};
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  min-height: 100%;
  background-color: #f7f8fa;
  display: flex;
  flex-direction: column;
  align-items: center;
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
  &-shopImg {
    width: 750rpx;
    height: 500rpx;
    position: relative;
    z-index: 9;
    .position {
      padding: 4rpx 16rpx;
      background: rgba(0, 0, 0, 0.3);
      border-radius: 24rpx;
      font-size: 24rpx;
      color: #fff;
      position: absolute;
      right: 24rpx;
      bottom: 48rpx;
      z-index: 10;
    }
  }
  &-head {
    width: 686rpx;
    background-color: #ffffff;
    border-radius: 24rpx;
    padding: 32rpx;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    margin-top: -32rpx;
    z-index: 11;
    .name {
      font-size: 36rpx;
      color: #1d2129;
      font-weight: 500;
    }
    .class {
      font-size: 26rpx;
      color: #868d9c;
      margin-top: 4rpx;
    }
    .address {
      margin-top: 24rpx;
      display: flex;
      flex-direction: row;
      .address-left {
        flex: 1;
        display: flex;
        .label {
          font-size: 26rpx;
          color: #1d2129;
        }
        .value {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          font-size: 26rpx;
          color: #1d2129;
          line-height: 38rpx;
        }
      }
      .address-icon {
        width: 60rpx;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-around;
        .icon {
          width: 40rpx;
          height: 40rpx;
        }
      }
    }
    .business_time {
      margin-top: 8rpx;
      font-size: 26rpx;
      color: #1d2129;
    }
  }
  &-desc {
    width: 686rpx;
    background-color: #ffffff;
    border-radius: 24rpx;
    padding: 32rpx;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    .title {
      font-size: 32rpx;
      color: #1d2129;
      font-weight: 500;
    }
    .detail {
      margin-top: 16rpx;
      font-size: 26rpx;
      color: #868d9c;
      line-height: 38rpx;
    }
  }
  &-select {
    margin: 24rpx 0;
    width: 100%;
    .txt {
      margin-left: 56rpx;
      font-size: 32rpx;
      color: #1d2129;
    }
  }
  &-package {
    padding: 1px;
    box-sizing: border-box;
    width: 686rpx;
    background: #fff;
    border-radius: 24rpx;
    .child {
      background: #fff;
      padding: 32rpx;
      border-radius: 24rpx;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      .name {
        font-size: 28rpx;
        color: #1d2129;
      }
      .intro {
        margin-top: 8rpx;
        font-size: 26rpx;
        color: #1d2129;
        line-height: 38rpx;
      }
      .item {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        &-txt {
          font-size: 24rpx;
          color: #1d2129;
        }
        &-price {
          font-size: 24rpx;
          color: #868d9c;
        }
      }
      .line {
        margin: 20rpx 0;
        height: 0px;
        border: 1px dashed #e8eaef;
      }
      .amount {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        .txt {
          font-size: 24rpx;
          color: #1d2129;
        }
      }
      .p_label {
        font-size: 24rpx;
        color: #868d9c;
      }
      .p_price {
        margin-left: 16rpx;
        font-size: 36rpx;
        color: #f0251b;
        font-weight: 500;
      }
      .check_icon {
        width: 32rpx;
        height: 32rpx;
      }
    }
  }
  &-choice {
    background: linear-gradient(123deg, #4a97e7, #b57aff 100%);
  }
  &-step {
    width: 750rpx;
    height: 180rpx;
    background-color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    .next {
      width: 686rpx;
      height: 88rpx;
      line-height: 88rpx;
      text-align: center;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;
      font-size: 28rpx;
      font-weight: 500;
      color: #fff;
    }
  }
}
</style>
