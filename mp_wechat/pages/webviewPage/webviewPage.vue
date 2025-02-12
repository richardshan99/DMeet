<template>
    <view>
      <web-view :src="url"></web-view>
    </view>
  </template>
  
  <script lang="ts" setup>
  import { onLoad } from '@dcloudio/uni-app';
  import { ref } from 'vue';
  
  const url = ref('');
  
  onLoad((option) => {
    url.value = option.url;
  });
  </script>