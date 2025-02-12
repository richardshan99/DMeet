import { Http } from "lwu-request";
import store from "@/store/index.js";
export const api = new Http({
  loading: false,
  baseUrl: {
    dev: "https://wx.dmeetclub.com/api/",
    pro: "https://wx.dmeetclub.com/api/",  
  },
  takeTokenMethod: "header",
  takenTokenKeyName: "token",
  tokenValue: () => {
    return new Promise((resolve, reject) => {
      resolve(store.state.user.token);
    });
  },
  env: "mp-weixin",
  errorHandleByCode: (code: number, errMsg?: string) => {
    if (code != 200) {
      uni.showToast({
        duration: 2000,
        icon: "none",
        title: errMsg,
      });
    }
  },
  xhrCode: 1,
  xhrCodeName: "code",
  before: (res) => {
    const now = new Date();

    res.header.timezone = now.getTimezoneOffset() / 60;
    return res;
  },
  apiErrorInterception: (
    data: any,
    args?: UniApp.RequestSuccessCallbackResult
  ) => {
    uni.showToast({
      duration: 2000,
      icon: "none",
      title: data.msg,
    });
  },
});
export const formatDate = (date) => {
  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0"); // 月份是从0开始的
  const year = date.getFullYear();
  const hours = date.getHours().toString().padStart(2, "0");
  const minutes = date.getMinutes().toString().padStart(2, "0");
  // const seconds = date.getSeconds().toString().padStart(2, '0');

  return `${year}-${month}-${day} ${hours}:${minutes}`;
  // :${seconds}
};

export const formatDateForDay = (date) => {
  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0"); // 月份是从0开始的
  const year = date.getFullYear();

  return `${year}-${month}-${day}`;
};
export type * from "lwu-request";
