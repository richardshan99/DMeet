<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="系统消息" :statusBar="true"></uni-nav-bar>
			<scroll-view type="list" :scroll-y="true" class="scroll" @scrolltolower="loadSystemList()">
				<view class="system" v-for="(item,ind) in dataList" :key="'system'+ind">
					<text class="system-time">{{item.create_time_text}}</text>
					<view :style="{display:'flex',flexDirection:'row',alignItems:'flex-start'}">
						<image src="/static/news/system_mes.png" class="system-icon"></image>
						<view class="system-mes">
							<text class="txt">{{item.message}}</text>
						</view>
					</view>
				</view>
				<text v-if="finish" class="scroll-status">{{dataList.length == 0?'没数据了':'数据加载完毕'}}</text>
				<text v-else class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
			</scroll-view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import { getCurrentInstance, ref } from 'vue'
	import { onLoad } from '@dcloudio/uni-app'
	const app = getCurrentInstance().appContext.app
	
	const dataList = ref([]);
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	
	onLoad(() => {
		getSystemList(true)
	})
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const loadSystemList = () => {
		pageNo++
		getSystemList(false)
	}
	
	const getSystemList = async (refresh:boolean) => {
		if (refresh) {
			pageNo = 1
			finish.value = false
		}
		if (finish.value) {
			return;
		}
		if (loading.value) {
			return;
		}
		loading.value = true
		try {
			const res:any = await api.post('message/system_list',{
				page: pageNo
			})
			loading.value = false
			if (res.code == 1) {
				if (refresh) {
					dataList.value = res.data.data
				} else {
					dataList.value = dataList.value.concat(res.data.data)
				}
				if (dataList.value.length == res.data.total) {
					finish.value = true
				}
			}
		}catch(e) {
			loading.value = false
		}
	}
</script>

<style lang="scss" scoped>
.main{
	width: 100%;
	height: 100%;
	background-color: #f7f8fa;
	display: flex;
	flex-direction: column;
	position: relative;
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
		.scroll {
			margin-top: 32rpx;
			width: 100%;
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			&-status {
				font-size: 26rpx;
				color: #999;
				text-align: center;
				margin: 20rpx auto;
				display: block;
			}
			.system{
				width: 686rpx;
				display: flex;
				flex-direction: column;
				margin: 0 auto 40rpx auto;
				&-time{
					margin: 0 auto 16rpx auto;
					font-size: 20rpx;
					color: #868D9C;
				}
				&-icon{
					width: 72rpx;
					height: 72rpx;
				}
				&-mes{
					margin-left: 16rpx;
					width: 510rpx;
					background-color: #ffffff;
					border-radius: 0px 16rpx 16rpx 16rpx;
					padding: 16rpx 24rpx;
					box-sizing: border-box;
					.txt{
						display: block;
						font-size: 24rpx;
						color: #1D2129;
						line-height: 40rpx;
					}
				}
			}
		}
	}
}
</style>
