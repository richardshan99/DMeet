<template>
  <view class="main">
    <image class="main-top" :src="app.config.globalProperties.$imgBase + '/xlyl_meet/index/top_back.png'
      "></image>
    <view class="main-base">
      <uni-nav-bar left-icon="left" @clickLeft="navBack" :border="false" :shadow="false" title="资料编辑"
        background-color="transparent" :status-bar="true"></uni-nav-bar>

      <scroll-view class="scroll" :scroll-y="true">
        <view class="scroll-base">
          <view class="main-area" :style="{ marginTop: '16rpx' }">
            <view class="userInfo">
              <view class="userInfo-left">
                <view :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'center',
                  flexWrap: 'wrap',
                }">
                  <text class="username">{{
                    userInfo.is_check_nickname == 1
                      ? userInfo.last_checked_nickname
                      : userInfo.nickname
                  }}</text>
                  <image v-if="userInfo.gender == 1" src="/static/sex_man.png" class="sex"></image>
                  <image v-if="userInfo.gender == 2" src="/static/sex_woman.png" class="sex"></image>
                  <image v-if="userInfo.is_member == 1" src="/static/vip_icon.png" class="vip_tag"
                    :style="{ marginLeft: '12rpx' }"></image>
                  <image v-if="userInfo.is_cert_realname == 1" class="vip_tag" :style="{ marginLeft: '12rpx' }"
                    src="/static/person/confirm_name.png"></image>
                  <image v-if="userInfo.is_cert_education == 1" class="vip_tag" :style="{ marginLeft: '12rpx' }"
                    src="/static/person/edu.png"></image>
                </view>
                <text class="desc_info">{{ userInfo.birth_year }}年 · {{ userInfo.height }}cm /{{ userInfo.weight }}kg</text>
              </view>
              <view @click="toBasic" class="userInfo-focus">
                <text class="txt">编辑</text>
              </view>
            </view>
            <view class="activeZone">
              <view class="activeZone-left">
                <view class="activeZone-lable"> <text class="lable-text">活跃区域</text> </view>

                <view class="active_point_text">
                  {{ userInfo.active_point_text }}
                </view>
              </view>

            </view>
            <view class="infos">
 
              <view class="infos-item">
                <image class="icon" src="/static/personal_details/now_location.png"></image>
                <text class="txt">{{ userInfo.area }}</text>
              </view>
              <view class="infos-item">
                <image class="icon" src="/static/personal_details/constellation.png"></image>
                <text class="txt">{{ userInfo.constellation_text }}</text>
              </view>
			  <view class="infos-item">
			    <image class="icon" src="/static/personal_details/educational.png"></image>
			    <text class="txt">{{ userInfo.school }}{{ userInfo.education_type_text }}</text>
			  </view>
   
              <view class="infos-item">
                <image class="icon" src="/static/personal_details/advertisement.png"></image>
                <text class="txt">{{ userInfo.work_type_text }}</text>
              </view>
              <view class="infos-item">
                <image class="icon" src="/static/personal_details/income.png"></image>
                <text class="txt">年收入{{ userInfo.salary }}</text>
              </view>
			  <view class="infos-item">
			    <image class="icon" src="/static/personal_details/home_location.png"></image>
			    <text class="txt">籍贯{{ userInfo.hometown }}</text>
			  </view>
              <view class="infos-item" v-for="(item, index) in extraList" :key="'extra' + index">
                <text class="txt">{{ item.formatter_value }}</text>
              </view>
            </view>
          </view>
		  
          <view class="main-area" :style="{ marginTop: '24rpx' }">
            <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',justifyContent: 'space-between',}">
              <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',}">
                <image class="title_icon" src="/static/data_editing/album.png"></image>
                <text class="title_txt">我的照片</text>
              </view>
              <view @click="savePhotos" class="userInfo-focus">
                <text class="txt">保存</text>
              </view>
            </view>
            <drag-img showAudit keyName="path" v-model="avatarList" :cols="4":style="{ marginTop: '24rpx' }"></drag-img>
          </view>
		  
         <view class="main-area" :style="{ marginTop: '24rpx' }">
            <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',justifyContent: 'space-between',}">
              <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',}">
                <image class="title_icon" src="/static/personal_details/my_tag.png"></image>
                <text class="title_txt">我的标签</text>
              </view>
              <view @click="openPopup(tagsPopup)" class="userInfo-focus">
                <text class="txt">编辑</text>
              </view>
            </view>
            <view class="tags">
              <view class="tags-cell" v-for="(item, index) in tagsList" :key="index">
                <view class="tags-lable"> {{ item.name }}</view>
                <view class="tags-box">
                  <view class="tags-item" v-for="(item2, ind) in item.items" :key="'tag' + ind">
                    {{ item2.name }}
                  </view>
                </view>
              </view>
            </view>
          </view>

          <view class="main-area" :style="{ marginTop: '24rpx' }">
            <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',justifyContent: 'space-between',}">
              <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',}">
                <image class="title_icon" src="/static/personal_details/about_me.png"></image>
                <text class="title_txt">关于我</text>
              </view>
              <view @click="toEdit(1, userInfo.intro)" class="userInfo-focus">
                <text class="txt">编辑</text>
              </view>
            </view>
              <text class="content">{{userInfo.intro}}</text>
          </view>

        <view class="main-area" :style="{ marginTop: '24rpx' }">
            <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',justifyContent: 'space-between',}">
              <view :style="{display: 'flex',flexDirection: 'row',alignItems: 'center',}"> 
                <image class="title_icon" src="/static/personal_details/family_back.png"></image>
                <text class="title_txt">对Ta的要求/期望</text>
              </view>
              <view @click="toEdit(2, userInfo.myExpect)" class="userInfo-focus">
                <text class="txt">编辑</text>
              </view>
            </view>
            <text class="content">{{ userInfo.myExpect}}</text>
          </view>
        </view>
		
       <view class="start_base">
          <button @click="backtohome" class="start_base-btn"><text>返回首页</text></button>
        </view>		
		
      </scroll-view>
    </view>
	
    <uni-popup ref="tagsPopup" type="bottom">
      <view class="tagsView">
        <view class="tagsView-head">
          <text class="title">个人标签</text>
          <!-- <text class="describe">最多可添加15个标签</text> -->
        </view>
        <view :style="{
          display: 'flex',
          flexDirection: 'column',
          width: '100%',
          maxHeight: '750rpx',
          overflow: 'auto',
        }">
          <view v-for="(item, index) in labelList" :key="'label' + index" 
		  :style="{borderBottom: index == labelList.length - 1 ? 'none' : '1px solid #E8EAEF',}" 
		  class="tagsView-item">
            <text class="title">{{ item.name }}</text>
            <view class="options">
              <view v-for="(citem, cind) in item.childlist" @click="selectTag(citem)" :key="'tag' + cind"
                class="options-tag" :class="{ active: citem.checked }">
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
  </view>
</template>

<script lang="ts" setup>
import * as qiniuUploader from "@/common/upload/qiniuUploader.ts";
import { api } from "@/common/request/index.ts";
import { getCurrentInstance, ref, computed } from "vue";
import { useStore } from "vuex";
import { onLoad,onShow } from "@dcloudio/uni-app";
const selectedLabels = ref([]); // 选择的
const labelList = ref([]);
const app = getCurrentInstance().appContext.app;
const store = useStore();
const userInfo = computed(() => store.state.user.userInfo);
const tagsPopup = ref();

const avatarList = ref([]);

const navBack = () => {
  uni.navigateBack();
};

const backtohome = () => {
	console.log(selectedLabels.value);
	if (tagsList.value.length < 5) {
		uni.showToast({
		  icon: "none",
		  title: "请至少选择5个标签",
		});
		return;
	}
	uni.switchTab({url: '/pages/index/index'})
};	
	
const extraList = computed(() => {
  return userInfo.value.extra_info.filter(
    (item) => item.formatter_value != null && item.formatter_value.length > 0
  );
});

const toEdit = (type: number, content) => {
  uni.navigateTo({
    url: `/pages/edit_area/edit_area?type=${type}`,
    success: (res) => {
      res.eventChannel.emit("acceptDataFromOpenerPage", {
        content: content,
      });
    },
  });
};

const tagsList = ref([]);

const groupBy = (array, key) => {
  return array.reduce((result, currentItem) => {
    // 使用id作为key来分组
    const group = result.find((item) => item.id === currentItem[key]);
    if (group) {
      group.items.push(currentItem);
    } else {
      result.push({
        id: currentItem[key],
        items: [currentItem],
      });
    }
    return result;
  }, []);
};

const tabgetList = async () => {
  const res = await api.post("user/info");
  if (res.code == 1) {
    store.commit("setUserInfo", res.data);
    const obj = {};
    lableListNew.value.forEach((element) => {
      obj[element.id] = element.name;
    });
    const lable = res.data.label;
    const list = [];
    lable.map((i) => {
      lableListNew.value.map((j) => {
        const value = j.childlist.filter((item) => item.id == i.id);
        if (value.length != 0) {
          i.pid = j.id;
          list.push(i);
        }
      });
    });
    const groupById = groupBy(list, "pid").map((i) => {
      i.name = obj[i.id];
      return i;
    });
    console.log(groupById);
    tagsList.value = groupById;
  }
}

const lableListNew = ref([])


onShow(() => {
  avatarList.value = userInfo.value.albums_text.map((xitem: any) => {
    return {
      path: xitem,
      local: false,
    };
  });
  api.post("user/label_list").then((res: any) => {
    if (res.code == 1) {
		console.log(selectedLabels.value);
      lableListNew.value = res.data
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
      tabgetList()
    }
  });
});

onLoad(() => {
 
});

const savePhotos = async () => {  
  let notUploadImgs = avatarList.value.filter((item) => item.local);  
  uni.showLoading({  // 未上传的头像
    title: "loading...",
    mask: true,
  });
  if (notUploadImgs.length > 0) {
    const vres: any = await api.post("common/qiniu");
    qiniuUploader.init({
      domain: vres.data.cdnurl,
      region: "ECN",
      regionUrl: vres.data.uploadurl,
      uptoken: vres.data.multipart.qiniutoken,
    });
    let tasks = [];
    for (let img of notUploadImgs) {
      tasks.push(uplaodFile(img.path));
    }
    if (tasks.length > 0) {
      Promise.all(tasks)
        .then((imgList) => {
          let ind = 0;
          for (let imgItem of avatarList.value) {
            if (imgItem.local == true) {
              imgItem.path = imgList[ind];
              imgItem.local = false;
              ind++;
            }
          }
          submitAvatars();	
        })
        .catch((e) => {
          uni.hideLoading();
        });
    }
  } else {
    submitAvatars();
  }
};

const submitAvatars = () => {
  api
    .post("user/edit_avatar", {
      avatar: avatarList.value.map((item) => item.path),
    })
    .then((xres: any) => {
      uni.hideLoading();
      if (xres.code == 1) {
        uni.showToast({
          icon: "none",
          title: xres.msg,
        });
        store.commit("setAvatar", avatarList.value[0]);
      }
    })
    .catch((err) => {
      uni.hideLoading();
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

const saveLabels = async () => {
	console.log(selectedLabels);
  if (selectedLabels.value.length < 5) {
    uni.showToast({
      icon: "none",
      title: "请至少选择5个标签",
    });
    return;
  }
  const res: any = await api.post("user/edit_label", {label: selectedLabels.value.map((item) => item.id),});
  tagsPopup.value.close();
  if (res.code == 1) {
    store.commit("setLabels", selectedLabels.value);
    tabgetList()
    uni.showToast({
      icon: "none",
      title: res.msg,
    });
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

const openPopup = (e: any) => {
  selectedLabels.value = Object.assign([], userInfo.value.label);
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
  e.open();
};

const toBasic = () => {
  uni.navigateTo({
    url: "/pages/basic_data/basic_data",
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

    .scroll {
      flex: 1;
      flex-shrink: 0;
      min-height: 0;
      width: 100%;

      &-base {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
      }
    }

    .head {
      z-index: 12;
      width: 192rpx;
      height: 192rpx;
      position: relative;

      &-icon {
        width: 192rpx;
        height: 192rpx;
        border-radius: 96rpx;
        z-index: 9;
      }

      &-camera {
        width: 58rpx;
        height: 56rpx;
        position: absolute;
        bottom: 0;
        right: 0;
        z-index: 10;
      }
    }
  }

  &-area {
    margin-top: 16rpx;
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

    .audit_about_tag {
      width: 160rpx;
      height: 108rpx;
      position: absolute;
      top: 0;
      right: 182rpx;
      z-index: 99;
    }

    .activeZone {
      margin-top: 18rpx;
      display: flex;
      align-items: center;
      justify-content: space-between;

      .activeZone-left {
        display: flex;
        align-items: center;

        .activeZone-lable {
          width: 104rpx;
          height: 40rpx;
          // background: linear-gradient(108deg, #4a97e7, #b57aff 100%);
          // -webkit-background-clip: text;
          // -webkit-text-fill-color: transparent;
          border-radius: 96rpx;

          background: #eef4fd;
          // color: #6498ec;
          font-size: 20rpx;
          font-family: PingFang SC, PingFang SC-400;
          font-weight: 400;
          display: flex;
          align-items: center;
          justify-content: space-around;

          .lable-text {
            background: linear-gradient(108deg, #4a97e7, #b57aff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          }
        }

        .active_point_text {
          padding-left: 8rpx;
          background: linear-gradient(100deg, #4a97e7, #b57aff 100%);
          -webkit-background-clip: text;
          background-clip: text;
          color: transparent;
          font-size: 24rpx;
          font-family: PingFang SC, PingFang SC-500;
          font-weight: 500;
          text-align: LEFT;

          max-width: 326rpx;
          overflow: hidden;
          /*内容超出后隐藏*/
          text-overflow: ellipsis;
          /*超出内容显示为省略号*/
          white-space: nowrap;
          /*文本不进行换行*/
        }
      }

      .location_box {
        display: flex;
        align-items: center;
        opacity: 0.7;
        background: linear-gradient(106deg, #4a97e7, #b57aff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 24rpx;
        font-family: PingFang SC, PingFang SC-500;
        font-weight: 500;
        margin-left: 12rpx;

        .location_icon {
          width: 24rpx;
          height: 24rpx;
          padding-right: 4rpx;
        }
      }
    }

    .userInfo {
      display: flex;
      flex-direction: row;

      &-left {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;

        .audit {
          width: 124rpx;
          height: 40rpx;
          background: rgba(255, 84, 111, 0.1);
          border-radius: 10rpx;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: center;
          margin-left: 8rpx;

          .img {
            width: 24rpx;
            height: 24rpx;
          }

          .txt {
            margin-left: 4rpx;
            font-size: 24rpx;
            color: #ff546f;
            font-weight: 500;
          }
        }

        .username {
          font-size: 40rpx;
          color: #1d2129;
          line-height: 56rpx;
          font-weight: 600;
        }

        .sex {
          margin-left: 22rpx;
          width: 40rpx;
          height: 40rpx;
        }

        .vip_tag {
          width: 40rpx;
          height: 40rpx;
        }

        .desc_info {
          margin-top: 8rpx;
          font-size: 26rpx;
          color: #868d9c;
          line-height: 38rpx;
        }
      }

      &-focus {
        width: 112rpx;
        height: 56rpx;
        background: linear-gradient(111deg,
            rgba(74, 151, 231, 0.15),
            rgba(181, 122, 255, 0.15) 100%);
        border-radius: 96rpx;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;

        .txt {
          display: block;
          font-size: 24rpx;
          background: linear-gradient(121deg, #4a97e7, #b57aff 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
      }
    }

    .infos {
      margin-top: 32rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      flex-wrap: wrap;

      &-item {
        border: 1px solid #e8eaef;
        padding: 12rpx 24rpx;
        box-sizing: border-box;
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-right: 20rpx;
        margin-bottom: 20rpx;
        border-radius: 36rpx;

        .icon {
          width: 28rpx;
          height: 30rpx;
        }

        .txt {
          font-size: 26rpx;
          color: #1d2129;
          margin-left: 12rpx;
        }
      }
    }

    .title_icon {
      width: 36rpx;
      height: 36rpx;
    }

    .title_txt {
      font-size: 32rpx;
      font-weight: 500;
      color: #1d2129;
      margin-left: 16rpx;
    }

    .content {
      margin-top: 24rpx;
      font-size: 30rpx;
      color: #1d2129;
      line-height: 48rpx;
      word-wrap: break-word;
      /* 使得长单词或数字可以换行 */
      overflow-wrap: break-word;
      /* 确保兼容性 */
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

    .tags {
      display: flex;
      flex-direction: row;
      align-items: center;
      flex-wrap: wrap;
      margin-top: 24rpx;

      .tags-cell {
        width: 100%;
        display: flex;
        margin-bottom: 24rpx;

        // align-items: center;
        .tags-lable {
          // width: 100rpx;
          font-size: 26rpx;
          font-family: PingFang SC, PingFang SC-500;
          font-weight: 500;
          color: #1d2129;
          padding-top: 12rpx;
          margin-right: 16rpx;
        }

        .tags-box {
          display: flex;
          flex-wrap: wrap;
          flex: 1;

          .tags-item {
            padding: 12rpx 24rpx;
            background: #f4f5f7;
            border-radius: 32rpx;

            font-size: 26rpx;
            font-family: PingFang SC, PingFang SC-400;
            font-weight: 400;
            color: #1d2129;
            margin-right: 16rpx;
            margin-bottom: 16rpx;
          }
        }
      }

      // &-item {
      //   padding: 12rpx 24rpx;
      //   background-color: #f4f5f7;
      //   border-radius: 32rpx;
      //   margin-right: 16rpx;
      //   margin-bottom: 16rpx;
      //   .txt {
      //     font-size: 26rpx;
      //     color: #1d2129;
      //     display: block;
      //   }
      // }
    }

    .auth_view {
      width: 302rpx;
      height: 130rpx;
      background-color: #f7f8fa;
      border-radius: 16rpx;
      display: flex;
      flex-direction: row;
      align-items: center;
      padding: 24rpx;
      box-sizing: border-box;

      .txt1 {
        font-size: 26rpx;
        color: #1d2129;
        line-height: 38rpx;
      }

      .txt2 {
        margin-top: 4rpx;
        font-size: 24rpx;
        color: #0082ff;
        line-height: 40rpx;
      }

      .txt3 {
        margin-top: 4rpx;
        font-size: 24rpx;
        color: #00b4a9;
        line-height: 40rpx;
      }

      .icon {
        width: 48rpx;
        height: 50rpx;
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
          background: linear-gradient(118deg,
              rgba(74, 151, 231, 0.15),
              rgba(181, 122, 255, 0.15) 100%);
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
  
  .start_base {
     margin-top: 40rpx;
	 height: 180rpx;
     box-sizing: border-box;
	 display: flex;
	 justify-content: center; // 水平居中
     &-btn {
       width: 500rpx;
       height: 80rpx;
       background: linear-gradient(96deg, #4a97e7, #b57aff 100%);
       border-radius: 44rpx;
       line-height: 88rpx;
       text-align: center;
       font-size: 30rpx;
       color: #fff;
       font-weight: 500;
     }
   }
}
</style>
