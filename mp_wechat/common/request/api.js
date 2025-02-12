import { api } from "./index.ts";
// 获取验证码
export const getCode = (data) => {
  return api.post("ems/send", { email: data.email, event: "improve" });
};
