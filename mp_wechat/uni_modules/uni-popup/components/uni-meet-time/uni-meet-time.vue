<template>
  <view class="popup_stature">
    <view class="top">
      <text @click="cancel" class="top-cancel">取消</text>
      <text class="top-title">选择见面时间</text>
      <text @click="confirm" class="top-confirm">确定</text>
    </view>
    <picker-view
      immediate-change="true"
      class="picker"
      :value="choice"
      @change="changePicker"
      indicator-style="height:48px"
    >
      <picker-view-column>
        <view
          class="picker-item"
          v-for="(item, ind) in monthList"
          :key="'month' + ind"
        >
          <text>{{ item }}</text>
        </view>
      </picker-view-column>
      <picker-view-column>
        <view
          class="picker-item"
          v-for="(item, ind) in dayList"
          :key="'day' + ind"
        >
          <text>{{ item }}</text>
        </view>
      </picker-view-column>
      <picker-view-column>
        <view
          class="picker-item"
          v-for="(item, ind) in timeList"
          :key="'time' + ind"
        >
          <text>{{ item }}</text>
        </view>
      </picker-view-column>
    </picker-view>
  </view>
</template>

<script lang="ts" setup>
import { api, formatDateForDay } from "@/common/request/index.ts";
import popup from "../uni-popup/popup.js";
import { ref, getCurrentInstance, onMounted, reactive } from "vue";

const monthList = ref([]);
const dayList = ref([]);
const timeList = ref([]);

const datObj = reactive({});

const choice = reactive([0, 0, 0]);

const { proxy } = getCurrentInstance();
defineOptions({
  mixins: [popup],
});

const props = defineProps({
  nowOption: {
    type: Array<number>,
    default: () => [0, 0],
  },
});
const emit = defineEmits(["confirm", "cancel"]);

const isInChina = (lat, lon) => {
  return lat >= 3.5 && lat <= 53.5 && lon >= 116.9 && lon <= 135.1;
};

onMounted(() => {
  api.post("/invitation/disable_meet_date").then((res: any) => {
    if (res.code == 1) {
      const forbidden = res.data;
      let effective = [];
      let nowDate = new Date();
      for (let x = 1; x < 14; x++) {
        nowDate.setDate(nowDate.getDate() + (x == 1 ? 0 : 1));
        effective.push(formatDateForDay(nowDate));
      }
      effective.filter((item) => {
        let ind = forbidden.findIndex((vitem) => vitem == item);
        if (ind >= 0) {
          return false;
        } else {
          return true;
        }
      });

      for (let item of effective) {
        let all = item.split("-");
        dayList.value.push(all[2]);
        if (monthList.value.length <= 0) {
          monthList.value.push(all[1]);
        } else {
          let vind = monthList.value.findIndex((xitem) => xitem == all[1]);
          if (vind < 0) {
            monthList.value.push(all[1]);
          }
        }

        if (all[1] in datObj) {
          datObj[all[1]].push(all[2]);
        } else {
          datObj[all[1]] = [];
          datObj[all[1]].push(all[2]);
        }
      }

      uni.getLocation({
        type: "wgs84",
        isHighAccuracy: true,
        highAccuracyExpireTime: 3000,
        success: (success) => {
          console.log(success);
          console.log("当前位置的经度：" + success.longitude);
          console.log("当前位置的纬度：" + success.latitude);
          let isChinaBo = isInChina(39.9046, 116.407001);
          // let isChinaBo = isInChina(success.latitude, success.longitude);
          filterTimeList(isChinaBo);
        },
      });
    }
  });
});

const filterTimeList = (isChinaBo) => {
  const now = new Date();
  let ishalfHour = false;
  let currentHour = now.getHours();
  let currentMinute = now.getMinutes();

  console.log(currentHour, "currentHour--");
  console.log(currentMinute, "currentMinute---");

  // 半夜不约
  if (currentHour < 10) {
    currentHour = 10;
  } else {
    if (currentMinute >= 30) {
      currentHour = currentHour + 1;
    } else {
      ishalfHour = true;
    }
  }

  // if (!isChinaBo) {
  //   currentHour = currentHour + 1;
  // }

  for (let c = currentHour; c < 22; c++) {
    timeList.value.push(c.toString().padStart(2, "0") + ":00");
    timeList.value.push(c.toString().padStart(2, "0") + ":30");
  }

  if (ishalfHour) {
    timeList.value.shift();
  }
};

// 滚动选中项变化

// 取消
const cancel = () => {
  emit("cancel");
  proxy.popup.close();
};

const confirm = () => {
  emit(
    "confirm",
    monthList.value[choice[0]] +
      "-" +
      dayList.value[choice[1]] +
      " " +
      timeList.value[choice[2]]
  );
  proxy.popup.close();
};

const changePicker = (e) => {
  // 跨月才做处理 并且需要改变月份
  if (monthList.value.length > 1 && choice[0] != e.detail.value[0]) {
    const { detail } = e;
    const index = detail.value[0];
    dayList.value = datObj[monthList.value[index]];
    Object.assign(choice, [detail.value[0], 0, detail.value[2]]);
  } else {
    Object.assign(choice, e.detail.value);
  }

  if (e.detail.value[1] == 0 && e.detail.value[0] == 0) {
    timeList.value = [];
    filterTimeList();
  } else {
    timeList.value = [];
    for (let c = 10; c < 22; c++) {
      timeList.value.push(c.toString().padStart(2, "0") + ":00");
      timeList.value.push(c.toString().padStart(2, "0") + ":30");
    }
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
    width: 100%;
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