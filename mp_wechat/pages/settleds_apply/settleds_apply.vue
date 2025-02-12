<template>
  <view class="main">
    <image
      class="main-top"
      :src="
        app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "
    ></image>
    <view v-if="status == -1" class="main-base">
      <uni-nav-bar
        @clickLeft="navBack"
        left-icon="left"
        :border="false"
        title="我的申请入驻"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <scroll-view :scroll-y="true" class="scroll">
        <view class="scroll-back">
          <view class="content">
            <view class="content-item content-bd">
              <text class="label"
                >申请人姓名<text :style="{ color: '#FF546F' }">*</text></text
              >
              <input
                v-model="applyInfo.name"
                type="text"
                class="input"
                placeholder="请输入申请人姓名"
              />
            </view>
            <view class="content-item content-bd">
              <text class="label"
                >手机号码<text :style="{ color: '#FF546F' }">*</text></text
              >
              <input
                v-model="applyInfo.mobile"
                type="number"
                class="input"
                placeholder="请输入手机号码"
              />
            </view>
            <view class="content-item content-bd">
              <text class="label"
                >地区<text :style="{ color: '#FF546F' }">*</text></text
              >
              <view @click="openSelect(areaPopup)" class="select">
                <text
                  class="txt"
                  :class="applyInfo.area_id != null ? 'active' : ''"
                  >{{
                    applyInfo.area_id == null ? "请选择" : applyInfo.area_name
                  }}</text
                >
                <image
                  class="arrow_right"
                  src="/static/mine_center/arrow_left.png"
                ></image>
              </view>
            </view>
            <view class="content-item content-bd">
              <text class="label"
                >门店名称<text :style="{ color: '#FF546F' }">*</text></text
              >
              <input
                v-model="applyInfo.shop_name"
                type="text"
                class="input"
                placeholder="请输入门店名称"
              />
            </view>
            <!-- <text class="upload_title">门店缩略图(单张、160*160)<text :style="{color:'#FF546F'}">*</text></text> -->
            <!-- <text class="upload_title"
              >营业执照<text :style="{ color: '#FF546F' }">*</text></text
            >

            <view class="upload content-bd">
              <image
                v-if="applyInfo.thumb.path == null"
                @click="chooseStoreImg"
                src="/static/publish/addPhoto.png"
                class="upload-item"
              ></image>
              <view v-else class="upload-item">
                <image class="img" :src="applyInfo.thumb.path"></image>
                <image
                  @click="applyInfo.thumb.path = null"
                  src="/static/publish/delete.png"
                  class="delete"
                ></image>
              </view>
            </view> -->
            <!-- <text class="upload_title"
              >门店轮播图(多张、750*500)<text :style="{ color: '#FF546F' }"
                >*</text
              ></text
            >
            <view class="upload content-bd">
              <view
                v-for="(item, ind) in applyInfo.images"
                :key="'other' + ind"
                class="upload-item"
              >
                <image class="img" :src="item.path"></image>
                <image
                  @click="deleteSwiperImg(ind)"
                  src="/static/publish/delete.png"
                  class="delete"
                ></image>
              </view>
              <image
                @click="chooseSwiperImg"
                v-if="applyInfo.images.length < 9"
                src="/static/publish/addPhoto.png"
                class="upload-item"
              ></image>
              <view
                v-for="n of spaceNum"
                class="upload-item"
                :key="'apply-space' + n"
              ></view>
            </view> -->
            <view class="content-item content-bd">
              <text class="label"
                >选择地址<text :style="{ color: '#FF546F' }">*</text></text
              >
              <view @click="chooseLocation" class="select">
                <text
                  class="txt"
                  :class="applyInfo.position != null ? 'active' : ''"
                  >{{
                    applyInfo.position == null
                      ? "请选择"
                      : applyInfo.position_name
                  }}</text
                >
                <image
                  class="arrow_right"
                  src="/static/mine_center/arrow_left.png"
                ></image>
              </view>
            </view>
            <view class="content-item content-bd">
              <text class="label"
                >详细地址<text :style="{ color: '#FF546F' }">*</text></text
              >
              <input
                v-model="applyInfo.address"
                type="text"
                class="input"
                placeholder="请输入详细地址"
              />
            </view>
            <view class="content-item content-bd">
              <text class="label"
                >门店类别<text :style="{ color: '#FF546F' }">*</text></text
              >
              <view @click="openSelect(shopPopup)" class="select">
                <text
                  class="txt"
                  :class="applyInfo.shop_category_id != null ? 'active' : ''"
                  >{{
                    applyInfo.shop_category_id == null
                      ? "请选择"
                      : applyInfo.shop_category_name
                  }}</text
                >
                <image
                  class="arrow_right"
                  src="/static/mine_center/arrow_left.png"
                ></image>
              </view>
            </view>
            <text class="upload_title"
              >营业执照<text :style="{ color: '#FF546F' }">*</text></text
            >

            <view class="upload content-bd">
              <image
                v-if="applyInfo.business_license_image == null"
                @click="pickBusinessImg"
                src="/static/publish/addPhoto.png"
                class="upload-item"
              ></image>
              <view v-else class="upload-item">
                <image
                  class="img"
                  :src="applyInfo.business_license_image"
                ></image>
                <image
                  @click="applyInfo.business_license_image = null"
                  src="/static/publish/delete.png"
                  class="delete"
                ></image>
              </view>
            </view>
            <!-- 
            <view class="content-item">
              <text class="label"
                >营业时间段<text :style="{ color: '#FF546F' }">*</text></text
              >
              <view @click="openTimeLine" class="select">
                <text
                  class="txt"
                  :class="applyInfo.business_time != null ? 'active' : ''"
                  >{{
                    applyInfo.business_time == null
                      ? "请选择"
                      : applyInfo.business_time
                  }}</text
                >
                <image
                  class="arrow_right"
                  src="/static/mine_center/arrow_left.png"
                ></image>
              </view>
            </view> -->
            <!-- <text class="upload_title"
              >营业执照<text :style="{ color: '#FF546F' }">*</text></text
            >
            <view class="upload">
              <image
                v-if="applyInfo.business_image == null"
                @click="pickBusinessImg"
                src="/static/publish/addPhoto.png"
                class="upload-item"
              ></image>
              <view v-else class="upload-item">
                <image class="img" :src="applyInfo.business_image"></image>
                <image
                  @click="applyInfo.business_image = null"
                  src="/static/publish/delete.png"
                  class="delete"
                ></image>
              </view>
            </view> -->
          </view>

          <view class="content">
            <view class="content-item">
              <text class="label">备注</text>
              <input
                v-model="applyInfo.remark"
                type="text"
                class="input"
                placeholder="请输入备注内容"
              />
            </view>

            <text class="upload_title"
              >其他照片(3张以内)<text :style="{ color: '#FF546F' }"
                >*</text
              ></text
            >
            <view class="upload content-bd">
              <view
                v-for="(item, ind) in applyInfo.other_images"
                :key="'other' + ind"
                class="upload-item"
              >
                <image class="img" :src="item.path"></image>
                <image
                  @click="deleteOtherImg(ind)"
                  src="/static/publish/delete.png"
                  class="delete"
                ></image>
              </view>
              <image
                @click="chooseSwiperImg"
                v-if="applyInfo.other_images.length <= 3"
                src="/static/publish/addPhoto.png"
                class="upload-item"
              ></image>
              <view
                v-for="n of spaceNum"
                class="upload-item"
                :key="'apply-space' + n"
              ></view>
            </view>
            <!-- <text class="upload_title">其他照片(3张以内)<text :style="{color:'#FF546F'}">*</text></text>
						<view class="upload">
							<view v-for="(item,ind) in applyInfo.other_images" :key="'other'+ind" class="upload-item">
								<image class="img" :src="item"></image>
								<image @click="deleteOtherImg(ind)" src="/static/publish/delete.png" class="delete"></image>
							</view>
							<image @click="chooseOtherImg" v-if="applyInfo.other_images.length < 3" src="/static/publish/addPhoto.png" class="upload-item"></image>
							<view v-for="n of otherSpace" class="upload-item" :key="'apply-space'+n"></view>
						</view> -->
          </view>
          <!-- <view class="content" :style="{ marginTop: '24rpx' }">
            <textarea
              v-model="applyInfo.content"
              placeholder="请输入门店简介"
              :style="{
                height: '200rpx',
                fontSize: '28rpx',
                color: '#1D2129',
                width: 'auto',
                marginTop: '32rpx',
              }"
            ></textarea>
            <view class="upload">
              <view
                v-for="(item, ind) in applyInfo.content_images"
                :key="'other' + ind"
                class="upload-item"
              >
                <image class="img" :src="item.path"></image>
                <image
                  @click="deleteContentImg(ind)"
                  src="/static/publish/delete.png"
                  class="delete"
                ></image>
              </view>
              <image
                @click="chooseContentImgs"
                v-if="applyInfo.content_images.length < 9"
                src="/static/publish/addPhoto.png"
                class="upload-item"
              ></image>
              <view
                v-for="n of space_content"
                class="upload-item"
                :key="'apply-space' + n"
              ></view>
            </view>
          </view> -->
          <view
            @click="submitApply"
            class="scroll-btn"
            :style="{ marginBottom: '64rpx' }"
          >
            <text>提交申请</text>
          </view>
        </view>
      </scroll-view>
    </view>
    <view v-if="status == 1" class="main-base">
      <uni-nav-bar
        @clickLeft="navBack"
        left-icon="left"
        :border="false"
        title="我的申请入驻"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <image
        class="auditIcon"
        src="/static/authentication/wait_auditing.png"
      ></image>
      <text class="audit_txt1">入驻申请审核中</text>
      <text class="audit_txt2"
        >您的认证申请已提交，我们会尽快对您的信息进行审核，请等待审核结果</text
      >
    </view>
    <view v-if="status == 3" class="main-base">
      <uni-nav-bar
        @clickLeft="navBack"
        left-icon="left"
        :border="false"
        title="我的申请入驻"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <image
        class="auditIcon"
        src="/static/authentication/audit_reject.png"
      ></image>
      <text class="audit_txt1">入驻申请未通过</text>
      <text class="audit_txt2">驳回原因：{{ message }}</text>
      <view
        :style="{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          justifyContent: 'center',
          marginTop: '64rpx',
        }"
      >
        <view @click="status = -1" class="revise_btn">
          <text>重新修改</text>
        </view>
        <view @click="toHome" class="reback_btn">
          <text>返回首页</text>
        </view>
      </view>
    </view>
    <uni-popup ref="areaPopup" type="bottom">
      <city-select title="所在地" @confirm="confirmArea"></city-select>
    </uni-popup>
    <uni-popup ref="shopPopup" type="bottom">
      <view class="popup_stature">
        <view class="top">
          <text @click="closePopup(shopPopup)" class="top-cancel">取消</text>
          <text class="top-title">选择门店类别</text>
          <text @click="confirmShopType" class="top-confirm">确定</text>
        </view>
        <picker-view
          immediate-change="true"
          :value="[typeIndex]"
          @change="changeShopType"
          class="picker"
          indicator-style="height:48px"
        >
          <picker-view-column>
            <view
              class="picker-item"
              v-for="(item, ind) in shopTypeList"
              :key="'picker_type' + ind"
            >
              <text>{{ item.name }}</text>
            </view>
          </picker-view-column>
        </picker-view>
      </view>
    </uni-popup>
    <uni-popup ref="timePeriod" type="bottom">
      <time-select @confirm="confirmTime" :nowOption="[8, 20]"></time-select>
    </uni-popup>
  </view>
</template>

<script lang="ts" setup>
import * as qiniuUploader from "@/common/upload/qiniuUploader.ts";
import { api } from "@/common/request/index.ts";
import { computed, getCurrentInstance, reactive, ref } from "vue";
import { onLoad } from "@dcloudio/uni-app";
const status = ref(0);
const message = ref("");
const app = getCurrentInstance().appContext.app;
const areaPopup = ref(); // 区域弹窗
const shopPopup = ref();
const shopTypeList = ref([]);
const timePeriod = ref();

const typeIndex = ref(0);
const applyInfo = reactive({
  thumb: {
    local: true,
    path: null,
  }, // 缩略图
  images: [], // 轮播图
  content_images: [],
  //   content: null, // 详情
  name: null, //姓名
  mobile: null, //手机号
  shop_name: null, //门店名称
  shop_category_id: null, // 门店类型
  shop_category_name: null,
  business_time: null, // 营业时间
  business_license_image: null,
  //   business_image: null, //营业执照，单图
  remark: null, //备注
  other_images: [], // 其他图片， 最多3张
  area_id: null, //地区， 最后一级地区id
  area_name: null,
  position: null, //经纬度，逗号连接  没用到
  position_name: null,
  address: null, //详细地址 没用到
});

onLoad(() => {
  api.post("/my/shop/category").then((res: any) => {
    if (res.code == 1) {
      shopTypeList.value = res.data;
    }
  });
  api.post("/my/shop/check_apply").then((res: any) => {
    if (res.code == 1) {
      status.value = res.data.status;
      message.value = res.data.reject;
    }
  });
});

const spaceNum = computed(() => {
  return 4 - ((applyInfo.images.length % 4) + 1);
});

const space_content = computed(() => {
  return 4 - ((applyInfo.content_images.length % 4) + 1);
});

const deleteSwiperImg = (ind: number) => {
  applyInfo.images.splice(ind, 1);
};

const chooseContentImgs = () => {
  if (applyInfo.content_images.length >= 9) {
    uni.showToast({
      icon: "none",
      title: "选择图片已达上限",
    });
    return;
  }
  uni.chooseImage({
    count: 9 - applyInfo.content_images.length,
    success: (res) => {
      applyInfo.content_images = applyInfo.content_images.concat(
        res.tempFiles.map((item: any) => {
          return {
            path: item.path,
            local: true,
          };
        })
      );
    },
  });
};

const deleteContentImg = (ind: number) => {
  applyInfo.content_images.splice(ind, 1);
};

const chooseSwiperImg = () => {
  console.log(applyInfo, "applyInfo--");

  if (applyInfo.other_images.length >= 3) {
    uni.showToast({
      icon: "none",
      title: "选择图片已达上限",
    });
    return;
  }
  uni.chooseImage({
    count: 3 - applyInfo.other_images.length,
    success: (res) => {
      applyInfo.other_images = applyInfo.other_images.concat(
        res.tempFiles.map((item: any) => {
          return {
            path: item.path,
            local: true,
          };
        })
      );
      console.log(applyInfo.other_images);
    },
  });
};

const chooseStoreImg = () => {
  uni.chooseImage({
    count: 1,
    success: (res) => {
      applyInfo.thumb = {
        path: res.tempFilePaths[0],
        local: true,
      };
    },
  });
};

const confirmTime = (val) => {
  applyInfo.business_time = val;
};

const chooseLocation = () => {
  uni.chooseLocation({
    success: (res) => {
      applyInfo.position = res.longitude + "," + res.latitude;
      applyInfo.position_name = res.name;
    },
    fail: function (err) {
      console.error(err);
    },
  });
};

const navBack = () => {
  uni.navigateBack();
};

const toHome = () => {
  uni.switchTab({
    url: "/pages/index/index",
  });
};
const openTimeLine = () => {
  timePeriod.value.open();
};

const changeShopType = (e: any) => {
  typeIndex.value = e.detail.value[0];
};

const confirmShopType = () => {
  // 确认类型
  applyInfo.shop_category_id = shopTypeList.value[typeIndex.value].id;
  applyInfo.shop_category_name = shopTypeList.value[typeIndex.value].name;
  shopPopup.value.close();
};

// 选择地区
const confirmArea = (options: Array<number>, address: string, cityId: any) => {
  applyInfo.area_name = address;
  applyInfo.area_id = cityId;
};

const otherSpace = computed(() => {
  if (applyInfo.other_images.length >= 2) {
    return 1;
  } else {
    return 3 - applyInfo.other_images.length;
  }
});

const deleteOtherImg = (index: number) => {
  // 删除其他图片
  applyInfo.other_images.splice(index, 1);
};

const chooseOtherImg = () => {
  // 选择其他图片
  if (applyInfo.other_images.length >= 3) {
    uni.showToast({
      icon: "none",
      title: "选择图片已达上限",
    });
    return;
  }
  uni.chooseImage({
    count: 3 - applyInfo.other_images.length,
    success: (res) => {
      applyInfo.other_images = applyInfo.other_images.concat(res.tempFilePaths);
    },
  });
};

const closePopup = (e: any) => {
  e.close();
};

const openSelect = (e: any) => {
  // 打开弹窗
  e.open();
};

const pickBusinessImg = () => {
  // 选取营业执照
  uni.chooseImage({
    count: 1,
    success: (res) => {
      applyInfo.business_license_image = res.tempFilePaths[0];
    },
  });
};

const submitApply = async () => {
  console.log(applyInfo);

  // 提交申请
  if (
    applyInfo.name == null ||
    applyInfo.name.length <= 0 ||
    applyInfo.mobile == null ||
    applyInfo.mobile.length <= 0 ||
    applyInfo.shop_name == null ||
    applyInfo.shop_name.length <= 0 ||
    applyInfo.shop_category_id == null ||
    applyInfo.business_license_image == null ||
    // applyInfo.thumb.path == null ||
    // applyInfo.images.length <= 0 ||
    // applyInfo.business_time == null ||
    applyInfo.other_images.length <= 0 ||
    applyInfo.area_id == null ||
    applyInfo.position == null ||
    applyInfo.address == null ||
    applyInfo.address.length <= 0
  ) {
    uni.showToast({
      icon: "none",
      title: "请将表单补充完整",
    });
    return;
  }
  const vres: any = await api.post("common/qiniu");
  qiniuUploader.init({
    domain: vres.data.cdnurl,
    region: "ECN",
    regionUrl: vres.data.uploadurl,
    uptoken: vres.data.multipart.qiniutoken,
  });
  let tasks = [];
  tasks.push(uplaodFile(applyInfo.business_license_image));
  let ind1 = 1,
    ind2 = 1,
    ind3 = 1;
  for (let img of applyInfo.other_images) {
    tasks.push(uplaodFile(img.path));
    ind1++;
  }
  //   ind2 = ind1;
  //   for (let img of applyInfo.content_images) {
  //     tasks.push(uplaodFile(img.path));
  //     ind2++;
  //   }
  //   ind3 = ind2;
  //   for (let img of applyInfo.other_images) {
  //     tasks.push(uplaodFile(img));
  //     ind3++;
  //   }
  console.log(tasks, "tasks---");

  Promise.all(tasks)
    .then((imgList) => {
      console.log(imgList, "imgList---");

      const submitData = Object.assign({}, applyInfo);
      submitData.business_license_image = imgList[0];
      //   submitData.images = imgList.slice(1, ind1);
      //   submitData.content_images = imgList.slice(ind1, ind2);
      submitData.other_images = imgList.slice(ind2);
      delete submitData.thumb;
      console.log(submitData, "submitData---");

      api.post("/my/shop/apply", submitData).then((lastRes: any) => {
        if (lastRes.code == 1) {
          status.value = 1; //给个正在审核的状态
          uni.showToast({
            icon: "none",
            title: lastRes.msg,
          });
          //
        }
      });
    })
    .catch((e) => {
      uni.hideLoading();
    });
};

const uplaodFile = (path: string) => {
  return new Promise((resolve, reject) => {
    qiniuUploader.upload({
      filePath: path,
      success: (res) => {
        console.log(1231111);
        console.log(res);

        resolve(res.imageURL);
      },
      fail: (err) => {
        reject(null);
      },
    });
  });
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
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    .auditIcon {
      margin-top: 64rpx;
      width: 146rpx;
      height: 144rpx;
    }
    .audit_txt1 {
      margin-top: 40rpx;
      font-size: 32rpx;
      color: #1d2129;
      font-weight: 500;
    }
    .audit_txt2 {
      width: 622rpx;
      margin-top: 8rpx;
      font-size: 28rpx;
      color: #868d9c;
      line-height: 44rpx;
      text-align: center;
      word-wrap: break-word; /* 使得长单词或数字可以换行 */
      overflow-wrap: break-word; /* 确保兼容性 */
    }
    .revise_btn {
      width: 176rpx;
      height: 64rpx;
      background-color: #ffffff;
      border: 1px solid #dadce0;
      border-radius: 32rpx;
      line-height: 64rpx;
      text-align: center;
      font-size: 28rpx;
      color: #1d2129;
      font-weight: 500;
    }
    .reback_btn {
      width: 176rpx;
      height: 64rpx;
      background-color: #ffffff;
      border: 1px solid #2c94ff;
      border-radius: 32rpx;
      line-height: 64rpx;
      text-align: center;
      margin-left: 24rpx;
      font-size: 28rpx;
      color: #2c94ff;
      font-weight: 500;
    }
    .scroll {
      flex: 1;
      min-height: 0;
      flex-shrink: 0;
      width: 100%;
      &-back {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      &-btn {
        width: 686rpx;
        height: 88rpx;
        line-height: 88rpx;
        background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
        border-radius: 44rpx;
        text-align: center;
        font-size: 28rpx;
        color: #fff;
        font-weight: 500;
        margin-top: 64rpx;
      }
    }
    .content {
      margin-top: 24rpx;
      width: 686rpx;
      border-radius: 24rpx;
      background-color: #fff;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      padding: 0 32rpx;
      box-sizing: border-box;
      &-item {
        height: 108rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        .label {
          font-size: 28rpx;
          color: #1d2129;
        }
        .input {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          padding-left: 16rpx;
          box-sizing: border-box;
          text-align: right;
          font-size: 28rpx;
          color: #1d2129;
        }
        .select {
          flex: 1;
          flex-shrink: 0;
          min-width: 0;
          padding-left: 16rpx;
          box-sizing: border-box;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: flex-end;
          .txt {
            font-size: 28rpx;
            color: #c2c5cc;
          }
          .active {
            color: #1d2129;
          }
          .arrow_right {
            width: 16rpx;
            height: 24rpx;
            margin-left: 16rpx;
          }
        }
      }
      .upload_title {
        margin-top: 32rpx;
        font-size: 28rpx;
        color: #1d2129;
      }
      .upload {
        display: flex;
        flex-direction: row;
        margin-top: 24rpx;
        margin-bottom: 32rpx;
        flex-wrap: wrap;
        justify-content: space-between;
        &-item {
          position: relative;
          margin-top: 10rpx;
          width: 148rpx;
          height: 148rpx;
          .img {
            width: 148rpx;
            height: 148rpx;
            border-radius: 16rpx;
            z-index: 9;
          }
          .delete {
            width: 36rpx;
            height: 36rpx;
            position: absolute;
            top: 12rpx;
            right: 12rpx;
            z-index: 10;
          }
        }
      }
      &-bd {
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
</style>
