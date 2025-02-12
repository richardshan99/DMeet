<template>
  <view class="main">
    <image class="main-top" :src="app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "></image>
    <view class="main-base">
      <view class="nav" :style="{ marginTop: statusBarHeight + 'px' }">
        <view class="nav-item" :style="{ flexShrink: 1 }">
          <uni-icons type="left" @click="navBack" size="20" :style="{ marginLeft: '32rpx' }"></uni-icons>
        </view>
        <view class="nav-item" :style="{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          justifyContent: 'center',
        }">
          <view class="nav-item-title"> 邀约记录 </view>
          <!-- <view @click="changeTab(0)" class="bar">
            <text class="bar-txt" :class="tabIndex == 0 ? 'active' : 'common'"
              >我邀约的</text
            >
            <image
              :style="{ visibility: tabIndex == 0 ? 'visible' : 'hidden' }"
              class="bar-line"
              src="/static/index/current_line.png"
            ></image>
          </view>
          <view class="divide"></view>
          <view @click="changeTab(1)" class="bar">
            <text class="bar-txt" :class="tabIndex == 1 ? 'active' : 'common'"
              >邀约我的</text
            >
            <image
              :style="{ visibility: tabIndex == 1 ? 'visible' : 'hidden' }"
              class="bar-line"
              src="/static/index/current_line.png"
            ></image>
          </view> -->
        </view>
        <view class="nav-item" :style="{ minWidth: right_distance + 'px' }"></view>
      </view>
      <view class="tabbar">
        <view @click="changeStatus(0)" class="item" :class="{ active: status == 0 }">
          <text class="txt">全部</text>
        </view>
        <view @click="changeStatus(1)" class="item" :class="{ active: status == 1 }">
          <text class="txt">待确认</text>
        </view>
        <view @click="changeStatus(2)" class="item" :class="{ active: status == 2 }">
          <text class="txt">待见面</text>
        </view>
        <view @click="changeStatus(3)" class="item" :class="{ active: status == 3 }">
          <text class="txt">已完成</text>
        </view>
        <view @click="changeStatus(4)" class="item" :class="{ active: status == 4 }">
          <text class="txt">已取消</text>
        </view>
      </view>
      <scroll-view type="list" :scroll-y="true" class="scroll">
        <view class="item" v-for="(item, index) in invitations" :key="'invitation' + index"
          :class="{ other_invitation: item.invite_type == 1 }">
          <!-- other_invitation -->
          <view class="meet">
            <view class="meet-top">
              <view class="left">
                <view class="left-head">
                  <image @click="toUserDetail(item.inviteuser.id)" mode="aspectFill" v-if="item.invite_type == 1"
                    class="avatar2" :src="item.inviteuser.avatar_text"></image>
                  <view v-else class="space_avatar"></view>
                  <image @click="toUserDetail(item.user.id)" mode="aspectFill" class="avatar"
                    :src="item.user.avatar_text"></image>
                  <view class="pay_btn pay_me" v-if="item.shop_type == 1" :class="{
                    pay_me: item.pay_mode == 1,
                    pay_you: item.pay_mode == 2,
                    pay_aa: item.pay_mode == 3,
                  }">
                    <text v-if="item.pay_mode == 1">我付</text>
                    <text v-if="item.pay_mode == 2">你付</text>
                    <text v-if="item.pay_mode == 3">AA</text>
                  </view>
                </view>
                <view class="intro">
                  <view class="intro-part1" v-if="item.invite_type == 1">
                    <view class="txt1 name">{{
                      item.inviteuser.nickname
                    }}</view>
                    <image v-if="item.inviteuser.gender == 1" src="/static/sex_man.png" class="sex"></image>
                    <image v-else src="/static/sex_woman.png" class="sex"></image>
                    <image v-if="item.inviteuser.is_member == 1" src="/static/vip_icon.png" class="other_icon"></image>
                    <!-- <image
                      v-if="item.inviteuser.is_cert_realname == 1"
                      src="/static/person/confirm_name.png"
                      class="other_icon"
                    ></image>
                    <image
                      v-if="item.inviteuser.is_cert_education == 1"
                      src="/static/person/edu.png"
                      class="other_icon"
                    ></image> -->
                    <image v-if="item.invite_type == 1" src="/static/invitation/my_invitation.png"
                      class="inivitation_flag"></image>
                  </view>
                  <view class="intro-part1" v-if="item.invite_type == 2">
                    <view class="txt1 name">{{ item.user.nickname }}</view>

                    <image v-if="item.user.gender == 1" src="/static/sex_man.png" class="sex"></image>
                    <image v-else src="/static/sex_woman.png" class="sex"></image>
                    <image v-if="item.user.is_member == 1" src="/static/vip_icon.png" class="other_icon"></image>
                    <!-- <image
                      v-if="item.user.is_cert_realname == 1"
                      src="/static/person/confirm_name.png"
                      class="other_icon"
                    ></image>
                    <image
                      v-if="item.user.is_cert_education == 1"
                      src="/static/person/edu.png"
                      class="other_icon"
                    ></image> -->
                    <image src="/static/invitation/send_invitation.png" class="inivitation_flag"></image>
                  </view>
                  <text v-if="item.invite_type == 1" class="intro-boInfo">{{ item.inviteuser.birth_year }}年 ·
                    {{ item.inviteuser.height }}cm ·
                    {{ item.inviteuser.area }}</text>
                  <text v-if="item.invite_type == 2" class="intro-boInfo">{{ item.user.birth_year }}年 · {{
                    item.user.height }}cm ·
                    {{ item.user.area }}</text>
                </view>
              </view>
              <text v-if="item.status == 1 || item.status == 2" class="status">{{ item.status_text }}</text>
              <text v-if="item.status == 3 || item.status == 4" class="status_other">{{ item.status_text }}</text>
            </view>
            <view @click="toShopDetail(item.shop.id, item.package.name)" class="title">[{{ item.area_name }}] {{
              item.shop.name
              }}<image :style="{ width: '14rpx', height: '20rpx', marginLeft: '8rpx' }"
                src="/static/invitation/arrow_right.png"></image>
            </view>
            <view class="rules">
              <image src="/static/invitation/location.png" class="rules-icon"></image>
              <text class="rules-info">{{ item.address }}</text>
            </view>
            <view class="rules">
              <image src="/static/invitation/datetime.png" class="rules-icon"></image>
              <text class="rules-info">{{ item.meet_time_new_text }}</text>
            </view>
            <view class="rules" v-if="item.shop_type == 1">
              <image src="/static/invitation/package.png" class="rules-icon"></image>
              <text class="rules-info">{{ item.package.name }}(￥{{
                item.package.package2_price == null
                  ? item.package.package1_price
                  : item.package.package2_price
              }})</text>
            </view>
            <image src="/static/invitation/invitation_line.png" class="divide_line"></image>

            <!-- 区分开 类型 不做过多判断 -->
            <!-- 餐厅类型 按钮操作  -->
            <template v-if="item.shop_type == 1">
              <view class="lastPart">
                <view class="lastPart-money">
                  <text class="txt1">实付金额 </text>
                  <text class="txt2">¥{{ item.price }}</text>
                </view>
                <view v-if="item.invite_type == 1" class="lastPart-btns">
                  <view v-if="item.status == 1" @click="revokeInvitation(item.id)" class="btn1"><text>撤回邀约</text></view>
                  <view v-if="item.status == 2" @click="cancelInvitation(item.id)" class="btn1"><text>取消邀约</text></view>
                  <view v-if="item.status == 2" @click="openQrcode(item)" class="btn2" :style="{ marginLeft: '20rpx' }">
                    <text>核销码</text></view>
                </view>
                <view v-if="item.invite_type == 2" class="lastPart-btns">
                  <view v-if="item.status == 1" @click="refuseInvitation(item.id)" class="btn1"><text>拒绝</text></view>
                  <view v-if="item.status == 1" @click="agree(item)" class="btn2" :style="{ marginLeft: '20rpx' }">
                    <text>同意</text></view>
                  <view v-if="item.status == 2" @click="cancelInvitation(item.id)" class="btn1"><text>取消邀约</text></view>
                  <view v-if="item.status == 2" class="btn2" @click="openQrcode(item)" :style="{ marginLeft: '20rpx' }">
                    <text>核销码</text></view>
                </view>
                <view v-if="item.status == 3" class="lastPart-btns">
                  <view @click="shareWidth(item.id)" class="btn2"><text>分享动态</text></view>
                </view>
              </view>
            </template>
            <!-- 非餐厅类型 操作按钮 -->
            <template v-else>
              <view class="meet-operating">
                <view class="operating-info" v-if="item.invite_type == 2">
                  <view>
                    <text class="meet-title">
                      <image src="" mode="scaleToFill" />
                      {{ item.status == 1 ? "需要" : "已" }}支付保证金
                    </text>
                    <text class="price">¥{{ item.price }}</text>
                  </view>
                  <view>
                    <view class="meet-title">
                      <image class="red_packet" src="/static/red_packet.png" mode="scaleToFill" />
                      见面红包
                    </view>
                    <text class="price">¥{{ item.meeting_red_envelope_price }}</text>
                  </view>
                </view>
                <view class="operating-btn">
                  <!-- 订单状态为待确认 -->
                  <template v-if="item.status == 1">
                    <template v-if="item.invite_type == 2">
                      <view @click="agree(item)" class="btn2" :style="{ marginLeft: '20rpx' }"><text>同意</text></view>
                      <view @click="refuseInvitation(item.id)" class="btn1"><text>拒绝</text></view>
                      <view class="btn3" v-if="
                        item.invite_type == 2 &&
                        (item.change.id == null || item.change.id == '')
                      " @click="updataMeetInfo(item)"><text>修改见面信息</text></view>
                    </template>

                    <!-- 对方修改了见面信息，同意或者拒绝操作  -->
                    <template v-else-if="
                      item.invite_type == 1 &&
                      (item.change.id != null || item.change.id != '') &&
                      item.change.status == 1
                    ">
                      <view @click="changeAgree(item, 'agree')" class="btn2" :style="{ marginLeft: '20rpx' }">
                        <text>同意</text></view>
                      <view @click="changeAgree(item, 'refuse')" class="btn1"><text>拒绝</text></view>
                      <view class="lastPart-money">
                        <text class="txt1">实付金额 </text>
                        <text class="txt2">¥{{ item.price }}</text>
                      </view>
                    </template>

                    <template v-else>
                      <view @click="revokeInvitation(item.id)" class="btn1"><text>撤回邀约</text></view>
                      <view class="lastPart-money">
                        <text class="txt1">实付金额 </text>
                        <text class="txt2">¥{{ item.price }}</text>
                      </view>
                    </template>
                  </template>
                  <!-- 订单状态为待见面 -->
                  <template v-else-if="item.status == 2">
                    <template v-if="item.invite_type == 1">
                      <view v-if="
                        item.invite_type == 1 && item.inviter_is_verify == -1
                      " @click="signIn(item)" class="btn2" :style="{ marginLeft: '20rpx' }"><text>签到</text></view>
                      <view v-else class="btn2" :style="{ marginLeft: '20rpx' }"><text> 已签到</text></view>
                    </template>
                    <template v-else>
                      <view v-if="
                        item.invite_type == 2 && item.invitee_is_verify == -1
                      " @click="signIn(item)" class="btn2" :style="{ marginLeft: '20rpx' }"><text>签到</text></view>
                      <view v-else class="btn2" :style="{ marginLeft: '20rpx' }"><text> 已签到</text></view>
                    </template>

                    <!-- <view
                      @click="signIn(item)"
                      class="btn2"
                      :style="{ marginLeft: '20rpx' }"
                      ><text>签到</text></view
                    > -->

                    <template v-if="item.invite_type == 1">
                      <!-- 邀请人 未签到  -->
                      <view v-if="
                        item.invite_type == 1 && item.inviter_is_verify == -1
                      " @click="cancelInvitation(item.id)" class="btn1"><text>取消邀约</text></view>
                    </template>
                    <template v-else>
                      <!-- 被邀请人 未签到  -->
                      <view v-if="
                        item.invite_type == 2 && item.invitee_is_verify == -1
                      " @click="cancelInvitation(item.id)" class="btn1"><text>取消邀约</text></view>
                    </template>

                    <view class="lastPart-money">
                      <text class="txt1">实付金额 </text>
                      <text class="txt2">¥{{ item.price }}</text>
                    </view>
                  </template>
                  <template v-else-if="item.status == 3">
                    <view @click="shareWidth(item.id)" class="btn2" :style="{ marginLeft: '20rpx' }"><text>分享动态</text>
                    </view>
                    <view @click="getByPhone(item)" class="btn3" :style="{ marginLeft: '20rpx' }"><text>查看联系方式</text>
                    </view>
                  </template>
                </view>
              </view>
            </template>

            <view class="IssignIn" v-if="item.invite_type == 1 && item.invitee_is_verify != -1">
              <view class="siginStatus">
                <image class="siginStatus_icon" src="/static/isSingin.png" mode="scaleToFill" />
                对方已签到
              </view>
              <view class="go" @click="opMap(item)">
                查看Ta的位置
                <image class="go_icon" src="/static/go.png" mode="scaleToFill" />
              </view>
            </view>
            <view class="IssignIn" v-if="item.invite_type == 2 && item.inviter_is_verify != -1">
              <view class="siginStatus">
                <image class="siginStatus_icon" src="/static/isSingin.png" mode="scaleToFill" />
                对方已签到
              </view>
              <view class="go" @click="opMap(item)">
                查看Ta的位置
                <image class="go_icon" src="/static/go.png" mode="scaleToFill" />
              </view>
            </view>
          </view>
        </view>
        <text :style="{
          margin: '20rpx 0',
          fontSize: '24rpx',
          color: '#999',
          textAlign: 'center',
          display: 'block',
        }">没有更多数据了</text>
      </scroll-view>
    </view>

    <uni-popup ref="QrcodePopup" type="bottom">
      <uni-verify-code :codeInfo="choiceInfo"></uni-verify-code>
    </uni-popup>
    <uni-popup ref="rejectPopup" type="bottom">
      <uni-revoke @confirm="rejectInvitation"></uni-revoke>
    </uni-popup>

    <uni-popup :style="{ zIndex: '99999' }" ref="payPopup" type="bottom">
      <view class="main-payView">
        <text class="title">同意邀约</text>
        <view class="acitivity_price">
          <text class="txt1">邀约费用</text>
          <text class="txt2">￥{{ selectInvitation.price }}</text>
        </view>
        <text class="secondTitle">选择支付方式</text>
        <view class="methods">
          <view @click="changePayMethod(1)" class="parent" :class="payType == 1 ? 'parent_select' : ''">
            <view class="second">
              <view class="item" :class="payType == 1 ? 'selected' : ''">
                <image class="item-wechat" src="/static/wechat_pay.png"></image>
                <text class="item-txt">微信支付</text>
              </view>
            </view>
          </view>
          <view v-if="
            selectInvitation.invite_type == 2 &&
            selectInvitation.pay_mode == 2
          " class="space"></view>
          <view v-if="
            selectInvitation.invite_type == 2 &&
            selectInvitation.pay_mode == 2
          " @click="changePayMethod(2)" class="parent" :class="payType == 2 ? 'parent_select' : ''">
            <view class="second">
              <view class="item" :class="payType == 2 ? 'selected' : ''">
                <image class="item-balance" src="/static/balance_pay.png"></image>
                <text class="item-txt">余额支付</text>
              </view>
            </view>
          </view>
        </view>
        <view @click="payNow" class="pay_now">
          <text>立即支付</text>
        </view>
        <!-- 	<view class="warn">
					<text class="warn-txt">查看退款规则</text>
					<image src="/static/notices/arrow_right.png" class="warn-arrow"></image>
				</view> -->
      </view>
    </uni-popup>

    <!-- 签到 -->
    <uni-popup :style="{ zIndex: '99999' }" ref="signInPopup" background-color="#fff" type="bottom"
      borderRadius="24rpx 24rpx 0rpx 0rpx;">
      <view class="sign-box">
        <view class="title">签到</view>
        <view class="content-box">
          <view class="image-text">
            <image class="selfie" src="/static/selfie.png" mode="scaleToFill" />
            <view class="text">到达现场后，请在现场自拍签到 </view>
          </view>
          <view class="btn" @click="openSelfie(chooseItme)">
            打开相机自拍
          </view>
          <view class="agreement">
            <checkbox-group @change="changeShare">
              <label>
                <checkbox style="transform: scale(0.7)" value="cb" />同步分享见面动态（勾选此项，在对方完成签到后，系统会自动生成见面动态）
              </label>
            </checkbox-group>
          </view>
        </view>
      </view>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import * as qiniuUploader from "@/common/upload/qiniuUploader.ts";
import { api } from "@/common/request/index.ts";
import { reactive, ref, getCurrentInstance } from "vue";
import { onLoad } from "@dcloudio/uni-app";
const app = getCurrentInstance().appContext.app;
const statusBarHeight = ref(20);
const right_distance = ref(0);
const tabIndex = ref(0);
const status = ref(0); // 1待确认 2待见面 3已完成 4已取消

const payType = ref(1);
const selectInvitation = reactive({
  id: null,
  price: null,
  invite_type: null,
  pay_mode: null,
});

const choiceInfo = reactive({
  id: null,
  packName: null,
  price: null,
});

const QrcodePopup = ref(); // 核销码弹窗
const rejectPopup = ref(); // 拒绝弹窗
const payPopup = ref(); // 同意弹窗

const signInPopup = ref(); // 签到弹窗
const isShareValue = ref(false); // 是否同步分享见面动态
const chooseItme = ref({}); // 选中的 订单

let rejectId = null;

const position = ref("");

const invitations = ref([]);
onLoad(() => {
  statusBarHeight.value = uni.getSystemInfoSync().statusBarHeight;
  right_distance.value =
    uni.getSystemInfoSync().windowWidth -
    uni.getMenuButtonBoundingClientRect().left;
  getInvitations();
});

// 获取手机号
const getByPhone = (item) => {
  console.log(item, "item");
  let phone =
    item.invite_type == 1
      ? item.inviteuser.contact_mobile || item.inviteuser.mobile
      : item.user.contact_mobile || item.user.mobile;
  uni.showModal({
    title: "是否联系对方！",
    content: `拨打电话:${phone}`,
    confirmText: "拨打电话",
    success: (ures) => {
      if (ures.confirm) {
        uni.makePhoneCall({
          phoneNumber: phone, //仅为示例
        });
      }
    },
  });
};

// 对方修改了见面信息  同意/拒绝操作
const changeAgree = async (item, type) => {
  const res = await api.post("invite/handleChange", {
    invite_id: item.id,
    scene: type,
  });

  if (res.code == 1) {
    getInvitations();
    uni.showToast({
      icon: "none",
      title: "已同意",
    });
    setTimeout(() => {
      uni.hideToast();
    }, 1500);
  } else {
    uni.showToast({
      icon: "none",
      title: res.msg,
    });
    setTimeout(() => {
      uni.hideToast();
    }, 1500);
  }
};

const changeShare = (value) => {
  console.log(value);
  isShareValue.value = value.detail.value.length > 0 ? true : false;
};

// 签到
const signIn = (item) => {
  uni.getLocation({
    type: "wgs84",
    isHighAccuracy: true,
    highAccuracyExpireTime: 6000,
    success: (info) => {
      console.log(info);
      const latitude = info.latitude;
      const longitude = info.longitude;
      position.value = [longitude, latitude].toString();
    },
  });
  chooseItme.value = item;
  signInPopup.value.open();
};

// 上传文件 到七牛云
const uplaodFile = (path: string) => {
  return new Promise((resolve, reject) => {
    qiniuUploader.upload({
      filePath: path,
      success: (res) => {
        resolve(res.imageURL);
      },
      fail: (err) => {
        reject(null);
      },
    });
  });
};

// 打开相机自拍
const openSelfie = (item) => {
  uni.chooseImage({
    count: 1,
    sourceType: ["camera"],
    success: async (res) => {
      console.log(res);
      const vres = await api.post("common/qiniu");
      qiniuUploader.init({
        domain: vres.data.cdnurl,
        region: "ECN",
        regionUrl: vres.data.uploadurl,
        uptoken: vres.data.multipart.qiniutoken,
      });
      const file = await uplaodFile(res.tempFilePaths[0]);
      console.log(isShareValue.value);

      const apiRes = await api.post("invite/signIn", {
        position: position.value,
        invite_id: item.id,
        is_share: isShareValue.value ? 1 : -1,
        sign_image: file,
      });

      if (apiRes.code == 1) {
        uni.showToast({
          icon: "none",
          title: "签到成功",
        });
      } else {
        uni.showToast({
          icon: "none",
          title: apiRes.msg,
        });
      }

      setTimeout(() => {
        uni.hideToast();
      }, 1500);
    },
    fail: (err) => {
      console.log(err);
    },
  });
};

// 修改见面信息
const updataMeetInfo = (userDetail) => {
  console.log(userDetail, "userDetail--");
  api.post("/invitation/check").then((res: any) => {
    if (res.code == 1 && res.data.can_invite == 1) {
      uni.navigateTo({
        url: `/pages/give_invitation_updata/give_invitation_updata`,
        success(res) {
          console.log(res);
          res.eventChannel.emit("acceptDataFromOpenerPage", {
            invite_type: userDetail.invite_type,
            nickname: userDetail.inviteuser.nickname,
            avatar: userDetail.inviteuser.avatar_text,
            gender: userDetail.inviteuser.gender,
            id: userDetail.inviteuser.id,
            invite_id: userDetail.id,
            user: userDetail.user,
          });
        },
      });
    } else {
      uni.showModal({
        title: "提示",
        content: "只有会员才能使用邀约，是否购买会员",
        success: (bres) => {
          if (bres.confirm) {
            uni.navigateTo({
              url: "/pages/member_purchase/member_purchase",
            });
          }
        },
      });
    }
  });
};

const toShopDetail = (shopId, serviceName: string) => {
  if (shopId == null || shopId.length <= 0) {
    uni.showToast({
      icon: "none",
      title: "无此店铺",
    });
    return;
  }
  uni.navigateTo({
    url: `/pages/shop_detail/shop_detail?id=${shopId}&serviceName=${serviceName}&type=watch`,
  });
};

const toUserDetail = (id: string) => {
  uni.navigateTo({
    url: `/pages/personal_details/personal_details?id=${id}`,
  });
};

const changeTab = function (index: number) {
  if (tabIndex.value == index) {
    return;
  }
  tabIndex.value = index;
  getInvitations();
};

const navBack = () => {
  uni.navigateBack();
};

const changeStatus = (tab: number) => {
  if (status.value != tab) {
    status.value = tab;
    getInvitations();
  }
};

const getInvitations = () => {
  let param = {};
  let url = "invite/my_invitation_list";
  if (tabIndex.value == 0) {
    url = "invite/my_invitation_list";
  }
  if (tabIndex.value == 1) {
    url = "invite/invite_me_list";
  }
  if (status.value != 0) {
    param = {
      status: status.value,
    };
  }
  api.post(url, param).then((res: any) => {
    if (res.code == 1) {
      invitations.value = res.data;
    }
  });
};

const opMap = (item) => {
  console.log(item);
  let list = "";

  if (item.invite_type == 1 && item.invitee_is_verify != -1) {
    list = item.invitee_sign_longitude_and_latitude;
  } else {
    list = item.inviter_sign_longitude_and_latitude;
  }
  const newList = list.split(",");
  console.log(newList);

  console.log(list);
  uni.openLocation({
    latitude: Number(newList[1]),
    longitude: Number(newList[0]),
  });
};

const openQrcode = (item) => {
  choiceInfo.id = item.id;
  choiceInfo.packName = item.package.name;
  choiceInfo.price = item.price;
  QrcodePopup.value.open();
};

const changePayMethod = (val) => {
  if (payType.value != val) {
    payType.value = val;
  }
};

const shareWidth = (id: string) => {
  uni.navigateTo({
    url: `/pages/publish_trends/publish_trends?invitationId=${id}`,
  });
};

const revokeInvitation = (id: string) => {
  rejectId = id;
  rejectPopup.value.open();
};

const cancelInvitation = async (id: string) => {
  const res: any = await api.post("invite/cancel", { invite_id: id });
  if (res.code == 1) {
    //
    uni.showToast({
      icon: "none",
      title: res.msg,
    });
    getInvitations();
  }
};

const rejectInvitation = async () => {
  // toggleBottomBar(true)
  // 拒绝
  const res: any = await api.post("/invite/revoke", { invite_id: rejectId });
  if (res.code == 1) {
    uni.showToast({
      icon: "none",
      title: res.msg,
    });
    getInvitations();
  }
};

const agree = (item: any) => {
  selectInvitation.id = item.id;
  selectInvitation.price = item.price;
  selectInvitation.invite_type = item.invite_type;
  selectInvitation.pay_mode = item.pay_mode;
  payType.value = 1;
  payPopup.value.open();
};

const payNow = async () => {
  const res: any = await api.post("/invite/approve", {
    invite_id: selectInvitation.id,
    pay_type: payType.value,
  });
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
          uni.showToast({
            icon: "none",
            title: "支付成功",
          });
          getInvitations();
        },
        fail: () => {
          uni.showToast({
            icon: "none",
            title: "支付失败",
          });
        },
      });
    } else {
      getInvitations();
      uni.showToast({
        icon: "none",
        title: res.msg,
      });
    }
  }
};

// 拒绝
const refuseInvitation = async (id: string) => {
  const res: any = await api.post("invite/reject", { invite_id: id });
  if (res.code == 1) {
    uni.showToast({
      icon: "none",
      title: res.msg,
    });
    getInvitations();
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
    align-items: center;

    .nav {
      width: 750rpx;
      height: 44px;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;

      &-item {
        flex: 1;
      }

      .nav-item-title {
        font-size: 32rpx;
        font-family: PingFang SC, PingFang SC-500;
        font-weight: 500;
        color: #1d2129;
      }

      .icon {
        width: 20px;
        height: 20px;
      }

      .divide {
        width: 1px;
        height: 12px;
        background-color: rgba(0, 0, 0, 0.15);
        margin: 0 32rpx;
      }

      .bar {
        display: flex;
        flex-direction: column;
        align-items: center;

        &-txt {
          font-size: 16px;
          font-weight: 600;
          white-space: nowrap;
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

    .tabbar {
      width: 686rpx;
      margin-top: 24rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;

      .item {
        padding: 10rpx 24rpx;
        box-sizing: border-box;
        border-radius: 32rpx;

        .txt {
          display: block;
          font-size: 28rpx;
          color: #4e5769;
        }
      }

      .active {
        background: linear-gradient(118deg,
            rgba(74, 151, 231, 0.1),
            rgba(181, 122, 255, 0.1) 100%);

        .txt {
          background: linear-gradient(125deg, #4a97e7, #b57aff 100%);
          font-weight: 500;
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
      }
    }

    .scroll {
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      margin-top: 24rpx;

      .item {
        width: 686rpx;
        position: relative;
        margin: 0 auto;

        &::before {
          position: absolute;
          width: 100%;
          height: 100%;
          background-image: linear-gradient(123deg, #4a97e7, #b57aff 100%);
          border-radius: 24rpx;
          opacity: 0.1;
          left: 0;
          top: 0;
          z-index: -1;
          display: block;
          content: "";
        }

        .meet {
          width: 686rpx;
          z-index: 10;
          display: flex;
          flex-direction: column;
          // padding: 0 32rpx;
          box-sizing: border-box;
          margin-bottom: 24rpx;

          .IssignIn {
            width: 100%;
            height: 64rpx;
            padding: 0 32rpx;
            display: flex;
            box-sizing: border-box;
            justify-content: space-between;

            .siginStatus {
              background: linear-gradient(104deg, #4a97e7, #b57aff 100%);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
              font-size: 26rpx;
              font-family: PingFang SC, PingFang SC-400;
              font-weight: 400;
              display: flex;
              align-items: center;

              .siginStatus_icon {
                width: 32rpx;
                height: 32rpx;
                margin-right: 8rpx;
              }
            }

            .go {
              background: linear-gradient(103deg, #4a97e7, #b57aff 100%);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
              font-size: 24rpx;
              font-family: PingFang SC, PingFang SC-400;
              font-weight: 400;

              display: flex;
              align-items: center;

              .go_icon {
                width: 28rpx;
                height: 28rpx;
              }
            }
          }

          .title {
            font-size: 32rpx;
            margin: 40rpx 32rpx 0 32rpx;
            color: #1d2129;
            font-weight: 500;
          }

          .rules {
            margin: 16rpx 32rpx 0 32rpx;
            display: flex;
            flex-direction: row;
            align-items: center;

            &-icon {
              width: 32rpx;
              height: 32rpx;
            }

            &-info {
              flex: 1;
              flex-shrink: 0;
              min-width: 0;
              margin-left: 16rpx;
              font-size: 24rpx;
              color: #4e5769;
            }
          }

          .divide_line {
            width: 686rpx;
            height: 24rpx;
            margin: 16rpx 0;
            z-index: 16;
          }

          .meet-operating {
            display: flex;
            flex-direction: column;

            .operating-info {
              display: flex;
              flex-direction: row-reverse;
              padding: 0 32rpx;

              view {
                display: flex;
                align-items: center;

                .meet-title {
                  font-size: 26rpx;
                  font-family: PingFang SC, PingFang SC-400;
                  font-weight: 400;
                  color: #868d9c;
                  display: flex;
                  align-items: center;

                  .red_packet {
                    width: 26rpx;
                    height: 34rpx;
                    padding: 0 8rpx;
                  }
                }

                .price {
                  font-size: 26rpx;
                  font-family: PingFang SC, PingFang SC-400;
                  font-weight: 400;
                  color: #1d2129;
                }

                text {
                  padding: 0 8rpx;
                }
              }
            }

            .operating-btn {
              display: flex;
              align-items: center;
              flex-direction: row-reverse;
              margin-bottom: 16rpx;
              margin-top: 28rpx;
              padding: 0 32rpx;

              .btn1 {
                width: 160rpx;
                height: 64rpx;
                line-height: 64rpx;
                text-align: center;
                border: 1px solid #cccccc;
                border-radius: 36rpx;
                font-size: 28rpx;
                color: #222;
                margin-left: 20rpx;
              }

              .btn2 {
                width: 160rpx;
                height: 64rpx;
                line-height: 64rpx;
                text-align: center;
                border-radius: 36rpx;
                font-size: 28rpx;
                color: #fff;
                background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
              }

              .btn3 {
                width: 216rpx;
                height: 64rpx;
                border: 1rpx solid #cccccc;
                border-radius: 36rpx;

                font-size: 28rpx;
                font-family: PingFang SC, PingFang SC-400;
                font-weight: 400;
                color: #222222;
                text-align: center;
                line-height: 64rpx;
              }

              .btn4 {
                width: 216rpx;
                height: 64rpx;
                line-height: 64rpx;
                text-align: center;
                border-radius: 36rpx;
                font-size: 28rpx;
                color: #fff;
                background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
              }
            }
          }

          .lastPart {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin: 0 32rpx 24rpx 32rpx;

            &-money {
              display: inline-block;
              flex: 1;
              flex-shrink: 0;
              min-width: 0;

              .txt1 {
                font-size: 26rpx;
                color: #868d9c;
                line-height: 38rpx;
              }

              .txt2 {
                font-size: 26rpx;
                color: #1d2129;
                line-height: 38rpx;
              }
            }

            &-btns {
              display: flex;
              flex-direction: row;

              .btn1 {
                width: 160rpx;
                height: 64rpx;
                line-height: 64rpx;
                text-align: center;
                border: 1px solid #cccccc;
                border-radius: 36rpx;
                font-size: 28rpx;
                color: #222;
              }

              .btn2 {
                width: 160rpx;
                height: 64rpx;
                line-height: 64rpx;
                text-align: center;
                border-radius: 36rpx;
                font-size: 28rpx;
                color: #fff;
                background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
              }
            }
          }

          .other {
            width: 686rpx;
            position: relative;
            background-color: #fff;
            border-radius: 24rpx;
            display: flex;
            flex-direction: column;
            padding: 0 32rpx;

            &-headImgs {
              width: 164rpx;
              height: 96rpx;
              position: relative;

              .avatar1 {
                // 元素放在后面，以保证ios上可以盖住前面元素
                width: 88rpx;
                height: 88rpx;
                position: absolute;
                right: 0;
                left: 0;
                border: 4rpx solid #fff;
                border-radius: 44rpx;
                z-index: 9;
              }

              .avatar2 {
                width: 88rpx;
                height: 88rpx;
                border-radius: 44rpx;
                position: absolute;
                right: 0;
                top: 0;
                z-index: 8;
              }

              .payFlag {
                width: 64rpx;
                height: 32rpx;
                border: 1px solid #ffffff;
                border-radius: 18rpx;
                font-size: 20rpx;
                color: #fff;
                position: absolute;
                z-index: 10;
                left: 16rpx;
                bottom: 0;
              }

              .pay_aa {
                background: linear-gradient(116deg, #00bc6d 0%, #2acf8a 100%);
              }

              .pay_me {
                background: linear-gradient(114deg, #4a97e7, #b57aff 100%);
              }

              .pay_you {
                background: linear-gradient(114deg, #ff66c2, #ff7ccb 100%);
              }
            }
          }

          &-top {
            display: flex;
            flex-direction: row;
            margin-top: 32rpx;
            margin: 32rpx 32rpx 0 32rpx;

            .intro {
              display: flex;
              flex-direction: column;
              flex: 1;
              flex-shrink: 0;
              min-width: 0;
              margin-left: 24rpx;

              &-part1 {
                display: flex;
                flex-direction: row;
                align-items: center;

                .name {
                  max-width: 160rpx;
                  height: 36rpx;
                  overflow: hidden;
                  /*内容超出后隐藏*/
                  text-overflow: ellipsis;
                  /*超出内容显示为省略号*/
                  white-space: nowrap;
                  /*文本不进行换行*/
                }

                .txt1 {
                  font-size: 28rpx;
                  color: #1d2129;
                  font-weight: 500;
                  line-height: 44rpx;
                }

                .sex {
                  width: 32rpx;
                  height: 32rpx;
                  margin-left: 8rpx;
                }

                .other_icon {
                  width: 32rpx;
                  height: 32rpx;
                  margin-left: 8rpx;
                }

                .inivitation_flag {
                  width: 144rpx;
                  height: 36rpx;
                  margin-left: 16rpx;
                }
              }

              &-boInfo {
                font-size: 22rpx;
                color: #868d9c;
                line-height: 38rpx;
                margin-top: 2rpx;
              }
            }

            .left {
              flex: 1;
              flex-shrink: 0;
              min-width: 0;
              display: flex;
              flex-direction: row;

              &-head {
                // width: 88rpx;
                height: 96rpx;
                position: relative;

                .avatar {
                  width: 88rpx;
                  height: 88rpx;
                  border-radius: 44rpx;
                  z-index: 9;
                  position: absolute;
                  left: 0;
                  top: 0;
                  border: 2px solid #fff;
                }

                .avatar2 {
                  width: 88rpx;
                  height: 88rpx;
                  border-radius: 44rpx;
                  z-index: 8;
                  margin-left: 68rpx;
                }

                .space_avatar {
                  width: 88rpx;
                  height: 88rpx;
                  margin-right: 4rpx;
                }

                .pay_btn {
                  width: 64rpx;
                  height: 32rpx;
                  border: 1px solid #ffffff;
                  border-radius: 18rpx;
                  font-size: 20rpx;
                  color: #fff;
                  position: absolute;
                  z-index: 10;
                  bottom: 0;
                  left: 12rpx;
                  text-align: center;
                  line-height: 32rpx;
                }

                .pay_aa {
                  background: linear-gradient(116deg, #00bc6d 0%, #2acf8a 100%);
                }

                .pay_me {
                  background: linear-gradient(114deg, #4a97e7, #b57aff 100%);
                }

                .pay_you {
                  background: linear-gradient(114deg, #ff66c2, #ff7ccb 100%);
                }
              }
            }

            .status {
              font-size: 28rpx;
              background: linear-gradient(115deg, #4a97e7, #b57aff 100%);
              font-weight: 500;
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
            }
          }

          .status_other {
            font-size: 28rpx;
            font-weight: 500;
            color: #b4b7bf;
          }
        }
      }

      .other_invitation {
        &::before {
          position: absolute;
          width: 100%;
          height: 100%;
          border-radius: 24rpx;
          left: 0;
          top: 0;
          z-index: 8;
          display: block;
          content: "";
          opacity: unset;
          // background: none;
          background-color: #fff;
          background-image: none;
        }

        .meet {
          position: relative;
          z-index: 11;
        }
      }
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
            background: linear-gradient(106deg,
                rgba(74, 151, 231, 0.1),
                rgba(181, 122, 255, 0.1) 100%);
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

.sign-box {
  width: 100%;
  height: 680rpx;
  padding: 32rpx;
  display: flex;
  flex-direction: column;

  .title {
    width: 64rpx;
    height: 48rpx;
    font-size: 32rpx;
    font-family: PingFang SC, PingFang SC-500;
    font-weight: 500;
    color: #1d2129;
    line-height: 48rpx;
  }

  .content-box {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding-right: 64rpx;

    .image-text {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: 32rpx;

      .selfie {
        width: 242rpx;
        height: 300rpx;
      }

      .text {
        font-size: 32rpx;
        font-family: PingFang SC, PingFang SC-500;
        font-weight: 500;
        color: #1d2129;
        margin-top: 32rpx;
      }
    }

    .btn {
      width: 686rpx;
      height: 88rpx;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;
      display: flex;
      align-items: center;
      justify-content: space-around;

      font-size: 28rpx;
      font-family: PingFang SC, PingFang SC-500;
      font-weight: 500;
      color: #ffffff;
      margin-top: 32rpx;
      margin-bottom: 32rpx;
    }

    .agreement {
      height: 88rpx;
      font-size: 28rpx;
      font-family: PingFang SC, PingFang SC-400;
      font-weight: 400;
      text-align: LEFT;
      color: #868d9c;
    }
  }
}
</style>
