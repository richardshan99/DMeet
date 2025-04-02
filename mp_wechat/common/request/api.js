import { api } from "./index.ts";

// 获取邮箱验证码
export const getCode = (data) => {
  return api.post("ems/send", { email: data.email, event: "improve" });
};
// 获取手机验证码
export const getPhoneCode = (data) => {
  return api.post("sms/send", { mobile: data.mobile, event: "register" });
};
