<template>
  <view class="popup_stature">
    <view class="top">
      <text @click="cancel" class="top-cancel">取消</text>
      <text class="top-title">选择{{ title }}</text>
      <text @click="confirm" class="top-confirm">确定</text>
    </view>
    <view class="second">
      <view @click="changeTab(0)" class="second-tab">
        <text class="item" :class="current == 0 ? 'checked' : ''"
          >海外</text
        >
      </view>
      <view @click="changeTab(1)" class="second-tab">
        <text class="item" :class="current == 1 ? 'checked' : ''">国内</text>
      </view>
    </view>
    <swiper
      :disable-touch="true"
      class="picker"
      :style="{ marginTop: '10rpx' }"
      :current="current"
      @change="tabChange"
    >
      <swiper-item>
        <picker-view
          immediate-change="true"
          :style="{ width: '100%', height: '100%' }"
          :value="second"
          @change="changePicker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in contryList"
              :key="'province_for' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in abroadCitys"
              :key="'city_for' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </swiper-item>
      <swiper-item>
        <picker-view
          immediate-change="true"
          :style="{ width: '100%', height: '100%' }"
          :value="first"
          @change="changePicker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in provinces"
              :key="'province' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in cityList"
              :key="'city' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </swiper-item>
    </swiper>
  </view>
</template>

<script lang="ts" setup>
import popup from "../uni-popup/popup.js";
import {
  ref,
  watch,
  getCurrentInstance,
  reactive,
  onMounted,
  computed,
  nextTick,
} from "vue";
import { api } from "@/common/request/index.ts";
import { useStore } from "vuex";
const store = useStore();
let cityCacheList = []; // 城市缓存

const { proxy } = getCurrentInstance();
const defaultCity = computed(() => store.state.user.defaultCity);
defineOptions({
  mixins: [popup],
});

const first = reactive([0, 0]);
const second = reactive([0, 0]);
const provinces = ref([]); // 省份
const cityList = ref([]); // 城市
const contryList = ref([]); // 国家
const abroadCitys = ref([]); // 城市
const current = ref(0);
const props = defineProps({
  // nowOption: {
  // 	type: Array<number>,
  // 	default: () => [0,0,0]
  // },
  title: {
    type: String,
    default: "地区",
  },
});
// watch(() => props.nowOption[0],(n,o) => {
// 	if (current.value != n) {
// 		current.value = n
// 	}
// })
const emit = defineEmits(["confirm", "cancel"]);

const changeTab = (ind: number) => {
  if (current.value != ind) {
    current.value = ind;
  }
};

// 获取国内省份列表数据
const getProvinces = async (
  cityInd: number = 0,
  firstFlag: boolean = false
) => {
  const res: any = await api.post("common/china_list");
  if (res.data != null && res.data.length > 0) {
    provinces.value = res.data;
    await getCitys(provinces.value[cityInd].id, 0);
    if (firstFlag) {
      nextTick(() => {
        first[0] = defaultCity.value.indexList[0];
        first[1] = defaultCity.value.indexList[1];
      });
    }
  }
};

//获取海外国家列表
const getCountries = async (
  cityInd: number = 0,
  firstFlag: boolean = false
) => {
  const res: any = await api.post("common/oversea_list");
  if (res.data != null && res.data.length > 0) {
    contryList.value = res.data;
    await getCitys(contryList.value[cityInd].id, 1);
    if (firstFlag) {
      nextTick(() => {
        second[0] = defaultCity.value.indexList[0];
        second[1] = defaultCity.value.indexList[1];
      });
    }
  }
};

// 获取城市列表
const getCitys = async (pid: number, type: number) => {
  const citys = cityCacheList.filter((item) => item.pid == pid);
  if (citys.length > 0) {
    if (type == 0) {
      cityList.value = citys;
    } else {
      abroadCitys.value = citys;
    }
  } else {
    const res: any = await api.post("common/area_list", {
      pid: pid,
    });
    if (res.data != null && res.data.length > 0) {
      if (type == 0) {
        cityList.value = res.data;
      } else {
        abroadCitys.value = res.data;
      }
      cityCacheList = cityCacheList.concat(res.data); // 临时存储城市数据
    }
  }
};

onMounted(() => {
  // if (props.nowOption[0] == 0) {
  // 	// 说明当前选的是国内省份
  // 	current.value = 0
  // 	cityList.value = []
  // 	getProvinces(props.nowOption[1])
  // 	getCountries(0)
  // 	first[0] = props.nowOption[1]
  // 	first[1] = props.nowOption[2]
  // } else if (props.nowOption[1] == 1) {
  // 	current.value = 1
  // 	abroadCitys.value = []
  // 	getCountries(props.nowOption[1])
  // 	getProvinces(0)
  // 	second[0] = props.nowOption[1]
  // 	second[1] = props.nowOption[2]
  // }
  if (defaultCity.value && defaultCity.value.china == 1) {
    // 说明当前选的是国内省份
    current.value = 1;
    cityList.value = [];
    getProvinces(defaultCity.value.indexList[0], true);
    getCountries(0);
    // nextTick(() => {
    // 	first[0] = defaultCity.value.indexList[0]
    // 	first[1] = defaultCity.value.indexList[1]
    // })
  } else if (defaultCity.value && defaultCity.value.china == 0) {
    current.value = 0;
    abroadCitys.value = [];
    getCountries(defaultCity.value.indexList[0], true);
    getProvinces(0);
    // nextTick(() => {
    // 	second[0] = defaultCity.value.indexList[0]
    // 	second[1] = defaultCity.value.indexList[1]
    // })
  }

  if (!defaultCity.value) {
    getCountries(0);
    getProvinces(0);
  }
});

// 滚动选中项变化
const changePicker = (event) => {
  let choice = event.detail.value;
  if (current.value == 1) {
    if (first[0] != choice[0]) {
      first[0] = choice[0];
      first[1] = 0; // 市重置第一个
      getCitys(provinces.value[choice[0]].id, 0);
    } else {
      first[1] = choice[1];
    }
    // first[1] = choice[1]
  } else {
    if (second[0] != choice[0]) {
      second[0] = choice[0];
      second[1] = 0; // 市重置第一个
      getCitys(contryList.value[choice[0]].id, 1);
    } else {
      second[1] = choice[1];
    }
    // second[1] = choice[1]
  }
};

// 取消
const cancel = () => {
  emit("cancel");
  proxy.popup.close();
};

const confirm = () => {
  let options = current.value == 1 ? first : second;
  let location = [];
  let cityId = null;
  if (current.value == 1) {
    location.push("国内");
    location.push(provinces.value[options[0]].name);
    location.push(cityList.value[options[1]].name);
    cityId = cityList.value[options[1]].id;
  }
  if (current.value == 0) {
    location.push(contryList.value[options[0]].name);
    location.push(abroadCitys.value[options[1]].name);
    cityId = abroadCitys.value[options[1]].id;
  }
  let cityName =
    current.value == 1
      ? cityList.value[options[1]].name
      : abroadCitys.value[options[1]].name;
  emit(
    "confirm",
    [current.value == 0 ? 1 : 0].concat(options),
    current.value == 1
      ? provinces.value[options[0]].name + cityList.value[options[1]].name
      : contryList.value[options[0]].name + abroadCitys.value[options[1]].name,
    cityId,
    cityName,
    location
  );
  proxy.popup.close();
};

// 切换中国、外国
const tabChange = (event: any) => {
  if (current.value != event.detail.current) {
    current.value = event.detail.current;
  }
};
</script>

<style lang="scss" scoped>
.popup_stature {
  width: 750rpx;
  height: 708rpx;
  background-color: #fff;
  border-radius: 32rpx 32rpx 0px 0px;
  padding: 0 32rpx;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  .top {
    display: flex;
    flex-direction: row;
    height: 112rpx;
    align-items: center;
    justify-content: space-between;
    &-cancel {
      font-size: 32rpx;
      color: #868d9c;
    }
    &-title {
      font-size: 32rpx;
      color: #1d2129;
      font-weight: 500;
    }
    &-confirm {
      font-size: 32rpx;
      color: #2c94ff;
      font-weight: 500;
    }
  }
  .second {
    display: flex;
    flex-direction: row;
    width: 100%;
    &-tab {
      flex: 1;
      flex-shrink: 0;
      min-width: 0;
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      .item {
        height: 88rpx;
        display: inline-block;
        border-bottom: 2px solid #fff;
        font-size: 32rpx;
        color: #868d9c;
        font-weight: 500;
        text-align: center;
        line-height: 88rpx;
      }
      .checked {
        border-bottom: 2px solid #2c94ff;
        color: #2c94ff;
      }
    }
  }
  .picker {
    flex: 1;
    flex-shrink: 0;
    min-height: 0;
    &-item {
      text-align: center;
      line-height: 48px;
      font-size: 16px;
      color: #1d2129;
    }
  }
}
</style>