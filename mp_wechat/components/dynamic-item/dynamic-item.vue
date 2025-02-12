<template>
  <view
    v-if="dynamicItem != null && dynamicItem.id != null"
    class="item"
    :style="{ marginBottom: '32rpx' }"
    @click="toDetail"
  >
    <image
      @click.stop="toUserDetail(dynamicItem.user.id)"
      class="item-avatar"
      mode="aspectFill"
      :src="dynamicItem.user.avatar_text"
    ></image>
    <view class="item-content">
      <view :style="{ display: 'flex', flexDirection: 'row' }">
        <view
          :style="{
            display: 'flex',
            flexDirection: 'column',
            flex: 1,
            flexShrink: 0,
            minWidth: 0,
          }"
        >
          <view
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'center',
            }"
          >
            <text class="userName">{{ dynamicItem.user.nickname }}</text>
            <image
              v-if="dynamicItem.user.gender == 1"
              src="/static/dynamic/man.png"
              class="gender"
            ></image>
            <image
              v-if="dynamicItem.user.gender == 2"
              src="/static/dynamic/woman.png"
              class="gender"
            ></image>
            <image
              v-if="dynamicItem.user.is_member == 1"
              src="/static/vip_icon.png"
              class="other_icon"
            ></image>
            <image
              v-if="dynamicItem.user.is_cert_realname == 1"
              src="/static/person/confirm_name.png"
              class="other_icon"
            ></image>
            <image
              v-if="dynamicItem.user.is_cert_education == 1"
              src="/static/person/edu.png"
              class="other_icon"
            ></image>
          </view>
          <text class="desc"
            >{{ dynamicItem.user.birth_year }}年 ·
            {{ dynamicItem.user.height }}cm · {{ dynamicItem.user.area }}</text
          >
        </view>
        <!-- <image @click.stop="operationItem" class="options" src="/static/dynamic/options.png"></image> -->
      </view>
      <text class="substance">{{ dynamicItem.slice_content }}</text>
      <image
        class="oneImg"
        v-if="dynamicItem.images_text.length == 1"
        :src="dynamicItem.images_text[0]"
        mode="aspectFill"
      ></image>
      <view
        v-else-if="dynamicItem.images_text.length == 2"
        :style="{ display: 'flex', flexDirection: 'row', alignItems: 'center' }"
      >
        <image
          class="twoImg_left"
          :src="dynamicItem.images_text[0]"
          mode="aspectFill"
        ></image>
        <image
          class="twoImg_right"
          :style="{ marginLeft: '6rpx' }"
          :src="dynamicItem.images_text[1]"
          mode="aspectFill"
        ></image>
      </view>
      <view
        v-else-if="dynamicItem.images_text.length == 3"
        :style="{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          flexWrap: 'wrap',
        }"
      >
        <image
          :style="{
            'border-top-left-radius': '16rpx',
            'border-bottom-left-radius': '16rpx',
          }"
          class="img3"
          :src="dynamicItem.images_text[0]"
          mode="aspectFill"
        ></image>
        <image
          class="img3"
          :src="dynamicItem.images_text[1]"
          :style="{ marginLeft: '6rpx' }"
          mode="aspectFill"
        ></image>
        <image
          :style="{
            'border-top-right-radius': '16rpx',
            'border-bottom-right-radius': '16rpx',
            marginLeft: '6rpx',
          }"
          class="img3"
          :src="dynamicItem.images_text[2]"
          mode="aspectFill"
        ></image>
      </view>
      <view
        v-else-if="dynamicItem.images_text.length == 4"
        :style="{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          flexWrap: 'wrap',
        }"
      >
        <image
          :style="{ 'border-top-left-radius': '16rpx', marginBottom: '6rpx' }"
          class="img4"
          :src="dynamicItem.images_text[0]"
          mode="aspectFill"
        ></image>
        <image
          :style="{
            'border-top-right-radius': '16rpx',
            marginBottom: '6rpx',
            marginLeft: '6rpx',
          }"
          class="img4"
          :src="dynamicItem.images_text[1]"
          mode="aspectFill"
        ></image>
        <image
          :style="{ 'border-bottom-left-radius': '16rpx' }"
          class="img4"
          :src="dynamicItem.images_text[2]"
          mode="aspectFill"
        ></image>
        <image
          :style="{ 'border-bottom-right-radius': '16rpx', marginLeft: '6rpx' }"
          class="img4"
          :src="dynamicItem.images_text[3]"
          mode="aspectFill"
        ></image>
      </view>
      <view
        v-else-if="dynamicItem.images_text.length == 5"
        :style="{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          flexWrap: 'wrap',
        }"
      >
        <image
          :style="{ 'border-top-left-radius': '16rpx', marginBottom: '6rpx' }"
          class="img3"
          :src="dynamicItem.images_text[0]"
          mode="aspectFill"
        ></image>
        <image
          :style="{ marginBottom: '6rpx', marginLeft: '6rpx' }"
          class="img3"
          :src="dynamicItem.images_text[1]"
          mode="aspectFill"
        ></image>
        <image
          :style="{
            'border-top-right-radius': '16rpx',
            marginBottom: '6rpx',
            marginLeft: '6rpx',
          }"
          class="img3"
          :src="dynamicItem.images_text[2]"
          mode="aspectFill"
        ></image>
        <image
          :style="{ 'border-bottom-left-radius': '16rpx' }"
          class="img3"
          :src="dynamicItem.images_text[3]"
          mode="aspectFill"
        ></image>
        <image
          :style="{ 'border-bottom-right-radius': '16rpx', marginLeft: '6rpx' }"
          class="img3"
          :src="dynamicItem.images_text[4]"
          mode="aspectFill"
        ></image>
        <view class="img3"></view>
      </view>
      <view
        v-else-if="dynamicItem.images_text.length >= 6"
        :style="{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          flexWrap: 'wrap',
        }"
      >
        <image
          :style="{ 'border-top-left-radius': '16rpx', marginBottom: '6rpx' }"
          class="img3"
          :src="dynamicItem.images_text[0]"
          mode="aspectFill"
        ></image>
        <image
          :style="{ marginBottom: '6rpx', marginLeft: '6rpx' }"
          class="img3"
          :src="dynamicItem.images_text[1]"
          mode="aspectFill"
        ></image>
        <image
          :style="{
            'border-top-right-radius': '16rpx',
            marginBottom: '6rpx',
            marginLeft: '6rpx',
          }"
          class="img3"
          :src="dynamicItem.images_text[2]"
          mode="aspectFill"
        ></image>
        <image
          :style="{ 'border-bottom-left-radius': '16rpx' }"
          class="img3"
          :src="dynamicItem.images_text[3]"
          mode="aspectFill"
        ></image>
        <image
          class="img3"
          :src="dynamicItem.images_text[4]"
          :style="{ marginLeft: '6rpx' }"
          mode="aspectFill"
        ></image>
        <view class="img3" :style="{ marginLeft: '6rpx' }">
          <image
            :style="{ 'border-bottom-right-radius': '16rpx' }"
            mode="aspectFill"
            class="img3"
            :src="dynamicItem.images_text[5]"
          ></image>
          <view class="imgMask" v-if="dynamicItem.images_text.length > 6">
            <text class="imgMask-txt"
              >+{{ dynamicItem.images_text.length - 6 }}</text
            >
          </view>
        </view>
      </view>

      <view
        class="address"
        v-if="dynamicItem.shop_id && dynamicItem.shop_id != ''"
      >
        <view class="city-shop">
          【{{ dynamicItem.shop_area }}】 {{ dynamicItem.shop_address }}</view
        >
        <view class="time">{{ dynamicItem.create_time_text }}</view>
      </view>
      <view class="bottomInfo">
        <text class="bottomInfo-time">{{ dynamicItem.create_time_text }}</text>
        <view
          @click.stop="toggleFocus"
          :style="{
            display: 'flex',
            flexDirection: 'row',
            alignItems: 'center',
          }"
        >
          <image
            v-if="dynamicItem.is_like == 1"
            src="/static/dynamic/like.png"
            class="bottomInfo-zan"
          ></image>
          <image
            v-else
            src="/static/dynamic/unlike.png"
            class="bottomInfo-zan"
          ></image>
          <text class="bottomInfo-zans">{{ dynamicItem.likes }}</text>
        </view>
      </view>

      <view v-if="isMine && dynamicItem.status == 1" class="own_dynamic">
        <image src="/static/index/auditing.png" class="audit"></image>
        <text class="audit_desc">动态正在审核中，请耐心等待</text>
      </view>
      <view v-else-if="isMine && dynamicItem.status == 3" class="reject">
        <image src="/static/dynamic/reject.png" class="reject-icon"></image>
        <text class="reject-txt"
          >动态审核未通过，原因：{{ dynamicItem.reject_reason }}</text
        >
      </view>
      <view v-else :style="{ width: '100%', height: '36rpx' }"></view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";

const emit = defineEmits(["refresh"]);

const props = withDefaults(
  defineProps<{
    dynamicItem: any;
    isMine: boolean;
  }>(),
  {
    dynamicItem: () => {},
    isMine: false,
  }
);
const toggleFocus = async () => {
  const res: any = await api.post("blog/like", {
    blog_id: props.dynamicItem.id,
  });
  if (res.code == 1) {
    props.dynamicItem.is_like = props.dynamicItem.is_like == 1 ? -1 : 1;
    props.dynamicItem.likes =
      parseInt(props.dynamicItem.likes) +
      (props.dynamicItem.is_like == 1 ? 1 : -1);
  }
};

const toUserDetail = (id: string) => {
  uni.navigateTo({
    url: `/pages/personal_details/personal_details?id=${id}`,
  });
};

const toDetail = () => {
  uni.navigateTo({
    url: `/pages/dynamic_detail/dynamic_detail?id=${
      props.dynamicItem.id
    }&isMine=${props.isMine ? "T" : "F"}`,
  });
};

const operationItem = () => {
  let list = ["举报"];
  if (props.isMine) {
    list = ["删除"];
  }
  if (props.dynamicItem.status == 3) {
    list.push("编辑");
  }
  uni.showActionSheet({
    itemList: list,
    success: (res) => {
      if (res.tapIndex == 0) {
        if (props.isMine) {
          uni.showModal({
            title: "提示",
            content: "是否确认删除动态？",
            success: (ures) => {
              if (ures.confirm) {
                api
                  .post("my/dynamic/del", {
                    blog_id: props.dynamicItem.id,
                  })
                  .then((res: any) => {
                    if (res.code == 1) {
                      emit("refresh");
                    }
                  });
              }
            },
          });
        } else {
          uni.navigateTo({
            url: `/pages/report/report?id=${props.dynamicItem.id}`,
          });
        }
      } else if (res.tapIndex == 1) {
        // 去编辑
        uni.navigateTo({
          url: "/pages/publish_trends/publish_trends",
          success: (ures) => {
            ures.eventChannel.emit("acceptDataFromOpenerPage", {
              imgs: props.dynamicItem.images_text.map((item: any) => {
                return {
                  path: item,
                  local: false,
                };
              }),
              content: props.dynamicItem.slice_content,
              id: props.dynamicItem.id,
            });
          },
        });
      }
    },
  });
};
</script>

<style lang="scss" scoped>
.item {
  width: 100%;
  border-bottom: 1px solid #f0f2f5;
  display: flex;
  flex-direction: row;
  // margin-top: 36rpx;
  &-avatar {
    width: 80rpx;
    height: 80rpx;
    border-radius: 40rpx;
  }
  &-content {
    flex: 1;
    flex-shrink: 0;
    min-width: 0;
    margin-left: 16rpx;
    display: flex;
    flex-direction: column;
    .userName {
      font-size: 28rpx;
      color: #1d2129;
      font-weight: 500;
      line-height: 44rpx;
    }
    .gender {
      margin-left: 8rpx;
      width: 28rpx;
      height: 28rpx;
    }
    .other_icon {
      margin-left: 8rpx;
      width: 28rpx;
      height: 28rpx;
    }
    .options {
      width: 40rpx;
      height: 40rpx;
    }
    .desc {
      font-size: 22rpx;
      color: #868d9c;
      line-height: 34rpx;
    }
    .substance {
      margin: 16rpx 0;
      width: 100%;
      font-size: 28rpx;
      color: #1d2129;
      line-height: 44rpx;
      word-wrap: break-word; /* 使得长单词或数字可以换行 */
      overflow-wrap: break-word; /* 确保兼容性 */
    }
    .oneImg {
      width: 406rpx;
      height: 542rpx;
      border-radius: 16rpx;
    }
    .twoImg_left {
      width: 268rpx;
      height: 268rpx;
      border-top-left-radius: 16rpx;
      border-bottom-left-radius: 16rpx;
      margin-left: 6rpx;
    }
    .twoImg_right {
      width: 268rpx;
      height: 268rpx;
      border-top-right-radius: 16rpx;
      border-bottom-right-radius: 16rpx;
    }
    .img3 {
      width: 192rpx;
      height: 192rpx;
      position: relative;
      z-index: 9;
    }
    .imgMask {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border-bottom-right-radius: 16rpx;
      background-color: rgba(0, 0, 0, 0.3);
      z-index: 10;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      align-items: flex-end;
      &-txt {
        margin-bottom: 14rpx;
        margin-right: 24rpx;
        font-size: 32rpx;
        color: #ffffff;
        font-weight: 500;
      }
    }
    .img4 {
      width: 268rpx;
      height: 268rpx;
    }

    .address {
      width: 100%;
      // height: 100%;
      background-color: #f7f8fa;
      padding: 10rpx 32rpx;
      border-radius: 20rpx;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      justify-content: center;
      .city-shop {
        font-size: 28rpx;
        color: #11161e;
      }
      .time {
        font-size: 26rpx;
        color: #414b5f;
        margin-top: 16rpx;
      }
    }
    .bottomInfo {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      margin-top: 24rpx;
      // margin-bottom: 36rpx;
      &-time {
        font-size: 24rpx;
        color: #b4b7bf;
      }
      &-zan {
        width: 36rpx;
        height: 36rpx;
      }
      &-zans {
        font-size: 24rpx;
        color: #b4b7bf;
        margin-left: 8rpx;
      }
    }
    .own_dynamic {
      margin-top: 16rpx;
      margin-bottom: 40rpx;
      width: 100%;
      height: 56rpx;
      background: linear-gradient(
        94deg,
        rgba(74, 151, 231, 0.12),
        rgba(181, 122, 255, 0.12) 100%
      );
      border-radius: 16rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      .audit {
        margin-left: 24rpx;
        width: 24rpx;
        height: 24rpx;
      }
      .audit_desc {
        margin-left: 12rpx;
        font-size: 24rpx;
        font-weight: 500;
        background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
    }
    .reject {
      margin-top: 16rpx;
      margin-bottom: 36rpx;
      width: 100%;
      padding: 8rpx 24rpx;
      box-sizing: border-box;
      background: rgba(255, 84, 111, 0.08);
      border-radius: 16rpx;
      display: flex;
      flex-direction: row;
      align-items: flex-start;
      &-icon {
        width: 24rpx;
        height: 24rpx;
        margin-top: 6rpx;
      }
      &-txt {
        margin-left: 12rpx;
        font-size: 24rpx;
        color: #ff546f;
        line-height: 36rpx;
        font-weight: 500;
        word-wrap: break-word; /* 使得长单词或数字可以换行 */
        overflow-wrap: break-word; /* 确保兼容性 */
        flex: 1;
        flex-shrink: 0;
        min-width: 0;
      }
    }
  }
}
</style>