<template>
  <view class="main">
    <view
      @click="toNoticeList"
      v-if="firstNotice.id != null"
      class="main-notice"
    >
      <image class="icon" src="/static/index/notice.png"></image>
      <text class="info">{{ firstNotice.intro }}</text>
    </view>
    <view class="main-swiper" id="mainSwiper">
      <fljwz-swiper
        class="base"
        ref="fljwzSwiperRef"
        :length="totalNum"
        @change="swiperChangeFun"
      >
        <template v-slot:default="{ item, index }">
          <view
            class="item"
            @click="toUserDetail(item.id)"
            :style="{ height: swiperHeight + 'px' }"
          >
            <image
              mode="aspectFill"
              class="item-img"
              :src="item.avatar_text"
            ></image>
            <view class="item-info">
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
                  class="sexIcon"
                  src="/static/sex_man.png"
                ></image>
                <image
                  v-if="item.gender == 2"
                  class="sexIcon"
                  src="/static/sex_woman.png"
                ></image>
                <image
                  v-if="item.is_member == 1"
                  src="/static/vip_icon.png"
                  class="icon_last"
                ></image>
                <image
                  v-if="item.is_cert_realname == 1"
                  class="icon_last"
                  src="/static/person/confirm_name.png"
                ></image>
                <image
                  v-if="item.is_cert_education == 1"
                  class="icon_last"
                  src="/static/person/edu.png"
                ></image>
              </view>
              <text class="person_desc"
                >{{ item.birth_year }}年 · {{ item.height }}cm ·
                {{ item.area }}</text
              >
              <view class="info-tag">
                <view class="left">
                  <view
                    v-if="item.school != null && item.school.length > 0"
                    class="tag"
                    :style="{ marginRight: '12rpx' }"
                  >
                    <text>{{ item.school }}</text>
                  </view>
                  <view
                    v-if="
                      item.education_type_text != null &&
                      item.education_type_text.length > 0
                    "
                    class="tag"
                    :style="{ marginRight: '12rpx' }"
                  >
                    <text>{{ item.education_type_text }}</text>
                  </view>
                  <view
                    v-if="
                      item.work_type_text != null &&
                      item.work_type_text.length > 0
                    "
                    class="tag"
                  >
                    <text>{{ item.work_type_text }}</text>
                  </view>
                  <view
                    v-for="(dom, index) in item.label_text"
                    :key="'label' + index"
                    class="tag"
                    :style="{ marginLeft: '12rpx' }"
                  >
                    <text>{{ dom }}</text>
                  </view>
                </view>
                <view class="right" v-if="item.distance != ''">
                  <image
                    class="icon_location"
                    src="/static/person/location.png"
                  ></image>
                  距你{{ item.distance }}km</view
                >
              </view>
              <!-- <view :style="{display: 'flex',flexDirection:'row',alignItems:'center',flexWrap:'wrap', marginTop:'10rpx',marginBottom:'40rpx'}">
								<view v-if="item.school != null && item.school.length > 0" class="tag" :style="{marginRight: '12rpx'}">
									<text>{{item.school}}</text>
								</view>
								<view v-if="item.education_type_text != null && item.education_type_text.length > 0" class="tag" :style="{marginRight: '12rpx'}">
									<text>{{item.education_type_text}}</text>
								</view>
								<view v-if="item.work_type_text != null && item.work_type_text.length > 0" class="tag">
									<text>{{item.work_type_text}}</text>
								</view>
								<view v-for="(dom,index) in item.label_text" :key="'label'+index" class="tag" :style="{marginLeft: '12rpx'}">
									<text>{{dom}}</text>
								</view>
							</view> -->
            </view>
          </view>
        </template>
      </fljwz-swiper>

      <!-- <swiper @change="changeCurrent" :circular="circular" :current="currentTab" class="base" next-margin="36rpx" previous-margin="36rpx">
				<swiper-item v-for="(item,ind) in swiperList" :key="'swiper'+ind">
					<view class="item" @click="toUserDetail(item.id)">
						<image mode="aspectFill" class="item-img" :src="item.avatar_text"></image>
						<view class="item-info">
							<view :style="{display: 'flex',flexDirection:'row',alignItems:'center'}">
								<text class="name">{{item.nickname}}</text>
								<image v-if="item.gender == 1" class="sexIcon" src="/static/sex_man.png"></image>
								<image v-if="item.gender == 2" class="sexIcon" src="/static/sex_woman.png"></image>
								<image v-if="item.is_member == 1" src="/static/vip_icon.png" class="icon_last"></image>
								<image v-if="item.is_cert_realname == 1" class="icon_last" src="/static/person/confirm_name.png"></image>
								<image v-if="item.is_cert_education == 1" class="icon_last" src="/static/person/edu.png"></image>
							</view>
							<text class="person_desc">{{item.birth_year}}年 · {{item.height}}cm · {{item.area}}</text>
							<view :style="{display: 'flex',flexDirection:'row',alignItems:'center', marginTop:'20rpx',marginBottom:'40rpx'}">
								<view class="tag" :style="{marginRight: '12rpx'}">
									<text>{{item.school}}</text>
								</view>
								<view class="tag" :style="{marginRight: '12rpx'}">
									<text>{{item.education_type_text}}</text>
								</view>
								<view class="tag">
									<text>{{item.work_type_text}}</text>
								</view>
							</view>
						</view>
					</view>
				</swiper-item>
				<swiper-item>
					<view class="item">
						<image mode="aspectFill" class="item-img" src="https://photo.16pic.com/00/53/88/16pic_5388659_b.jpg"></image>
						<view class="item-info"></view>
					</view>
				</swiper-item> 
			</swiper> -->
      <view @touchend="toLogin" v-if="!swiper" class="mask"></view>
    </view>

    <view class="main-indicator">
      <view class="active" :style="{ width: progress + 'rpx' }"></view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import {
  ref,
  computed,
  onUnmounted,
  onMounted,
  reactive,
  nextTick,
  getCurrentInstance,
} from "vue";
import { useStore } from "vuex";
const store = useStore();
const totalNum = ref(0); // 总数
let dataList = []; // 存储所有推荐
const firstNotice = reactive({
  id: null,
  intro: null,
});
const swiperHeight = ref(0);

const activeI = ref(0);

const searchData = computed(() => store.state.user.searchData);
const token = computed(() => store.state.user.token);
const isImprove = computed(() => store.state.user.is_improve);
// const swiper = ref(false)
const swiper = computed(() => {
  if (token.value != null && (isImprove.value == 1 || isImprove.value == -2)) {
    return true;
  }
  return false;
});
const emit = defineEmits(["login"]);
let pageNo = 1; // 当前页码
let finish = false;
const swiperList = ref([]);

const nowIndex = ref(0);

const fljwzSwiperRef = ref();

onMounted(() => {
  const query = uni.createSelectorQuery().in(getCurrentInstance());
  setTimeout(() => {
    query
      .select("#mainSwiper")
      .boundingClientRect((data) => {
        swiperHeight.value = data.height;
      })
      .exec();
  }, 400);
  getRecommendations(true);
  uni.$on("refreshRecommendations", refreshData);
  api.post("index/notice_list").then((ures: any) => {
    if (ures.code == 1 && ures.data.total > 0) {
      firstNotice.id = ures.data.data[0].id;
      firstNotice.intro = ures.data.data[0].intro;
    }
  });
});

onUnmounted(() => {
  uni.$off("refreshRecommendations", refreshData);
});

const toNoticeList = () => {
  uni.navigateTo({
    url: "/pages/announcement_list/announcement_list",
  });
};

// 轮播下标改变时触发
const swiperChangeFun = (e, callback) => {
  // 当前数据的下标
  if (e.index < dataList.length) {
    activeI.value = e.index;
  }
  // 要获取的数据的下标
  if (e.get) {
    // 获取数据
    if (dataList.length <= e.getIndex - 2 && !finish) {
      pageNo++;
      getRecommendations(false);
    }
    let listData = dataList.slice(e.getIndex, e.getIndex + 1);
    callback(listData[0]);
  }
};

const refreshData = () => {
  getRecommendations(true);
};

const toUserDetail = (id: string) => {
  uni.navigateTo({
    url: `/pages/personal_details/personal_details?id=${id}`,
  });
};

const getRecommendations = async (refresh: boolean) => {
  if (refresh) {
    finish = false;
    pageNo = 1;
    dataList = [];
  }
  const param = {};
  if (searchData.value != null) {
    Object.assign(param, searchData.value);
    param.labels = param.labels.map((item) => item.id);
    param.height = param.height.join("-");
    param.age = param.age.join("-");
  }
  // 获取推荐人员列表
  uni.getLocation({
    type: "wgs84",
    isHighAccuracy: true,
    highAccuracyExpireTime: 3000,
    success: async (res) => {
      var res: any = await api.post("index/recommend_list", {
        position: [res.longitude, res.latitude].toString(),
        page: pageNo,
        per_page: 20,
        ...param,
      });
      if (res.code == 1) {
        totalNum.value = res.data.total;
        if (refresh) {
          dataList = res.data.data.map((item) => {
            if (item.label_text.length <= 5) {
              return item;
            } else {
              return {
                ...item,
                label_text: item.label_text.slice(0, 5),
              };
            }
          });
          nextTick(() => {
            fljwzSwiperRef.value
              .getDataIndexFun(activeI.value)
              .then((index) => {
                // 获取列表数据
                let listData = dataList.slice(index, index + 5);
                fljwzSwiperRef.value.linkFun(activeI.value, listData);
              });
          });
        } else {
          dataList = dataList.concat(res.data);
        }
        if (dataList.length == res.data.total) {
          finish = true;
        }
      }
    },
  });
};

const progress = computed(() => {
  return ((activeI.value + 1) / totalNum.value) * 200;
});

const toLogin = () => {
  emit("login");
};
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  &-notice {
    margin-top: 12rpx;
    width: 686rpx;
    height: 70rpx;
    background: linear-gradient(
      95deg,
      rgba(74, 151, 231, 0.12),
      rgba(181, 122, 255, 0.12) 100%
    );
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
      flex: 1;
      flex-shrink: 0;
      min-width: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }
  &-swiper {
    width: 100%;
    margin-top: 32rpx;
    flex: 1;
    flex-shrink: 0;
    min-height: 0;
    position: relative;
    .mask {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 11;
    }
    .base {
      width: 100%;
      height: 100%;
      z-index: 9;
      .item {
        width: 650rpx;
        height: 100%;
        margin: 0 auto;
        position: relative;
        &-img {
          position: absolute;
          top: 0;
          left: 0;
          width: 650rpx;
          height: 100%;
          border-radius: 40rpx;
          box-shadow: 0px 24rpx 48rpx 0px rgba(0, 0, 0, 0.1);
          z-index: 9;
        }
        &-info {
          position: absolute;
          left: 0;
          bottom: 0;
          width: 650rpx;
          height: 360rpx;
          border-bottom-left-radius: 40rpx;
          border-bottom-right-radius: 40rpx;
          background: linear-gradient(
            180deg,
            rgba(0, 0, 0, 0),
            rgba(0, 0, 0, 0.29) 21%,
            rgba(0, 0, 0, 0.54) 39%,
            rgba(0, 0, 0, 0.75) 59%,
            rgba(0, 0, 0, 0.95) 79%,
            rgba(0, 0, 0, 0.95) 100%
          );
          box-shadow: 0px 24rpx 48rpx 0px rgba(0, 0, 0, 0.1);
          z-index: 10;
          display: flex;
          flex-direction: column;
          justify-content: flex-end;
          padding: 0 40rpx;
          box-sizing: border-box;
          .name {
            font-size: 44rpx;
            font-weight: 600;
            color: #fff;
            // text-shadow: 0 24rpx 48rpx 0px rgba(0,0,0,0.10);
          }
          .sexIcon {
            width: 40rpx;
            height: 42rpx;
            margin-left: 16rpx;
          }
          .icon_last {
            width: 40rpx;
            height: 40rpx;
            margin-left: 12rpx;
          }
          .person_desc {
            font-size: 24rpx;
            font-weight: 500;
            line-height: 40rpx;
            color: #fff;
            margin-top: 8rpx;
          }
          .info-tag {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            padding-bottom: 40rpx;
            .left {
              flex: 1;
              display: flex;
              flex-direction: row;
              align-items: center;
              flex-wrap: wrap;
            }
            .right {
              height: 100%;
              opacity: 0.7;
              font-size: 24rpx;
              font-family: PingFang SC, PingFang SC-400;
              font-weight: 400;
              color: #ffffff;
              text-shadow: 0rpx 24rpx 48rpx 0rpx rgba(0, 0, 0, 0.1);
              display: flex;
              align-items: center;
              padding-top: 6rpx;
            }
          }
          .tag {
            // width: 128px;
            margin-top: 10rpx;
            height: 48rpx;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 12rpx;
            font-size: 24rpx;
            line-height: 48rpx;
            color: #fff;
            text-align: center;
            padding: 0 16rpx;
          }
        }
      }
    }
  }
  &-indicator {
    width: 200rpx;
    height: 12rpx;
    border-radius: 6rpx;
    background-color: rgba(0, 0, 0, 0.05);
    margin-top: 24px;
    margin-bottom: 19px;
    .active {
      height: 12rpx;
      border-radius: 6rpx;
      transition-property: width;
      transition-duration: 300ms;
      background: linear-gradient(97deg, #4a97e7, #b57aff 100%);
    }
  }
}

.icon_location {
  width: 24rpx;
  height: 24rpx;
  margin-right: 4rpx;
}
</style>