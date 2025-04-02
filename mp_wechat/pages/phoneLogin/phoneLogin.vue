<template>
    <view class="phone-login">
      <input type="text" v-model="phone" placeholder="请输入手机号" />
      <input type="text" v-model="code" placeholder="请输入验证码" />
      <button @click="getCode">获取验证码</button>
      <button @click="loginByPhone">登录</button>
    </view>
  </template>
  
  <script>
  export default {
    data() {
      return {
        phone: '',
        code: ''
      };
    },
    methods: {
      getCode() {
        // 调用获取验证码的接口
        uni.request({
          url: '/api/getCode',
          method: 'POST',
          data: {
            phone: this.phone
          },
          success: (res) => {
            if (res.data.code === 200) {
              uni.showToast({
                title: '验证码发送成功',
                icon: 'success'
              });
            } else {
              uni.showToast({
                title: res.data.message,
                icon: 'none'
              });
            }
          },
          fail: (err) => {
            uni.showToast({
              title: '网络请求失败',
              icon: 'none'
            });
          }
        });
      },
      loginByPhone() {
        // 调用手机号登录的接口
        uni.request({
          url: '/api/loginByPhone',
          method: 'POST',
          data: {
            phone: this.phone,
            code: this.code
          },
          success: (res) => {
            if (res.data.code === 200) {
              uni.showToast({
                title: '登录成功',
                icon: 'success'
              });
              // 登录成功后的处理，例如跳转页面
              uni.navigateTo({
                url: '/pages/home/home'
              });
            } else {
              uni.showToast({
                title: res.data.message,
                icon: 'none'
              });
            }
          },
          fail: (err) => {
            uni.showToast({
              title: '网络请求失败',
              icon: 'none'
            });
          }
        });
      }
    }
  };
  </script>
  
  <style scoped>
  .phone-login {
    padding: 20px;
  }
  </style>