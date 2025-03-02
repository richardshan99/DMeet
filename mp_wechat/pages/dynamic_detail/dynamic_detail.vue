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
        title="动态详情"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view class="dynamic">
        <view class="dynamic-head">
          <image
            @click="toUserDetail"
            mode="aspectFill"
            :src="dynamicDetail.user.avatar_text"
            class="avatar"
          ></image>
          <view class="part">
            <view class="row1">
              <text class="row1-name">{{ dynamicDetail.user.nickname }}</text>
              <image
                v-if="dynamicDetail.user.gender == 1"
                src="/static/dynamic/man.png"
                class="row1-sex"
              ></image>
              <image
                v-if="dynamicDetail.user.gender == 2"
                src="/static/dynamic/woman.png"
                class="row1-sex"
              ></image>
              <image
                v-if="dynamicDetail.user.is_member == 1"
                src="/static/vip_icon.png"
                class="other_icon"
              ></image>
              <image
                v-if="dynamicDetail.user.is_cert_realname == 1"
                src="/static/person/confirm_name.png"
                class="other_icon"
              ></image>
              <image
                v-if="dynamicDetail.user.is_cert_education == 1"
                src="/static/person/edu.png"
                class="other_icon"
              ></image>
            </view>
            <text class="part-row2"
              >{{ dynamicDetail.user.birth_year }}年 ·
              {{ dynamicDetail.user.height }}cm ·
              {{ dynamicDetail.user.area }}</text
            >
          </view>
          <!-- <image @click="operationItem" class="tools" src="/static/dynamic/options.png"></image> -->
        </view>
        <text class="dynamic-substance">{{ dynamicDetail.content }}</text>
        <view class="dynamic-imgs" v-if="dynamicDetail.images_text.length == 1">
          <image
            @click="showPreviewImgs(0)"
            :src="dynamicDetail.images_text[0]"
            mode="aspectFill"
            class="img1"
          ></image>
        </view>
        <view
          class="dynamic-imgs"
          v-else-if="dynamicDetail.images_text.length == 2"
          :style="{ justifyContent: 'space-between' }"
        >
          <image
            @click="showPreviewImgs(0)"
            :src="dynamicDetail.images_text[0]"
            class="img2"
            mode="aspectFill"
            :style="{
              borderTopLeftRadius: '16rpx',
              borderBottomLeftRadius: '16rpx',
            }"
          ></image>
          <image
            @click="showPreviewImgs(1)"
            :src="dynamicDetail.images_text[1]"
            class="img2"
            mode="aspectFill"
            :style="{
              borderTopRightRadius: '16rpx',
              borderBottomRightRadius: '16rpx',
            }"
          ></image>
        </view>
        <view
          class="dynamic-imgs"
          v-else-if="dynamicDetail.images_text.length == 4"
          :style="{ justifyContent: 'space-between' }"
        >
          <image
            @click="showPreviewImgs(0)"
            :src="dynamicDetail.images_text[0]"
            class="img2"
            :style="{ borderTopLeftRadius: '16rpx', marginBottom: '6rpx' }"
            mode="aspectFill"
          ></image>
          <image
            @click="showPreviewImgs(1)"
            :src="dynamicDetail.images_text[1]"
            class="img2"
            :style="{ borderTopRightRadius: '16rpx', marginBottom: '6rpx' }"
            mode="aspectFill"
          ></image>
          <image
            @click="showPreviewImgs(2)"
            :src="dynamicDetail.images_text[2]"
            class="img2"
            :style="{ borderBottomLeftRadius: '16rpx' }"
            mode="aspectFill"
          ></image>
          <image
            @click="showPreviewImgs(3)"
            mode="aspectFill"
            :src="dynamicDetail.images_text[3]"
            class="img2"
            :style="{ borderBottomRightRadius: '16rpx' }"
          ></image>
        </view>
        <view
          class="dynamic-imgs"
          v-else
          :style="{ justifyContent: 'space-between' }"
        >
          <image
            v-for="(item, index) in dynamicDetail.images_text"
            @click="showPreviewImgs(index)"
            :key="'dynamicImg' + index"
            :src="item"
            class="img3"
            :class="{
              tl: index == 0,
              tr: index == 2,
              bl: index == blIndex,
              br: index == dynamicDetail.images_text.length - 1,
            }"
            :style="{ marginBottom: '6rpx' }"
            mode="aspectFill"
          >
          </image>

          <view
            class="img3"
            v-for="(vitem, vind) in spaceNum"
            :key="'space' + vind"
          ></view>
        </view>
        <view class="dynamic-bottom">
          <text class="txt">{{ dynamicDetail.create_time_text }}</text>
          <view
            @click="toggleFocus"
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'center',
            }"
          >
            <image
              v-if="dynamicDetail?.is_like == 1"
              class="icon"
              src="/static/dynamic/like.png"
            ></image>
            <image v-else class="icon" src="/static/dynamic/unlike.png"></image>
            <text class="txt" :style="{ marginLeft: '8rpx' }">{{
              dynamicDetail.likes
            }}</text>
          </view>
        </view>
      </view>
      <view @click="toLikes" v-if="isMine && totalUserNum > 0" class="seen">
        <image
          mode="aspectFill"
          class="child"
          v-for="(xitem, xind) in showUsers"
          :src="xitem.user.avatar_text"
          :class="'item' + (xind + 1)"
          :key="'look' + xind"
        ></image>
        <text class="seen-txt">{{ totalUserNum }}人赞过</text>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { computed, getCurrentInstance, reactive, ref } from "vue";
import { onLoad } from "@dcloudio/uni-app";
const isMine = ref<boolean>(false);

const users = ref([]);

const showUsers = computed(() => {
  if (users.value.length > 3) {
    return users.value.slice(0, 3);
  } else {
    return users.value;
  }
});
const totalUserNum = ref(0);
const dynamicDetail = reactive({
  id: null,
  content: null,
  likes: 0,
  user: {
    id: null,
    nickname: null,
    gender: null,
    height: null,
  },
  images_text: [],
  create_time_text: null,
  status: null,
  is_like: -1,
});
const blIndex = computed(() => {
  if (dynamicDetail.images_text.length == 3) {
    return 0;
  } else if (
    dynamicDetail.images_text.length > 3 &&
    dynamicDetail.images_text.length <= 6
  ) {
    return 3;
  } else {
    return 6;
  }
});

const toUserDetail = () => {
  uni.navigateTo({
    url: `/pages/personal_details/personal_details?id=${dynamicDetail.user.id}`,
  });
};

const toLikes = () => {
  uni.navigateTo({
    url: `/pages/likers/likers?id=${dynamicDetail.id}&title=${totalUserNum.value}人赞过`,
  });
};

const operationItem = () => {
  let list = ["举报"];
  if (isMine.value) {
    list = ["删除"];
  }
  if (dynamicDetail.status == 3) {
    list.push("编辑");
  }
  uni.showActionSheet({
    itemList: list,
    success: (res) => {
      if (res.tapIndex == 0) {
        if (isMine.value) {
          api
            .post("my/dynamic/del", {
              blog_id: dynamicDetail.id,
            })
            .then((res: any) => {
              if (res.code == 1) {
                uni.$emit("refreshMyDynamic");
                setTimeout(() => {
                  uni.navigateBack();
                }, 1000);
              }
            });
        } else {
          uni.navigateTo({
            url: `/pages/report/report?id=${dynamicDetail.id}`,
          });
        }
      } else if (res.tapIndex == 1) {
        // 去编辑
        uni.navigateTo({
          url: "/pages/publish_trends/publish_trends",
          success: (ures) => {
            ures.eventChannel.emit("acceptDataFromOpenerPage", {
              imgs: dynamicDetail.images_text.map((item: any) => {
                return {
                  path: item,
                  local: false,
                };
              }),
              content: dynamicDetail.content,
              id: dynamicDetail.id,
            });
          },
        });
      }
    },
  });
};

const showPreviewImgs = (index: number) => {
  uni.previewImage({
    urls: dynamicDetail.images_text,
    current: index,
  });
};
const toggleFocus = async () => {
  const res: any = await api.post("blog/like", { blog_id: dynamicDetail.id });
  if (res.code == 1) {
    uni.$emit("updataDynamic");
    dynamicDetail.is_like = dynamicDetail.is_like == 1 ? -1 : 1;
    dynamicDetail.likes =
      dynamicDetail.likes + (dynamicDetail.is_like == 1 ? 1 : -1);
  }
};
const spaceNum = computed(() => {
  if (dynamicDetail.images_text.length % 3 == 0) {
    return 0;
  }
  return 3 - (dynamicDetail.images_text.length % 3);
});
onLoad((options) => {
  isMine.value = options.isMine == "T" ? true : false;
  if (isMine.value) {
    api
      .post("my/dynamic/detail", {
        blog_id: options.id,
      })
      .then((vres: any) => {
        if (vres.code == 1) {
          Object.assign(dynamicDetail, vres.data);
        }
      });
    api
      .post("my/dynamic/like_user", {
        blog_id: options.id,
      })
      .then((result: any) => {
        if (result.code == 1) {
          users.value = result.data.data;
          totalUserNum.value = result.data.total;
        }
      });
  } else {
    api
      .post("/blog/detail", {
        blog_id: options.id,
      })
      .then((xres: any) => {
        if (xres.code == 1) {
          Object.assign(dynamicDetail, xres.data);
        }
      });
  }
});

const app = getCurrentInstance().appContext.app;

const navBack = () => {
  uni.navigateBack();
};
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  height: 100%;
  background-color: #f7f8fa;
  display: flex;
  flex-direction: column;
  align-items: center;
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
    min-height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow-y: auto;
    .dynamic {
      margin-top: 24rpx;
      width: 686rpx;
      display: flex;
      flex-direction: column;
      padding-bottom: 32rpx;
      box-sizing: border-box;
      border-bottom: 1px solid #f0f2f5;
      &-head {
        display: flex;
        flex-direction: row;
        .avatar {
          width: 80rpx;
          height: 80rpx;
          border-radius: 40rpx;
        }
        .part {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          margin-left: 24rpx;
          display: flex;
          flex-direction: column;
          justify-content: center;
          .row1 {
            display: flex;
            flex-direction: row;
            align-items: center;
            &-name {
              font-size: 28rpx;
              color: #1d2129;
              font-weight: 500;
              line-height: 44rpx;
            }
            &-sex {
              margin-left: 8rpx;
              width: 28rpx;
              height: 28rpx;
            }
            .other_icon {
              margin-left: 8rpx;
              width: 28rpx;
              height: 28rpx;
            }
          }
          &-row2 {
            font-size: 22rpx;
            color: #868d9c;
            margin-top: 2rpx;
            line-height: 34rpx;
          }
        }
        .tools {
          width: 40rpx;
          height: 40rpx;
        }
      }
      &-substance {
        margin-top: 24rpx;
        width: 686rpx;
        font-size: 28rpx;
        color: #1d2129;
        line-height: 44rpx;
        word-wrap: break-word; /* 使得长单词或数字可以换行 */
        overflow-wrap: break-word; /* 确保兼容性 */
      }
      &-imgs {
        margin-top: 16rpx;
        width: 686rpx;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        .img1 {
          width: 406rpx;
          height: 542rpx;
          border-radius: 16rpx;
        }
        .img2 {
          width: 340rpx;
          height: 340rpx;
        }
        .img3 {
          width: 224rpx;
          height: 224rpx;
        }
        .tl {
          border-top-left-radius: 16rpx;
        }
        .tr {
          border-top-right-radius: 16rpx;
        }
        .bl {
          border-bottom-left-radius: 16rpx;
        }
        .br {
          border-bottom-right-radius: 16rpx;
        }
      }
      &-bottom {
        margin-top: 24rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        .txt {
          font-size: 24rpx;
          color: #b4b7bf;
        }
        .icon {
          width: 36rpx;
          height: 36rpx;
        }
      }
    }
    .seen {
      margin-top: 32rpx;
      width: 686rpx;
      position: relative;
      display: flex;
      flex-direction: row;
      align-items: center;
      .child {
        width: 48rpx;
        height: 48rpx;
        border-radius: 24rpx;
        border: 3rpx solid #ffffff;
        // position: absolute;
        // top: 0;
      }
      .item1 {
        margin-left: 0;
        z-index: 1;
      }
      .item2 {
        margin-left: -12rpx;
        z-index: 2;
      }
      .item3 {
        margin-left: -12rpx;
        z-index: 3;
      }
      &-txt {
        margin-left: 16rpx;
        font-size: 28rpx;
        color: #868d9c;
      }
    }
  }
}
</style>
