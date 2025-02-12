<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" :border="false" :shadow="false" title="见面分享" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<scroll-view class="scroll" :scroll-y="true" type="list" @scrolltolower="loadDynamic">
				<view class="scroll-base" v-for="(item,ind) in dataList" :key="ind+'dynamic'">
					<dynamic-item @refresh="getDynamics(true)" :isMine="false" :dynamicItem="item"></dynamic-item>
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
	import { onLoad, onUnload } from '@dcloudio/uni-app'
	const dataList = ref([]);
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	const app = getCurrentInstance().appContext.app
	
	
	
	onLoad(() => {
		getDynamics(true)
		uni.$on('refreshMyDynamic',refreshData)
	})
	
	onUnload(() => {
		uni.$off('refreshMyDynamic',refreshData)
	})
	const navBack = () => {
		uni.navigateBack()
	}
	const loadDynamic = () => {
		pageNo++
		getDynamics(false)
	}
	
	const refreshData = () => {
		getDynamics(true)
	}
	
	const getDynamics = async (refresh:boolean) => {
		if (refresh) {
			pageNo = 1;
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
			const res:any = await api.post('/blog/list',{
				page: pageNo,
				style: 2
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
	background-color: #fff;
	display: flex;
	flex-direction: column;
	&-add{
		width: 88rpx;
		height: 88rpx;
		position: fixed;
		right: 32rpx;
		bottom: 96rpx;
		z-index: 999;
	}
	&-top{
		position: absolute;
		top: 0;
		left: 0;
		z-index: 9;
		width: 750rpx;
		height: 500rpx;
	}
	&-base{
		position: relative;
		width: 100%;
		height: 100%;
		z-index: 10;
		display: flex;
		flex-direction: column;
		align-items: center;
		.scroll{
			margin-top: 40rpx;
			flex: 1;
			min-height: 0;
			width: 100%;
			&-base{
				width: 686rpx;
				display: flex;
				flex-direction: column;
				margin: 0 auto;
			}
			&-status {
				font-size: 26rpx;
				color: #999;
				text-align: center;
				margin: 20rpx auto;
				display: block;
			}
		}
	}
}
</style>
