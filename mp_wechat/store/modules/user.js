import { api } from "@/common/request/index.ts";

export default {
  state: {
    userName: null,
    token: null,
    is_improve: -1, // 是否完善信息，1：是，-1：否
    is_follow_wechat: 1, // 默认已经关注公众号，
    userInfo: null,
    defaultCity: null,
    shopInfo: null,
    searchData: null,
    source: null, // 扫码传递的参数
    invationCount: 0,
  },
  getters: {},
  mutations: {
    setInvationNum(state, count) {
      state.invationCount = count;
    },
    setIsCertRealName(state, data) {
      state.userInfo.is_cert_realname = data;
    },
    setLabels(state, labels) {
      state.userInfo.label = labels;
    },
    setSource(state, source) {
      state.source = source;
    },
    setShopStatus(state, status) {
      state.shopInfo.shop_info_status = status;
    },
    setmyExpect(state, myExpect) {
      state.userInfo.myExpect = myExpect;
    },
  
    setAbout(state, about) {
      state.userInfo.last_checked_intro = about;
      state.userInfo.is_check_intro = 1;
    },
    setAvatar(state, avatar) {
      state.userInfo.last_checked_avatar = avatar;
      state.userInfo.is_check_avatar = 1;
    },
    saveSearch(state, data) {
      state.searchData = data;
    },
    setCity(state, data) {
      state.defaultCity = data;
    },
    sToken(state, data) {
      state.token = data;
    },
    sImprove(state, data) {
      state.is_improve = data;
    },
    sFollow(state, data) {
      state.is_follow_wechat = data;
    },
    setUserInfo(state, user) {
      state.userInfo = user;
    },
    setRealNameStatus(state, real_status) {
      state.userInfo.cert.realname_status = real_status;
    },
    setEduStatus(state, edu) {
      state.userInfo.cert.education_status = edu;
    },
    setWorkStatus(state, work) {
      state.userInfo.cert.work_status = work;
    },
    setShop(state, shop) {
      state.shopInfo = shop;
    },
    clearToken(state) {
      state.token = null;
      uni.removeStorageSync("token");
    },
  },
  actions: {
    setToken({ commit }, data) {
      if (data != null) {
        commit("sToken", data);
        uni.setStorageSync("token", data);
      } else if (uni.getStorageSync("token")) {
        commit("sToken", uni.getStorageSync("token"));
      } else {
        commit("sToken", null);
      }
    },
    setImprove({ commit }, data) {
      if (data != null) {
        commit("sImprove", data);
        uni.setStorageSync("is_improve", data);
      } else if (uni.getStorageSync("is_improve")) {
        commit("sImprove", uni.getStorageSync("is_improve"));
      } else {
        commit("sImprove", -1);
      }
    },
    setFollowFlag({ commit }, data) {
      if (data != null) {
        commit("sFollow", data);
        uni.setStorageSync("is_follow_wechat", data);
      } else if (uni.getStorageSync("is_follow_wechat")) {
        commit("sFollow", uni.getStorageSync("is_follow_wechat"));
      } else {
        commit("sFollow", 1);
      }
    },
    refreshInvitationNum({ commit }) {
      api.post("message/list").then((res) => {
        if (res.code == 1) {
          let xitem = res.data.find((dom) => dom.key == "invitation");
          var curPages = getCurrentPages()[0]; // 获取当前页面实例
          if (
            typeof curPages.getTabBar === "function" &&
            curPages.getTabBar()
          ) {
            commit("setInvationNum", xitem.count);
            curPages.getTabBar().setData({
              ["list[1].invationCount"]: xitem.count, // 表示当前菜单的索引，该值在不同的页面表示不同
            });
          }
        }
      });
    },

    refreshInfo({ state, commit, dispatch }) {
      if (state.token == null) {
        return;
      }
      api
        .post("user/info")
        .then((res) => {
          if (res.code == 1) {
            dispatch("setImprove", res.data.is_improve);
            dispatch("setFollowFlag", res.data.is_follow_wechat);
            commit("setUserInfo", res.data);
            var curPages = getCurrentPages()[0]; // 获取当前页面实例
            if (
              typeof curPages.getTabBar === "function" &&
              curPages.getTabBar()
            ) {
              curPages.getTabBar().setData({
                ["list[4].count"]: res.data.new_message_num, // 表示当前菜单的索引，该值在不同的页面表示不同
              });
            }
          }
        })
        .catch((err) => {
          if (err.data.code == -99) {
            uni.showToast({
              icon: "none",
              title: "token被删除了",
            });
            uni.removeStorageSync("token");
            commit("sToken", null);
          }
        });
      api.post("/my/shop/info").then((res) => {
        if (res.code == 1) {
          commit("setShop", res.data);
        }
      });
    },
    clearTokenAction({ commit }) {
      commit("clearToken");
    },
  },
};
