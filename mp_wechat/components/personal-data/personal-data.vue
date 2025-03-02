<template>
  <view class="main">
    <view class="main-area">
      <view class="userInfo">
        <view class="userInfo-left">
          <view
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'center',
            }"
          >
            <text class="username">{{ userDetail.nickname }}</text>
            <image
              v-if="userDetail.gender == 1"
              src="/static/sex_man.png"
              class="sex"
            ></image>
            <image
              v-if="userDetail.gender == 2"
              src="/static/sex_woman.png"
              class="sex"
            ></image>
            <image
              v-if="userDetail.is_member == 1"
              src="/static/vip_icon.png"
              class="vip_tag"
              :style="{ marginLeft: '12rpx' }"
            ></image>
            <image
              v-if="userDetail.is_cert_realname == 1"
              src="/static/person/confirm_name.png"
              class="other_icon"
            >
            </image>
            <image v-if="userDetail.is_cert_education == 1"
              src="/static/person/edu.png"
              class="other_icon"></image>
          </view>
          <view class="activeZone">
          <view class="desc_info">
            {{ userDetail.birth_year }}年 · {{ userDetail.height }}cm/{{ userDetail.weight }}kg 
          </view>
          <view class="location_box" v-if="userDetail.distance != ''">
          <image class="location_icon" src="/static/location.png" mode="scaleToFill"/>
          距你{{ userDetail.distance }}km
        </view>
        </view>
        </view>
        <view @click="toggleFocus"
          v-if="userDetail.is_follow == -1"
          class="userInfo-focus">
          <image src="/static/personal_details/focus_add.png" class="add"></image>
          <text class="txt">关注</text>
        </view>
      </view>

      <view class="activeZone">
        <view class="activeZone-left">
          <view class="activeZone-lable">
            <text class="lable-text">活跃区域</text>
          </view>
          <view class="active_point_text">
             {{ userDetail.active_point_text }}
          </view>
        </view>
   
      </view>

      <view class="infos">
        <view class="infos-item">
          <image
            class="icon"
            src="/static/personal_details/constellation.png"
          ></image>
          <text class="txt">现居{{ userDetail.area }}</text>
        </view>

        <view class="infos-item">
          <image
            class="icon"
            src="/static/personal_details/educational.png"
          ></image>
          <text class="txt"
            >{{ userDetail.school }} ·
            {{ userDetail.education_type_text }}</text
          >
        </view>

        <view class="infos-item">
          <image
            class="icon"
            src="/static/personal_details/constellation.png"
          ></image>
          <text class="txt">{{ userDetail.constellation_text }}</text>
        </view>

        <view class="infos-item">
          <image
            class="icon"
            src="/static/personal_details/home_location.png"
          ></image>
          <text class="txt">{{ userDetail.hometown }}</text>
        </view>
        <view class="infos-item">
          <image
            class="icon"
            src="/static/personal_details/advertisement.png"
          ></image>
          <text class="txt">{{ userDetail.work_type_text }}</text>
        </view>
        <view class="infos-item">
          <image class="icon" src="/static/personal_details/income.png"></image>
          <text class="txt">{{ userDetail.salary }}</text>
        </view>
        <view
          class="infos-item"
          v-for="(item, index) in extraList"
          :key="'extra' + index"
        >
          <text class="txt">{{ item.name }}:{{ item.formatter_value }}</text>
        </view>
      </view>
    </view>
    <view
      v-if="userDetail.label != null && userDetail.label.length > 0"
      class="main-area"
      :style="{ marginTop: '24rpx' }"
    >
      <view
        :style="{ display: 'flex', flexDirection: 'row', alignItems: 'center' }"
      >
        <image
          class="title_icon"
          src="/static/personal_details/my_tag.png"
        ></image>
        <text class="title_txt">我的标签</text>
      </view>
      <view class="tags">
        <view class="tags-cell" v-for="(item, index) in tagsList" :key="index">
          <view class="tags-lable"> {{ item.name }}</view>
          <view class="tags-box">
            <view
              class="tags-item"
              v-for="(item2, ind) in item.items"
              :key="'tag' + ind"
            >
              {{ item2.name }}
            </view>
          </view>
        </view>
      </view>
    </view>

    <view
      v-if="userDetail.intro != null && userDetail.intro.length > 0"
      class="main-area"
      :style="{ marginTop: '24rpx' }"
    >
      <view :style="{ display: 'flex', flexDirection: 'row', alignItems: 'center' }">
        <image class="title_icon" src="/static/personal_details/about_me.png"></image>
        <text class="title_txt">关于我</text>
      </view>
      <text class="content">{{ userDetail.intro}}</text>
    </view>

    <view
      v-if="userDetail.myExpect != null && userDetail.myExpect.length > 0"
      class="main-area"
      :style="{ marginTop: '24rpx' }"
    >
      <view :style="{ display: 'flex', flexDirection: 'row', alignItems: 'center' }">
        <image class="title_icon" src="/static/personal_details/about_me.png"></image>
        <text class="title_txt">对TA的要求/期望</text>
      </view>
      <text class="content">{{ userDetail.myExpect}}</text>
    </view>

    <view
      class="main-area"
      :style="{ marginTop: '24rpx', marginBottom: '280rpx' }"
    >
      <view
        :style="{ display: 'flex', flexDirection: 'row', alignItems: 'center' }"
      >
        <image
          class="title_icon"
          src="/static/personal_details/my_authentication.png"
        ></image>
        <text class="title_txt">我的认证</text>
      </view>
      <view
        :style="{
          marginTop: '24rpx',
          display: 'flex',
          flexDirection: 'row',
          justifyContent: 'space-between',
        }"
      >
        <view class="auth_view">
          <view :style="{ flex: 1, display: 'flex', flexDirection: 'column' }">
            <!-- <text class="txt1">{{userDetail.gender == 1?'男':'女'}} · {{userDetail.birth_year}}年出生</text> -->
            <text v-if="userDetail.is_cert_realname == 1" class="txt2"
              >已实名认证</text
            >
            <text v-else class="non_txt">未实名认证</text>
          </view>
          <image
            class="icon"
            src="/static/personal_details/real_name.png"
          ></image>
        </view>
        <view class="auth_view">
          <view :style="{ flex: 1, display: 'flex', flexDirection: 'column' }">
            <text class="txt1"
              >{{ userDetail.school }} ·
              {{ userDetail.education_type_text }}</text
            >
            <text v-if="userDetail.is_cert_education == 1" class="txt3"
              >已学历认证</text
            >
            <text v-else class="non_txt">未学历认证</text>
          </view>
          <image
            class="icon"
            src="/static/personal_details/edu_back.png"
          ></image>
        </view>
      </view>
    </view>
    <view class="bottom_btns">
      <view @click="toLeaveMes" class="btn1">
        <image
          class="btn1-icon"
          src="/static/personal_details/leave_mes.png"
        ></image>
        <text class="btn1-txt">给TA留言</text>
      </view>
      <view @click="launchInvitation" class="btn2">
        <image
          class="btn1-icon"
          src="/static/personal_details/invitation.png"
        ></image>
        <text class="btn1-txt">发起邀约</text>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { onMounted, ref, defineProps, watch } from "vue";
import { useStore } from "vuex";
import { computed } from "vue";
const props = defineProps(["userDetail"]);
const store = useStore();
const userInfo = computed(() => store.state.user.userInfo);
// const props = withDefaults(
//   defineProps<{
//     userDetail: any;
//   }>(),
//   {
//     userDetail: () => {},
//   }
// );

watch(props.userDetail, (newVal) => {
  console.log(newVal, "userDetail---");
  const obj = {};
  labelList.value.forEach((element) => {
    obj[element.id] = element.name;
  });

  const lable = props.userDetail.label;

  const list = [];
  if (lable) {
    lable.map((i) => {
      labelList.value.map((j) => {
        const value = j.childlist.filter((item) => item.id == i.id);
        if (value.length != 0) {
          i.pid = j.id;
          list.push(i);
        }
      });
    });

    const groupById = groupBy(list, "pid").map((i) => {
      i.name = obj[i.id];
      return i;
    });
    console.log(groupById);
    tagsList.value = groupById;
  }
});

const labelList = ref([]);

const tagsList = ref([]);

const groupBy = (array, key) => {
  return array.reduce((result, currentItem) => {
    // 使用id作为key来分组
    const group = result.find((item) => item.id === currentItem[key]);
    if (group) {
      group.items.push(currentItem);
    } else {
      result.push({
        id: currentItem[key],
        items: [currentItem],
      });
    }
    return result;
  }, []);
};

onMounted(async () => {
  console.log(props.userDetail.label, "props--");

  const res = await api.post("user/label_list");
  if (res.code == 1) {
    const obj = {};
    labelList.value = res.data;
    labelList.value.forEach((element) => {
      obj[element.id] = element.name;
    });

    const lable = props.userDetail.label;

    const list = [];
    if (lable) {
      lable.map((i) => {
        labelList.value.map((j) => {
          const value = j.childlist.filter((item) => item.id == i.id);
          if (value.length != 0) {
            i.pid = j.id;
            list.push(i);
          }
        });
      });

      const groupById = groupBy(list, "pid").map((i) => {
        i.name = obj[i.id];
        return i;
      });
      console.log(groupById);
      tagsList.value = groupById;
    }
  }
});

const extraList = computed(() => {
  if (props.userDetail.extra != null && props.userDetail.extra.length > 0) {
    return props.userDetail.extra.filter(
      (item) => item.formatter_value != null && item.formatter_value.length > 0
    );
  } else {
    return [];
  }
});

const toggleFocus = (userId) => {
  api
    .post("index/follow", {
      follow_id: props.userDetail.id,
    })
    .then((res: any) => {
      if (res.code == 1) {
        props.userDetail.is_follow = 1;
        uni.showToast({
          icon: "none",
          title: "有新关注",
        });
      }
    });
};

const toLeaveMes = () => {
  if (props.userDetail.id == userInfo.value.id) {
    uni.showToast({
      icon: "none",
      title: "不可以给自己留言",
    });
    return;
  }
  uni.navigateTo({
    url: `/pages/leave_message/leave_message`,
    success(res) {
      res.eventChannel.emit("acceptDataFromOpenerPage", {
        nickname: props.userDetail.nickname,
        avatar: props.userDetail.avatar_text,
        id: props.userDetail.id,
      });
    },
  });
};
const launchInvitation = () => {
  if (props.userDetail.is_cert_realname != 1) {
    uni.showModal({
      title: "提示",
      content: "该用户还未实名认证，是否给TA留言",
      success: (ures) => {
        if (ures.confirm) {
          toLeaveMes();
        }
      },
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
  if (props.userDetail.id == userInfo.value.id) {
    uni.showToast({
      icon: "none",
      title: "不可以给自己发起邀请",
    });
    return;
  }
  api.post("/invitation/check").then((res: any) => {
    if (res.code == 1 && res.data.can_invite == 1) {
      console.log(props.userDetail);

      uni.navigateTo({
        url: `/pages/give_invitation/give_invitation`,
        success(res) {
          res.eventChannel.emit("acceptDataFromOpenerPage", {
            nickname: props.userDetail.nickname,
            avatar: props.userDetail.avatar_text,
            gender: props.userDetail.gender,
            id: props.userDetail.id,
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
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  background-color: #f7f8fa;
  display: flex;
  flex-direction: column;
  align-items: center;

  &-area {
    margin-top: 16rpx;
    width: 686rpx;
    background-color: #fff;
    border-radius: 24rpx;
    padding: 32rpx;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    align-items: stretch;

    .userInfo {
      display: flex;
      flex-direction: row;

      &-left {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;

        .username {
          font-size: 40rpx;
          color: #1d2129;
          line-height: 56rpx;
          font-weight: 600;
        }

        .sex {
          margin-left: 22rpx;
          width: 40rpx;
          height: 40rpx;
        }

        .vip_tag {
          width: 40rpx;
          height: 40rpx;
        }

        .other_icon {
          width: 40rpx;
          height: 40rpx;
          margin-left: 12rpx;
        }

        .desc_info {
          margin-top: 8rpx;
          font-size: 26rpx;
          color: #868d9c;
          line-height: 38rpx;
          display: flex;
          align-items: center;
        }
      }

      &-focus {
        width: 128rpx;
        height: 56rpx;
        background: linear-gradient(
          111deg,
          rgba(74, 151, 231, 0.1),
          rgba(181, 122, 255, 0.1) 100%
        );
        border-radius: 96rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;

        .add {
          width: 28rpx;
          height: 28rpx;
        }

        .txt {
          display: block;
          font-size: 24rpx;
          font-weight: 500;
          background: linear-gradient(121deg, #4a97e7, #b57aff 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
      }
    }

    .activeZone {
      margin-top: 18rpx;
      display: flex;
      align-items: center;
      justify-content: space-between;

      .activeZone-left {
        display: flex;
        align-items: center;

        .activeZone-lable {
          width: 104rpx;
          height: 40rpx;
          // background: linear-gradient(108deg, #4a97e7, #b57aff 100%);
          border-radius: 96rpx;

          background: #eef4fd;
          color: #6498ec;
          font-size: 20rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          display: flex;
          align-items: center;
          justify-content: space-around;

          .lable-text {
            background: linear-gradient(108deg, #4a97e7, #b57aff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          }
        }

        .active_point_text {
          padding-left: 8rpx;
          background: linear-gradient(100deg, #4a97e7, #b57aff 100%);
          -webkit-background-clip: text;
          background-clip: text;
          color: transparent;
          font-size: 24rpx;
          font-family: PingFang SC, PingFang SC-500;
          font-weight: 500;
          text-align: LEFT;

          max-width: 480rpx;
          overflow: hidden;
          /*内容超出后隐藏*/
          text-overflow: ellipsis;
          /*超出内容显示为省略号*/
          white-space: nowrap;
          /*文本不进行换行*/
        }
      }

      .location_box {
        display: flex;
        align-items: center;
        opacity: 0.7;
        background: linear-gradient(106deg, #4a97e7, #b57aff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 24rpx;
        font-family: PingFang SC, PingFang SC-500;
        font-weight: 500;
        margin-left: 12rpx;

        .location_icon {
          width: 24rpx;
          height: 24rpx;
          padding-right: 4rpx;
        }
      }
    }

    .infos {
      margin-top: 32rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      flex-wrap: wrap;

      &-item {
        border: 1px solid #e8eaef;
        padding: 12rpx 24rpx;
        box-sizing: border-box;
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-right: 20rpx;
        margin-bottom: 20rpx;
        border-radius: 36rpx;

        .icon {
          width: 28rpx;
          height: 30rpx;
        }

        .txt {
          font-size: 26rpx;
          color: #1d2129;
          margin-left: 12rpx;
        }
      }
    }

    .title_icon {
      width: 36rpx;
      height: 36rpx;
    }

    .title_txt {
      font-size: 32rpx;
      font-weight: 500;
      color: #1d2129;
      margin-left: 16rpx;
    }

    .content {
      margin-top: 24rpx;
      font-size: 30rpx;
      color: #1d2129;
      line-height: 48rpx;
      word-wrap: break-word;
      /* 使得长单词或数字可以换行 */
      overflow-wrap: break-word;
      /* 确保兼容性 */
    }

    .tags {
      display: flex;
      flex-direction: row;
      align-items: center;
      flex-wrap: wrap;
      margin-top: 24rpx;

      .tags-cell {
        width: 100%;
        display: flex;
        margin-bottom: 24rpx;

        // align-items: center;
        .tags-lable {
          // width: 100rpx;
          font-size: 26rpx;
          font-family: PingFang SC, PingFang SC-500;
          font-weight: 500;
          color: #1d2129;
          padding-top: 12rpx;
          margin-right: 16rpx;
        }

        .tags-box {
          display: flex;
          flex-wrap: wrap;
          flex: 1;

          .tags-item {
            padding: 12rpx 24rpx;
            background: #f4f5f7;
            border-radius: 32rpx;

            font-size: 26rpx;
            font-family: PingFang SC, PingFang SC-400;
            font-weight: 400;
            color: #1d2129;
            margin-right: 16rpx;
            margin-bottom: 16rpx;
          }
        }
      }

      // &-item {
      //   padding: 12rpx 24rpx;
      //   background-color: #f4f5f7;
      //   border-radius: 32rpx;
      //   margin-right: 16rpx;
      //   margin-bottom: 16rpx;
      //   .txt {
      //     font-size: 26rpx;
      //     color: #1d2129;
      //     display: block;
      //   }
      // }
    }

    .auth_view {
      width: 302rpx;
      height: 130rpx;
      background-color: #f7f8fa;
      border-radius: 16rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      padding: 24rpx;
      box-sizing: border-box;

      .txt1 {
        font-size: 26rpx;
        color: #1d2129;
        line-height: 38rpx;
      }

      .txt2 {
        margin-top: 4rpx;
        font-size: 24rpx;
        color: #0082ff;
        line-height: 40rpx;
      }

      .txt3 {
        margin-top: 4rpx;
        font-size: 24rpx;
        color: #00b4a9;
        line-height: 40rpx;
      }

      .non_txt {
        margin-top: 4rpx;
        font-size: 24rpx;
        color: #999;
        line-height: 40rpx;
      }

      .icon {
        width: 48rpx;
        height: 50rpx;
      }
    }
  }

  .bottom_btns {
    width: 100%;
    position: fixed;
    left: 0;
    bottom: 116rpx;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    z-index: 999;

    .btn1 {
      width: 278rpx;
      height: 96rpx;
      background: linear-gradient(107deg, #ff66c2, #ff7ccb 100%);
      border-radius: 48rpx;
      box-shadow: 0px 16rpx 64rpx 0px rgba(255, 102, 194, 0.15);
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;

      &-icon {
        width: 40rpx;
        height: 40rpx;
      }

      &-txt {
        font-size: 32rpx;
        color: #ffffff;
        font-weight: 500;
        margin-left: 20rpx;
      }
    }

    .btn2 {
      width: 268rpx;
      height: 96rpx;
      background: linear-gradient(107deg, #4a97e7, #b57aff 100%);
      border-radius: 48rpx;
      box-shadow: 0px 16rpx 64rpx 0px rgba(129, 137, 244, 0.15);
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      margin-left: 32rpx;

      &-icon {
        width: 40rpx;
        height: 40rpx;
      }

      &-txt {
        font-size: 32rpx;
        color: #ffffff;
        font-weight: 500;
        margin-left: 20rpx;
      }
    }
  }
}
</style>