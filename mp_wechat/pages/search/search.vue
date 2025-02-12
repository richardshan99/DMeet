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
        :border="false"
        title="搜索"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <image
        :src="
          app.config.globalProperties.$imgBase +
          '/xlyl_meet/announcement/banner.png'
        "
        class="banner"
      ></image>
      <view class="search_area">
        <view class="search_area-back">
          <image src="/static/search/search_big.png" class="icon1"></image>
          <input
            @confirm="confirmSearch"
            v-model="searchVal"
            placeholder="搜索用户昵称/手机号"
            class="input"
            type="text"
          />
          <image
            v-if="searchVal.length > 0"
            @click="clearSearch"
            src="/static/search/clear.png"
            class="clear"
          ></image>
        </view>
        <text @click="confirmSearch" class="search_area-btn">搜索</text>
      </view>
      <scroll-view
        type="list"
        class="scroll"
        :scroll-y="true"
        @scrolltolower="loadSearchList"
      >
        <view
          class="item"
          v-for="(item, ind) in dataList"
          :key="'search' + ind"
          @click="toUserDetail(item.id)"
        >
          <view class="item-left">
            <image
              mode="aspectFill"
              :src="item.avatar_text"
              class="head_icon"
            ></image>
            <view
              :style="{
                display: 'flex',
                flexDirection: 'column',
                marginLeft: '24rpx',
                flex: 1,
              }"
            >
              <view
                :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'center',
                }"
              >
                <text class="name">{{ item.nickname }}</text>
                <image
                  v-if="item.gender == 1"
                  src="/static/focus/man.png"
                  :style="{
                    width: '28rpx',
                    height: '28rpx',
                    marginLeft: '8rpx',
                  }"
                ></image>
                <image
                  v-if="item.gender == 2"
                  src="/static/focus/woman.png"
                  :style="{
                    width: '28rpx',
                    height: '28rpx',
                    marginLeft: '8rpx',
                  }"
                ></image>
                <image
                  v-if="item.is_member == 1"
                  src="/static/vip_icon.png"
                  :style="{
                    width: '28rpx',
                    height: '28rpx',
                    marginLeft: '8rpx',
                  }"
                ></image>
                <image
                  v-if="item.is_cert_realname == 1"
                  :style="{
                    width: '28rpx',
                    height: '28rpx',
                    marginLeft: '8rpx',
                  }"
                  src="/static/person/confirm_name.png"
                ></image>
                <image
                  v-if="item.is_cert_education == 1"
                  :style="{
                    width: '28rpx',
                    height: '28rpx',
                    marginLeft: '8rpx',
                  }"
                  src="/static/person/edu.png"
                ></image>
                <!-- <image v-if="item.is_member == 1" src="/static/vip_icon.png" :style="{width: '28rpx',height:'28rpx',marginLeft:'8rpx'}"></image> -->
              </view>
              <text class="desc"
                >{{ item.birth_text }}年 · {{ item.height }}cm ·
                {{ item.area }}</text
              >
            </view>
          </view>
          <view
            @click.stop="toggleFocus(item.id)"
            v-if="item.follow_type == 1"
            class="item-focus"
          >
            <view class="child">
              <text class="txt">关注</text>
            </view>
          </view>
          <text
            @click.stop="toggleFocus(item.id)"
            v-if="item.follow_type == 2"
            class="item-status"
            >已关注</text
          >
          <text
            @click.stop="toggleFocus(item.id)"
            v-if="item.follow_type == 3"
            class="item-status"
            >互相关注</text
          >
        </view>
        <view v-if="finish" class="scroll-status">
          <view class="box" v-if="dataList.length == 0">
            <image src="/static/search/notData.png" mode="scaleToFill" />
            <view class="desc">没有搜索到你要找的人</view>
          </view>
          <view v-else class="text"> 数据加载完毕 </view>
        </view>
        <text v-else class="scroll-status">{{
          loading ? "正在加载" : "加载完毕"
        }}</text>
        <view :style="{ height: '32rpx', width: '100%' }"></view>
      </scroll-view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, ref } from "vue";
import { onLoad } from "@dcloudio/uni-app";
const app = getCurrentInstance().appContext.app;
const searchVal = ref("");
const dataList = ref([]);
let pageNo = 1;
const loading = ref(false);
const finish = ref(false);

onLoad(() => {
  getSearchList(true);
});

const navBack = () => {
  uni.navigateBack();
};

const clearSearch = () => {
  searchVal.value = "";
  getSearchList(true);
};

const loadSearchList = () => {
  pageNo++;
  getSearchList(false);
};

const toUserDetail = (id: string) => {
  uni.navigateTo({
    url: `/pages/personal_details/personal_details?id=${id}`,
  });
};

// 关注
const toggleFocus = (userId) => {
  api
    .post("index/follow", {
      follow_id: userId,
    })
    .then((res: any) => {
      if (res.code == 1) {
        uni.showToast({
          icon: "none",
          title: res.msg,
        });
        getSearchList(true);
      }
    });
};

const getSearchList = async (refresh: boolean) => {
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
    const res: any = await api.post("index/search", {
      page: pageNo,
      keyword: searchVal.value,
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

const confirmSearch = () => {
  getSearchList(true);
};
</script>

<style lang="scss" scoped>
.main {
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
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    .banner {
      width: 686rpx;
      height: 224rpx;
      margin-top: 16rpx;
    }
    .search_area {
      width: 686rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      margin-top: 32rpx;
      &-back {
        flex: 1;
        flex-shrink: 0;
        min-width: 0;
        height: 64rpx;
        background-color: #f7f8fa;
        border-radius: 32rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        .icon1 {
          width: 24rpx;
          height: 24rpx;
          margin-left: 24rpx;
          margin-right: 8rpx;
        }
        .input {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          font-size: 24rpx;
          color: #1d2129;
        }
        .clear {
          width: 32rpx;
          height: 32rpx;
          margin: 0 24rpx;
        }
      }
      &-btn {
        margin-left: 32rpx;
        font-size: 28rpx;
        color: #1d2129;
      }
    }
    .scroll {
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      width: 100%;
      margin-top: 12rpx;
      &-status {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-around;
        font-size: 26rpx;
        color: #999;
        text-align: center;
        margin: 20rpx auto;
        // display: block;
		
		.text{
			width: 100%;
			height: 100%;
			text-align: center;
		}

        .box {
          width: 280rpx;
          height: 212rpx;
          display: flex;
          flex-direction: column;
          align-items: center;
          margin-bottom: 180rpx;
          image {
            width: 194rpx;
            height: 144rpx;
          }
          .desc {
            width: 100%;
            // width: 100%;
            text-align: center;
            margin-top: 24rpx;
            padding-left: 20rpx;

            font-size: 28rpx;
            font-family: PingFang SC, PingFang SC-400;
            font-weight: 400;
            color: #868d9c;
          }
        }
      }
      .item {
        width: 686rpx;
        height: 144rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        margin: 0 auto;
        &-left {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          display: flex;
          flex-direction: row;
          align-items: center;
          .head_icon {
            width: 96rpx;
            height: 96rpx;
            border-radius: 48rpx;
          }
          .name {
            font-size: 28rpx;
            color: #1d2129;
            font-weight: 500;
            line-height: 44rpx;
          }
          .desc {
            margin-top: 6rpx;
            font-size: 22rpx;
            line-height: 38rpx;
            margin-top: 6rpx;
            color: #868d9c;
          }
        }
        &-status {
          width: 128rpx;
          height: 56rpx;
          border: 1px solid #dadce0;
          border-radius: 28rpx;
          line-height: 56rpx;
          text-align: center;
          font-size: 24rpx;
          color: #b4b7bf;
        }
        &-focus {
          width: 128rpx;
          height: 56rpx;
          border-radius: 28rpx;
          background: linear-gradient(111deg, #4a97e7, #b57aff 100%);
          padding: 1px;
          box-sizing: border-box;
          display: flex;
          flex-direction: column;
          align-items: stretch;
          .child {
            border-radius: 28rpx;
            flex: 1;
            flex-shrink: 0;
            min-height: 0;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            .txt {
              font-size: 24rpx;
              background: linear-gradient(126deg, #4a97e7, #b57aff 100%);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
            }
          }
        }
      }
    }
  }
}
</style>
