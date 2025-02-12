<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="邀约" :statusBar="true"></uni-nav-bar>
			<scroll-view type="list" :scroll-y="true" class="scroll" @scrolltolower="loadSystemList()">
				<view class="invitation" v-for="(item,index) in dataList" :key="'invitation'+index" @click="toInvitation">
					<view class="invitation-top">
						<view class="head">
							<image mode="aspectFill" :src="item.fromuser.avatar_text" class="avatar"></image>
							<image v-if="item.biz_type == 1" mode="aspectFill" src="/static/invitation/send_invitation.png" class="tag"></image>
							<image v-else mode="aspectFill" src="/static/news/accept_tag.png" class="tag"></image>
						</view>
						<view class="userInfo">
							<view class="row1">
								<text class="username">{{item.fromuser.nickname}}</text>
								<image v-if="item.fromuser.gender == 1" src="/static/sex_man.png" class="sex"></image>
								<image v-if="item.fromuser.gender == 2" src="/static/sex_woman.png" class="sex"></image>
								<image v-if="item.fromuser.is_cert_realname == 1" src="/static/person/confirm_name.png" class="icon_other"></image>
								<image v-if="item.fromuser.is_cert_education == 1" src="/static/person/edu.png" class="icon_other"></image>
								<image v-if="item.fromuser.is_member == 1" src="/static/vip_icon.png" class="icon_other"></image>
							</view>
							<text class="row2">{{item.fromuser.birth_text}}年 · {{item.fromuser.height}}cm · {{item.fromuser.area}}</text>
							<view class="row3">
								<text class="item">{{item.fromuser.school}}</text>
								<text class="item">{{item.fromuser.education_type_text}}</text>
								<text class="item">{{item.fromuser.work_type_text}}</text>
							</view>
						</view>
					</view>
					<view class="invitation-bottom">
						<text class="time">{{item.create_time_text}}</text>
						<image src="/static/invitation/right.png" class="right"></image>
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
	
	const toInvitation = () => {
		uni.switchTab({
			url: '/pages/invitation/invitation'
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
			const res:any = await api.post('message/invitation_list',{
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
			margin-top: 24rpx;
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
			.invitation{
				width: 686rpx;
				background-color: #ffffff;
				border-radius: 24rpx;
				padding: 32rpx 32rpx 24rpx 32rpx;
				box-sizing: border-box;
				display: flex;
				flex-direction: column;
				align-items: stretch;
				margin: 0 auto 24rpx auto;
				&-top{
					padding-bottom: 24rpx;
					box-sizing: border-box;
					display: flex;
					flex-direction: row;
					align-items: center;
					border-bottom: 1px dashed #E8EAEF;
					.head{
						width: 160rpx;
						height: 176rpx;
						position: relative;
						display: flex;
						flex-direction: column;
						align-items: center;
						.avatar{
							width: 160rpx;
							height: 160rpx;
							border-radius: 16rpx;
							position: relative;
							z-index: 9;
						}
						.tag{
							width: 166rpx;
							height: 36rpx;
							position: absolute;
							bottom: 0;
							z-index: 11;
						}
					}
					.userInfo{
						display: flex;
						flex-direction: column;
						margin-left: 24rpx;
						justify-content: center;
						.row1{
							display: flex;
							flex-direction: row;
							align-items: center;
							.username{
								font-size: 28rpx;
								font-weight: 500;
								color: #1D2129;
							}
							.sex{
								margin-left: 16rpx;
								height: 32rpx;
								width: 32rpx;
							}
							.icon_other{
								margin-left: 8rpx;
								height: 32rpx;
								width: 32rpx;
							}
						}
						.row2{
							margin-top: 2rpx;
							font-size: 22rpx;
							color: #868D9C;
							line-height: 38rpx;
						}
						.row3{
							display: flex;
							flex-direction: row;
							align-items: center;
							margin-top: 12rpx;
							.item{
								padding: 4rpx 16rpx;
								border: 1px solid #e8eaef;
								border-radius: 12rpx;
								box-sizing: border-box;
								display: block;
								font-size: 24rpx;
								color: #4E5769;
								margin-right: 12rpx;
							}
						}
					}
				}
				&-bottom{
					margin-top: 24rpx;
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: space-between;
					.time{
						font-size: 24rpx;
						color: #868D9C;
					}
					.right{
						width: 16rpx;
						height: 24rpx;
					}
				}
			}
		}
	}
}
</style>
