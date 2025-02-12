<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="打招呼" :statusBar="true"></uni-nav-bar>
			<scroll-view type="list" :scroll-y="true" class="scroll" @scrolltolower="loadSystemList">
				<view class="hello" v-for="(item,index) in dataList" :key="'hello'+index" @click="toLeaveMessage(item)">
					<image mode="aspectFill" :src="item.relationuser.avatar_text" class="hello-icon"></image>
					<view class="hello-desc">
						<view class="first">
							<text class="name">{{item.relationuser.nickname}}</text>
							<text class="time">{{item.update_time_text}}</text>
						</view>
						<view class="second">
							<text class="txt1">{{item.message}}</text>
							<text v-if="item.unread_num > 0" class="txt2">{{item.unread_num}}</text>
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
	
	const toLeaveMessage = (item:any) => {
		uni.navigateTo({
			url: `/pages/leave_message/leave_message`,
			success(res) {
				res.eventChannel.emit('acceptDataFromOpenerPage',{
					nickname: item.relationuser.nickname,
					avatar: item.relationuser.avatar_text,
					id: item.relationuser.id
				})
			}
		})
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
			const res:any = await api.post('message/chat_user_list',{
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
			.hello{
				width: 686rpx;
				height: 144rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				margin: 0 auto;
				&-icon{
					width: 96rpx;
					height: 96rpx;
					flex-shrink: 0;
					border-radius: 48rpx;
				}
				&-desc{
					margin-left: 24rpx;
					flex: 1;
					flex-shrink: 0;
					display: flex;
					flex-direction: column;
					min-width: 0;
					.first{
						display: flex;
						flex-direction: row;
						width: 100%;
						// justify-content: space-between;
						align-items: center;
						.name{
							flex: 1;
							flex-shrink: 0;
							min-width: 0;
							font-size: 28rpx;
							color: #1D2129;
							white-space: nowrap;
							overflow: hidden;
							text-overflow: ellipsis;
						}
						.time{
							font-size: 20rpx;
							color: #1D2129;
						}
					}
					.second{
						margin-top: 4rpx;
						display: flex;
						flex-direction: row;
						width: 100%;
						align-items: center;
						.txt1{
							flex: 1;
							flex-shrink: 0;
							min-width: 0;
							font-size: 24rpx;
							color: #868d9c;
							line-height: 40rpx;
							white-space: nowrap;
							overflow: hidden;
							text-overflow: ellipsis;
						}
						.txt2{
							display: block;
							padding: 6rpx 15rpx;
							box-sizing: border-box;
							font-size: 20rpx;
							color: #FFFFFF;
							background-color: #FF546F;
							border-radius: 50%;
						}
					}
				}
			}

		}
	}
}
</style>
