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
        color="#1D2129"
        :border="false"
        background-color="transparent"
        :title="pageTitle"
        :statusBar="true"
      ></uni-nav-bar>
      <scroll-view
        type="list"
        :scroll-y="true"
        class="scroll"
        @scrolltolower="loadMessageList"
        ref="scrollView"
      >
        <view
          class="scroll-mes"
          v-for="(item, ind) in sortedDataList"
          :key="'mes' + ind"
          @click="opMap(item)"
        >
          <text class="time">{{ item.create_time_text }}</text>
          <view :class="item.role_type == 1 ? 'own' : 'other'">
            <image
              @click="toUserDetail(item.role_type)"
              class="icon"
              :src="item.avatar"
            ></image>
            <view class="mesView">
              <text>{{ item.message }}</text>
            </view>
          </view>
        </view>
      </scroll-view>
      <view class="bottom_btns">
        <view @click="openMesModel" class="sendMes">
          <text>发送留言</text>
        </view>
        <view @click="launchInvitation" class="launch_btn">
          <text class="txt">发起邀约</text>
        </view>
      </view>
    </view>

    <uni-popup ref="messagePopup" type="bottom">
      <view class="messView">
        <text class="title">消息模版</text>
        <scroll-view :scroll-y="true" class="mes_scroll">
          <view
            @click="chooseMes(item)"
            class="item"
            v-for="(item, index) in mesList"
            :key="'mes' + index"
            :class="{ selected: item.id == checkMes.id }"
          >
            <view class="child">
              <text class="txt">{{ item.content }}</text>
            </view>
          </view>
          <!-- <image class="mask" src="/static/mes_bottom.png"></image> -->
        </scroll-view>
        <view @click="sendMes" class="send">
          <text>发送</text>
        </view>
      </view>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import { api, formatDate } from "@/common/request/index.ts";
import { getCurrentInstance, reactive, ref, computed} from "vue";
import { onLoad } from "@dcloudio/uni-app";
import { useStore } from "vuex";
const store = useStore();
const app = getCurrentInstance().appContext.app;
const { proxy } = getCurrentInstance();
const messagePopup = ref();
const pageTitle = ref("");
const mesList = ref([]);
const checkMes = reactive({
  id: null,
  content: null,
});
const userInfo = computed(() => store.state.user.userInfo);

const dataList = ref([]);
let pageNo = 1;
const loading = ref(false);
const finish = ref(false);

let otherUserId = null;
const otherUserInfo = reactive({});
const otherAvatar = ref(null);

onLoad(() => {
  const eventChannel = proxy.getOpenerEventChannel();
  eventChannel.on("acceptDataFromOpenerPage", (data: any) => {
    // 获取上个页面的传值
    if (data != null && data.id != null) {
      pageTitle.value = data.nickname;
      otherUserId = data.id;
      otherAvatar.value = data.avatar;
      // avatar
      api.post("/index/user_info", { id: otherUserId }).then((res: any) => {
        if (res.code == 1) {
          Object.assign(otherUserInfo, res.data);
        }
      });
    }
    getMessageList(true);
  });

  getMesList();
});

const opMap = (item) => {
  console.log(item);
  if (item.longitude_and_latitude != "") {
    const list = item.longitude_and_latitude.split(",");
    console.log(list);
    uni.openLocation({
      latitude: Number(list[1]),
      longitude: Number(list[0]),
    });
  }
};

const launchInvitation = () => {
  if (otherUserInfo.is_cert_realname != 1) {
    uni.showToast({
      icon: "none",
      title: "该用户还未实名认证",
    });
    return;
  }
  if (userInfo.value.is_cert_realname != 1) {
    uni.showModal({
      title: "提示",
      content: "请先完成实名认证",
      success: (ures) => {
        if (ures.confirm) {
          uni.navigateTo({
            url: `/pages/attestation/attestation?type=1`,
          });
        }
      },
    });
    return;
  }
  if (otherUserInfo.id == userInfo.value.id) {
    uni.showToast({
      icon: "none",
      title: "不可以给自己发起邀请",
    });
    return;
  }
  api.post("/invitation/check").then((res: any) => {
    if (res.code == 1 && res.data.can_invite == 1) {
      uni.navigateTo({
        url: `/pages/give_invitation/give_invitation`,
        success(res) {
          res.eventChannel.emit("acceptDataFromOpenerPage", {
            nickname: otherUserInfo.nickname,
            avatar: otherUserInfo.avatar_text,
            gender: otherUserInfo.gender,
            id: otherUserInfo.id,
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

const toUserDetail = (type) => {
  if (parseInt(type) == 1) {
    uni.navigateTo({
      url: `/pages/personal_details/personal_details?id=${userInfo.value.id}`,
    });
  } else {
    uni.navigateTo({
      url: `/pages/personal_details/personal_details?id=${otherUserId}`,
    });
  }
};
const getMesList = async () => {
  const res: any = await api.post("message/template");
  if (res.code == 1) {
    mesList.value = res.data;
  }
};

const loadMessageList = () => {
  pageNo++;
  getMessageList(false);
};

const getMessageList = async (refresh: boolean) => {
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
    const res: any = await api.post("message/chat_list", {
      page: pageNo,
      user_id: otherUserId,
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

const navBack = () => {
  uni.navigateBack();
};

const openMesModel = () => {
  messagePopup.value.open();
};

const sendMes = async () => {
  if (checkMes.id == null) {
    uni.showToast({
      icon: "none",
      title: "请先选择消息模版",
    });
    return;
  }
  const res: any = await api.post("/message/send", {
    to_user_id: otherUserId,
    message_id: checkMes.id,
  });
  if (res.code == 1) {
    dataList.value.push({
      create_time_text: formatDate(new Date()),
      role_type: 1,
      message: checkMes.content,
      avatar: userInfo.value.avatar_text,
    });
    messagePopup.value.close();
  }
};

const chooseMes = (item: any) => {
  if (checkMes.id != item.id) {
    Object.assign(checkMes, item);
  }
};

// 改成时间逆排序, by Richard
const sortedDataList = computed(() => {
  return dataList.value.slice().sort((a, b) => {
    // 假设 create_time_text 是可以比较的日期字符串，可以根据实际情况进行转换
//    return new Date(b.create_time_text).getTime() - new Date(a.create_time_text).getTime();
    return new Date(convertToIOSDateFormat(b.create_time_text)).getTime() - new Date(convertToIOSDateFormat(a.create_time_text)).getTime();

  });
});
// 新增日期格式转换函数, by Richard
const convertToIOSDateFormat = (dateString) => {
  // 将日期字符串转换为 iOS 支持的格式，例如 "yyyy-MM-ddTHH:mm:ss"
  return dateString.replace(' ', 'T');
};


</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  height: 100%;
  background-color: #f4f5f7;
  display: flex;
  flex-direction: column;
  position: relative;
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
    .scroll {
      flex: 1;
      flex-shrink: 0;
      width: 100%;
      min-height: 0;
      margin-top: 24rpx;
      margin-bottom: 200rpx;
      &-mes {
        display: flex;
        flex-direction: column;
        width: 100%;
        margin-bottom: 40rpx;
        .time {
          margin: 0 auto;
          font-size: 20rpx;
          color: #868d9c;
          line-height: 36rpx;
        }
        .own {
          margin-top: 24rpx;
          display: flex;
          flex-direction: row-reverse;
          align-items: flex-start;
          .icon {
            border-radius: 32rpx;
            width: 64rpx;
            height: 64rpx;
            margin-left: 24rpx;
            margin-right: 32rpx;
          }
          .mesView {
            padding: 18rpx 24rpx;
            background: linear-gradient(101deg, #4a97e7, #b57aff 100%);
            border-radius: 16rpx 0px 16rpx 16rpx;
            font-size: 28rpx;
            color: #fff;
            line-height: 44rpx;
            box-sizing: border-box;
            max-width: 510rpx;
          }
        }
        .other {
          margin-top: 24rpx;
          display: flex;
          flex-direction: row;
          .icon {
            width: 64rpx;
            height: 64rpx;
            border-radius: 32rpx;
            margin-right: 24rpx;
            margin-left: 32rpx;
          }
          .mesView {
            padding: 18rpx 24rpx;
            background: #fff;
            border-radius: 0px 16rpx 16rpx 16rpx;
            font-size: 28rpx;
            color: #1d2129;
            line-height: 44rpx;
            box-sizing: border-box;
            max-width: 510rpx;
          }
        }
      }
    }
    .bottom_btns {
      position: fixed;
      bottom: 80rpx;
      width: 100%;
      left: 0;
      z-index: 9;
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      .launch_btn {
        margin-left: 32rpx;
        width: 256rpx;
        height: 88rpx;
        background: linear-gradient(
          107deg,
          rgba(74, 151, 231, 0.2),
          rgba(181, 122, 255, 0.2) 100%
        );
        border-radius: 48rpx;
        box-shadow: 0px 16rpx 64rpx 0px rgba(129, 137, 244, 0.15);
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        .txt {
          display: block;
          font-size: 32rpx;
          font-weight: 500;
          background: linear-gradient(108deg, #4a97e7, #b57aff 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
      }
    }

    .sendMes {
      width: 256rpx;
      height: 88rpx;
      background: linear-gradient(107deg, #4a97e7, #b57aff 100%);
      border-radius: 48rpx;
      box-shadow: 0px 16rpx 64rpx 0px rgba(129, 137, 244, 0.15);
      font-size: 32rpx;
      color: #ffffff;
      line-height: 88rpx;
      text-align: center;
    }
  }
  .messView {
    width: 750rpx;
    background-color: #f7f8fa;
    border-radius: 32rpx 32rpx 0px 0px;
    display: flex;
    flex-direction: column;
    .title {
      margin-top: 40rpx;
      margin-left: 32rpx;
      font-size: 32rpx;
      font-weight: 500;
      color: #1d2129;
    }
    .mes_scroll {
      margin: 32rpx auto 0 auto;
      width: 686rpx;
      height: 592rpx;
      position: relative;
      z-index: 9;
      overflow-y: auto;
      .mask {
        width: 750rpx;
        height: 80rpx;
        position: absolute;
        bottom: 0;
        left: 0;
      }
      .item {
        margin-bottom: 20rpx;
        width: 686rpx;
        padding: 1px;
        border-radius: 24rpx;
        background: #fff;
        .child {
          background: #fff;
          padding: 24rpx 40rpx;
          box-sizing: border-box;
          border-radius: 24rpx;
          .txt {
            font-size: 28rpx;
            color: #1d2129;
            line-height: 44rpx;
          }
        }
      }
      .selected {
        background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
        width: 686rpx;
        padding: 1px;
        box-sizing: border-box;
        .child {
          .txt {
            color: transparent;
            background: linear-gradient(93deg, #4a97e7, #b57aff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          }
        }
      }
    }
    .send {
      margin: 16rpx auto 30rpx auto;
      width: 686rpx;
      height: 88rpx;
      line-height: 88rpx;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;
      font-size: 28rpx;
      color: #fff;
      font-weight: 500;
      text-align: center;
    }
  }
}
</style>
