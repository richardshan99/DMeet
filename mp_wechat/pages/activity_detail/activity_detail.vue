<template>
	<view class="main">
		<view class="main-swiper">
			<swiper :current="current" @change="changeCurrent" class="imgs" v-if="detail != null && detail.images_text.length > 0">
				<swiper-item v-for="(item,index) in detail.images_text" :key="'swiper'+index">
					<image class="imgs" :src="item"></image>
				</swiper-item>
			</swiper>
			<view class="progress" v-if="detail != null && detail.images_text.length > 0">
				<text class="progress-txt">{{current+1}}/{{detail.images_text.length}}</text>
			</view>
		</view>
		<text class="main-name">{{detail?.name}}</text>
		<view class="main-intro" :style="{marginTop:'24rpx'}">
			<image src="/static/activity/title_tag.png" class="icon"></image>
			<text class="txt">活动类型：{{detail?.type_text}}</text>
		</view>
		<view class="main-intro" :style="{marginTop:'12rpx'}">
			<image src="/static/activity/time.png" class="icon"></image>
			<text class="txt">活动时间：{{detail?.begin_time_text}}</text>
		</view>
		<view class="main-intro" :style="{marginTop:'12rpx'}">
			<image src="/static/activity/area_location.png" class="icon2"></image>
			<text class="txt">活动地点：{{detail?.area}}</text>
		</view>
		<view class="main-detail" v-html="detail?.content"></view>
		<view class="main-pay">
			<text class="price">￥{{detail?.price}}</text>
			<view class="btns">
				<button open-type="share" class="btns-right">
					<view class="child">
						<text class="txt">微信转发</text>
					</view>
				</button>
				<view v-if="detail?.can_join" @click="openPayPopup" class="btns-left">
					<text>立即报名</text>
				</view>
			</view>
		</view>
		<uni-popup :style="{zIndex:'99999'}" ref="payPopup" type="bottom">
			<view class="main-payView">
				<text class="title">报名活动</text>
				<view class="acitivity_price">
					<text class="txt1">活动费用</text>
					<text class="txt2">￥{{detail?.price}}</text>
				</view>
				<text class="secondTitle">选择支付方式</text>
				<view class="methods">
					<view @click="changePayMethod(1)" class="parent" :class="payMethod == 1?'parent_select':''">
						<view class="second">
							<view class="item" :class="payMethod == 1?'selected':''">
								<image class="item-wechat" src="/static/wechat_pay.png"></image>
								<text class="item-txt">微信支付</text>
							</view>
						</view>
					</view>
					<view @click="changePayMethod(2)" class="parent" :class="payMethod == 2?'parent_select':''">
						<view class="second">
							<view class="item" :class="payMethod == 2?'selected':''">
								<image class="item-balance" src="/static/balance_pay.png"></image>
								<text class="item-txt">余额支付</text>
							</view>
						</view>
					</view>
				</view>
				<view @click="payNow" class="pay_now">
					<text>立即支付</text>
				</view>
				<!-- <view class="warn">
					<text class="warn-txt">查看退款规则</text>
					<image src="/static/notices/arrow_right.png" class="warn-arrow"></image>
				</view> -->
			</view>
		</uni-popup>
	</view>
</template>

<script lang="ts" setup>
	import { computed, ref } from 'vue';
	import { api } from '@/common/request/index.ts'
	import { useStore } from 'vuex'
	import { onLoad, onShareAppMessage,onShareTimeline } from "@dcloudio/uni-app"
	const payPopup = ref()
	const current = ref(0)
	const payMethod = ref(1)
	let activityId = null
	const store = useStore()
	const token = computed(() => store.state.user.token)
	const isImprove = computed(() => store.state.user.is_improve)
	const index = computed(() => (current.value+1))
	const detail = ref(null)
	onLoad((options) => {
		// 加载活动详情options.id
		activityId = options.id
		getActivityDetail(options.id)
	})
	
	// 切换支付方式
	const changePayMethod = (m:number) => {
		if (m != payMethod.value) {
			payMethod.value = m;
		}
	}
	
	onShareAppMessage(() => {
		return {
			title: detail.value?.name,
			path: `/pages/activity_detail/activity_detail?id=${activityId}`
		}
	})
	
	onShareTimeline(() => {
		return {
			title: detail.value?.name,
			query: `id=${activityId}`
		}
	})
	
	// 立即支付
	const payNow = async () => {
		const res:any = await api.post('activity/pay',{
			activity_id: detail.value.id,
			pay_type: payMethod.value
		})
		if (res.code == 1) {
			payPopup.value.close()
			if (payMethod.value == 2) {
				uni.showToast({
					icon:'none',
					title: res.data.message
				})
				getActivityDetail(activityId)
				return;
			} else if (payMethod.value == 1) {
				uni.requestPayment({
					provider: 'wxpay',
					orderInfo: null,
					timeStamp: res.data.payment.timeStamp,
					nonceStr: res.data.payment.nonceStr,
					package: res.data.payment.package,
					signType: res.data.payment.signType,
					paySign: res.data.payment.paySign,
					success: (result) => {
						getActivityDetail(activityId)
						uni.showToast({
							icon:'none',
							title: "成功报名"
						})
					},
					fail: () => {
						uni.showToast({
							icon:'none',
							title: "支付失败"
						})
					}
				})
			}
		}
	}
	
	
	// 打开支付弹窗
	const openPayPopup = () => {
		if (token.value == null) {
			uni.showModal({
				title: '提示',
				content: '您还未登录，是否去我的页面登录？',
				success(xres) {
					if (xres.confirm) {
						uni.switchTab({
							url: '/pages/mine/mine'
						})
					}
				}
			})
			return
		}
		if (isImprove.value == -1) {
			uni.showModal({
				title:'提示',
				content:'你还没有完善资料,无法参与活动',
				confirmText:"立即完善",
				success: (ures) => {
					if (ures.confirm) {
						uni.navigateTo({
							url: '/pages/complete_infomation/complete_infomation'
						})
					}
				}
			})
			return
		}
	  /* 暂时关闭此功能，by Richard	
		if (isImprove.value == -2) {
			uni.showToast({
				icon:'none',
				title:'您的信息正在审核，请先等待审核结果。'
			})
			return
		}*/
		payPopup.value.open()
	}
	
	//
	const changeCurrent = (e:any) => {
		if (current.value != e.detail.current) {
			current.value = e.detail.current
		}
	}
	
	const getActivityDetail = async (id:string) => {
		const res:any = await api.post('activity/detail',{
			activity_id: id
		})
		if (res.code == 1) {
			detail.value = res.data
		}
	}
</script>

<style lang="scss" scoped>
.main{
	width: 100%;
	min-height: 100%;
	background-color: #FFF;
	display: flex;
	flex-direction: column;
	overflow-y: auto;
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
	&-name{
		margin-top: 32rpx;
		margin-left: 32rpx;
		margin-right: 32rpx;
		font-size: 36rpx;
		color: #1D2129;
		font-weight: 500;
	}
	&-intro {
		margin-left: 32rpx;
		margin-right: 32rpx;
		display: flex;
		flex-direction: row;
		align-items: center;
		.icon{
			width: 24rpx;
			height: 26rpx;
		}
		.icon2{
			width: 20rpx;
			height: 24rpx;
		}
		.txt{
			margin-left: 18rpx;
			font-size: 26rpx;
			color: #4E5769;
		}
	}
	&-detail {
		margin: 24rpx auto 96px auto;
		width: 686rpx;
		border-top: 1px solid #E8EAEF;
		padding-top: 24rpx;
		box-sizing: border-box;
	}
	&-pay {
		width: 100%;
		height: 90px;
		background-color: #fff;
		position: fixed;
		z-index: 999;
		bottom: 0;
		left: 0;
		display: flex;
		flex-direction: row;
		align-items: center;
		padding-top: 16rpx;
		box-sizing: border-box;
		.price{
			display: block;
			flex: 1;
			flex-shrink: 0;
			min-width: 0;
			margin-left: 32rpx;
			font-size: 36rpx;
			font-weight: 500;
			color: #FF546F;
		}
		.btns{
			display: flex;
			flex-direction: row;
			align-items: center;
			margin-right: 32rpx;
			&-left{
				width: 208rpx;
				height: 88rpx;
				background: linear-gradient(110deg,#4a97e7, #b57aff 100%);
				border-radius: 44rpx;
				line-height: 88rpx;
				text-align: center;
				font-size: 28rpx;
				color: #fff;
				font-weight: 500;
				margin-left: 24rpx;
			}
			&-right{
				width: 208rpx;
				height: 88rpx;
				// border-image: linear-gradient(110deg, #4a97e7, #b57aff 100%) 1 1;
				border-radius: 23px;
				padding: 1px;
				display: flex;
				flex-direction: column;
				background: linear-gradient(110deg, #4a97e7, #b57aff 100%);
				.child{
					background-color: #fff;
					border-radius: 23px;
					flex: 1;
					min-height: 0;
					flex-shrink: 0;
					text-align: center;
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
	&-payView{
		width: 100%;
		padding: 40rpx;
		box-sizing: border-box;
		background-color: #fff;
		border-radius: 32rpx 32rpx 0px 0px;
		display: flex;
		flex-direction: column;
		align-items: stretch;
		.title{
			font-size: 32rpx;
			color: #1D2129;
			font-weight: 500;
		}
		.acitivity_price{
			margin-top: 32rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			justify-content: space-between;
			padding-bottom: 36rpx;
			box-sizing: border-box;
			border-bottom: 1px solid #E8EAEF;
			.txt1{
				font-size: 28rpx;
				color: #868D9C;
			}
			.txt2{
				font-size: 36rpx;
				color: #FF546F;
				font-weight: 500;
			}
		}
		.secondTitle{
			margin-top: 32rpx;
			font-size: 28rpx;
			color: #868D9C;
		}
		.methods{
			margin-top: 24rpx;
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			.parent{
				width: 324rpx;
				height: 108rpx;
				padding: 3rpx;
				box-sizing: border-box;
				border-radius: 24rpx;
				background: #f0f2f5;
				display: flex;
				flex-direction: column;
				.second{
					flex: 1;
					min-height: 0;
					border-radius: 24rpx;
					background-color: #fff;
					.item{
						width: 100%;
						height: 100%;
						border-radius: 24rpx;
						background-color: #fff;
						display: flex;
						flex-direction: row;
						align-items: center;
						&-wechat{
							margin-left: 40rpx;
							width: 40rpx;
							height: 40rpx;
						}
						&-balance{
							margin-left: 40rpx;
							width: 42rpx;
							height: 40rpx;
						}
						&-txt{
							margin-left: 24rpx;
							font-size: 28rpx;
							color: #222222;
						}
					}
					.selected{
						position: relative;
						background: linear-gradient(106deg,rgba(74,151,231,0.1), rgba(181,122,255,0.1) 100%);
						// border-image: linear-gradient(106deg, #4a97e7, #b57aff 100%) 1.5 1.5;
					}
				}
				
			}
			.parent_select{
				background: linear-gradient(106deg, #4a97e7, #b57aff 100%);
			}
		}
		.pay_now{
			margin-top: 64rpx;
			height: 88rpx;
			background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
			border-radius: 44rpx;
			line-height: 88rpx;
			text-align: center;
			font-size: 28rpx;
			color: #fff;
			font-weight: 500;
			margin-bottom: 84rpx;
		}
		.warn{
			margin-top: 32rpx;
			margin-bottom: 84rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			justify-content: center;
			&-txt{
				font-size: 24rpx;
				color: #868D9C;
			}
			&-arrow{
				margin-left: 8rpx;
				width: 14rpx;
				height: 16rpx;
			}
		}
	}
}
</style>
