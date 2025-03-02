<template>
  <view class="main">
    <image class="main-top" :src="app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'"></image>
    <view class="main-base">
      <uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false"
        background-color="transparent" title="基本资料" :statusBar="true"></uni-nav-bar>
      <view class="scroll">
        <view class="data">          
          <view v-for="(item, index) in optionsTop"
            @click="openSelect(item)"
            class="data-item"
            :key="'optionTop' + index"
            :class="{ bd: index < optionsTop.length - 1 }">
            <text class="label">{{ item.label}}</text>
            <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',}">
              <input
                v-if="item.type == 'input'"
                type="text"
                v-model="basicInfo[item.key]"
                class="input"/> 
              <text v-else-if="item.key == 'height'" class="value">{{ basicInfo[item.showKey] }}cm</text>
              <text v-else-if="item.key == 'weight'" class="value">{{ basicInfo[item.showKey] }}kg</text>
              <text v-else-if="item.key == 'activeRegion'" class="value">
                {{ active_Name == "" ? "请选择活跃区域" : active_Name }}</text>
              <text v-else class="value">{{ basicInfo[item.showKey] }}</text>
              <image v-if="item.type == 'select'"
                src="/static/mine_center/arrow_left.png" class="arrow"></image>
            </view>
          </view>
        </view>
        <view class="data" :style="{ marginTop: '24rpx' }">
          <view
            v-for="(item, index) in optionsBottom"
            @click="openSelect(item)"
            class="data-item"
            :key="'optionBottom' + index"
            :class="{ bd: index < optionsBottom.length - 1 }">
            <text class="label">{{ item.label}}</text>
            <view :style="{
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'center',}"
            >
              <input
                v-if="item.type == 'input'"
                type="text"
                v-model="basicInfo[item.key]"
                class="input"
              />
              <text v-else class="value">{{ basicInfo[item.showKey] }}</text>
              <image
                v-if="item.type == 'select'"
                src="/static/mine_center/arrow_left.png"
                class="arrow"
              ></image>
            </view>
          </view>
        </view>

        <view class="data" :style="{ marginTop: '24rpx' }">
          <view class="data-item">
            <view class="code-lable">手机号</view>
            <view class="code-value">
              <input
                style="text-align: right"
                type="number"
                v-model="phone"
                class="input"
                placeholder="请输入手机号"
              />
            </view>
          </view>
        </view>

        <view class="data" :style="{ marginTop: '24rpx' }">
          <view class="data-item">
            <view class="code-lable">邮箱</view>
            <view class="code-value">
              <input
                :disabled="!updataPhone"
                style="text-align: right"
                type="input"
                v-model="email"
                class="input"
                placeholder="请输入邮箱"
              />

              <text
                v-if="!updataPhone"
                class="updata"
                @click="updataPhone = true"
                >修改</text
              >
            </view>
          </view>

          <view class="data-item" v-if="updataPhone">
            <view class="code-lable">验证码</view>
            <view class="code-value">
              <input
                style="text-align: right"
                type="text"
                v-model="email_code"
                class="input phone_code"
                placeholder="填写验证码"
              />

              <template>
                <view class="segmentation"></view>
                <text class="code" @click="getSenCode">{{
                  phone_text
                }}</text></template
              >
            </view>
          </view>
        </view>

        <view class="data" :style="{ marginTop: '24rpx' }">
          <view
            v-for="(item, index) in selfList"
            :key="'self' + index"
            class="data-item"
            :class="{ bd: index < selfList.length - 1 }"
            :style="{
              alignItems:
                item.type == 3 || item.type == 10 ? 'flex-start' : 'center',
            }"
          >
            <text :style="{marginTop: item.type == 3 || item.type == 10 ? '10rpx' : '0',}"
              class="label">{{ item.name}}</text>
            <input v-if="item.type == 2"
              type="text"
              v-model="basicInfo[item.key]"
              class="input"
              placeholder="请输入昵称"/>
            <textarea v-else-if="item.type == 3"
              v-model="basicInfo[item.key]"
              :style="{
                height: '140rpx',
                flex: 1,
                fontSize: '28rpx',
                padding: '10rpx 10rpx',
                boxSizing: 'border-box',
              }"
            ></textarea>
            <picker v-else-if="item.type == 5"
              @change="changePickerDay($event, item.key)"
              mode="date"
              :style="{ flex: 1, height: '100%' }"
              :value="basicInfo[item.key]"
            >
              <view :style="{display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'center',
                  'justify-content': 'flex-end',
                  width: '100%',
                  height: '108rpx',
                }">
                <text class="choose_val">{{ basicInfo[item.key] }}</text>
                <image class="arrow_left" src="/static/mine_center/arrow_left.png"></image>
              </view>
            </picker>
            <input v-else-if="item.type == 7"
              v-model="basicInfo[item.key]"
              type="digit"
              :style="{ flex: 1, fontSize: '28rpx', textAlign: 'right' }"
            />
            <uni-region-city
              :areaName="basicInfo[item.key + '_name'] || item.content.name"
              :title="item.name"
              v-else-if="item.type == 8"
              :style="{ flex: 1, height: '108rpx' }"
              @confirm="confirmRegion($event, item.key)"
            ></uni-region-city>
            <picker
              v-else-if="item.type == 9"
              @change="choosePickerValue($event, item.key, item.content)"
              mode="selector"
              :range="item.content"
              range-key="title"
              :style="{ flex: 1, height: '100%' }"
            >
              <view
                :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'center',
                  'justify-content': 'flex-end',
                  width: '100%',
                  height: '108rpx',
                }"
              >
                <text class="choose_val">{{
                  basicInfo[item.key + "_name"]
                }}</text>
                <image
                  class="arrow_left"
                  src="/static/mine_center/arrow_left.png"
                ></image>
              </view>
            </picker>
            <uni-data-checkbox
              :style="{ flex: 1, marginLeft: '20rpx', fontSize: '28rpx' }"
              multiple
              v-else-if="item.type == 10"
              :map="{ text: 'title', value: 'value' }"
              :localdata="item.content"
              :value="basicInfo[item.key].split(',')"
              @change="changeMultiPicker($event, item.key)"
            ></uni-data-checkbox>
          </view>
        </view>
        <view class="saveView">
          <view @click="saveNow" class="btn">
            <text>保存</text>
          </view>
        </view>
      </view>
    </view>

    <uni-popup ref="salaryPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(salaryPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择年收入</text>
          <text @click="confirmVal('salary')" class="top-confirm">确定</text>
        </view>
        <picker-view
          immediate-change="true"
          :value="[caches.salary]"
          @change="changePicker('salary', $event)"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in salaryList"
              :key="'picker_salary' + ind"
            >
              <text>{{ item }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

    <uni-popup ref="workPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(workPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择工作情况</text>
          <text @click="confirmVal('work_type')" class="top-confirm">确定</text>
        </view>
        <picker-view
          immediate-change="true"
          :value="[caches.work_type]"
          @change="changePicker('work_type', $event)"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in workTypes"
              :key="'picker_work_type' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

    <uni-popup ref="eduPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(eduPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择学历</text>
          <text @click="confirmVal('education_type')" class="top-confirm"
            >确定</text
          >
        </view>
        <picker-view
          immediate-change="true"
          :value="[caches.education_type]"
          @change="changePicker('education_type', $event)"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in eduList"
              :key="'picker_education_type' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>
    <uni-popup ref="constellPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(constellPopup)" class="top-cancel"
            >取消</text
          >
          <text class="top-title">选择星座</text>
          <text @click="confirmVal('constellation')" class="top-confirm"
            >确定</text
          >
        </view>
        <picker-view
          immediate-change="true"
          :value="[caches.constellation]"
          @change="changePicker('constellation', $event)"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in constellList"
              :key="'picker_constellation' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>
    <uni-popup ref="heightPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(heightPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择身高</text>
          <text @click="confirmVal('height')" class="top-confirm">确定</text>
        </view>
        <picker-view
          immediate-change="true"
          :value="[caches.height]"
          @change="changePicker('height', $event)"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in heightList"
              :key="'picker_height' + ind"
            >
              <text>{{ item }}cm</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>
    <uni-popup ref="weightPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(weightPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择体重</text>
          <text @click="confirmVal('weight')" class="top-confirm">确定</text>
        </view>
        <picker-view
          immediate-change="true"
          :value="[caches.weight]"
          @change="changePicker('weight', $event)"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in weightList"
              :key="'picker_weight' + ind"
            >
              <text>{{ item }}kg</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>
    <uni-popup ref="birthPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(birthPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择生日</text>
          <text @click="confirmVal('birth')" class="top-confirm">确定</text>
        </view>
        <!-- @change="changePicker('nowBirth',$event)" -->
        <picker-view
          immediate-change="true"
          :value="caches.birth"
          @change="changePicker('birth', $event)"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in years"
              :key="'year' + ind"
            >
              <text>{{ item }}年</text>
            </view>
          </picker-view-column>
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in months"
              :key="'month' + ind"
            >
              <text>{{ item }}月</text>
            </view>
          </picker-view-column>
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in daysList"
              :key="'day' + ind"
            >
              <text>{{ item }}日</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>
    <uni-popup ref="areaPopup" type="bottom">
      <city-select @confirm="confirmArea" title="所在地"></city-select>
    </uni-popup>
    <uni-popup ref="homePopup" type="bottom">
      <city-select @confirm="confirmHomeTown" title="家乡"></city-select>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, nextTick, reactive, ref, computed } from "vue";
import { onLoad } from "@dcloudio/uni-app";
import { useStore } from "vuex";
import validate from "@/js_sdk/a-hua-validate/index.js";
import { getCode } from "@/common/request/api.js";

const store = useStore();
const userInfo = computed(() => store.state.user.userInfo);

const birthPopup = ref();
const heightPopup = ref();
const areaPopup = ref();
const weightPopup = ref();
const constellPopup = ref();
const homePopup = ref();
const eduPopup = ref();
const workPopup = ref();
const salaryPopup = ref();

const birthList = ref([]);
const heightList = ref([]);
const weightList = ref([]);
const constellList = ref([]);
const eduList = ref([]); // 学历
const workTypes = ref([]);

const years = ref([]);
const months = ref([]);
const daysList = ref([]);

const app = getCurrentInstance().appContext.app;
const salaryList = ref([]); // 年收入写死
const caches = reactive({
  weight: 0, // 重量
  height: 0, // 身高
  birth: [0, 0, 0], //当前生日
  constellation: 0, //星座
  education_type: 0,
  work_type: 0,
  salary: 0,
}); // 索引集
const optionsTop = reactive([
  {
    label: "昵称",
    key: "nickname",
    showKey: "nickname",
    type: "input",
    disabled: false,
  },
  {
    label: "生日",
    key: "birth",
    showKey: "birth",
    type: "select",
    popInfo: null,
  },
 
  {
    label: "所在地",
    key: "permanent_area",
    showKey: "permanent_area_name",
    type: "select",
    popInfo: null,
  },
  {
    label: "身高",
    key: "height",
    showKey: "height",
    type: "select",
    popInfo: null,
  },
  {
    label: "体重",
    key: "weight",
    showKey: "weight",
    type: "select",
    popInfo: null,
  },
  {
    label: "星座",
    key: "constellation",
    showKey: "constellation_name",
    type: "select",
    popInfo: null,
  },
  {
    label: "家乡",
    key: "hometown",
    showKey: "hometown_name",
    type: "select",
    popInfo: null,
  },
  {
    label: "活跃区域",
    key: "activeRegion",
    type: "select",
  },
]);

const optionsBottom = [
  {
    label: "学校",
    key: "school",
    showKey: "school",
    type: "input",
  },
  {
    label: "学历",
    key: "education_type",
    showKey: "education_type_name",
    type: "select",
    popInfo: null,
  },
  {
    label: "工作情况",
    key: "work_type",
    showKey: "work_type_name",
    type: "select",
    popInfo: null,
  },
  {
    label: "年收入",
    key: "salary",
    showKey: "salary",
    type: "select",
    popInfo: null,
  },
];
const basicInfo = reactive({
  nickname: null, // 昵称
  birth: null, // 生日
  height: null, //身高
  permanent_area: null, //所在地
  permanent_area_name: null, // 所在地展示
  weight: null, //体重
  constellation: null, //星座
  constellation_name: null,
  hometown: null, // 家乡
  hometown_name: null,
  school: null, // 学校
  education_type: null, // 学历类型
  education_type_name: null,
  work_type: null, // 工作类型
  work_type_name: null,
  salary: null, // 年收入
});

const selfList = ref([]);

const formRules = reactive({
  nickname: { message: "昵称不能为空", required: true, type: "string" },
  birth: { required: true, type: "string", message: "请选择生日" },
  height: { required: true, message: "请选择身高" },
  weight: { required: true, message: "请选择体重" },
  permanent_area: { required: true, message: "请选择所在地" },
  constellation: { required: true, message: "请选择星座" },
  hometown: { required: true, message: "请选择家乡" },
});

const updataPhone = ref(false); // 修改手机号
const phone = ref(""); // 手机号
const email = ref(""); // 邮箱
const email_code = ref(""); // 邮箱验证码
const phone_code = ref(""); // 手机号验证码
const phone_text = ref("获取验证码");
const phone_time = ref(60);
const phone_isGet = ref(true); // 是否可以获取验证码
let phone_Interval = null;

const active_point = ref(""); // 活跃区域
const active_Name = ref(""); // 活跃区域 地名

// 获取验证码
const getSenCode = async () => {
  if (phone_isGet.value) {
    const res = await getCode({
      email: email.value,
    });
    if (res.code != 1) {
      uni.showToast({
        title: "发送失败",
        icon: "none",
      });
    } else {
      uni.showToast({
        title: "发送成功",
        icon: "none",
      });
      phone_isGet.value = false;
      phone_text.value = `${phone_time.value}后可获取`;
      phone_Interval = setInterval(() => {
        phone_time.value--;
        if (phone_time.value == 0) {
          phone_isGet.value = true;
          phone_text.value = `获取验证码`;
          clearInterval(phone_Interval);
          return;
        } else {
          phone_text.value = `${phone_time.value}后可获取`;
        }
      }, 1000);
      console.log(res);
    }
  }
};
onLoad(() => {
  api.post("/common/salary_list").then((pres: any) => {
    if (pres.code == 1) {
      salaryList.value = pres.data;
      if (userInfo != null) {
        nextTick(() => {
          caches.salary = salaryList.value.findIndex(
            (item) => item == userInfo.value.salary
          );
        });
      }
    }
  });
  if (userInfo != null) {
    console.log(userInfo.value, "userInfo---");
    phone.value = userInfo.value.contact_mobile;
    email.value = userInfo.value.email;
    active_Name.value = userInfo.value.active_point_text;
    active_point.value = userInfo.value.active_point;
    let homes = userInfo.value.howntown_last_id.split(",");

    basicInfo.nickname = userInfo.value.nickname;
    basicInfo.birth = userInfo.value.birth;

    basicInfo.height = userInfo.value.height;
    basicInfo.weight = userInfo.value.weight;

    basicInfo.permanent_area = userInfo.value.area_id;
    basicInfo.permanent_area_name = userInfo.value.area;

    basicInfo.constellation = userInfo.value.constellation;
    basicInfo.constellation_name = userInfo.value.constellation_text;

    basicInfo.hometown = homes[homes.length - 1];
    basicInfo.hometown_name = userInfo.value.hometown;

    basicInfo.school = userInfo.value.school;
    basicInfo.education_type = userInfo.value.education_type;
    basicInfo.education_type_name = userInfo.value.education_type_text;
    basicInfo.work_type = userInfo.value.work_type;
    basicInfo.work_type_name = userInfo.value.work_type_text;
    basicInfo.salary = userInfo.value.salary;
  }
  const birthList = basicInfo.birth.split("-");
  for (let year = 1970; year <= new Date().getFullYear(); year++) {
    years.value.push(year);
  }
  for (let month = 1; month <= 12; month++) {
    months.value.push(month);
  }
  for (let x = 1; x <= new Date(birthList[0], birthList[1], 0).getDate(); x++) {
    daysList.value.push(x);
  }
  for (let a = 140; a <= 200; a++) {
    heightList.value.push(a);
  }
  for (let x = 35; x <= 100; x++) {
    weightList.value.push(x);
  }
  
  nextTick(() => {
    optionsTop[1].popInfo = birthPopup.value;
    optionsTop[2].popInfo = areaPopup.value;
    optionsTop[3].popInfo = heightPopup.value;
    optionsTop[4].popInfo = weightPopup.value;
    optionsTop[5].popInfo = constellPopup.value;
    optionsTop[6].popInfo = homePopup.value;
    optionsBottom[1].popInfo = eduPopup.value;
    optionsBottom[2].popInfo = workPopup.value;
    optionsBottom[3].popInfo = salaryPopup.value;
    setTimeout(() => {
      const yearInd = years.value.findIndex((item) => item == birthList[0]);
      caches.birth = [
        yearInd,
        parseInt(birthList[1]) - 1,
        parseInt(birthList[2]) - 1,
      ];
      const weightIndex = weightList.value.findIndex(
        (item) => item == userInfo.value.weight
      );
      const heightIndex = heightList.value.findIndex(
        (item) => item == userInfo.value.height
      );
      caches.height = heightIndex;
      caches.weight = weightIndex;
    }, 300);
  });

  // 获取星座
  getConstellations();
  getEduList();
  getWorks();
  for (const element of userInfo.value.extra_info) {
    if (element.type == 8 || element.type == 9) {
      basicInfo[element.key + "_name"] = element.formatter_value;
    }
    basicInfo[element.key] = element.real_value;
  }
  api.post("user/improve_field_list").then((vres: any) => {
    if (vres.code == 1) {
      const schoolInfo = vres.data.regular.find(
        (uitem) => uitem.key == "school"
      );
      const eduInfo = vres.data.regular.find(
        (uitem) => uitem.key == "education_type"
      );
      const workInfo = vres.data.regular.find(
        (uitem) => uitem.key == "work_type"
      );
      const salaryInfo = vres.data.regular.find(
        (uitem) => uitem.key == "salary"
      );
      if (schoolInfo.is_require == 1) {
        formRules["school"] = {
          required: true,
          type: "string",
          message: "学校不得为空",
        };
      }
      if (eduInfo.is_require == 1) {
        formRules["education_type"] = { required: true, message: "请选择学历" };
      }
      if (workInfo.is_require == 1) {
        formRules["work_type"] = { required: true, message: "请选择工作" };
      }
      if (salaryInfo.is_require == 1) {
        formRules["salary"] = {
          required: true,
          type: "string",
          message: "请选择年收入",
        };
      }
      selfList.value = vres.data.self;
      for (const xdom of vres.data.self) {
        if (xdom.is_require == 1) {
          formRules[xdom.key] = {
            required: true,
            type: "string",
            message: xdom.name + "不得为空",
          };
        }
        if (xdom.value != null && xdom.value.length > 0) {
          if (basicInfo[xdom.key] == null || basicInfo[xdom.key].length <= 0) {
            basicInfo[xdom.key] = xdom.value;
            if (xdom.type == 9) {
              let selectedInfo = xdom.content.find(
                (uitem) => uitem.value == xdom.value
              );
              basicInfo[xdom.key + "_name"] = selectedInfo.title;
            }
            if (xdom.type == 8) {
              basicInfo[xdom.key + "_name"] = xdom.content.name;
            }
          }
        }
      }
    }
  });
});
const navBack = () => {
  uni.navigateBack();
};

// 自定义选择日期
const changePickerDay = (e, key) => {
  basicInfo[key] = e.detail.value;
};

// 自定义区域
const confirmRegion = (e, key) => {
  basicInfo[key] = e.cityId;
};

// 自定义多选
const changeMultiPicker = (e, key) => {
  basicInfo[key] = e.detail.value.join(",");
};

// 自定义单选
const choosePickerValue = (e, key, list) => {
  basicInfo[key] = list[e.detail.value].value;
  basicInfo[key + "_name"] = list[e.detail.value].title;
};

// 选择所在地
const confirmArea = (options: Array<number>, address: string, cityId: any) => {
  basicInfo.permanent_area_name = address;
  basicInfo.permanent_area = cityId;
};

// 选择家乡
const confirmHomeTown = (
  options: Array<number>,
  address: string,
  cityId: any
) => {
  basicInfo.hometown_name = address;
  basicInfo.hometown = cityId;
  // setTimeout(() => {
  // 	weightPopup.value.open()
  // },300)
};

const changePicker = async (key: string, event: any) => {
  let choice = event.detail.value;
  if (
    key == "birth" &&
    (caches[key][0] != choice[0] || caches[key][1] != choice[1])
  ) {
    let list = [];
    choice[2] = 0;
    for (
      let x = 1;
      x <=
      new Date(years.value[choice[0]], months.value[choice[1]], 0).getDate();
      x++
    ) {
      list.push(x);
    }
    daysList.value = list;
  }
  if (key == "birth") {
    caches[key] = choice;
  } else {
    caches[key] = event.detail.value[0];
  }
};

// 打开选择弹窗
const openSelect = (item: any) => {
  // 打印传入的参数 item
  console.log('传入的参数 item:', item);

  if (item.key == "activeRegion") {
    uni.getLocation({
      type: "wgs84",
      isHighAccuracy: true,
      highAccuracyExpireTime: 6000,
      success: (info) => {
        console.log(info);
        const latitude = info.latitude;
        const longitude = info.longitude;
        uni.chooseLocation({
          latitude: latitude,
          longitude: longitude,
          success: (res) => {
            console.log(res);
            const { latitude, longitude, name } = res;
            const list = [longitude, latitude];
            active_point.value = list.toString();
            active_Name.value = name;
          },
        });
      },
    });
  } else if (item.type == "select") {
    item.popInfo.open();
  }
};

const closePopup = (e: any) => {
  e.close();
};

const getConstellations = async () => {
  const res: any = await api.post("common/constellation_type_list");
  if (res.code == 1) {
    constellList.value = res.data;
    nextTick(() => {
      caches.constellation = res.data.findIndex(
        (item) => item.value == userInfo.value.constellation
      );
    });
  }
};

const getEduList = async () => {
  const res: any = await api.post("/common/education_type_list");
  if (res.code == 1) {
    eduList.value = res.data;
    nextTick(() => {
      caches.education_type = res.data.findIndex(
        (item) => item.value == userInfo.value.education_type
      );
    });
  }
};

const getWorks = async () => {
  const res: any = await api.post("common/work_type_list");
  if (res.code == 1) {
    workTypes.value = res.data;
    nextTick(() => {
      caches.work_type = res.data.findIndex(
        (item) => item.value == userInfo.value.work_type
      );
    });
  }
};

const saveNow = async () => {
  validate(basicInfo, formRules)
    .then(async (hInfo) => {
      if (active_point.value == "") {
        uni.showToast({
          icon: "none",
          title: "请选择活跃区域",
        });
        return;
      }
      basicInfo.active_point_text = active_Name.value;
      basicInfo.active_point = active_point.value;
      basicInfo.contact_mobile = phone.value;
      if (updataPhone.value) {
        basicInfo.email_code = email_code.value;
      }
      basicInfo.email = email.value;

      console.log("basicInfo:", basicInfo);
      const res: any = await api.post("user/edit", basicInfo);
      if (res.code == 1) {
        uni.showToast({
          icon: "none",
          title: "basicInfo保存成功",
        });
        store.dispatch("refreshInfo");
 
      }
	  uni.navigateBack();
    })
 
};

const confirmVal = (key: string, tab = 0) => {
  switch (key) {
    case "birth":
      const res = caches["birth"];
      basicInfo[key] = [
        years.value[res[0]],
        months.value[res[1]],
        daysList.value[res[2]],
      ].join("-");
      break;
    case "height":
      basicInfo.height = heightList.value[caches.height];
      break;
    case "constellation":
      basicInfo.constellation = constellList.value[caches.constellation].value;
      basicInfo.constellation_name =
        constellList.value[caches.constellation].name;
      break;
    case "weight":
      basicInfo.weight = weightList.value[caches.weight];
      break;
    case "education_type":
      basicInfo.education_type = eduList.value[caches.education_type].value;
      basicInfo.education_type_name = eduList.value[caches.education_type].name;
      break;
    case "work_type":
      basicInfo.work_type = workTypes.value[caches.work_type].value;
      basicInfo.work_type_name = workTypes.value[caches.work_type].name;
      break;
    case "salary":
      basicInfo.salary = salaryList.value[caches.salary];
      break;
  }
  let basicItem = optionsTop.find((item) => item.key == key);
  if (basicItem == null) {
    basicItem = optionsBottom.find((item) => item.key == key);
  }
  if (basicItem != null) {
    basicItem.popInfo.close();
  }
  // optionsTop[1].popInfo = birthPopup.value
  // optionsTop[2].popInfo = heightPopup.value
  // optionsTop[3].popInfo = areaPopup.value
  // optionsTop[4].popInfo = weightPopup.value
  // optionsTop[5].popInfo = constellPopup.value
  // optionsTop[6].popInfo = homePopup.value

  // optionsBottom[1].popInfo = eduPopup.value
  // optionsBottom[2].popInfo = workPopup.value
  // optionsBottom[3].popInfo = salaryPopup.value
  // let itemInd = options1.value.findIndex((item) => (item.key == key));
  // options1.value[itemInd].popup.close()
  // if (itemInd + 1<options1.value.length) {
  // 	setTimeout(() => {
  // 		options1.value[itemInd + 1].popup.open()
  // 	},300)
  // } else {
  // 	educationPopup.value.open()
  // }
};
</script>

<style lang="scss" scoped>
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
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    .scroll {
      width: 100%;
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      .saveView {
        margin-top: 24rpx;
        width: 750rpx;
        height: 180rpx;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-shrink: 0;
        .btn {
          margin-top: 16rpx;
          width: 686rpx;
          height: 88rpx;
          line-height: 88rpx;
          text-align: center;
          background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
          border-radius: 44rpx;
          font-size: 28rpx;
          font-weight: 500;
          color: #fff;
        }
      }
    }
    .data {
      width: 686rpx;
      background-color: #ffffff;
      border-radius: 24rpx;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      padding: 0 32rpx;
      box-sizing: border-box;
      &-item {
        min-height: 108rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        .label {
          font-size: 28rpx;
          color: #1d2129;
        }
        .code-lable {
          font-size: 28rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          color: #1d2129;
        }
        .code-value {
          font-size: 28rpx;
          display: flex;
          align-items: center;
          color: #4e5769;

          .code {
            // min-width: 150rpx;
            font-size: 28rpx;
            font-family: PingFang SC, PingFang SC-400;
            font-weight: 400;
            color: #2c94ff;
          }

          .updata {
            font-size: 28rpx;
            font-family: PingFang SC, PingFang SC-400;
            font-weight: 400;
            color: #2c94ff;
            padding-left: 10rpx;
          }

          .segmentation {
            width: 0rpx;
            height: 28rpx;
            border: 1rpx solid #e8eaef;
            margin: 0 10rpx;
          }
        }

        .value {
          font-size: 28rpx;
          color: #4e5769;
        }
        .arrow {
          width: 16rpx;
          height: 24rpx;
          margin-left: 16rpx;
        }
        .input {
          font-size: 28rpx;
          color: #4e5769;
          flex: 1;
          min-width: 0;
          margin-left: 10rpx;
          text-align: right;
        }
        .choose_val {
          font-size: 28rpx;
          color: #4e5769;
        }
        .arrow_left {
          width: 16rpx;
          height: 24rpx;
          margin-left: 16rpx;
        }
      }
      .bd {
        border-bottom: 1px solid #e8eaef;
      }
    }
  }

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
}
::v-deep .uni-numbox__value {
  font-size: 28rpx;
}
::v-deep .checklist-text {
  font-size: 28rpx !important;
}
</style>
