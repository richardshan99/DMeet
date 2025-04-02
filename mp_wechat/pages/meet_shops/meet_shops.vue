<template>
  <view class="main">
    <image
      class="main-top"
      :src="
        app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "
    ></image>
    <view class="main-base">
		<!--李川-->
		
      <uni-nav-bar
        left-icon="left"
        @clickLeft="navBack"
        :border="false"
        :shadow="false"
       
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
	  <view class="topfix">
	  	<view class="topfix-left" @click="openCity">
	  		<image src="/static/activity/location.png" class="location"></image>
	  		<text class="city">{{activeCity.name}}</text>
	  		<view class="down_icon">
	  			<image :style="{transform: `rotate(${cityFlag?180:0}deg)`}" class="img" src="/static/activity/arrow_down.png"></image>
	  		</view>
	  	</view>
	  	
	  </view>
      <view :scroll-x="true" class="scroll_bar">
        <view
          @click="chooseType({ id: null, name: '全部' })"
          class="bar"
          :class="{ choice: currentType.id == null }"
        >
          <text class="txt">全部</text>
        </view>
        <view
          @click="chooseType(item)"
          class="bar"
          v-for="(item, ind) in typeList"
          :key="'type' + ind"
          :class="{ choice: item.id == currentType.id }"
        >
          <text class="txt">{{ item.name }}</text>
        </view>
      </view>
      <view v-if="showSuggest" class="scroll">
        <view
          v-if="submitStatus == 0"
          :style="{
            width: '100%',
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
          }"
        >
          <image class="empty_shop" src="/static/empty_shop.png"></image>
          <text class="first_title">当前城市无门店</text>
          <text class="second_title">您可以点击新地点建议，我们会努力开通</text>
          <view @click="openAddNewCity" class="new_add">
            <text>新地点建议</text>
          </view>
        </view>
        <view v-else class="submit_success">
          <image
            src="/static/give_invitation/sucess.png"
            class="success"
          ></image>
          <text class="title">提交成功</text>
          <text class="second_title"
            >您的需求我们已收到，会尽快增加见面地点</text
          >
          <view class="third">
            <view @click="toHome" class="left">
              <text>返回首页</text>
            </view>
            <view @click="toOtherCity" class="right">
              <view class="child">
                <text class="txt">去其他城市看看</text>
              </view>
            </view>
          </view>
        </view>
      </view>
      <scroll-view
        v-else
        class="scroll"
        type="list"
        :scroll-y="true"
        @scrolltolower="loadShops"
      >
        <view
          class="shop"
          v-for="(item, ind) in dataList"
          :key="'shop' + ind"
          @click="toShopDetail(item)"
        >
          <image class="shop-icon" :src="item.thumb_text"></image>
          <view class="shop-content">
            <view class="first">
              <text class="shop_name">{{ item.name }}</text>
              <text class="distance">{{ item.distance }} km</text>
            </view>
            <text class="second">{{ item.category_text }}</text>
            <view class="third">
              <image
                src="/static/activity/area_location.png"
                class="loca_icon"
              ></image>
              <text class="address">{{ item.address }}</text>
            </view>
          </view>
        </view>
      </scroll-view>
    </view>
    <uni-popup ref="chooseCity" type="bottom">
      <view class="city_proposal">
        <text class="title">我希望开通的城市是</text>
        <view @click="openCitysShow" class="chose_view">
          <view
            :style="{
              display: 'flex',
              flexDirection: 'row',
              alignItems: 'center',
            }"
          >
            <image
              class="location_icon"
              src="/static/give_invitation/now_city.png"
              :style="{ marginLeft: '32rpx' }"
            ></image>
            <text class="city">{{ nowCity.name }}</text>
          </view>
          <image
            src="/static/mine_center/arrow_left.png"
            class="arrow_left"
            :style="{ marginRight: '32rpx' }"
          ></image>
        </view>
        <view @click="submitAddNewCity" class="submit_suggest">
          <text>提交</text>
        </view>
      </view>
    </uni-popup>
    <uni-popup ref="cityPopup" type="bottom">
      <city-select @confirm="confirmSelectCity"></city-select>
    </uni-popup>
	<!--李川-->
    <!--<view class="float" @click="goRouter()">我是商家</view>-->
  </view>
  <uni-popup ref="citySelectPopup" type="bottom" @maskClick="setStatus">
  	<city-select :nowOption="[0,0,0]" @confirm="confirmCity" @cancel="setStatus"></city-select>
  </uni-popup>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { reactive, ref, getCurrentInstance,computed } from "vue";
import { onLoad } from "@dcloudio/uni-app";
import { useStore } from "vuex";
import { onShow } from "@dcloudio/uni-app"
const store = useStore();
const token = computed(() => store.state.user.token);
const { proxy } = getCurrentInstance();
const app = getCurrentInstance().appContext.app;
const type = ref(1);
const pageTitle = ref("");
const chooseCity = ref();
const cityPopup = ref();
const showSuggest = ref(true);
const currentType = reactive({
  id: null,
  name: "全部",
});
const typeList = ref([]);

const dataList = ref([]);
let pageNo = 1;
const loading = ref(false);
const finish = ref(false);
let transfer = null;
const submitStatus = ref(0);

const nowCity = reactive({
  name: null,
  id: null,
});

onLoad(() => {
  const eventChannel = proxy.getOpenerEventChannel();
  eventChannel.on("acceptDataFromOpenerPage", (data: any) => {
    // 获取上个页面的传值
    if (data != null) {
      console.log(data, "shop_list---");
      console.log(data.type, "data.type---");

      type.value = data.type;
      pageTitle.value = data.cityName;
      transfer = data;
      nowCity.id = transfer.cityId;
      nowCity.name = transfer.cityShow;
      api.post("my/shop/category").then((res: any) => {
        if (res.code == 1 && res.data.length > 0) {
          typeList.value = res.data;
          // Object.assign(currentType,res.data[0])
          getShops(true,'');//李川
        }
      });
    }
  });
});

//李川
const activeCity = reactive({
		name: null,
		id: null
	})
const currentCity = ref(0);
const defaultCity = computed(() => store.state.user.defaultCity);
const citySelectPopup = ref() // 城市弹窗
const cityFlag = ref(false)
const openCity = () => {
		cityFlag.value = !cityFlag.value
		citySelectPopup.value.open()
		
	}
const confirmCity = (options,name,cityId,cityName) => {
		console.log(cityName);
		setStatus()
		console.log('confirmCity');
		getShops(true,cityName);
	}	
	
const setStatus = () => {
		cityFlag.value = !cityFlag.value
}

onShow(() => {
		 if (token.value != null) {
			 if (defaultCity.value != null) {
			 	activeCity.id = defaultCity.value.cityInd
			 	activeCity.name = defaultCity.value.child
			 
			 }
			 
		 }
	})
	

// 前往商家申请入驻页面
const goRouter = () => {
  uni.navigateTo({
    url: "/pages/settleds_apply/settleds_apply",
  });
};

const openCitysShow = () => {
  cityPopup.value.open();
};

const toHome = () => {
  uni.switchTab({
    url: "/pages/index/index",
  });
};

const submitAddNewCity = () => {
  // 添加新城市
  api
    .post("/invitation/suggest_new_address", {
      area_id: nowCity.id,
    })
    .then((res: any) => {
      if (res.code == 1) {
        chooseCity.value.close();
        uni.showToast({
          icon: "none",
          title: res.msg,
        });
        submitStatus.value = 1;
      }
    });
};

const confirmSelectCity = (
  options: Array<number>,
  address: string,
  cityId: any,
  cityName,
  location
) => {
  if (options[0] == 0) {
    nowCity.name = location.slice(1, 3).join("-");
  } else {
    nowCity.name = location.join("-");
  }
  nowCity.id = cityId;
};

const openAddNewCity = () => {
  chooseCity.value.open();
};

const toOtherCity = () => {
  uni.$emit("changeCity");
  uni.navigateBack();
};

const toShopDetail = (item: any) => {
  uni.redirectTo({
    url: `/pages/shop_detail/shop_detail?id=${item.id}`,
  });
};

const chooseType = (item: any) => {
  if (currentType.id != item.id) {
    Object.assign(currentType, item);
    getShops(true,'');
  }
};

const navBack = () => {
  uni.navigateBack();
};

const loadShops = () => {
  pageNo++;
  getShops(false,'');
};

const getShops = async (refresh: boolean,cityName:string) => {
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
    const params = {
      shop_category_id: currentType.id,
      ...transfer,
      page: pageNo,
    };
	if(cityName!=''){
		params.area=cityName;
		params.position=0;
	}
    console.log(type.value, " type.value---");

    if (type.value == 2) {
      params.type = type.value;
    } else {
      delete params.type;
    }
    const res: any = await api.post("invitation/shop_list", params);
    loading.value = false;
    if (refresh && currentType.id == null && res.data.data.length > 0) {
      showSuggest.value = false;
    }
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
</script>

<style lang="scss" scoped>
	.topfix{
		width: 100%;
		
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 8rpx;
		&-left{
			margin-left: 32rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			.location{
				width: 40rpx;
				height: 40rpx;
			}
			.city{
				font-size: 32rpx;
				color: #1D2129;
				font-weight: 600;
				margin-left: 8rpx;
			}
			.down_icon{
				width: 32rpx;
				height: 32rpx;
				background: rgba(0,0,0,0.05);
				border-radius: 32rpx;
				margin-left: 16rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				justify-content: center;
				.img{
					width: 16rpx;
					height: 8rpx;
					transition-property: transform;
					transition-duration: 200ms;
					transition-timing-function: linear;
				}
			}
		}
		&-type{
			margin-right: 32rpx;
			padding: 0 24rpx;
			box-sizing: border-box;
			height: 48rpx;
			background-color: rgba(0,0,0,0.05);
			border-radius: 32rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			.txt{
				font-size: 24rpx;
				color: #1D2129;
			}
			.down{
				margin-left: 8rpx;
				width: 16rpx;
				height: 8rpx;
				transition-property: transform;
				transition-duration: 200ms;
				transition-timing-function: linear;
			}
		}
	}
.main {
  width: 100%;
  height: 100%;
  background-color: #f7f8fa;
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
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    .submit_success {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      .success {
        width: 146rpx;
        height: 144rpx;
        margin-top: 64rpx;
      }
      .title {
        margin-top: 40rpx;
        font-size: 32rpx;
        color: #1d2129;
        font-weight: 500;
      }
      .second_title {
        font-size: 28rpx;
        color: #868d9c;
        margin-top: 8rpx;
      }
      .third {
        margin-top: 40rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        .left {
          width: 176rpx;
          height: 64rpx;
          background: #ffffff;
          border: 1px solid #dadce0;
          border-radius: 32rpx;
          font-size: 28rpx;
          color: #1d2129;
          font-weight: 500;
          line-height: 64rpx;
          text-align: center;
        }
        .right {
          margin-left: 24rpx;
          width: 260rpx;
          height: 64rpx;
          background: linear-gradient(107deg, #4a97e7, #b57aff 100%);
          border-radius: 32rpx;
          padding: 1rpx;
          box-sizing: border-box;
          display: flex;
          flex-direction: column;
          .child {
            flex: 1;
            flex-shrink: 0;
            min-height: 0;
            background-color: #fff;
            border-radius: 32rpx;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            .txt {
              font-size: 28rpx;
              font-weight: 500;
              background: linear-gradient(109deg, #4a97e7, #b57aff 100%);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
            }
          }
        }
      }
    }
    .empty_shop {
      width: 144rpx;
      height: 144rpx;
    }
    .first_title {
      margin-top: 32rpx;
      color: #000000;
      font-size: 32rpx;
      font-weight: 500;
    }
    .second_title {
      margin-top: 8rpx;
      font-size: 28rpx;
      color: #868d9c;
    }
    .new_add {
      margin-top: 64rpx;
      width: 288rpx;
      height: 88rpx;
      background: linear-gradient(105deg, #4a97e7, #b57aff 100%);
      border-radius: 96rpx;
      line-height: 88rpx;
      text-align: center;
      font-size: 32rpx;
      font-weight: 500;
      color: #fff;
    }
    .scroll_bar {
      width: 100%;
      padding: 16rpx 32rpx;
      box-sizing: border-box;
      overflow-x: auto;
      display: flex;
      flex-direction: row;
      align-items: center;
      .bar {
        margin-right: 16rpx;
        padding: 10rpx 24rpx;
        border-radius: 32rpx;
        .txt {
          display: block;
          font-size: 28rpx;
          color: #4e5769;
        }
      }
      .choice {
        background: linear-gradient(
          113deg,
          rgba(74, 151, 231, 0.15),
          rgba(181, 122, 255, 0.15) 100%
        );
        .txt {
          color: transparent;
          background: linear-gradient(115deg, #4a97e7, #b57aff 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
      }
    }
    .scroll {
      width: 100%;
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      .shop {
        width: 686rpx;
        height: 224rpx;
        margin: 0 auto;
        border-bottom: 1px solid #f0f2f5;
        display: flex;
        flex-direction: row;
        align-items: center;
        &-icon {
          width: 160rpx;
          height: 160rpx;
          border-radius: 24rpx;
        }
        &-content {
          margin-left: 28rpx;
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          height: 160rpx;
          .first {
            display: flex;
            flex-direction: row;
            margin-top: 2rpx;
            align-items: flex-start;
            .shop_name {
              flex: 1;
              flex-shrink: 0;
              min-width: 0;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
              font-size: 28rpx;
              font-weight: 500;
              color: #1d2129;
            }
            .distance {
              margin-top: 6rpx;
              font-size: 20rpx;
              color: #b4b7bf;
            }
          }
          .second {
            margin-top: 8rpx;
            font-size: 24rpx;
            color: #868d9c;
          }
          .third {
            margin-top: 24rpx;
            display: flex;
            flex-direction: row;
            align-items: center;
            .loca_icon {
              width: 20rpx;
              height: 24rpx;
            }
            .address {
              margin-left: 8rpx;
              flex: 1;
              flex-shrink: 0;
              min-width: 0;
              font-size: 24rpx;
              color: #868d9c;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }
          }
        }
      }
    }
  }
  .city_proposal {
    width: 750rpx;
    background-color: #ffffff;
    border-radius: 24rpx 24rpx 0px 0px;
    display: flex;
    flex-direction: column;
    align-items: center;
    .title {
      margin-top: 40rpx;
      width: 686rpx;
      font-size: 28rpx;
      color: #868d9c;
    }
    .chose_view {
      margin-top: 24rpx;
      width: 686rpx;
      height: 112rpx;
      background-color: #f7f8fa;
      border-radius: 24rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      .location_icon {
        width: 40rpx;
        height: 40rpx;
      }
      .city {
        margin-left: 16rpx;
        font-size: 32rpx;
        color: #1d2129;
        font-weight: 500;
      }
      .arrow_left {
        width: 16rpx;
        height: 24rpx;
      }
    }
    .submit_suggest {
      margin-top: 56rpx;
      margin-bottom: 100rpx;
      width: 686rpx;
      height: 88rpx;
      line-height: 88rpx;
      text-align: center;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;
      font-size: 28rpx;
      color: #fff;
      font-weight: 500;
    }
  }

  .float {
    width: 192rpx;
    height: 84rpx;
    background: linear-gradient(111deg, #4a97e7, #b57aff 100%);
    border-radius: 48rpx;
    box-shadow: 0rpx 16rpx 64rpx 0rpx rgba(129, 137, 244, 0.15);
    position: absolute;
    bottom: 80rpx;
    left: 280rpx;

    font-size: 28rpx;
    font-family: PingFang SC, PingFang SC-500;
    font-weight: 500;
    text-align: LEFT;
    color: #ffffff;

    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 100;
  }
}
</style>
