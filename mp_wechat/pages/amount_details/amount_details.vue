<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar @clickLeft="navBack" left-icon="left" :border="false" title="金额明细" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<scroll-view type="list" class="scroll" @scrolltolower="loadDetails" :scroll-y="true">
				<view class="item" v-for="(item,index) in dataList" :key="'amount'+index">
					<view class="item-left">
						<text class="txt1">{{item.balance_type_text}}</text>
						<text class="txt2">{{item.create_time_text}}</text>
					</view>
					<text class="item-amount">{{item.price}}</text>
				</view>
				<text v-if="finish" class="scroll-status">{{dataList.length == 0?'没数据了':'数据加载完毕'}}</text>
				<text v-else class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
				<view :style="{height: '96px',width:'100%'}"></view>
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
		getDetails(true)
	})
	
	const loadDetails = () => {
		pageNo++
		getDetails(false)
	}
	
	const getDetails = async (refresh:boolean) => {
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
			const res:any = await api.post('my/shop/balance_list',{
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
	
	const navBack = () => {
		uni.navigateBack()
	}
</script>

<style lang="scss" scoped>
.main{
	width: 100%;
	height: 100%;
	background-color: #F7F8FA;
	display: flex;
	flex-direction: column;
	align-items: center;
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
			width: 100%;
			flex: 1;
			min-height: 0;
			flex-shrink: 0;
			&-status {
				font-size: 26rpx;
				color: #999;
				text-align: center;
				margin: 20rpx auto;
				display: block;
			}
			.item{
				width: 686rpx;
				margin: 0 auto;
				display: flex;
				flex-direction: row;
				align-items: center;
				padding: 32rpx 0;
				box-sizing: border-box;
				border-bottom: 1px solid #eee;
				&-left{
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					display: flex;
					flex-direction: column;
					.txt1{
						width: 100%;
						font-size: 28rpx;
						color: #1D2129;
					}
					.txt2{
						margin-top: 8rpx;
						font-size: 24rpx;
						color: #868D9C;
					}
				}
				&-amount{
					font-size: 32rpx;
					color: #1D2129;
					font-weight: 500;
				}
			}
		}
	}
	
}
</style>
