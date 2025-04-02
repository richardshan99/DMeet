<template>
  <view class="main">
    <image class="main-top":src="app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'"></image>
    <view class="main-base">
      <uni-nav-bar :border="false" title="" background-color="transparent" :status-bar="true"></uni-nav-bar>
 
      <scroll-view :style="{ flex: 1, minHeight: 0, width: '90%' }" :scroll-y="true">
        <view :style="{display: 'flex', flexDirection: 'column', alignItems: 'center',}">
          <text class="page_title">完善基本资料</text>
          <text class="intro">咱这儿先见面再聊天！资料越完整，才有小哥哥小姐姐主动邀你哦～</text>
          
          <view class="head">
            <text class="title">我的照片</text>
            <drag-img keyName="path" v-model="avatarList" :cols="4" :style="{ marginTop: '24rpx' }"></drag-img>
			      <text class="meno">传照片首张当头像，多多益善，展现最有魅力的你~</text>
          </view>

          <view class="identity" :style="{ marginTop: '24rpx' }"> 
            <view class="identity-item">
              <text class="label">昵称</text>
              <input type="text" v-model="formData.nickname" class="input" placeholder="给自己起个好听的昵称"/>
            </view>

            <view class="identity-item"> 
              <text class="label">性别<text class="label-intro">（选择后不可更改）</text></text>
              <view :style="{display: 'flex', flexDirection: 'row', alignItems: 'center',}">
                <text @click="changeSex(1)" class="option":class="formData.gender == 1 ? 'active' : ''">男</text>
                <text @click="changeSex(2)" class="option":class="formData.gender == 2 ? 
                'active' : ''":style="{ marginLeft: '20rpx' }">女</text>
              </view>
            </view>

            <!--填写生日、所在地、身高、体重、星座、活跃地区-->
            <view class="identity-item" 
              v-for="(item, ind) in options1" :key="'option1' + ind" 
              @click="openPopup(item.popup, item.key)"
              :style="{borderBottom:ind == options1.length - 1 ? 'none' : '1px solid #E8EAEF;',}">
              <text class="label"> {{item.title}} </text>
              <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',
                    'justify-content': 'flex-end', flex: 1, height: '100%',}">
                <text class="choose_val" v-if="item.key == 'activeRegion'">
                  {{ active_Name == "" ? "请选择活跃区域" : active_Name }}</text>
                <text v-else class="choose_val">{{ formData[item.show]}}
                  {{item.key == "height" && formData[item.show] != null? "cm": ""}}
                  {{item.key == "weight" && formData[item.show] != null? "kg": ""}}
                </text>
                <image class="arrow_left" src="/static/mine_center/arrow_left.png"></image>
              </view>
            </view>
          </view>

 
		
         <view class="identity" :style="{ marginTop: '24rpx' }">
             <view class="identity-item" @click="openPopup(educationPopup)">
              <text class="label">学历</text>
              <view :style="{display: 'flex', flexDirection: 'row', alignItems: 'center', 'justify-content': 'flex-end', flex: 1, height: '100%',}">
                <text class="choose_val">{{formData.education_type_name}}</text>
                <image class="arrow_left" src="/static/mine_center/arrow_left.png"></image>
              </view>
            </view>

            <view class="identity-item" @click="openPopup(workPopup)">
              <text class="label">职业</text>
              <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center', 'justify-content': 'flex-end', flex: 1,height: '100%',}">
                <text class="choose_val">{{ formData.work_type_name }}</text>
                <image  class="arrow_left" src="/static/mine_center/arrow_left.png"></image>
              </view>
            </view>

            <view class="identity-item" @click="openPopup(salaryPopup)">
              <text class="label">年收入</text>
              <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',
                  'justify-content': 'flex-end',flex: 1,height: '100%',}">
                <text class="choose_val">{{ formData.salary }}</text>
                <image class="arrow_left" src="/static/mine_center/arrow_left.png"></image>
              </view>
            </view>
			
			<view class="identity-item">
			  <text class="label">学校<text class="label-intro">（选填）</text></text>
			  <input type="text" v-model="formData.school"
			    class="input" placeholder="填了没准校友来撩～"/>
			</view>
			
			<view class="identity-item" @click="openPopup(homeTownPopup)">
			    <text class="label"> 籍贯<text class="label-intro">（选填）</text></text>
			    <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',
			          'justify-content': 'flex-end', flex: 1, height: '100%',}">	
			      <text class="choose_val">{{ formData.hometown}}</text>
			      <image class="arrow_left" src="/static/mine_center/arrow_left.png"></image>
			    </view>
			</view>			
          </view>

		 <view class="phone-code-box">
			 <view class="cell">
			   <view class="lable">邮箱</view>
			   <view class="value">
				 <input style="text-align: right;" type="text" 
					v-model="email" class="input" placeholder="有消息和邀约会发通知~"/>
			   </view>
			 </view>
		 
			 <view class="cell">
			   <view class="lable">验证码</view>
			   <view class="code">
				 <input style="text-align: right" type="text" v-model="email_code"
				   class="input phone_code" placeholder="填写验证码"/>
				 <view class="segmentation"></view>
				 <view class="text" @click="getSenCode"> {{ phone_text }}</view>
			   </view>
			 </view>
		   </view>
		 </view>  
		 
          <view class="phone-code-box1">
            <view class="cell">
              <view class="lable">手机号</view>
              <view class="value">
                <input style="text-align: right;height: 30rpx;" type="number" v-model="phone"
                  class="input" placeholder="请输入手机号"/>
              </view>
            </view>
			<text class="m_contact">见面联系方式在这儿，双方都签到才可见，不怕个人隐私提前泄露哦～</text>
          </view>
	

        <view class="start_base">
          <button @click="openJourney" class="start_base-btn"><text>保存/下一步</text></button>
        </view>
      </scroll-view>
    </view>

    
    <uni-popup ref="birthPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(birthPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择生日</text>
          <text @click="confirmVal('birth')" class="top-confirm">确定</text>
        </view>
        <picker-view immediate-change="true" :value="nowOptions.nowBirth"
          @change="changePicker('nowBirth', $event)" class="picker" indicator-style="height:48px">
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in years":key="'year' + ind">
              <text>{{ item }}年</text>
            </view>
          </picker-view-column>
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in months":key="'month' + ind">
              <text>{{ item }}月</text>
            </view>
          </picker-view-column>
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in daysList":key="'day' + ind">
              <text>{{ item }}日</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

    <uni-popup ref="areaPopup" type="bottom">
      <city-select title="居住地" :nowOption="nowOptions.permanent_area" @confirm="confirmArea"></city-select>
    </uni-popup>
	
    <uni-popup ref="homeTownPopup" type="bottom">
      <city-select title="籍贯" :nowOption="nowOptions.hometown" @confirm="confirmHomeTown"></city-select>
    </uni-popup>

    <uni-popup ref="staturePopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(staturePopup)" class="top-cancel">取消</text>
          <text class="top-title">选择身高</text>
          <text @click="confirmVal('height')" class="top-confirm">确定</text>
        </view>
        <picker-view immediate-change="true":value="nowOptions.height"
          @change="changePicker('height', $event)" class="picker" indicator-style="height:48px">
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in heightList":key="'picker_height' + ind">
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
        <picker-view immediate-change="true":value="nowOptions.weight"
          @change="changePicker('weight', $event)" class="picker" indicator-style="height:48px">
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in weightList":key="'picker_weight' + ind">
              <text>{{ item }}kg</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>
    
    <uni-popup ref="constellationPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(constellationPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择星座</text>
          <text @click="confirmVal('constellation')" class="top-confirm">确定</text>
        </view>
        <picker-view immediate-change="true":value="nowOptions.constellation"
          @change="changePicker('constellation', $event)" class="picker" indicator-style="height:48px">
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in constellationList":key="'picker_constellation' + ind">
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

    <uni-popup ref="educationPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(educationPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择学历</text>
          <text @click="confirmBottom('education_type')" class="top-confirm">确定</text>
        </view>
        <picker-view immediate-change="true":value="nowOptions.education_type"
          @change="changePicker('education_type', $event)" class="picker" indicator-style="height:48px">
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in educationalList":key="'picker_education_type' + ind">
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

    <uni-popup ref="workPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(workPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择职业</text>
          <text @click="confirmBottom('work_type')" class="top-confirm">确定</text>
        </view>
        <picker-view immediate-change="true" :value="nowOptions.work_type"
          @change="changePicker('work_type', $event)" class="picker" indicator-style="height:48px">
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in workTypeList":key="'picker_work_type' + ind">
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

    <uni-popup ref="salaryPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(salaryPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择年收入</text>
          <text @click="confirmBottom('salary')" class="top-confirm">确定</text>
        </view>
        <picker-view immediate-change="true":value="nowOptions.salary"
          @change="changePicker('salary', $event)" class="picker" indicator-style="height:48px">
          <picker-view-column>
            <view class="picker-item" v-for="(item, ind) in salaryList":key="'picker_salary' + ind">
              <text>{{ item }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>

  </view>
</template>

<script lang="ts" setup>
import * as qiniuUploader from "@/common/upload/qiniuUploader.ts";
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, ref, reactive, nextTick, computed } from "vue";
import { onLoad } from "@dcloudio/uni-app";
import { useStore } from "vuex";
import validate from "@/js_sdk/a-hua-validate/index.js";
import { getCode } from "@/common/request/api.js";

const formRules = reactive({
  nickname: { message: "昵称不能为空", required: true, type: "string" },
  gender: { message: "请选择性别", required: true },
  birth: { required: true, type: "string", message: "请选择生日" },
  permanent_area: { required: true, message: "请选择所在地" },
  height: { required: true, message: "请选择身高" },
  weight: { required: true, message: "请选择体重" },
  constellation: { required: true, message: "请选择星座" },
 // hometown: { required: true, message: "请选择籍贯" },
});
const store = useStore();
const app = getCurrentInstance().appContext.app;
const years = [], //年份
  months = [], //月份
  heightList = [], //身高范围
  weightList = []; // 重量
const salaryList = ref([]);

const selfList = ref([]);
const avatarList = ref([]);
const isImprove = computed(() => store.state.user.is_improve);
const userInfo = computed(() => store.state.user.userInfo);
const educationalList = ref([]);
const workTypeList = ref([]);
const constellationList = ref([]);
const daysList = ref([]); // 日
const options1 = ref([]); // 第一个区域项
const nowOptions = reactive({
  nowBirth: [0, 0, 0],
  height: [0],
  permanent_area: [0, 0, 0],
  hometown: [0, 0, 0],
  weight: [0],
  constellation: [0],
  education_type: [0],
  work_type: [0],
  salary: [0],
});


const birthPopup = ref(); // 生日弹窗
const areaPopup = ref(); //区域弹窗
const staturePopup = ref(); //身高弹窗
const weightPopup = ref(); //重量弹窗
const constellationPopup = ref(); // 星座弹窗
const homeTownPopup = ref(); // 家乡
const educationPopup = ref(); // 学历弹窗
const workPopup = ref(); // 工作情况
const salaryPopup = ref(); // 年收入弹窗

const phone = ref(""); // 手机号
const email_code = ref(""); // 手机号验证码
const email = ref("");
const phone_text = ref("获取验证码");
const phone_time = ref(60);
const phone_isGet = ref(true); // 是否可以获取验证码
let phone_Interval = null;

const active_point = ref(""); // 活跃区域
const active_Name = ref(""); // 活跃区域 地名或地址

const formData = reactive({
  avatar: null,
  file: null,
  nickname: null,
  gender: 1,
  birth: null,
  height: null,
  weight: null,
  permanent_area: null, // 所在地
  permanent_area_name: "",
  constellation: null, //星座
  constellation_name: null,
  hometown: null, //家乡
  hometown_name: null, //家乡
  education_type: null, //学历
  education_type_name: null, //学历
  work_type: null, // 工作情况
  work_type_name: null,
  salary: null, // 年收入
  school: null,
  active_point: "", // 活跃区域
}); // 最终的表单

onLoad(async () => {
  console.log(userInfo.value, "userInfo---");
  // 获取授权的手机号
  if (userInfo.value) {
    phone.value = userInfo.value.mobile;
  }

  for (let year = 1960; year <= new Date().getFullYear(); year++) {
    years.push(year);
  }
  for (let month = 1; month <= 12; month++) {
    months.push(month);
  }
  for (let x = 1; x <= new Date().getDate(); x++) {
    daysList.value.push(x);
  }
  for (let a = 140; a <= 200; a++) {
    heightList.push(a);
  }
  for (let x = 35; x <= 100; x++) {
    weightList.push(x);
  }
  nextTick(() => {
    options1.value = [
      {
        key: "birth",
        show: "birth",
        popup: birthPopup.value,
        title: "生日",
      },
      {
        show: "permanent_area_name",
        key: "permanent_area",
        popup: areaPopup.value,
        title: "所在地",
      }, 
      {
        key: "activeRegion",
        title: "活跃区域",
      },    	  
      {
        key: "height",
        show: "height",
        popup: staturePopup.value,
        title: "身高",
      },      
      {
        key: "weight",
        show: "weight",
        popup: weightPopup.value,
        title: "体重",
      },
      {
        key: "constellation",
        show: "constellation_name",
        popup: constellationPopup.value,
        title: "星座",
      },
  /*    {
        key: "hometown",
        show: "hometown_name",
        popup: homeTownPopup.value,
        title: "籍贯",
      },*/

    ];
    
    setTimeout(() => {
      nowOptions.nowBirth = [
        years.length - 30, // 出生年份初始为当前年份-30
        new Date().getMonth(),
        new Date().getDate() - 1,
      ];
      nowOptions.height = [30]; // 身高初始为最低值+30
      nowOptions.weight = [15]; // 体重初始为最低值+15
    }, 300);
  });

  api.post("/common/salary_list").then((pres: any) => {
    if (pres.code == 1) {
      salaryList.value = pres.data;
    }
  });

// 并行调用三个异步函数，等待所有函数执行完成
// getEduList()：获取学历列表
// getConstellationList()：获取星座列表
// getWorkTypes()：获取工作类型列表
await Promise.all([getEduList(), getConstellationList(), getWorkTypes()]);
// 判断用户信息是否需要完善，isImprove.value == -1 表示需要完善信息
if (isImprove.value == -1) {
    // 调用接口获取用户需要完善的信息
    api.post("/user/get_improve_info").then((xres: any) => {
        // 检查接口返回状态码是否为 1，1 表示请求成功
        if (xres.code == 1) {
            // 将接口返回的数据合并到 formData 对象中
            Object.assign(formData, xres.data);
            // 处理星座信息
              if (
                formData.constellation != null && formData.constellation.length > 0) {
                // 在星座列表中查找与 formData 中星座值匹配的项
                let selected1 = constellationList.value.find((item) => item.value == formData.constellation);
                // 将找到的星座名称赋值给 formData 中的星座名称字段
                formData.constellation_name = selected1.name;
            } else {
                 formData.constellation = null;
                formData.constellation_name = null;
            }

            // 处理学历信息
             if (formData.education_type != null && formData.education_type.length > 0) {
                // 在学历列表中查找与 formData 中学历值匹配的项
                let selected2 = educationalList.value.find((item) => item.value == formData.education_type);
                // 将找到的学历名称赋值给 formData 中的学历名称字段
                formData.education_type_name = selected2.name;
            } else {
                formData.education_type = null;
                formData.education_type_name = null;
            }

            // 处理工作类型信息
            if (formData.work_type != null && formData.work_type.length > 0) {
                // 在工作类型列表中查找与 formData 中工作类型值匹配的项
                let selected3 = workTypeList.value.find(
                    (item) => item.value == formData.work_type
                );
                // 将找到的工作类型名称赋值给 formData 中的工作类型名称字段
                formData.work_type_name = selected3.name;
            } else {
                formData.work_type = null;
                formData.work_type_name = null;
            }

            // 处理家乡信息
              if (formData.hometown != null && formData.hometown.length > 0) {
                // 将家乡信息按逗号分割成数组
                let home = formData.hometown.split(",");
                // 取数组的最后一个元素作为新的家乡信息
                formData.hometown = home[home.length - 1];
            } else {
                formData.hometown = null;
                formData.hometown_name = null;
            }

            // 处理所在地信息
            if (
                formData.permanent_area != null &&
                formData.permanent_area.length > 0
            ) {
                // 将所在地信息按逗号分割成数组
                let area = formData.permanent_area.split(",");
                // 取数组的最后一个元素作为新的所在地信息
                formData.permanent_area = area[area.length - 1];
            } else {
                formData.permanent_area = null;
                formData.permanent_area_name = null;
            }

            // 处理头像信息, 将接口返回的头像数组转换为包含路径和本地标识的对象数组
            avatarList.value = xres.data.avatar.map((item) => {
                return {
                    path: item,
                    local: false,
                };
            });
        }

        // 调用接口获取用户信息完善字段列表
        api.post("user/improve_field_list").then((vres: any) => {
            // 检查接口返回状态码是否为 1，1 表示请求成功
            if (vres.code == 1) {
                // 在返回的数据中查找学校信息
                const schoolInfo = vres.data.regular.find((uitem) => uitem.key == "school");
                // 在返回的数据中查找学历信息
                const eduInfo = vres.data.regular.find((uitem) => uitem.key == "education_type");
                // 在返回的数据中查找工作类型信息
                const workInfo = vres.data.regular.find((uitem) => uitem.key == "work_type");
                // 在返回的数据中查找年收入信息
                const salaryInfo = vres.data.regular.find((uitem) => uitem.key == "salary");
    /*      if (schoolInfo.is_require == 1) {
            formRules["school"] = {
              required: true,
              type: "string",
              message: "学校不得为空",
            };
          }*/
          if (eduInfo.is_require == 1) {
            formRules["education_type"] = {
              required: true,
              message: "请选择学历",
            };
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
          if (formData.school == null || formData.school.length <= 0) {
            formData.school = schoolInfo.value;
          }
		  
          if (formData.education_type == null ||formData.education_type.length <= 0) {
            formData.education_type = eduInfo.value;
            let eduSelected = educationalList.value.find(
              (item) => item.value == formData.education_type);
			  // 检查 eduSelected 是否为 undefined
			    if (eduSelected) {
			        formData.education_type_name = eduSelected.name;
			    } else {
			        formData.education_type_name = ''; // 如果未找到匹配项，给 education_type_name 赋一个默认值
			    }
          }
          if (formData.work_type == null || formData.work_type.length <= 0) {
            formData.work_type = workInfo.value;
            let workSelected = workTypeList.value.find(
              (item) => item.value == formData.work_type);
			   // 检查 workSelected 是否为 undefined
			   if (workSelected) {
					formData.work_type_name = workSelected.name;
			   } else {
				   formData.work_type_name = '';
			   }
			}
		  
          if (formData.salary == null || formData.salary.length <= 0) {
            formData.salary = salaryInfo.value;
          }
        }
      });
    });
  }
});

//获取workTypeList
const getWorkTypes = async () => {
  const res: any = await api.post("common/work_type_list");
  if (res.code == 1) {
    workTypeList.value = res.data;
  }
};

// 获取验证码
const getSenCode = async () => {
  if (phone_isGet.value) {
    const res = await getCode({
      email: email.value,
    });
    phone_isGet.value = false;
    phone_text.value = `${phone_time.value}后可获取`;
    phone_Interval = setInterval(() => {
      phone_time.value--;
      if (phone_time.value == 0) {
        phone_time.value = 60;
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
};

const confirmRegion = (e, key) => {
  formData[key] = e.cityId;
};

const changeMultiPicker = (e, key) => {
  formData[key] = e.detail.value.join(",");
};
// 获取星座
const getConstellationList = async () => {
  const res: any = await api.post("common/constellation_type_list");
  if (res.code == 1) {
    constellationList.value = res.data;
  }
};

// 单选
const choosePickerValue = (e, key, list) => {
  formData[key] = list[e.detail.value].value;
  formData[key + "_name"] = list[e.detail.value].title;
};

// 自定义选择日期
const changePickerDay = (e, key) => {
  formData[key] = e.detail.value;
};

// 获取学历
const getEduList = async () => {
  const res: any = await api.post("common/education_type_list");
  if (res.code == 1) {
    educationalList.value = res.data;
  }
};

// 选择所在地
const confirmArea = (options: Array<number>, address: string, cityId: any) => {
  nowOptions.permanent_area = options;
  formData.permanent_area_name = address;
  formData.permanent_area = cityId;
};

// 选择家乡
const confirmHomeTown = (
  options: Array<number>,
  address: string,
  cityId: any) => {
  nowOptions.hometown = options;
  formData.hometown_name = address;
  formData.hometown = cityId;
};

const openJourney = async () => {
  // 保存当前信息，并进入下一页继续完善信息  
  if (avatarList.value.length <= 0) {
    uni.showToast({
      icon: "none",
      title: "请选择至少一张照片",
    });
    return;
  }

  validate(formData, formRules)
    .then(async (hInfo) => {
      if (active_point.value == "") {
        uni.showToast({
          icon: "none",
          title: "请选择活跃区域",
        });
        return;
      }
   /*   if (email_code.value == "") {
        uni.showToast({
          icon: "none",
          title: "请输入验证码",
        });
        return;
      }*/
      uni.showLoading({
        title: "正在完善...",
      });
      try {
        const vres: any = await api.post("common/qiniu");
        qiniuUploader.init({
          domain: vres.data.cdnurl,
          region: "ECN",
          regionUrl: vres.data.uploadurl,
          uptoken: vres.data.multipart.qiniutoken,
        });
        let tasks = [];
        for (let img of avatarList.value) {
          if (img.local == true) {
            tasks.push(uploadFile(img.path));
          }
        }
        Promise.all(tasks)
          .then((imgList) => {
            let vind = 0;
            let arr = avatarList.value.map((vitem) => {
              if (vitem.local) {
                return {
                  path: imgList[vind++],
                  local: false,
                };
              }
              return vitem;
            });
            formData.avatar = arr.map((item) => item.path);
            improveInfo();  //保存当前页面的信息
          })
          .catch((e) => {
            uni.hideLoading();
          });
      } catch (e) {
        uni.hideLoading();
      }
    })
    .catch((err) => {
      console.log(err);
      uni.showToast({
        icon: "none",
        title: err[0].message,
      });
    });
};

const uploadFile = (path: string) => {
  return new Promise((resolve, reject) => {
    qiniuUploader.upload({
      filePath: path,
      success: (res) => {
        resolve(res.imageURL);
      },
      fail: (err) => {
        reject(null);
      },
    });
  });
};
// 完善信息
const improveInfo = async () => {
  try {
    formData.active_point_text = active_Name.value;
    formData.active_point = active_point.value;
    formData.contact_mobile = phone.value;
    formData.email = email.value;
    formData.email_code = email_code.value;

    console.log("formData的值：",formData);
    const res: any = await api.post("/user/improve", formData);	
    uni.hideLoading();
    uni.showToast({
      icon: "none",
      title: "已完善",
    });
    console.log("res.code的值：",res.code)
    if (res.code == 1) {
      uni.navigateTo({url: "/pages/data_editing/data_editing",});
    }
  } catch (e) {
    uni.hideLoading();
  }
};	


const changePicker = async (key: string, event: any) => {
  let choice = event.detail.value;
  if (
    key == "nowBirth" &&
    (nowOptions[key][0] != choice[0] || nowOptions[key][1] != choice[1])
  ) {
    let list = [];
    for (
      let x = 1;
      x <= new Date(years[choice[0]], months[choice[1]], 0).getDate();
      x++
    ) {
      list.push(x);
    }
    daysList.value = list;
    choice[2] = 0;
  }
  nowOptions[key] = choice;
};

// 下半部分弹窗
const confirmBottom = (key: string) => {
  // education_type
  switch (key) {
    case "education_type":
	console.log("educationalList的值：",educationalList);
      formData.education_type = educationalList.value[nowOptions.education_type[0]].value;
      formData.education_type_name = educationalList.value[nowOptions.education_type[0]].name;
      educationPopup.value.close();
      setTimeout(() => {
        workPopup.value.open();
      }, 300);
      break;
    case "work_type":
      formData.work_type = workTypeList.value[nowOptions.work_type[0]].value;
      formData.work_type_name =  workTypeList.value[nowOptions.work_type[0]].name;
      workPopup.value.close();
      setTimeout(() => {
        salaryPopup.value.open();
      }, 300);
      break;
    case "salary":
      formData.salary = salaryList.value[nowOptions.salary[0]];
      salaryPopup.value.close();
      break;
  }
};

const confirmVal = (key: string, tab = 0) => {
  switch (key) {
    case "birth":
      const res = nowOptions["nowBirth"];      
      formData[key] = [
        years[res[0]],
        months[res[1]],
        daysList.value[res[2]],
      ].join("-");
      break;
    case "height":
      formData.height = heightList[nowOptions.height[0]];
      break;
    case "constellation":
      formData.constellation =
        constellationList.value[nowOptions.constellation[0]].value;
      formData.constellation_name =
        constellationList.value[nowOptions.constellation[0]].name;
      break;
    case "weight":
      formData.weight = weightList[nowOptions.weight[0]];
      break;
  }
  let itemInd = options1.value.findIndex((item) => item.key == key);
  options1.value[itemInd].popup.close();
  if (itemInd + 1 < options1.value.length) {
    setTimeout(() => {
      options1.value[itemInd + 1].popup.open();
    }, 300);
  } else {
    educationPopup.value.open();
  }
};

const closePopup = (e: any) => {
  e.close();
};

const openPopup = (e: any, type = null) => {
  if (type == "activeRegion") {
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
            const { latitude, longitude, name, address } = res;
            const list = [longitude, latitude];
            active_point.value = list.toString();
            if (name!=""){
              active_Name.value = name;
            }else{
              active_Name.value = address;
			}     			
          },
        });
      },
    });
  } else {
    e.open();
  }
};

const changeSex = (sex: number) => {
  if (formData.gender != sex) {
    formData.gender = sex;
  }
};
</script>

<style lang="scss" scoped>
.main {
  width: 100%;
  height: 100%;
  background-color: #f7f8fa;
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
    .page_title {
      margin-top: 10rpx;
      font-size: 36rpx;
      color: #1d2129;
      font-weight: 600;
      text-align: center;
    }
    .intro {
      margin-top: 10rpx;
	  margin-left: 10rpx;
	  margin-right: 10rpx;
      font-size: 28rpx;
      color: #666;
    }
	.m_contact {
	  font-size: 25rpx;
	  color: #666;
	}

    .head {
      margin-top: 32rpx;
      width: 686rpx;
      background-color: #fff;
      border-radius: 24rpx;
      padding: 32rpx;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      min-height: 44rpx;
      position: relative;
      .title {
        font-size: 30rpx;
        font-weight: 500;
        color: #1d2129;
      }
	  .meno {
		font-size: 25rpx;
		margin-top: 10rpx;
		color: #666;
	  }
      .avatar_list {
        margin-top: 24rpx;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        position: relative;
        min-height: 148rpx;
        flex-wrap: wrap;
        .ablum {
          width: 148rpx;
          height: 148rpx;
          position: relative;
          margin-bottom: 10rpx;
          &-img {
            width: 148rpx;
            height: 148rpx;
            border-radius: 16rpx;
          }
          &-delete {
            width: 36rpx;
            height: 36rpx;
            position: absolute;
            right: 12rpx;
            top: 12rpx;
            z-index: 99;
          }
        }
        .audit_mask {
          width: 140rpx;
          height: 112rpx;
          position: absolute;
          top: 18rpx;
          left: 4rpx;
          z-index: 10;
        }
      }
    }
    .identity {
      width: 686rpx;
      border-radius: 24rpx;
      background-color: #fff;
      padding: 0 32rpx;
      box-sizing: border-box;
      margin: 0 auto;
      &-item {
        min-height: 108rpx;
        border-bottom: 1px solid #e8eaef;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        .label {
          font-size: 28rpx;
          color: #222;
          &-intro {
            font-size: 24rpx;
            color: #666;
          }
        }
        .input {
          flex: 1;
          flex-shrink: 0;
          margin-left: 10rpx;
          text-align: right;
          font-size: 26rpx;
          color: #666;
        }
        .option {
          width: 104rpx;
          height: 56rpx;
          background: #ffffff;
          border: 1px solid #dadce0;
          border-radius: 28rpx;
          line-height: 56rpx;
          text-align: center;
          font-size: 24rpx;
          text-align: center;
        }
        .active {
          color: #2c94ff;
          border: 1px solid #2c94ff;
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
    }
    .phone-code-box1 {
      width: 686rpx;
      border-radius: 24rpx;
      background-color: #fff;
      height: 160rpx;
      padding: 0 32rpx;
      box-sizing: border-box;
      margin: 0 auto;
      margin-top: 24rpx;

      display: flex;
      flex-direction: column;
      justify-content: space-between;

      .cell {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;

        .lable {
          font-size: 28rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          color: #1d2129;
        }
        .value {
          font-size: 28rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          color: #4e5769;
        }

        .code {
          width: 350rpx;
          display: flex;
          justify-content: space-around;
          align-items: center;
          font-size: 28rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          text-align: LEFT;
          .phone_code {
            color: #4e5769;
            padding-top: 6rpx;
          }

          .segmentation {
            width: 0rpx;
            height: 28rpx;
            border: 1rpx solid #e8eaef;
            margin: 0 10rpx;
          }

          .text {
            min-width: 150rpx;
            font-size: 28rpx;
            font-family: PingFang SC, PingFang SC-400;
            font-weight: 400;
            color: #2c94ff;
          }
        }
      }
      .cell:last-child {
        // border-top: #e8eaef solid 1rpx;
      }
    }

    .phone-code-box {
      width: 686rpx;
      border-radius: 24rpx;
      background-color: #fff;
      height: 216rpx;
      padding: 0 32rpx;
      box-sizing: border-box;
      margin: 0 auto;
      margin-top: 24rpx;

      display: flex;
      flex-direction: column;
      justify-content: space-between;

      .cell {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;

        .lable {
          font-size: 28rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          color: #1d2129;
        }
        .value {
          font-size: 28rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          color: #4e5769;
        }

        .code {
          width: 350rpx;
          display: flex;
          justify-content: space-around;
          align-items: center;
          font-size: 28rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          text-align: LEFT;

          .phone_code {
            color: #4e5769;
            padding-top: 6rpx;
          }

          .segmentation {
            width: 0rpx;
            height: 28rpx;
            border: 1rpx solid #e8eaef;
            margin: 0 10rpx;
          }

          .text {
            min-width: 150rpx;
            font-size: 28rpx;
            font-family: PingFang SC, PingFang SC-400;
            font-weight: 400;
            color: #2c94ff;
          }
      }
        }
      .cell:last-child {
        border-top: #e8eaef solid 1rpx;
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

  .start_base {
    margin-top: 64rpx;
    width: 100%;
    background-color: transparent;
    padding: 16rpx 32rpx 42px 32rpx;
    box-sizing: border-box;
    &-btn {
      width: 600rpx;
      height: 88rpx;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 44rpx;
      line-height: 88rpx;
      text-align: center;
      font-size: 28rpx;
      color: #fff;
      font-weight: 500;
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
