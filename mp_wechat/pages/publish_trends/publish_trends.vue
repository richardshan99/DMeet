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
        leftIcon="left"
        @clickLeft="navBack"
        :border="false"
        title="发布动态"
        background-color="transparent"
        :status-bar="true"
      ></uni-nav-bar>
      <view v-if="invitationId != null" class="invitation_info">
        <text class="warn_icon">!</text>
        <text class="warn_txt">发布不少于50字的分享可获得积分奖励</text>
      </view>
      <view class="publish_area">
        <textarea
          v-model="dynamicContent"
          placeholder="你有什么想说的，跟大家唠唠"
          class="input_area"
          :maxlength="500"
          auto-focus
        ></textarea>
        <view class="upload">
          <view
            v-for="(item, ind) in checkedImgs"
            :key="'img' + ind"
            class="upload-item"
          >
            <image mode="aspectFill" class="img" :src="item.path"></image>
            <image
              @click="deleteImg(ind)"
              src="/static/publish/delete.png"
              class="delete"
            ></image>
          </view>
          <image
            @click="pickImgs"
            v-if="checkedImgs.length < 9"
            src="/static/publish/addPhoto.png"
            class="upload-item"
          ></image>
          <view
            v-for="n of spaceNum"
            class="upload-item"
            :key="'space' + n"
          ></view>
        </view>
      </view>
      <view @click="submitInfo" class="submit">
        <text>提交</text>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import * as qiniuUploader from "@/common/upload/qiniuUploader.ts";
import { computed, getCurrentInstance, ref } from "vue";
import { api } from "@/common/request/index.ts";
import { onLoad } from "@dcloudio/uni-app";
const { proxy } = getCurrentInstance();
const app = getCurrentInstance().appContext.app;
const checkedImgs = ref<Array<any>>([]);
let dynamicId = null;
const dynamicContent = ref(null);
const invitationId = ref(null);

onLoad((options) => {
  invitationId.value = options.invitationId;
  const eventChannel = proxy.getOpenerEventChannel();
  eventChannel.on("acceptDataFromOpenerPage", (data: any) => {
    // 获取上个页面的传值
    if (data != null && data.id != null) {
      checkedImgs.value = data.imgs; // 图片传过来
      dynamicContent.value = data.content; // 信息传过来
      dynamicId = data.id;
    }
  });
});

const navBack = () => {
  uni.navigateBack();
};

const spaceNum = computed(() => {
  return 4 - ((checkedImgs.value.length % 4) + 1);
});

const deleteImg = (ind: number) => {
  checkedImgs.value.splice(ind, 1);
};

const pickImgs = () => {
  if (checkedImgs.value.length >= 9) {
    uni.showToast({
      icon: "none",
      title: "选择图片已达上限",
    });
    return;
  }
  uni.chooseImage({
    count: 9 - checkedImgs.value.length,
    success: (res) => {
      checkedImgs.value = checkedImgs.value.concat(
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

const uplaodFile = (path: string) => {
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

// 发布动态
const publishContent = () => {
  if (invitationId.value != null) {
    api
      .post("invite/share", {
        invite_id: invitationId.value,
        content: dynamicContent.value,
        images: checkedImgs.value.map((item) => item.path),
      })
      .then((result: any) => {
        if (result.code == 1) {
          uni.showToast({
            icon: "none",
            title: "发布成功",
          });
          uni.hideLoading();
          setTimeout(() => {
            uni.navigateBack();
          }, 1000);
        } else {
          setTimeout(() => {
            uni.navigateBack();
          }, 1000);
        }
      })
      .catch((e) => {
        setTimeout(() => {
          uni.hideLoading();
        }, 1500);
      });
    return;
  }
  if (dynamicId == null) {
    api
      .post("blog/publish", {
        content: dynamicContent.value,
        images: checkedImgs.value.map((item) => item.path),
      })
      .then((result: any) => {
        if (result.code == 1) {
          uni.$emit("refreshMyDynamic");
          uni.showToast({
            icon: "none",
            title: "发布成功",
          });
          uni.hideLoading();
          setTimeout(() => {
            uni.navigateBack();
          }, 1000);
        }
      })
      .catch((e) => {
        uni.hideLoading();
      });
  } else {
    uni.showToast({
      icon: "none",
      title: "暂无编辑接口",
    });
  }
};

// 上传图片
const submitInfo = async () => {
  if (dynamicContent.value == null || dynamicContent.value.length <= 0) {
    uni.showToast({
      icon: "none",
      title: "请输入发布内容",
    });
    return;
  }
  uni.showLoading({
    mask: true,
    title: "发布中",
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
    for (let img of checkedImgs.value) {
      if (img.local == true) {
        tasks.push(uplaodFile(img.path));
      }
    }
    if (tasks.length > 0) {
      Promise.all(tasks)
        .then((imgList) => {
          let ind = 0;
          for (let imgItem of checkedImgs.value) {
            if (imgItem.local == true) {
              imgItem.path = imgList[ind];
              imgItem.local = false;
              ind++;
            }
          }
          publishContent();
        })
        .catch((e) => {
          setTimeout(() => {
            uni.hideLoading();
          }, 1500);
        });
    } else {
      // 肯定是编辑
      publishContent();
    }
  } catch (e) {
    uni.hideLoading();
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
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    .invitation_info {
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
      .warn_icon {
        display: block;
        width: 28rpx;
        height: 28rpx;
        line-height: 28rpx;
        text-align: center;
        border-radius: 14rpx;
        background: linear-gradient(131deg, #4a97e7, #b57aff 100%);
        color: #fff;
        margin-left: 28rpx;
        font-size: 20rpx;
      }
      .warn_txt {
        margin-left: 14rpx;
        font-size: 26rpx;
        color: #1d2129;
      }
    }
    .publish_area {
      margin-top: 24rpx;
      width: 686rpx;
      background-color: #ffffff;
      border-radius: 24rpx;
      display: flex;
      flex-direction: column;
      align-items: center;
      .input_area {
        margin-top: 32rpx;
        width: 622rpx;
        height: 352rpx;
        font-size: 28rpx;
        color: #1d2129;
      }
      .upload {
        width: 622rpx;
        display: flex;
        flex-direction: row;
        margin-top: 14rpx;
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
    }
    .submit {
      width: 686rpx;
      height: 88rpx;
      background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
      border-radius: 96rpx;
      line-height: 88rpx;
      text-align: center;
      font-size: 32rpx;
      font-weight: 500;
      color: #fff;
      margin-top: 64rpx;
    }
  }
}
</style>
