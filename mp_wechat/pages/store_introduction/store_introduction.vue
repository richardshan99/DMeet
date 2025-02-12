<template>
	<view class="main">
		<view class="main-swiper">
			<swiper :current="current" @change="changeCurrent" class="imgs" v-if="contentImgs.length > 0">
				<swiper-item v-for="(item,index) in contentImgs" :key="'swiper'+index">
					<image class="imgs" :src="item"></image>
				</swiper-item>
			</swiper>
			<view class="progress" v-if="contentImgs.length > 0">
				<text class="progress-txt">{{current+1}}/{{contentImgs.length}}</text>
			</view>
		</view>
		<text class="main-content">{{contentHtml}}</text>	
			
	</view>
</template>

<script lang="ts" setup>
	import {ref, getCurrentInstance } from 'vue'
	import { api } from '@/common/request/index.ts'
	import { onLoad } from '@dcloudio/uni-app'
	const { proxy } = getCurrentInstance()
	const current = ref(0)
	
	onLoad(() => {
		const eventChannel = proxy.getOpenerEventChannel()
		eventChannel.on('acceptDataFromOpenerPage',(data:any) => {
			// 获取上个页面的传值
			if (data != null && data.html != null) {
				console.log(data.imgs)
				contentImgs.value = data.imgs
				contentHtml.value = data.html
			}
		})
	})
	
	const contentImgs = ref([])
	const contentHtml = ref(null)
	const changeCurrent = (e:any) => {
		if (current.value != e.detail.current) {
			current.value = e.detail.current
		}
	}
</script>

<style lang="scss" scoped>
.main{
	width: 100%;
	min-height: 100%;
	background-color: #f7f8fa;
	display: flex;
	flex-direction: column;
	position: relative;
	overflow-y: auto;
	&-content{
		width: 686rpx;
		margin: 32rpx auto 0 auto;
		font-size: 26rpx;
		color: #1D2129;
		line-height: 46rpx;
	}
	&-swiper{
		width: 750rpx;
		height: 500rpx;
		position: relative;
		z-index: 9;
		.imgs{
			width: 100%;
			height: 100%;
			position: relative;
			z-index: 10;
		}
		.progress{
			position: absolute;
			z-index: 11;
			right: 24rpx;
			bottom: 24rpx;
			padding: 4rpx 16rpx;
			background-color: rgba(0,0,0,0.30);
			border-radius: 24rpx;
			&-txt{
				display: block;
				font-size: 24rpx;
				color: #FFFFFF;
			}
		}
	}
}
</style>
