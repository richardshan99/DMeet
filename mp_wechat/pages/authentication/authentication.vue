<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="实名认证" :statusBar="true"></uni-nav-bar>
			<view class="authItem">
				<view class="authItem-option">
					<image src="/static/authentication/real_name.png" class="icon"></image>
					<view class="intro">
						<text class="intro-title">实名认证</text>
						<text class="intro-desc">提供真实可信的交友环境</text>
					</view>
					<view @click="openPage(1)" v-if="userInfo.cert.realname_status == -1" class="status2">
						<text>去认证</text>
					</view>
					<view v-else class="status1">
						<image v-if="userInfo.cert.realname_status == 2" class="status1-icon" src="/static/authentication/correct.png"></image>
						<text class="status1-txt"> {{userInfo.cert.realname_status == 1?'审核中':'已认证'}}</text>
					</view>
				</view>
			</view>
			<view class="authItem">
				<view class="authItem-option">
					<image src="/static/authentication/edu_record.png" class="icon"></image>
					<view class="intro">
						<text class="intro-title">学历认证</text>
						<text class="intro-desc">优秀的学历人群都在这里</text>
					</view>
					<view @click="openPage(2)" v-if="userInfo.cert.education_status == -1" class="status2">
						<text>去认证</text>
					</view>
					<view v-else-if="userInfo.cert.education_status == 1||userInfo.cert.education_status == 2" class="status1">
						<image v-if="userInfo.cert.education_status == 2" class="status1-icon" src="/static/authentication/correct.png"></image>
						<text class="status1-txt"> {{userInfo.cert.education_status == 1?'审核中':'已认证'}}</text>
					</view>
					<view @click="openPage(2)" v-else-if="userInfo.cert.education_status == 3" class="status2">
						<text>重新认证</text>
					</view>
				</view>
				<view v-if="userInfo.cert.education_status == 3" class="authItem-error">
					<view class="first">
						<image src="/static/authentication/reject.png" class="first-img"></image>
						<text class="first-result">认证未通过</text>
					</view>
					<text class="reason">拒绝原因：{{userInfo.cert.cert_reject_education}}</text>
				</view>
			</view>
			<view class="authItem">
				<view class="authItem-option">
					<image src="/static/authentication/work.png" class="icon"></image>
					<view class="intro">
						<text class="intro-title">工作认证</text>
						<text class="intro-desc">职场单身人士聚集地</text>
					</view>
					<view @click="openPage(3)" v-if="userInfo.cert.work_status == -1" class="status2">
						<text>去认证</text>
					</view>
					<view v-else-if="userInfo.cert.work_status == 1||userInfo.cert.work_status == 2" class="status1">
						<image v-if="userInfo.cert.work_status == 2" class="status1-icon" src="/static/authentication/correct.png"></image>
						<text class="status1-txt"> {{userInfo.cert.work_status == 1?'审核中':'已认证'}}</text>
					</view>
					<view @click="openPage(3)" v-else-if="userInfo.cert.work_status == 3" class="status2">
						<text>重新认证</text>
					</view>
				</view>
				<view v-if="userInfo.cert.work_status == 3" class="authItem-error">
					<view class="first">
						<image src="/static/authentication/reject.png" class="first-img"></image>
						<text class="first-result">认证未通过</text>
					</view>
					<text class="reason">拒绝原因：{{userInfo.cert.cert_reject_work}}</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { getCurrentInstance,computed } from 'vue'
	import { useStore } from 'vuex'
	const app = getCurrentInstance().appContext.app
	const store = useStore()
	const userInfo = computed(() => store.state.user.userInfo)
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const openPage = (index:number) => {
		uni.navigateTo({
			url: `/pages/attestation/attestation?type=${index}`
		})
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
		.authItem{
			width: 686rpx;
			background-color: #fff;
			border-radius: 24rpx;
			margin-top: 24rpx;
			padding: 40rpx 32rpx;
			box-sizing: border-box;
			display: flex;
			flex-direction: column;
			align-items: stretch;
			&-option{
				display: flex;
				flex-direction: row;
				align-items: center;
				.icon{
					width: 64rpx;
					height: 64rpx;
				}
				.intro{
					display: flex;
					flex-direction: column;
					margin-left: 24rpx;
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					&-title{
						font-size: 32rpx;
						color: #1D2129;
						font-weight: 500;
						line-height: 48rpx;
					}
					&-desc{
						margin-top: 4rpx;
						font-size: 24rpx;
						color: #868D9C;
						line-height: 40rpx;
					}
				}
				.status1{
					width: 160rpx;
					height: 64rpx;
					background-color: #f7f8fa;
					border-radius: 36rpx;
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: center;
					&-icon{
						width: 24rpx;
						height: 24rpx;
					}
					&-txt{
						font-size: 28rpx;
						color: #868D9C;
						margin-left: 8rpx;
					}
				}
				.status2{
					width: 160rpx;
					height: 64rpx;
					background: linear-gradient(109deg,#4a97e7, #b57aff 100%);
					border-radius: 36rpx;
					line-height: 64rpx;
					text-align: center;
					font-size: 28rpx;
					color: #FFFFFF;
				}
				
			}
			&-error{
				margin-top: 24rpx;
				padding: 16rpx 24rpx;
				box-sizing: border-box;
				background-color: rgba(255,84,111,0.10);
				border-radius: 16rpx;
				display: flex;
				flex-direction: column;
				.first{
					display: flex;
					flex-direction: row;
					align-items: center;
					&-img{
						width: 32rpx;
						height: 32rpx;
					}
					&-result{
						margin-left: 8rpx;
						font-size: 28rpx;
						color: #FF546F;
						font-weight: 500;
					}
				}
				.reason{
					width: 100%;
					margin-top: 14rpx;
					font-size: 24rpx;
					color: #FF546F;
					line-height: 40rpx;
					word-wrap: break-word; /* 使得长单词或数字可以换行 */
					overflow-wrap: break-word; /* 确保兼容性 */
				}
			}
		}
	}
	
}
</style>
