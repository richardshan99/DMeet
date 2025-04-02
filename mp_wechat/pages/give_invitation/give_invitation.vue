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
        @clickLeft="navBack"
        left-icon="left"
        :border="false"
        :title="conveneFlag == 'T' ? '发起召集' : '发起邀约'"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view
        :style="{
          flex: 1,
          flexShrink: 0,
          minHeight: 0,
          overflowY: 'auto',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
        }"
      >
        <view v-if="conveneFlag != 'T'" class="avatars">
          <view
            class="avatars-first"
            :style="{
              border:
                userInfo.gender == 1
                  ? '1px solid rgba(102,145,255,0.1)'
                  : '1px solid rgba(255,102,194,0.1)',
            }"
          >
            <view
              class="second"
              :style="{
                border:
                  userInfo.gender == 1
                    ? '1px solid rgba(102,145,255,0.4)'
                    : '1px solid rgba(255,102,194,0.4)',
              }"
            >
              <image
                mode="aspectFill"
                :style="{
                  border:
                    userInfo.gender == 1
                      ? '2px solid #6691FF'
                      : '2px solid #FF66C2',
                }"
                class="third"
                :src="userInfo.avatar_text"
              ></image>
            </view>
          </view>
          <text class="avatars-invition">向TA发起邀约</text>
          <image
            src="/static/give_invitation/heart.png"
            class="avatars-heart"
          ></image>
          <view
            class="avatars-first"
            :style="{
              border:
                otherUser.gender == 1
                  ? '1px solid rgba(102,145,255,0.1)'
                  : '1px solid rgba(255,102,194,0.1)',
            }"
          >
            <view
              class="second"
              :style="{
                border:
                  otherUser.gender == 1
                    ? '1px solid rgba(102,145,255,0.4)'
                    : '1px solid rgba(255,102,194,0.4)',
              }"
            >
              <image
                :style="{
                  border:
                    otherUser.gender == 1
                      ? '2px solid #6691FF'
                      : '2px solid #FF66C2',
                }"
                class="third"
                :src="otherUser.avatar"
              ></image>
            </view>
          </view>
        </view>
        <view class="meet_location">
          <view
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'center',
            }"
          >
            <view
              @click="changeCity(0)"
              class="common"
              :class="currentCity == 0 ? 'checked' : 'border_color'"
              ><text>当前地区</text></view
            >
			<!--李川-->
			<!--
            <view
              @click="changeCity(1)"
              class="common"
              :class="currentCity == 1 ? 'checked' : 'border_color'"
              ><text>更换地区</text>
              </view>-->
          </view>
          <view @click="openSelectCity" v-if="currentCity == 1" class="city">
            <image
              src="/static/give_invitation/meet_city.png"
              class="city-icon"
            ></image>
            <text v-if="selectCity.name == null" class="city-unSelected"
              >选择地区</text
            >
            <text v-else class="city-unSelected city-selected">{{
              selectCity.name
            }}</text>
            <image
              src="/static/mine_center/arrow_left.png"
              class="city-arrow"
            ></image>
          </view>
          <view v-else class="city">
            <image
              src="/static/give_invitation/meet_city.png"
              class="city-icon"
            ></image>
            <text class="city-unSelected city-selected" v-if="defaultCity">{{
              defaultCity?.parent + "-" + defaultCity?.child
            }}</text>
            <text class="city-unSelected city-selected" v-else>{{
              pointCity?.parent + "-" + pointCity?.child
            }}</text>
          </view>
          <view
            v-if="meetShop.shopId == null"
            @click="chooseMeetLocation"
            class="address"
          >
            <image
              src="/static/give_invitation/now_city.png"
              class="address-icon1"
            ></image>
            <text class="address-noCheck">请选择见面地点</text>
            <image
              src="/static/mine_center/arrow_left.png"
              class="address-arrow"
            ></image>
          </view>
          <view v-else @click="chooseMeetLocation" class="address">
            <view
              :style="{
                display: 'flex',
                flexDirection: 'row',
                flex: 1,
                flexShrink: 0,
                minWidth: 0,
              }"
            >
              <image
                class="address-img"
                style="min-width: '80rpx'"
                :src="meetShop.shopImg"
              ></image>
              <view
                :style="{
                  display: 'flex',
                  flexDirection: 'column',
                  marginLeft: '24rpx',
                }"
              >
                <text class="address-txt1">{{ meetShop.shopName }}</text>
                <text class="address-txt2" v-if="!meetShop.isCanteenType">{{
                  meetShop.packageName
                }}</text>
                <text class="address-txt2" v-else
                  >门店地址：{{ meetShop.address }}</text
                >
                <text
                  class="address-txt3"
                  v-if="!meetShop.isCanteenType"
                  :style="{ marginTop: '12rpx' }"
                  >￥{{ meetShop.packagePrice }}</text
                >
              </view>
            </view>
            <image
              src="/static/mine_center/arrow_left.png"
              class="address-arrow"
            ></image>
          </view>
        </view>

        <view class="meet_other" :style="{ marginTop: '24rpx' }">
          <view @click="openMeetTime" class="meet_time">
            <text class="txt">见面时间</text>
            <view
              :style="{
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'center',
              }"
            >
              <text class="txt">{{
                meetTime == null ? "请选择" : meetTime
              }}</text>
              <image
                src="/static/mine_center/arrow_left.png"
                class="arrow"
              ></image>
            </view>
          </view>
          <template v-if="!meetShop.isCanteenType">
            <view class="pay_way">
              <text class="txt">付费方式</text>
              <view
                :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'center',
                }"
              >
                <view
                  @click="changePayWay(1)"
                  class="way"
                  :class="{ checked: payWay == 1 }"
                >
                  <text class="txt">我付</text>
                </view>
                <view
                  @click="changePayWay(3)"
                  class="way"
                  :style="{ marginLeft: '20rpx' }"
                  :class="{ checked: payWay == 3 }"
                >
                  <text class="txt">AA</text>
                </view>
                <view
                  @click="changePayWay(2)"
                  class="way"
                  :style="{ marginLeft: '20rpx' }"
                  :class="{ checked: payWay == 2 }"
                >
                  <text class="txt">你付</text>
                </view>
              </view>
            </view>
            <view v-if="payWay == 1" class="warn_info">
              <text>发起人支付套餐费用，被邀约人如接受需支付履约保证金；</text>
            </view>
            <view v-if="payWay == 2" class="warn_info">
              <text
                >发起人须支付履约保证金，被邀约人如同意需支付套餐费用；</text
              >
            </view>
            <view v-if="payWay == 3" class="warn_info">
              <text>见面双方各付一半的套餐费用</text>
            </view>
          </template>
          <template v-else>
            <view class="meet_time">
              <text class="txt">联系方式</text>
              <view
                :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'center',
                }"
              >
                <text class="txt">{{
                  userInfo.contact_mobile != ""
                    ? userInfo.contact_mobile
                    : userInfo.mobile
                }}</text>
              </view>
            </view>
            <view class="meet_time meet_time_noBorder">
              <text class="txt">选择见面红包</text>
              <view
                @click="chosePice"
                :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'center',
                }"
              >
                <text class="txt">{{ Math.round(meetValue.price) }} 元</text>
                <image
                  src="/static/mine_center/arrow_left.png"
                  class="arrow"
                ></image>
              </view>
            </view>
          </template>
        </view>

        <view
          v-if="
            meetShop.shopId != null &&
            lastInfo.label != null &&
            conveneFlag != 'T'
          "
          class="pay_last"
        >
          <view class="cell" v-if="meetShop.isCanteenType">
            <text class="txt1">履约保证金</text>
            <text class="txt2">￥{{ depositPice }}</text>
          </view>
          <view class="cell">
            <text class="txt1">{{ lastInfo.label }}</text>
            <text class="txt3">￥{{ lastInfo.value }}</text>
          </view>
        </view>
        <view
          v-if="conveneFlag == 'T'"
          class="cost"
          :style="{ marginTop: '24rpx' }"
        >
          <view
            v-if="meetShop.isCanteenType"
            :style="{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
            }"
          >
            <text class="txt1">履约保证金</text>
            <text class="txt1">￥{{ depositPice }}</text>
          </view>
          <view
            :style="{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              marginTop: '16rpx',
            }"
          >
            <text class="txt1">{{ lastInfo.label }}</text>
            <text class="txt1">￥{{ lastInfo.value }}</text>
          </view>
          <view
            :style="{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              marginTop: '16rpx',
            }"
          >
            <text class="txt1">发布费用</text>
            <text class="txt1">￥{{ convenePrice }}</text>
          </view>
          <view
            :style="{
              marginTop: '24rpx',
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'center',
              justifyContent: 'space-between',
              height: '100rpx',
              borderTop: '1px solid #E8EAEF',
            }"
          >
            <text class="txt2">合计费用</text>
            <text class="txt3"
              >￥{{
                (
                  parseFloat(lastInfo.value == null ? 0 : lastInfo.value) +
                  parseFloat(convenePrice)
                ).toFixed(2)
              }}</text
            >
          </view>
        </view>

        <view @click="payAndGive" class="pay_and_give">
          <text>{{
            conveneFlag == "T" ? "支付并发布" : "支付并发起邀约"
          }}</text>
        </view>
        <text class="pay_warn_txt"
          >被邀约人拒绝或48小时内未处理，所支付费用原路退回</text
        >
        <view :style="{ width: '100%', height: '40rpx', flexShrink: 0 }"></view>
      </view>
    </view>

    <uni-popup ref="meetPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(shopPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择见面红包</text>
          <text @click="confirmShopType" class="top-confirm">确定</text>
        </view>
        <picker-view
          immediate-change="true"
          :value="[meetIndex]"
          @change="changeShopType"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in meetRange"
              :key="item.id"
            >
              <text>{{ item.price }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

    <uni-popup ref="cityPopup" type="bottom">
      <city-select title="其它地区" @confirm="confirmSelectCity"></city-select>
    </uni-popup>
    <uni-popup ref="meetTimePopup" type="bottom">
      <uni-meet-time @confirm="confirmMeetTime"></uni-meet-time>
    </uni-popup>
    <uni-popup :style="{ zIndex: '99999' }" ref="payPopup" type="bottom">
      <view class="main-payView">
        <text class="title">支付费用</text>
        <view v-if="conveneFlag == 'T'" class="acitivity_price">
          <text class="txt1">合计费用</text>
          <text class="txt2"
            >￥{{
              (
                parseFloat(lastInfo.value == null ? 0 : lastInfo.value) +
                parseFloat(convenePrice)
              ).toFixed(2)
            }}</text
          >
        </view>
        <view v-else class="acitivity_price">
          <text class="txt1">{{ lastInfo.label }}</text>
          <text class="txt2">￥{{ lastInfo.value }}</text>
        </view>

        <text class="secondTitle">选择支付方式</text>
        <view class="methods">
          <view
            @click="changePayMethod(1)"
            class="parent"
            :class="payType == 1 ? 'parent_select' : ''"
          >
            <view class="second">
              <view class="item" :class="payType == 1 ? 'selected' : ''">
                <image class="item-wechat" src="/static/wechat_pay.png"></image>
                <text class="item-txt">微信支付</text>
              </view>
            </view>
          </view>
          <view v-if="payWay == 2 || payWay == 4" class="space"></view>
          <view
            v-if="payWay == 2"
            @click="changePayMethod(2)"
            class="parent"
            :class="payType == 2 ? 'parent_select' : ''"
          >
            <view class="second">
              <view class="item" :class="payType == 2 ? 'selected' : ''">
                <image
                  class="item-balance"
                  src="/static/balance_pay.png"
                ></image>
                <text class="item-txt">积分支付</text>
              </view>
            </view>
          </view>
        </view>
        <view @click="payNow" class="pay_now">
          <text>立即支付</text>
        </view>
        <!-- <view class="warn">
					<text class="warn-txt">查看退款规则</text>
					<image src="/static/notices/arrow_right.png" class="warn-arrow"></image>
				</view> -->
      </view>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, reactive, ref, computed } from "vue";
import { useStore } from "vuex";
import { onLoad, onUnload,onShow } from "@dcloudio/uni-app";
let chooseFlag = false;
const cityPopup = ref();
const meetTimePopup = ref();
const payPopup = ref();
const defaultCity = computed(() => store.state.user.defaultCity);
const { proxy } = getCurrentInstance();
const store = useStore();
const app = getCurrentInstance().appContext.app;
const currentCity = ref(0);
const userInfo = computed(() => store.state.user.userInfo);
const payWay = ref(1); // 1我付 2你付 3AA 4非餐厅
const payType = ref(1); //1微信 2余额 - 仅pay_mode=2时可使用
let conveneFlag = ref(null);
const convenePrice = ref(0);

const pointCity = ref({}); // 没有默认城市 使用这个 根据当前经纬度获取

const lastInfo = computed(() => {
  if (payWay.value == 1) {
    return {
      label: "套餐费用",
      value: meetShop.packagePrice,
    };
  }
  if (payWay.value == 2) {
    return {
      label: "履约保证金",
      value: meetShop.packagePrice / 2,
    };
  }
  if (payWay.value == 3) {
    return {
      label: "套餐AA费用",
      value: meetShop.packagePrice / 2,
    };
  }
  if (payWay.value == 4) {
    return {
      label: "费用总额",
      value: Number(meetValue.value.price) + Number(depositPice.value),
    };
  }
  return {
    label: null,
    value: null,
  };
});
const otherUser = reactive({
  id: null,
  nickname: null,
  avatar: null,
  gender: null,
});
const selectCity = reactive({
  value: null,
  name: null,
  child: null,
  cityId: null,
});
const meetTime = ref(null);

const meetShop = reactive({
  isCanteenType: null, // 是否是 非餐厅类型
  shopId: null,
  shopName: null,
  shopImg: null, //假的
  packagePrice: null,
  packageName: null,
  packageType: null,
});

const meetIndex = ref(0);
const meetPopup = ref(0);
const meetValue = ref(""); // 见面红包
const meetRange = ref([]); // 下拉选择框

const depositPice = ref(0);

// 获取保证金
const getSecurityPice = async () => {
  const res = await api.post("common/info");
  depositPice.value = Number(res.data.new_deposit_price) / 2;
};

const chosePice = () => {
  meetPopup.value.open();
};

const closePopup = () => {
  meetPopup.value.close();
};

const changeShopType = (e) => {
  meetIndex.value = e.detail.value[0];
};

// 确定见面红包
const confirmShopType = () => {
  const value = meetRange.value[meetIndex.value];
  console.log(value);
  meetValue.value = value;
  meetPopup.value.close();
};

const getMeetList = async () => {
  const res = await api.get("common/meetingRedEnvelopeList");
  console.log(res);
  meetRange.value = res.data;
  meetValue.value = res.data[0];
};

onLoad((options) => {
  conveneFlag.value = options.convene;
  const eventChannel = proxy.getOpenerEventChannel();
  eventChannel.on("acceptDataFromOpenerPage", (data: any) => {
    // 获取上个页面的传值
    if (data != null && data.id != null) {
      Object.assign(otherUser, data);
    }
  });
  if (conveneFlag.value == "T") {
    api.post("common/info").then((vres: any) => {
      if (vres.code == 1) {
        convenePrice.value = vres.data.meeting_publish_price;
      }
    });
  }

  getSecurityPice();

  uni.$on("updateMeetLocation", updateMeet);
  uni.$on("changeCity", updateCityInd);
});


onShow(() => {
  uni.$on("updateMeetLocation", this.updateMeet);
});
const updateCityInd = () => {
  changeCity(1);
};

const openMeetTime = () => {
  meetTimePopup.value.open();
};

const changePayWay = (val) => {
  if (payWay.value != val) {
    payWay.value = val;
  }
};
const changePayMethod = (val) => {
  if (payType.value != val) {
    payType.value = val;
  }
};

const confirmMeetTime = (data) => {
  meetTime.value = new Date().getFullYear() + "-" + data;
};

onUnload(() => {
  uni.$off("updateMeetLocation", updateMeet);
  uni.$off("changeCity", updateCityInd);
});

const updateMeet = (data: any) => {
  console.log("updateMeet", data);
  if (data.isCanteenType) {
    getMeetList();
    payWay.value = 4;
  }

  Object.assign(meetShop, data);
};

const openSelectCity = () => {
  cityPopup.value.open();
};

const confirmSelectCity = (
  options: Array<number>,
  address: string,
  cityId: any,
  cityName,
  location
) => {
  if (options[0] == 0) {
    selectCity.name = location.slice(1, 3).join("-");
  } else {
    selectCity.name = location.join("-");
  }
  selectCity.value = location.join("-");
  selectCity.child = cityName;
  selectCity.cityId = cityId;
};

const chooseMeetLocation = () => {
  if (chooseFlag) {
    // 防止重复点击
    return;
  }
  chooseFlag = true;
  uni.getLocation({
    type: "wgs84",
    highAccuracyExpireTime: 3000,
    isHighAccuracy: true,
    success: (vres) => {
      if (vres.latitude != null) {
        uni.navigateTo({
          url: "/pages/meet_shops/meet_shops",
          success: (res) => {
            chooseFlag = false;
            if (currentCity.value == 0) {
              res.eventChannel.emit("acceptDataFromOpenerPage", {
                type: 1,
                cityName: defaultCity.value?.child,
                area: defaultCity.value?.all,
                cityId: defaultCity.value?.cityInd,
                cityShow:
                  defaultCity.value?.parent + "-" + defaultCity.value?.child,
                position: vres.longitude + "," + vres.latitude,
              });
            } else {
              res.eventChannel.emit("acceptDataFromOpenerPage", {
                cityName: selectCity.child,
                area: selectCity.value,
                cityId: selectCity.cityId,
                cityShow: selectCity.name,
                position: vres.longitude + "-" + vres.latitude,
              });
            }
          },
          fail: () => {
            chooseFlag = false;
          },
        });
      } else {
        chooseFlag = false;
      }
    },
    fail: (err) => {
      chooseFlag = false;
    },
  });
};

const changeCity = (ind: number) => {
  if (currentCity.value != ind) {
    currentCity.value = ind;
    Object.assign(meetShop, {
      shopId: null,
      shopName: null,
      shopImg: null, //假的
      packagePrice: null,
      packageName: null,
      packageType: null,
    });
  }
};

const payAndGive = () => {
  if (meetShop.shopId == null) {
    uni.showToast({
      icon: "none",
      title: "请选择见面地点",
    });
    return;
  }
  if (meetTime.value == null) {
    uni.showToast({
      icon: "none",
      title: "请选择见面时间",
    });
    return;
  }
  if (payWay.value != 2) {
    payType.value = 1;
  }
  payPopup.value.open();
  // api.post('/invitation/pay',{
  // 	invite_user_id: otherUser.id,
  // 	shop_id: meetShop.shopId,
  // 	package: meetShop.packageType,
  // 	meet_time: meetTime.value,
  // 	pay_mode: payWay.value,
  // 	pay_type:
  // })
};

const payNow = async () => {
  let url = "/invitation/pay";
  let param = {
    invite_user_id: otherUser.id,
    shop_id: meetShop.shopId,
    package: meetShop.packageType,
    meet_time: meetTime.value,
    pay_mode: payWay.value,
    pay_type: payType.value,
  };

  if (meetShop.isCanteenType) {
    param.pay_mode = 3;
    param.meeting_red_envelope_id = meetValue.value.id;
  }

  if (conveneFlag.value == "T") {
    url = "call/initiate";
    delete param.invite_user_id;
  } else {
    url = "/invitation/pay";
  }
  const res: any = await api.post(url, param);
  if (res.code == 1) {
    payPopup.value.close();
    if (payType.value == 1) {
      uni.requestPayment({
        provider: "wxpay",
        orderInfo: null,
        timeStamp: res.data.payment.timeStamp,
        nonceStr: res.data.payment.nonceStr,
        package: res.data.payment.package,
        signType: res.data.payment.signType,
        paySign: res.data.payment.paySign,
        success: (result) => {
          if (conveneFlag.value == "T") {
            uni.$emit("meetingUpdate");
            setTimeout(() => {
              uni.navigateBack();
            }, 1000);
          } else {
            uni.redirectTo({
              url: "/pages/give_success/give_success",
            });
          }
          uni.showToast({
            icon: "none",
            title: "支付成功",
          });
        },
        fail: () => {
          uni.showToast({
            icon: "none",
            title: "支付失败",
          });
        },
      });
    } else {
      if (conveneFlag.value == "T") {
        uni.$emit("meetingUpdate");
        setTimeout(() => {
          uni.navigateBack();
        }, 1000);
      } else {
        uni.redirectTo({
          url: "/pages/give_success/give_success",
        });
      }
    }
  }
};

const navBack = () => {
  uni.navigateBack();
};
</script>

<style lang="scss">
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
    // overflow-y: auto;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    .avatars {
      margin-top: 12rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      &-first {
        width: 120rpx;
        height: 120rpx;
        border-radius: 60rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        .second {
          width: 96rpx;
          height: 96rpx;
          border-radius: 48rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: center;
        }
        .third {
          width: 72rpx;
          height: 72rpx;
          border-radius: 36rpx;
        }
      }
      &-invition {
        margin-left: 24rpx;
        font-size: 28rpx;
        color: #ff546f;
        font-weight: 500;
      }
      &-heart {
        margin-left: 8rpx;
        margin-right: 24rpx;
        width: 64rpx;
        height: 64rpx;
      }
    }
    .meet_location {
      width: 686rpx;
      background-color: #fff;
      border-radius: 24rpx;
      padding: 0 32rpx;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      margin-top: 32rpx;
      .common {
        flex: 1;
        flex-shrink: 0;
        min-width: 0;
        height: 96rpx;
        line-height: 96rpx;
        text-align: center;
        border-bottom: 1px solid;
        font-size: 28rpx;
        font-weight: 500;
      }
      .border_color {
        border-color: #e8eaef;
        color: #1d2129;
      }
      .checked {
        background: linear-gradient(105deg, #4a97e7, #b57aff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        border-image: linear-gradient(105deg, #4a97e7, #b57aff 100%) 0.5 0.5;
      }
      .address {
        height: 200rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        &-icon1 {
          width: 40rpx;
          height: 40rpx;
        }
        &-noCheck {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          font-size: 28rpx;
          color: #c2c5cc;
          font-weight: 500;
          margin: 0 16rpx;
        }
        &-img {
          width: 136rpx;
          height: 136rpx;
          border-radius: 12rpx;
        }
        &-txt1 {
          font-size: 28rpx;
          font-weight: 500;
          color: #1d2129;
          line-height: 44rpx;
        }
        &-txt2 {
          font-size: 24rpx;
          color: #868d9c;
          line-height: 40rpx;
        }
        &-txt3 {
          margin-top: 12rpx;
          font-size: 24rpx;
          color: #1d2129;
          line-height: 40rpx;
        }
        &-arrow {
          width: 16rpx;
          height: 24rpx;
        }
      }
      .city {
        display: flex;
        flex-direction: row;
        align-items: center;
        height: 108rpx;
        border-bottom: 1px solid #e8eaef;
        &-icon {
          width: 40rpx;
          height: 40rpx;
        }
        &-unSelected {
          flex: 1;
          min-width: 0;
          flex-shrink: 0;
          margin-left: 16rpx;
          margin-right: 16rpx;
          font-size: 28rpx;
          color: #c2c5cc;
          font-weight: 500;
        }
        &-selected {
          color: #1d2129;
        }
        &-arrow {
          width: 16rpx;
          height: 24rpx;
        }
      }
    }
    .meet_other {
      width: 686rpx;
      background-color: #ffffff;
      border-radius: 24rpx;
      padding: 0 32rpx;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      .meet_time_noBorder {
        border-bottom: none !important;
      }
      .meet_time {
        display: flex;
        flex-direction: row;
        align-items: center;
        height: 108rpx;
        justify-content: space-between;
        border-bottom: 1px solid #e8eaef;
        .txt {
          font-size: 28rpx;
          color: #1d2129;
          font-weight: 500;
        }
        .arrow {
          margin-left: 24rpx;
          width: 16rpx;
          height: 24rpx;
        }
      }
      .pay_way {
        margin-top: 32rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        .txt {
          font-size: 28rpx;
          color: #1d2129;
          font-weight: 500;
        }
        .way {
          width: 96rpx;
          height: 56rpx;
          background: #ffffff;
          border: 1px solid #dadce0;
          border-radius: 28rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: center;
          .txt {
            font-size: 24rpx;
            color: #1d2129;
          }
        }
        .checked {
          border: 1px solid transparent;
          background: linear-gradient(
            114deg,
            rgba(74, 151, 231, 0.15),
            rgba(181, 122, 255, 0.15) 100%
          );
          .txt {
            color: transparent;
            background: linear-gradient(126deg, #4a97e7, #b57aff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          }
        }
      }
      .warn_info {
        margin-top: 20rpx;
        margin-bottom: 32rpx;
        padding: 8rpx 16rpx;
        background: #f7f8fa;
        border-radius: 16rpx;
        font-size: 24rpx;
        color: #868d9c;
        line-height: 40rpx;
      }
    }
    .pay_last {
      width: 686rpx;
      height: 108rpx;
      background: #ffffff;
      flex-shrink: 0;
      margin-top: 24rpx;
      display: flex;
      flex-direction: column;
      padding: 0 32rpx;
      box-sizing: border-box;
      border-radius: 24rpx;
      .cell {
        flex: 1;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        .txt1 {
          font-size: 28rpx;
          color: #1d2129;
          font-weight: 500;
        }
        .txt2 {
          font-size: 28rpx;
          font-weight: 500;
          color: #ff546f;
        }
        .txt3 {
          font-size: 28rpx;
          color: #1d2129;
          font-weight: 500;
        }
      }
    }
    .pay_and_give {
      margin-top: 64rpx;
      width: 686rpx;
      height: 88rpx;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 96rpx;
      line-height: 88rpx;
      text-align: center;
      font-size: 32rpx;
      font-weight: 500;
      color: #fff;
    }
    .cost {
      width: 686rpx;
      padding: 32rpx;
      background-color: #ffffff;
      border-radius: 24rpx;
      box-sizing: border-box;
      .txt1 {
        font-size: 26rpx;
        color: #868d9c;
      }
      .txt2 {
        font-size: 28rpx;
        font-weight: 500;
        color: #1d2129;
      }
      .txt3 {
        font-size: 28rpx;
        font-weight: 500;
        color: #f0251b;
      }
    }
    .pay_warn_txt {
      margin-top: 32rpx;
      font-size: 24rpx;
      color: #868d9c;
    }
  }
  &-payView {
    width: 100%;
    padding: 40rpx;
    box-sizing: border-box;
    background-color: #fff;
    border-radius: 32rpx 32rpx 0px 0px;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    .title {
      font-size: 32rpx;
      color: #1d2129;
      font-weight: 500;
    }
    .acitivity_price {
      margin-top: 32rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      padding-bottom: 36rpx;
      box-sizing: border-box;
      border-bottom: 1px solid #e8eaef;
      .txt1 {
        font-size: 28rpx;
        color: #868d9c;
      }
      .txt2 {
        font-size: 36rpx;
        color: #ff546f;
        font-weight: 500;
      }
    }
    .secondTitle {
      margin-top: 32rpx;
      font-size: 28rpx;
      color: #868d9c;
    }
    .methods {
      margin-top: 24rpx;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      .space {
        width: 24rpx;
        height: 100%;
      }
      .parent {
        // width: 324rpx;
        flex: 1;
        height: 108rpx;
        padding: 3rpx;
        box-sizing: border-box;
        border-radius: 24rpx;
        background: #f0f2f5;
        display: flex;
        flex-direction: column;
        .second {
          flex: 1;
          min-height: 0;
          border-radius: 24rpx;
          background-color: #fff;
          .item {
            width: 100%;
            height: 100%;
            border-radius: 24rpx;
            background-color: #fff;
            display: flex;
            flex-direction: row;
            align-items: center;
            &-wechat {
              margin-left: 40rpx;
              width: 40rpx;
              height: 40rpx;
            }
            &-balance {
              margin-left: 40rpx;
              width: 42rpx;
              height: 40rpx;
            }
            &-txt {
              margin-left: 24rpx;
              font-size: 28rpx;
              color: #222222;
            }
          }
          .selected {
            position: relative;
            background: linear-gradient(
              106deg,
              rgba(74, 151, 231, 0.1),
              rgba(181, 122, 255, 0.1) 100%
            );
            // border-image: linear-gradient(106deg, #4a97e7, #b57aff 100%) 1.5 1.5;
          }
        }
      }
      .parent_select {
        background: linear-gradient(106deg, #4a97e7, #b57aff 100%);
      }
    }
    .pay_now {
      margin-top: 64rpx;
      height: 88rpx;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;
      line-height: 88rpx;
      text-align: center;
      font-size: 28rpx;
      color: #fff;
      font-weight: 500;
      margin-bottom: 84rpx;
    }
    .warn {
      margin-top: 32rpx;
      margin-bottom: 84rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      &-txt {
        font-size: 24rpx;
        color: #868d9c;
      }
      &-arrow {
        margin-left: 8rpx;
        width: 14rpx;
        height: 16rpx;
      }
    }
  }
}

.popup_stature {
  width: 750rpx;
  height: 708rpx;
  background-color: #fff;
  border-radius: 32rpx 32rpx 0px 0px;
  padding: 0 32rpx;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  .top {
    display: flex;
    flex-direction: row;
    height: 112rpx;
    align-items: center;
    justify-content: space-between;
    &-cancel {
      font-size: 32rpx;
      color: #868d9c;
    }
    &-title {
      font-size: 32rpx;
      color: #1d2129;
      font-weight: 500;
    }
    &-confirm {
      font-size: 32rpx;
      color: #2c94ff;
      font-weight: 500;
    }
  }
  .second {
    display: flex;
    flex-direction: row;
    width: 100%;
    &-tab {
      flex: 1;
      flex-shrink: 0;
      min-width: 0;
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      .item {
        height: 88rpx;
        display: inline-block;
        border-bottom: 2px solid #fff;
        font-size: 32rpx;
        color: #868d9c;
        font-weight: 500;
        text-align: center;
        line-height: 88rpx;
      }
      .checked {
        border-bottom: 2px solid #2c94ff;
        color: #2c94ff;
      }
    }
  }
  .picker {
    flex: 1;
    flex-shrink: 0;
    min-height: 0;
    &-item {
      text-align: center;
      line-height: 48px;
      font-size: 16px;
      color: #1d2129;
    }
  }
}
</style>
