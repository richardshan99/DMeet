<template>
  <view class="container">
    <view class="form-item">
      <text>手机号</text>
      <input type="number" v-model="phoneNumber" placeholder="请输入手机号" />
    </view>
    <view class="form-item">
      <text>验证码</text>
      <input type="number" v-model="verificationCode" placeholder="请输入验证码" />
      <button @click="sendVerificationCode" :disabled="isSending">{{ sendButtonText }}</button>
    </view>
    <button @click="register">注册</button>
  </view>
</template>

<script lang="ts">
import { defineComponent, ref, getCurrentInstance,  computed } from 'vue';
import { api } from "@/common/request/index.ts";

import { useStore } from "vuex";
export default defineComponent({
  setup() {
    const phoneNumber = ref<string>('');
    const verificationCode = ref<string>('');
    const isSending = ref<boolean>(false);
    const sendButtonText = ref<string>('发送验证码');
    const countdown = ref<number>(60);

    const sendVerificationCode =async () => {
      if (!phoneNumber.value) {
        uni.showToast({
          title: '请输入手机号',
          icon: 'none'
        });
        return;
      }

      isSending.value = true;
      sendButtonText.value = `${countdown.value}秒后重试`;

      // 调用发送验证码的接口
	  const result: any = await api.post("sms/send", {
	    mobile: phoneNumber.value, // res.code,
	  });
	  console.log(result);
	  if(result.code==1){
		uni.showToast({
		  title: result.msg,
		  icon: 'none'
		});
		startCountdown();	
	  }else{
		  uni.showToast({
		    title: result.msg,
		    icon: 'none'
		  });
		  isSending.value = false;
		  sendButtonText.value = '发送验证码';
	  }
	  
      
    };

    const startCountdown = () => {
      const interval = setInterval(() => {
        countdown.value--;
        sendButtonText.value = `${countdown.value}秒后重试`;
        if (countdown.value === 0) {
          clearInterval(interval);
          isSending.value = false;
          sendButtonText.value = '发送验证码';
          countdown.value = 60;
        }
      }, 1000);
    };

    const register = () => {
      if (!phoneNumber.value || !verificationCode.value) {
        uni.showToast({
          title: '请输入手机号和验证码',
          icon: 'none'
        });
        return;
      }

      // 调用注册接口
      uni.request({
        url: 'https://your-api-endpoint/register', // 替换为你的注册接口
        method: 'POST',
        data: {
          mobile: phoneNumber.value,
          captcha: verificationCode.value
        },
        success: (res) => {
          if (res.data.code === 1) {
            uni.showToast({
              title: '注册成功',
              icon: 'none'
            });
            // 注册成功后的操作，例如跳转到登录页面
          } else {
            uni.showToast({
              title: res.data.msg,
              icon: 'none'
            });
          }
        },
        fail: (err) => {
          uni.showToast({
            title: '注册失败，请重试',
            icon: 'none'
          });
        }
      });
    };

    return {
      phoneNumber,
      verificationCode,
      isSending,
      sendButtonText,
      sendVerificationCode,
      register
    };
  }
});
</script>

<style>
.container {
  padding: 20px;
}
.form-item {
  margin-bottom: 20px;
}
.form-item text {
  display: block;
  margin-bottom: 10px;
}
.form-item input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
button {
  width: 100%;
  padding: 10px;
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 4px;
  text-align: center;
}
button:disabled {
  background-color: #ccc;
}
</style>