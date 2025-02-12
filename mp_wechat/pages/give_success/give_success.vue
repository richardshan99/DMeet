<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar @clickLeft="navBack" left-icon="left" :border="false" title="发起成功" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<image src="/static/give_invitation/sucess.png" class="success"></image>
			<text class="title">已成功发起邀约</text>
			<text class="second_title">等待对方同意，你可在邀约中查看</text>
			<view class="third">
				<view @click="toHome" class="left">
					<text>返回首页</text>
				</view>
				<view @click="toInvitation" class="right">
					<view class="child">
						<text class="txt">查看邀约</text>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { getCurrentInstance } from 'vue'
	const app = getCurrentInstance().appContext.app
	const toInvitation = () => {
		uni.switchTab({
			url:'/pages/invitation/invitation'
		})
	}
	
	const toHome = () => {
		uni.switchTab({
			url:'/pages/index/index'
		})
	}
	
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
		position: relative;
		width: 100%;
		min-height: 100%;
		overflow-y: auto;
		z-index: 10;
		display: flex;
		flex-direction: column;
		align-items: center;
		.success{
			width: 146rpx;
			height: 144rpx;
			margin-top: 64rpx;
		}
		.title{
			margin-top: 40rpx;
			font-size: 32rpx;
			color: #1d2129;
			font-weight: 500;
		}
		.second_title{
			font-size: 28rpx;
			color: #868D9C;
			margin-top: 8rpx;
		}
		.third{
			margin-top: 40rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			.left{
				width: 176rpx;
				height: 64rpx;
				background: #ffffff;
				border: 1px solid #dadce0;
				border-radius: 32rpx;
				font-size: 28rpx;
				color: #1D2129;
				font-weight: 500;
				line-height: 64rpx;
				text-align: center;
			}
			.right{
				margin-left: 24rpx;
				width: 176rpx;
				height: 64rpx;
				background: linear-gradient(107deg, #4a97e7, #b57aff 100%);
				border-radius: 32rpx;
				padding: 1rpx;
				box-sizing: border-box;
				display: flex;
				flex-direction: column;
				.child{
					flex: 1;
					flex-shrink: 0;
					min-height: 0;
					background-color: #fff;
					border-radius: 32rpx;
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: center;
					.txt{
						font-size: 28rpx;
						font-weight: 500;
						background: linear-gradient(109deg,#4a97e7, #b57aff 100%);
						-webkit-background-clip: text;
						-webkit-text-fill-color: transparent;
					}
				}
			}
		}
	}
}
</style>
