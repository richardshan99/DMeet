<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar :border="false" left-icon="left" @clickLeft="navBack" title="详情" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<image :src="app.config.globalProperties.$imgBase+'/xlyl_meet/announcement/banner.png'" class="banner"></image>
			<view class="content" v-html="announceDetail.content"></view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { ref, getCurrentInstance, reactive } from 'vue'
	import { api } from '@/common/request/index.ts'
	import { onLoad } from '@dcloudio/uni-app'
	
	const app = getCurrentInstance().appContext.app
	
	const announceDetail = reactive({
		id: null,
		intro: null,
		content: null,
		create_time_text: null
	})
	
	onLoad((options) => {
		api.post('/index/notice_detail',{id: options.id}).then((res:any) => {
			if(res.code == 1) {
				Object.assign(announceDetail,res.data)
			}
		})
	})
	const navBack = () => {
		uni.navigateBack()
	}
</script>

<style lang="scss" scoped>
.main{
	width: 100%;
	height: 100%;
	background-color: #fff;
	display: flex;
	flex-direction: column;
	&-top{
		position: absolute;
		top: 0;
		left: 0;
		z-index: 9;
		width: 750rpx;
		height: 500rpx;
	}
	&-base{
		width: 100%;
		height: 100%;
		z-index: 10;
		display: flex;
		flex-direction: column;
		align-items: center;
		.banner{
			width: 686rpx;
			height: 224rpx;
			margin-top: 16rpx;
		}
		.content{
			width: 686rpx;
			margin-top: 40rpx;
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			overflow-y: auto;
		}
	}
}
</style>
