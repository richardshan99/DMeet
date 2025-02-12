<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="我的活动" :statusBar="true"></uni-nav-bar>
			<view class="navtion">
				<view @click="changeStatus(1)" class="navtion-bar">
					<text class="txt" :class="{'active':(status == 1)}">已报名</text>
					<view v-if="status == 1" class="line"></view>
				</view>
				<view @click="changeStatus(2)" class="navtion-bar">
					<text class="txt" :class="{'active':(status == 2)}">已结束</text>
					<view v-if="status == 2" class="line"></view>
				</view>
				<view @click="changeStatus(3)" class="navtion-bar">
					<text class="txt" :class="{'active':(status == 3)}">已退款</text>
					<view v-if="status == 3" class="line"></view>
				</view>
			</view>
			<scroll-view class="scroll" :scroll-y="true" type="list" @scrolltolower="loadMyActivity" :style="{marginTop:'24rpx'}">
				<view v-for="(item,ind) in dataList" :key="ind+'activity_mine'" class="item" @click="toDetail(item.activity.id)">
					<view :style="{display:'flex',flexDirection:'row'}">
						<image mode="aspectFill" :src="item.activity.thumb_text" class="item-mainImg"></image>
						<view class="item-intro">
							<text class="txt1">{{item.activity.name}}</text>
							<view :style="{display:'flex',flexDirection:'column'}">
								<view class="row">
									<image src="/static/activity/title_tag.png" class="icon"></image>
									<text class="txt">{{item.activity.type_text}}</text>
								</view>
								<view class="row" :style="{marginTop:'4rpx'}">
									<image src="/static/activity/time.png" class="icon"></image>
									<text class="txt">{{item.activity.begin_time_text}}</text>
								</view>
							</view>
						</view>
					</view>
					<view class="item-bottom">
						<view class="area">
							<image src="/static/activity/area_location.png" class="area-icon"></image>
							<text class="area-txt">{{item.activity.area}}</text>
						</view>
						<view @click="reFund(item.id)" v-if="item.status == 1" class="refund">
							<text class="refund-txt">我要退款</text>
						</view>
						<view v-else class="finish">
							<text class="finish-txt">{{item.status_text}}</text>
						</view>
					</view>
				</view>
				<text v-if="finish" class="scroll-status">{{dataList.length == 0?'没数据了':'数据加载完毕'}}</text>
				<text v-else class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
				<view :style="{height: '20px'}"></view>
			</scroll-view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import { ref, getCurrentInstance } from 'vue'
	import { onLoad } from '@dcloudio/uni-app'
	const app = getCurrentInstance().appContext.app
	const status = ref(1) //	参与状态： 1、已报名 2、已结束 3、已退款
	
	
	const dataList = ref([]);
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	
	onLoad(() => {
		getMyActivity(true)
	})
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const changeStatus = (num:number) => {
		if (status.value != num) {
			status.value = num
			getMyActivity(true)
		}
	}
	
	const loadMyActivity = () => {
		pageNo++
		getMyActivity(false)
	}
	
	const reFund = (ind:string) => {
		uni.showModal({
			title: "退款提示",
			content: "是否确认退款？",
			success:async function(res) {
				if (res.confirm) {
					const vres:any = await api.post('/my/activity/refund',{id: ind})
					if (vres.code == 1) {
						uni.showToast({
							icon: 'none',
							title: '已退款'
						})
						getMyActivity(true)
					}
				}
			}
		})
	}
	
	const toDetail = (id:string) => {
		uni.navigateTo({
			url: `/pages/activity_detail/activity_detail?id=${id}`
		})
	}
	
	const getMyActivity = async (refresh:boolean) => {
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
			const res:any = await api.post('my/activity/list',{
				status: status.value,
				page: pageNo
			})
			loading.value = false
			if (res.code == 1) {
				if (refresh) {
					dataList.value = res.data.data
					console.error(dataList.value)
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
.main {
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
		.navtion{
			width: 100%;
			height: 44px;
			display: flex;
			flex-direction: row;
			align-items: center;
			&-bar{
				flex: 1;
				height: 100%;
				min-width: 0;
				flex-shrink: 0;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				position: relative;
				.txt{
					font-size: 28rpx;
					color: #1D2129;
					font-weight: 500;
				}
				.active{
					color: #2C94FF;
				}
				.line{
					position: absolute;
					bottom: 0;
					left: 0;
					right: 0;
					margin: 0 auto;
					width: 40rpx;
					height: 2px;
					background-color: #2c94ff;
					border-radius: 2px;
				}
			}
		}
	
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
				padding: 24rpx;
				box-sizing: border-box;
				background-color: #fff;
				border-radius: 24px;
				display: flex;
				flex-direction: column;;
				margin: 0 auto 24rpx auto;
				&-mainImg{
					width: 288rpx;
					height: 192rpx;
					border-radius: 16rpx;
				}
				&-intro{
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					margin-left: 28rpx;
					display: flex;
					flex-direction: column;
					justify-content: space-between;
					.txt1{
						font-size: 28rpx;
						color: #1D2129;
						font-weight: 500;
						line-height: 44rpx;
						display: -webkit-box;
						-webkit-line-clamp: 2;
						-webkit-box-orient: vertical;
						overflow: hidden;
						text-overflow: ellipsis;
					}
					.row{
						display: flex;
						flex-direction: row;
						align-items: center;
						.icon{
							width: 24rpx;
							height: 26rpx;
						}
						.txt{
							margin-left: 8rpx;
							font-size: 22rpx;
							color: #868D9C;
							line-height: 38rpx;
						}
					}
				}
				&-bottom{
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: space-between;
					margin-top: 24rpx;
					.area{
						display: flex;
						flex-direction: row;
						align-items: center;
						&-icon{
							width: 20rpx;
							height: 24rpx;
						}
						&-txt{
							margin-left: 8rpx;
							font-size: 26rpx;
							color: #868D9C;
						}
					}
					.signUp{
						width: 144rpx;
						height: 56rpx;
						border-radius: 96rpx;
						font-size: 24rpx;
						line-height: 56rpx;
						text-align: center;
						color: #fff;
					}
					.refund{
						border: 1px solid #E8EAEF;
						padding: 8rpx 24rpx;
						box-sizing: border-box;
						border-radius: 96rpx;
						&-txt{
							display: block;
							font-size: 24rpx;
							color: #1D2129;
							font-weight: 500;
						}
					}
					.finish{
						padding: 8rpx 36rpx;
						background-color: #DADCE0;
						border-radius: 96rpx;
						&-txt{
							display: block;
							font-size: 24rpx;
							color: #fff;
							font-weight: 500;
						}
					}
					.already{
						background: #DADCE0;
					}
					.non{
						background: linear-gradient(109deg,#4a97e7, #b57aff 100%);;
					}
				}
			}
		}
	}
}
</style>
