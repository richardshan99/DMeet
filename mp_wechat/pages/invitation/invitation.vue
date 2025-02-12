<template>
  <view class="content">
    <image class="content-top" :src="app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "></image>

    <view class="content-base">
      <uni-nav-bar :border="false" title="邀约" background-color="transparent" :status-bar="true"></uni-nav-bar>
      <view class="content-notice">
        <image class="icon" src="/static/index/notice.png"></image>
        <swiper :interval="2000" :circular="true" class="swiper_notice" :autoplay="true" :vertical="true">
          <swiper-item v-for="(item, ind) in noticeList" :key="'m_notice' + ind" :style="{
            display: 'flex',
            flexDirection: 'row',
            alignItems: 'center',
          }">
            <text class="info">{{ item }}</text>
          </swiper-item>
        </swiper>
      </view>
      <view v-if="firstMeetShare.content != null" class="content-first">
        <view class="child">
          <view class="child-left">
            <text class="info">{{ firstMeetShare.content }}</text>
            <view class="btns">
              <view class="btns-head">
                <image :src="firstMeetShare.invitee_image_text" class="icon1"></image>
                <image :src="firstMeetShare.inviter_image_text" class="icon2"></image>
              </view>
              <view @click="openMoreShare" class="btns-share">
                <text class="txt">更多分享</text>
                <image class="arrow_left" src="/static/invitation/arrow_left.png"></image>
              </view>
            </view>
          </view>
          <image class="child-right" v-if="firstMeetShare.image_text != ''" :src="firstMeetShare.image_text"></image>
        </view>
      </view>
      <scroll-view class="content-scroll" :scroll-y="true" type="list">
        <!-- invitations -->
        <view class="item" v-for="(item, index) in invitations" :key="'invitation' + index"
          :class="{ other_invitation: item.invite_type == 1 }">
          <!-- other_invitation -->
          <view :class="['meet', item.invite_type == 1 ? '' : 'meet-shop-type2']">
            <view class="meet-top">
              <view class="left">
                <view class="left-head">
                  <image @click="toUserDetail(item.inviteuser.id)" mode="aspectFill" v-if="item.invite_type == 1"
                    class="avatar2" :src="item.inviteuser.avatar_text"></image>
                  <view v-else class="space_avatar"></view>
                  <image @click="toUserDetail(item.user.id)" mode="aspectFill" class="avatar"
                    :src="item.user.avatar_text"></image>
                  <view v-if="item.shop_type == 1" class="pay_btn pay_me" :class="{
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
                    <text class="txt1">{{ item.inviteuser.nickname }}</text>
                    <image v-if="item.inviteuser.gender == 1" src="/static/sex_man.png" class="sex"></image>
                    <image v-else src="/static/sex_woman.png" class="sex"></image>
                    <image v-if="item.inviteuser.is_member == 1" src="/static/vip_icon.png" class="other_icon"></image>
                    <image v-if="item.inviteuser.is_cert_realname == 1" src="/static/person/confirm_name.png"
                      class="other_icon"></image>
                    <image v-if="item.inviteuser.is_cert_education == 1" src="/static/person/edu.png"
                      class="other_icon"></image>
                    <image class="meet-image" src="/static/person/meet.png" v-if="item.invite_type == 2"
                      mode="scaleToFill" />
                    <!-- <image v-if="item.invite_type == 2" src="/static/invitation/send_invitation.png" class="inivitation_flag"></image> -->
                  </view>
                  <view class="intro-part1" v-if="item.invite_type == 2">
                    <text class="txt1">{{ item.user.nickname }}</text>
                    <image v-if="item.user.gender == 1" src="/static/sex_man.png" class="sex"></image>
                    <image v-else src="/static/sex_woman.png" class="sex"></image>
                    <image v-if="item.user.is_member == 1" src="/static/vip_icon.png" class="other_icon"></image>
                    <image v-if="item.user.is_cert_realname == 1" src="/static/person/confirm_name.png"
                      class="other_icon"></image>
                    <image v-if="item.user.is_cert_education == 1" src="/static/person/edu.png" class="other_icon">
                    </image>
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
              <text class="status">{{ item.status_text }}</text>
            </view>
            <view @click="
              toShopDetail(
                item.change.id != '' ? item.change.shop.id : item.shop.id,
                item.package.name
              )
              " class="title">[{{ item.area_name }}]
              {{
                item.change.id != "" ? item.change.shop.name : item.shop.name
              }}
              <image :style="{ width: '14rpx', height: '20rpx', marginLeft: '8rpx' }"
                src="/static/invitation/arrow_right.png"></image>
            </view>
            <view class="rules">
              <image src="/static/invitation/location.png" class="rules-icon"></image>
              <text class="rules-info" v-if="item.change.id == ''">{{
                item.address
              }}</text>
              <text class="rules-info" v-else-if="item.change.id != '' && item.change.status == 1">{{
                item.change.address }}</text>
              <text class="rules-info" v-else>{{ item.address }}</text>
            </view>
            <view class="rules">
              <image src="/static/invitation/datetime.png" class="rules-icon"></image>
              <text class="rules-info" v-if="item.change.id == ''">{{
                item.meet_time_new_text
              }}</text>
              <text class="rules-info" v-else-if="item.change.id != '' && item.change.status == 1">{{
                item.change.meet_time_new_text }}</text>
              <text class="rules-info" v-else>{{
                item.meet_time_new_text
              }}</text>
            </view>
            <view class="rules" v-if="item.shop_type == 1">
              <image src="/static/invitation/package.png" class="rules-icon"></image>
              <text class="rules-info">{{ item.package.name }}(￥{{
                item.package.package2_price == null
                  ? item.package.package1_price
                  : item.package.package2_price
              }})</text>
            </view>

            <view class="rules" v-if="
              item.shop_type == 2 &&
              (item.change.id != null || item.change.id != '') &&
              item.change.status == 1
            ">
              <image src="/static/invitation/tips.png" class="rules-icon"></image>
              <text class="rules-info rules-change">
                {{
                  item.invite_type == 1
                    ? "对方已修改见面信息，请选择同意或拒绝"
                    : "你已修改见面信息，等待对方选择"
                }}
              </text>
            </view>

            <image src="/static/invitation/invitation_line.png" class="divide_line"></image>
            <view v-if="item.shop_type == 1" class="lastPart">
              <view class="lastPart-money">
                <text class="txt1">实付金额 </text>
                <text class="txt2">¥{{ item.price }}</text>
              </view>
              <view v-if="item.invite_type == 1" class="lastPart-btns">
                <view v-if="item.status == 1" @click="revokeInvitation(item.id)" class="btn1"><text>撤回邀约</text></view>
                <view v-if="item.status == 2" @click="cancelInvitation(item.id)" class="btn1"><text>取消邀约</text></view>
                <view v-if="item.status == 2" @click="openQrcode(item)" class="btn2" :style="{ marginLeft: '20rpx' }">
                  <text>核销码</text>
                </view>
              </view>
              <view v-if="item.invite_type == 2" class="lastPart-btns">
                <view v-if="item.status == 1" @click="refuseInvitation(item.id)" class="btn1"><text>拒绝</text></view>
                <view v-if="item.status == 1" @click="agree(item)" class="btn2" :style="{ marginLeft: '20rpx' }">
                  <text>同意</text>
                </view>
                <view v-if="item.status == 2" @click="cancelInvitation(item.id)" class="btn1"><text>取消邀约</text></view>
                <view v-if="item.status == 2" class="btn2" @click="openQrcode(item)" :style="{ marginLeft: '20rpx' }">
                  <text>核销码</text>
                </view>
              </view>
            </view>
            <view v-else class="meet-operating">
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
                    <template v-if="!(item.change.id != '' && item.change.status == 1)">
                      <view @click="agree(item)" class="btn2" :style="{ marginLeft: '20rpx' }"><text>同意</text></view>
                      <view @click="refuseInvitation(item.id)" class="btn1"><text>拒绝</text></view>
                    </template>
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
                      <text>同意</text>
                    </view>
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

                  <template v-if="item.invite_type == 1">
                    <!-- 邀请人 未签到  -->
                    <view @click="cancelInvitation(item.id)" class="btn1" v-if="
                      item.invite_type == 1 && item.inviter_is_verify == -1
                    "><text>取消邀约</text></view>
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
        <view @click="toHisory" class="historyBtn">
          <text>查看历史记录</text>
        </view>
        <view :style="{ width: '100%', height: '90px' }"></view>
      </scroll-view>
    </view>
    <uni-popup @change="popPupChange" ref="QrcodePopup" type="bottom">
      <uni-verify-code :codeInfo="choiceInfo"></uni-verify-code>
    </uni-popup>
    <uni-popup @change="popPupChange" ref="rejectPopup" type="bottom">
      <uni-revoke @confirm="rejectInvitation"></uni-revoke>
    </uni-popup>

    <!-- 签到 -->
    <uni-popup :style="{ zIndex: '99999' }" @change="popPupChange" ref="signInPopup" background-color="#fff"
      type="bottom" borderRadius="24rpx 24rpx 0rpx 0rpx;">
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

    <uni-popup @change="popPupChange" :style="{ zIndex: '99999' }" ref="payPopup" type="bottom">
      <view class="content-payView">
        <text class="title">同意邀约</text>

        <template v-if="chooseItme.shop_type == 1">
          <view class="acitivity_price">
            <text class="txt1">邀约费用</text>
            <text class="txt2">￥{{ selectInvitation.price }}</text>
          </view>
        </template>
        <template v-else>
          <view class="acitivity_price">
            <text class="txt1">履约保证金</text>
            <text class="txt2">￥{{ chooseItme.price }}</text>
          </view>
          <view class="acitivity_price">
            <text class="txt1">联系方式</text>
            <!-- {{ chooseItme }} -->
            <text class="txt3">{{
              chooseItme?.inviteuser?.contact_mobile != ""
                ? chooseItme?.inviteuser?.contact_mobile
                : chooseItme?.inviteuser?.mobile
            }}</text>
          </view>
        </template>

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
        <!-- <view class="warn">
					<text class="warn-txt">查看退款规则</text>
					<image src="/static/notices/arrow_right.png" class="warn-arrow"></image>
				</view> -->
      </view>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import * as qiniuUploader from "@/common/upload/qiniuUploader.ts";
import { api } from "@/common/request/index.ts";
import { onLoad, onShow } from "@dcloudio/uni-app";
import { getCurrentInstance, ref, computed, reactive } from "vue";
import { useStore } from "vuex";

const store = useStore();

const noticeList = ref([]);
const QrcodePopup = ref();
const invitations = ref([]);
const rejectPopup = ref();
const payPopup = ref();
const signInPopup = ref(); // 签到弹窗
const isShareValue = ref(false); // 是否同步分享见面动态
const chooseItme = ref({}); // 选中的 订单
let rejectId = null;
const token = computed(() => store.state.user.token);
const userInfo = computed(() => store.state.user.userInfo);
const invationCount = computed(() => store.state.user.invationCount);
const app = getCurrentInstance().appContext.app;
const curPages = getCurrentPages()[0]; // 获取当前页面实例
const firstMeetShare = reactive({
  content: null,
  image_text: null,
  inviter_image_text: null,
  invitee_image_text: null,
});
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

const position = ref("");

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

onShow(() => {
  if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
    curPages.getTabBar().setData({
      ["list[1].invationCount"]: invationCount.value,
      ["list[4].count"]: userInfo.value.new_message_num,
      selected: 1, // 表示当前菜单的索引，该值在不同的页面表示不同
    });
  }
  if (token.value != null) {
    api.post("/common/meet_announce").then((res: any) => {
      if (res.code == 1) {
        noticeList.value = res.data.map((item) => item.content);
      }
    });
    getInvitations();
    api.post("/common/meet_share").then((vres: any) => {
      if (vres.code == 1 && vres.data.content != null) {
        Object.assign(firstMeetShare, vres.data);
      }
    });
  }
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

const shareWidth = (id: string) => {
  uni.navigateTo({
    url: `/pages/publish_trends/publish_trends?invitationId=${id}`,
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
        signInPopup.value.close();
        getInvitations();
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

const changeShare = (value) => {
  console.log(value);
  isShareValue.value = value.detail.value.length > 0 ? true : false;
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

// 签到
const signIn = (item) => {
  uni.showLoading({
    title: "获取定位中",
    mask: true,
  });
  uni.getLocation({
    type: "wgs84",
    isHighAccuracy: true,
    highAccuracyExpireTime: 6000,
    success: (info) => {
      console.log(info);
      const latitude = info.latitude;
      const longitude = info.longitude;
      position.value = [longitude, latitude].toString();

      chooseItme.value = item;
      uni.hideLoading();
      signInPopup.value.open();
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

// 修改见面信息
const updataMeetInfo = (userDetail) => {
  console.log(userDetail, "userDetail--");

  api.post("/invitation/check").then((res: any) => {
    if (res.code == 1 && res.data.can_invite == 1) {
      uni.navigateTo({
        url: `/pages/give_invitation_updata/give_invitation_updata?shop_id=${userDetail.shop.id}`,
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

const openMoreShare = () => {
  uni.navigateTo({
    url: "/pages/meeting_share/meeting_share",
  });
};

const toUserDetail = (id: string) => {
  uni.navigateTo({
    url: `/pages/personal_details/personal_details?id=${id}`,
  });
};

const toHisory = () => {
  if (token.value == null) {
    uni.showToast({
      icon: "none",
      title: "请先登录",
    });
    return;
  }
  uni.navigateTo({
    url: "/pages/history_records/history_records",
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

const toggleBottomBar = (con: boolean) => {
  if (typeof curPages.getTabBar === "function" && curPages.getTabBar()) {
    curPages.getTabBar().setData({
      showTabbar: con, // 显示tabbar
    });
  }
};

const getInvitations = () => {
  api.post("/invite/todo_list").then((res: any) => {
    if (res.code == 1) {
      invitations.value = res.data;
    }
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

// 同意操作
const agree = (item: any) => {
  console.log(item);

  chooseItme.value = item;
  selectInvitation.id = item.id;
  selectInvitation.price = item.price;
  selectInvitation.invite_type = item.invite_type;
  selectInvitation.pay_mode = item.pay_mode;
  // payType.value = 2; // 1 是微信支付 2 是余额支付
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

const popPupChange = (data: any) => {
  if (!data.show) {
    toggleBottomBar(true);
  } else {
    toggleBottomBar(false);
  }
};

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
.content {
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
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  &-notice {
    margin-top: 16rpx;
    width: 686rpx;
    height: 70rpx;
    background: linear-gradient(95deg,
        rgba(74, 151, 231, 0.12),
        rgba(181, 122, 255, 0.12) 100%);
    border-radius: 32rpx;
    display: flex;
    flex-direction: row;
    align-items: center;

    .icon {
      margin-left: 24rpx;
      width: 28rpx;
      height: 30rpx;
    }

    .info {
      margin-left: 12rpx;
      font-size: 26rpx;
      color: #1d2129;
    }

    .swiper_notice {
      flex: 1;
      flex-shrink: 0;
      min-width: 0;
      height: 70rpx;
    }
  }

  &-first {
    margin-top: 24rpx;
    width: 686rpx;
    border-radius: 24rpx;
    box-sizing: border-box;
    position: relative;

    &::before {
      content: "";
      display: block;
      width: 100%;
      height: 100%;
      border-radius: 24rpx;
      position: absolute;
      z-index: 9;
      background: linear-gradient(107deg, #ff66c2, #ff7ccb 100%);
      opacity: 0.16;
    }

    .child {
      position: relative;
      margin: 32rpx;
      z-index: 10;
      display: flex;
      flex-direction: row;

      &-left {
        flex: 1;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        min-width: 0;

        .info {
          font-size: 28rpx;
          color: #1d2129;
          line-height: 44rpx;
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .btns {
          margin-top: 30rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: space-between;

          &-head {
            display: flex;
            flex-direction: row;

            .icon1 {
              width: 48rpx;
              height: 48rpx;
              border-radius: 24rpx;
              border: 1px solid #ffffff;
              z-index: 9;
            }

            .icon2 {
              width: 48rpx;
              height: 48rpx;
              border-radius: 24rpx;
              border: 1px solid #ffffff;
              z-index: 10;
              margin-left: -12rpx;
            }
          }

          &-share {
            width: 132rpx;
            height: 44rpx;
            background: linear-gradient(106deg, #ff66c2, #ff7ccb 100%);
            border-radius: 24rpx;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;

            .txt {
              font-size: 20rpx;
              color: #fff;
              font-weight: 500;
            }

            .arrow_left {
              width: 8rpx;
              height: 12rpx;
              margin-left: 8rpx;
            }
          }
        }
      }

      &-right {
        margin-left: 40rpx;
        width: 176rpx;
        height: 176rpx;
        border-radius: 12rpx;
      }
    }
  }

  &-scroll {
    flex: 1;
    flex-shrink: 0;
    min-height: 0;
    margin-top: 40rpx;

    .historyBtn {
      margin: 40rpx auto 0 auto;
      width: 686rpx;
      height: 88rpx;
      background-color: #fff;
      border-radius: 44rpx;
      text-align: center;
      line-height: 88rpx;
      font-size: 28rpx;
      color: #1d2129;
      font-weight: 500;
    }

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

      .rules-change {
        font-size: 26rpx;
        font-family: PingFang SC, PingFang SC-400;
        font-weight: 400;
        line-height: 38rpx;
        color: #4a97e7;
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

    .item {
      width: 686rpx;
      position: relative;
      margin: 0 auto 24rpx auto;

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

      .meet-shop-type2 {
        background: linear-gradient(124deg, #e6eef8, #f0ebfa 100%);
        border-radius: 24rpx;
      }

      .meet {
        width: 686rpx;
        z-index: 10;
        display: flex;
        flex-direction: column;
        // padding: 0 32rpx;
        box-sizing: border-box;

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

        .meet-image {
          width: 144rpx;
          height: 36rpx;
          margin-left: 16rpx;
        }

        &-top {
          display: flex;
          flex-direction: row;
          margin-top: 32rpx;
          margin: 32rpx 32rpx 0 32rpx;

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

      .txt3 {
        font-size: 28rpx;
        font-family: PingFang SC, PingFang SC-400;
        font-weight: 400;
        color: #1d2129;
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
