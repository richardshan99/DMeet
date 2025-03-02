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
        title="筛选"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view class="option" :style="{ marginTop: '16rpx' }">
        <view class="option-label"
          >所在地
          <image
            class="vip_icon"
            src="/static/filter/vip_icon.png"
            mode="scaleToFill"
          />
        </view>

        <view @click="openPopup(areaPopup, 0)" class="option-location">
          <text class="txt">{{
            searchData.area_id == null ? "请选择所在地" : searchData.area_name
          }}</text>
          <image
            :style="{ transform: `rotate(${areaFlag ? 180 : 0}deg)` }"
            class="arrow_down"
            src="/static/filter/arrow_down.png"
          ></image>
        </view>
      </view>
      <view class="option" :style="{ marginTop: '16rpx' }">
        <text class="option-label">性别</text>
        <view
          :style="{
            display: 'flex',
            flexDirection: 'row',
            alignItems: 'center',
          }"
        >
          <view
            @click="chooseGender(1)"
            :class="`option-${searchData.gender == 1 ? 'checked' : 'common'}`"
          >
            <text class="txt">男</text>
          </view>
          <view
            @click="chooseGender(2)"
            :class="`option-${searchData.gender == 2 ? 'checked' : 'common'}`"
          >
            <text class="txt">女</text>
          </view>
          <view
            @click="chooseGender(-1)"
            :class="`option-${searchData.gender == -1 ? 'checked' : 'common'}`"
          >
            <text class="txt">不限</text>
          </view>
        </view>
      </view>
      <view class="range">
        <!-- <text class="range-left">年龄</text> -->
        <view class="option-label"
          >年龄
          <image
            class="vip_icon"
            src="/static/filter/vip_icon.png"
            mode="scaleToFill"
          />
        </view>
        <text class="range-value"
          >{{ searchData.age[0] }}-{{ searchData.age[1] }}</text
        >
      </view>
      <view class="slider">
        <view
          class="slider-vip"
          v-if="userInfo.is_member != 1"
          @click="verify"
        ></view>
        <kw-slider
          :customBlock="true"
          :showTip="false"
          :show-label="false"
          v-model="searchData.age"
          :min="18"
          :max="60"
          :step="1"
          activeColor="#2C94FF"
          backgroundColor="#E8EAEF"
          :barHeight="6"
        >
          <template #minBlock>
            <view class="slider-block"></view>
          </template>
          <template #maxBlock>
            <view class="slider-block"></view>
          </template>
        </kw-slider>
      </view>
      <view class="range">
        <!-- <text class="range-left">身高</text> -->
        <view class="range-left option-label"
          >身高
          <image
            class="vip_icon"
            src="/static/filter/vip_icon.png"
            mode="scaleToFill"
          />
        </view>
        <text class="range-value"
          >{{ searchData.height[0] }}-{{ searchData.height[1] }}</text
        >
      </view>
      <view class="slider">
        <view
          class="slider-vip"
          v-if="userInfo.is_member != 1"
          @click="verify"
        ></view>
        <kw-slider
          :customBlock="true"
          :showTip="false"
          :show-label="false"
          v-model="searchData.height"
          :min="150"
          :max="200"
          :step="1"
          activeColor="#2C94FF"
          backgroundColor="#E8EAEF"
          :barHeight="6"
        >
          <template #minBlock>
            <view class="slider-block"></view>
          </template>
          <template #maxBlock>
            <view class="slider-block"></view>
          </template>
        </kw-slider>
      </view>
      <!-- <text class="page_label">个人标签</text> -->
      <view class="page_label option-label"
        >个人标签
        <image
          class="vip_icon"
          src="/static/filter/vip_icon.png"
          mode="scaleToFill"
        />
      </view>
      <scroll-view class="tags_view" :scroll-y="true">
        <view class="child">
          <view
            v-for="(item, index) in searchData.labels"
            :key="'label' + index"
            class="tag_item"
          >
            <text>{{ item.name }}</text>
          </view>
          <text @click="openPopup(tagsPopup, 2)" class="tag_edit"
            >选择标签</text
          >
        </view>
      </scroll-view>
      <view @click="saveSearchInfo" class="save_btn">
        <text class="txt">保存</text>
      </view>
    </view>

    <uni-popup ref="areaPopup" type="bottom" @maskClick="setStatus">
      <city-select
        title="所在地"
        @confirm="confirmArea"
        @cancel="setStatus"
      ></city-select>
    </uni-popup>
    <uni-popup ref="tagsPopup" type="bottom">
      <view class="tagsView">
        <view class="tagsView-head">
          <text class="title">个人标签</text>
          <!-- <text class="describe">最多可添加10个标签</text> -->
        </view>
        <view
          :style="{
            display: 'flex',
            flexDirection: 'column',
            width: '100%',
            maxHeight: '1200rpx',
            overflow: 'auto',
          }"
        >
          <view
            v-for="(item, index) in labelList"
            :key="'label' + index"
            :style="{
              borderBottom:
                index == labelList.length - 1 ? 'none' : '1px solid #E8EAEF',
            }"
            class="tagsView-item"
          >
            <text class="title">{{ item.name }}</text>
            <view class="options">
              <view
                v-for="(citem, cind) in item.childlist"
                @click="selectTag(citem)"
                :key="'tag' + cind"
                class="options-tag"
                :class="{ active: citem.checked }"
              >
                <text class="txt">{{ citem.name }}</text>
              </view>
            </view>
          </view>
        </view>
        <view @click="saveLabels" class="saveBtn">
          <text>确定</text>
        </view>
      </view>
    </uni-popup>

    <uni-popup :style="{ zIndex: '99999' }" ref="searchPopup" type="center">
      <meet-popup
        @confirm="toBuyMember"
        msg="只有会员才能使用筛选"
        confirmText="购买会员"
        cancelText="知道了"
      ></meet-popup>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, reactive, ref, computed } from "vue";
import { useStore } from "vuex";
import { onLoad } from "@dcloudio/uni-app";
const app = getCurrentInstance().appContext.app;
const store = useStore();
const areaPopup = ref(); // 区域弹窗
const tagsPopup = ref(); // 标签弹窗
const areaFlag = ref(false); // 控制三角形

const labelList = ref([]);
const searchPopup = ref();

const selectedLabels = ref([]);
const searchData = reactive({
  gender: -1,
  age: [18, 28],
  height: [150, 190],
  area_id: null,
  area_name: null,
  labels: [],
});

const defaultCity = computed(() => store.state.user.defaultCity); // 默认城市
const searchInfo = computed(() => store.state.user.searchData); // 默认查询参数
const userInfo = computed(() => store.state.user.userInfo);
onLoad(() => {
  if (defaultCity.value) {
    searchData.area_id = defaultCity.value?.cityInd;
    searchData.area_name = defaultCity.value?.showName;
  }

  if (searchInfo.value != null) {
    searchData.gender = searchInfo.value.gender;
    searchData.age = searchInfo.value.age;
    searchData.height = searchInfo.value.height;
    searchData.area_id = searchInfo.value.area_id;
    searchData.area_name = searchInfo.value.area_name;
    searchData.labels = searchInfo.value.labels;
  }

  // 默认筛选异性
  searchData.gender = userInfo.value.gender == 1 ? 2 : 1;

  console.log(userInfo, "userInfo--");

  api.post("index/all_label_list").then((res: any) => {
    if (res.code == 1) {
      labelList.value = res.data
        .filter((item: any) => item.childlist.length > 0)
        .map((uitem: any) => {
          let chilList = uitem.childlist.map((childItem: any) => {
            let ind = selectedLabels.value.findIndex(
              (vitem) => vitem.id == childItem.id
            );
            if (ind >= 0) {
              return {
                ...childItem,
                checked: true,
              };
            } else {
              return {
                ...childItem,
                checked: false,
              };
            }
          });
          return {
            ...uitem,
            childlist: chilList,
          };
        });
    }
  });
});

const verify = () => {
  console.log(111);
  searchPopup.value.open();
};

const chooseGender = (gender: number) => {
  if (gender != searchData.gender) {
    searchData.gender = gender;
  }
};

const selectTag = (item: any) => {
  item.checked = !item.checked;
  if (item.checked) {
    selectedLabels.value.push(item);
  } else {
    let ind = selectedLabels.value.findIndex((vitem) => vitem.id == item.id);
    if (ind >= 0) {
      selectedLabels.value.splice(ind, 1);
    }
  }
};

// 返回
const navBack = () => {
  uni.navigateBack();
};

const confirmArea = (options: Array<number>, address: string, cityId: any) => {
  (searchData.area_id = cityId), (searchData.area_name = address);
  setStatus();
};

const toBuyMember = () => {
  // 去购买会员
  uni.navigateTo({
    url: "/pages/member_purchase/member_purchase",
  });
};

const openPopup = (e: any, type: number = 1) => {
  console.log(userInfo.value);
  if (userInfo.value.is_member != 1) {
    searchPopup.value.open();
    return;
  }
  if (type == 0) {
    setStatus();
  }
  if (type == 2) {
    selectedLabels.value = Object.assign([], searchData.labels);
    labelList.value.forEach((uitem: any) => {
      uitem.childlist.forEach((childItem: any) => {
        let ind = selectedLabels.value.findIndex(
          (vitem) => vitem.id == childItem.id
        );
        if (ind >= 0) {
          childItem.checked = true;
        } else {
          childItem.checked = false;
        }
      });
    });
  }
  e.open();
};

const setStatus = () => {
  areaFlag.value = !areaFlag.value;
};

const saveLabels = () => {
  searchData.labels = Object.assign([], selectedLabels.value);
  tagsPopup.value.close();
};

const saveSearchInfo = () => {
  store.commit("saveSearch", searchData);
  uni.showToast({
    icon: "none",
    title: "saveSearch已保存",
  });
  uni.$emit("refreshRecommendations");
  uni.navigateBack();
};
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  height: 100%;
  background-color: #fff;
  display: flex;
  flex-direction: column;
  .page_label {
    width: 670rpx;
    margin-top: 84rpx;
    font-size: 32rpx;
    color: #1d2129;
    font-weight: 500;
  }
  .tags_view {
    width: 670rpx;
    flex: 1;
    min-height: 100rpx;
    flex-shrink: 0;
    margin-top: 14rpx;
    .child {
      display: flex;
      flex-direction: row;
      align-items: center;
      flex-wrap: wrap;
      .tag_item {
        margin-right: 18rpx;
        margin-top: 18rpx;
        padding: 12rpx 28rpx;
        border-radius: 32rpx;
        border: 1px solid #dadce0;
        text {
          display: block;
          color: #1d2129;
          font-size: 24rpx;
        }
      }
      .tag_edit {
        padding: 12rpx 28rpx;
        border-radius: 32rpx;
        border: 1px solid #2c94ff;
        font-size: 24rpx;
        color: #2c94ff;
        display: block;
        margin-top: 18rpx;
      }
      .active {
        border: 1px solid #2c94ff;
        text {
          color: #2c94ff;
        }
      }
    }
  }
  .save_btn {
    width: 670rpx;
    height: 88rpx;
    margin-bottom: 80rpx;
    border-radius: 44rpx;
    background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
    line-height: 88rpx;
    text-align: center;
    .txt {
      font-size: 28rpx;
      color: #fff;
      font-weight: 500;
    }
  }
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
    .option {
      width: 670rpx;
      height: 104rpx;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      &-label {
        display: flex;
        align-items: center;
        font-size: 32rpx;
        color: #1d2129;
        font-weight: 500;
        .vip_icon {
          width: 68rpx;
          height: 32rpx;
          margin-left: 16rpx;
          margin-top: 8rpx;
        }
      }
      &-location {
        box-sizing: border-box;
        padding: 8rpx 32rpx;
        border-radius: 28rpx;
        border: 1px solid #2c94ff;
        display: flex;
        flex-direction: row;
        align-items: center;
        .txt {
          font-size: 24rpx;
          color: #2c94ff;
        }
        .arrow_down {
          width: 16rpx;
          height: 16rpx;
          margin-left: 8rpx;
          transition-property: transform;
          transition-duration: 200ms;
          transition-timing-function: linear;
        }
      }
      &-checked {
        box-sizing: border-box;
        padding: 8rpx 40rpx;
        border: 1px solid #2c94ff;
        border-radius: 28rpx;
        margin-left: 22rpx;
        .txt {
          display: block;
          color: #2c94ff;
          font-size: 24rpx;
        }
      }
      &-common {
        padding: 8rpx 40rpx;
        border: 1px solid #dadce0;
        border-radius: 28rpx;
        margin-left: 22rpx;
        .txt {
          display: block;
          color: #1d2129;
          font-size: 24rpx;
        }
      }
    }
    .range {
      width: 670rpx;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      margin-top: 48rpx;
      &-left {
        font-size: 32rpx;
        font-weight: 500;
        color: #1d2129;
      }
      &-value {
        font-size: 28rpx;
        color: #2c94ff;
        font-weight: 500;
      }
    }
    .slider {
      margin-top: 28rpx;
      width: 670rpx;
      position: relative;
      .slider-vip {
        position: absolute;
        top: 0;
        width: 100%;
        height: 50rpx;
        z-index: 9999;
        // background-color: #2c94ff;
      }
      &-block {
        width: 48rpx;
        height: 48rpx;
        background-color: #ffffff;
        border: 1px solid #f0f2f5;
        border-radius: 50%;
        box-shadow: 0px 4rpx 16rpx 0px rgba(0, 0, 0, 0.08);
      }
    }
  }
  .tagsView {
    width: 750rpx;
    background-color: #fff;
    border-radius: 32rpx 32rpx 0px 0px;
    padding: 32rpx 0 100rpx 0;
    box-sizing: border-box;
    &-head {
      // margin-top: 32rpx;
      width: 100%;
      display: flex;
      flex-direction: row;
      align-items: center;
      .title {
        font-size: 32rpx;
        color: #1d2129;
        font-weight: 500;
        margin-left: 40rpx;
      }
      .describe {
        margin-left: 16rpx;
        font-size: 24rpx;
        color: #868d9c;
      }
    }
    &-item {
      width: 670rpx;
      margin: 32rpx auto 0 auto;
      padding-bottom: 32rpx;
      box-sizing: border-box;
      border-bottom: 1px solid #e8eaef;
      .title {
        font-size: 32rpx;
        color: #1d2129;
        font-weight: 500;
      }
      .options {
        width: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        flex-wrap: wrap;
        &-tag {
          margin-top: 18rpx;
          margin-right: 18rpx;
          box-sizing: border-box;
          padding: 12rpx 28rpx;
          background: #ffffff;
          border: 1px solid #dadce0;
          border-radius: 32rpx;
          .txt {
            display: block;
            font-size: 24rpx;
            color: #868d9c;
          }
        }
        .active {
          border: 1px solid transparent;
          background: linear-gradient(
            118deg,
            rgba(74, 151, 231, 0.15),
            rgba(181, 122, 255, 0.15) 100%
          );
          color: transparent;
          .txt {
            background: linear-gradient(126deg, #4a97e7, #b57aff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          }
        }
      }
    }
    .saveBtn {
      width: 670rpx;
      height: 88rpx;
      margin: 16rpx auto 0 auto;
      line-height: 88rpx;
      text-align: center;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;
      font-size: 28rpx;
      color: #fff;
      font-weight: 500;
    }
  }
}
</style>
