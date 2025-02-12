<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar @clickLeft="navBack" left-icon="left" :border="false" title="提现记录" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<scroll-view :scroll-y="true" type="list" class="scroll" @scrolltolower="loadDetails">
				<view class="item" v-for="(item,index) in dataList" :key="'amount'+index">
					<view :style="{display:'flex',flexDirection:'row',alignItems:'center',justifyContent:'space-between'}">
						<view class="item-left">
							<text class="txt1">提现</text>
							<text class="txt2">{{item.create_time_text}}</text>
						</view>
						<view :style="{display:'flex',flexDirection:'column',alignItems:'flex-end'}">
							<text class="item-amount">{{item.price}}</text>
							<text v-if="item.status == 1" class="item-status type1">{{item.status_text}}</text>
							<text v-if="item.status == 2" class="item-status type2">{{item.status_text}}</text>
							<text v-if="item.status == 3" class="item-status type3">{{item.status_text}}</text>
							<text v-if="item.status == 4" class="item-status type2">{{item.status_text}}</text>
						</view>
					</view>
					<view v-if="item.status == 3" class="reject">
						<text class="reject-txt">驳回原因：{{item.reject_reason}}</text>
					</view>
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
			const res:any = await api.post('my/shop/cash_list',{
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
				padding: 32rpx 0;
				box-sizing: border-box;
				border-bottom: 1px solid #eee;
				display: flex;
				flex-direction: column;
				margin: 0 auto;
				&-left{
					display: flex;
					flex-direction: column;
					.txt1{
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
				&-status {
					margin-top: 8rpx;
					font-size: 24rpx;
				}
				.type1{
					color: #2C94FF;
				}
				.type2{
					color: #0DC84D;
				}
				.type3{
					color: #FF546F;
				}
				.reject{
					margin-top: 24rpx;
					width: 100%;
					background-color: #f7f8fa;
					border-radius: 8rpx;
					padding: 16rpx 24rpx;
					box-sizing: border-box;
					word-wrap: break-word; /* 使得长单词或数字可以换行 */
					overflow-wrap: break-word; /* 确保兼容性 */
					&-txt{
						font-size: 24rpx;
						color: #4E5769;
						word-wrap: break-word; /* 使得长单词或数字可以换行 */
						overflow-wrap: break-word; /* 确保兼容性 */
					}
				}
			}
		}
	}
	
}
</style>
