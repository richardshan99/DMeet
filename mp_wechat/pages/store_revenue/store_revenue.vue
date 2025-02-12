<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base" v-if="status == 0">
			<uni-nav-bar @clickLeft="navBack" left-icon="left" :border="false" title="门店收入" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<view class="revenue">
				<text class="revenue-txt1">当前余额</text>
				<text class="revenue-txt2">{{(shopInfo.balance== null||shopInfo.balance.length <= 0)?'0.00':parseFloat(shopInfo.balance).toFixed(2)}}</text>
				<view @click="status = 1" class="revenue-btn">
					<text>提现</text>
				</view>
			</view>
			<view class="parea" :style="{marginTop:'24rpx'}">
				<view @click="toPage('/pages/amount_details/amount_details')" class="parea-item bd_bottom">
					<text class="label">金额明细</text>
					<image src="/static/mine_center/arrow_left.png" class="arrow_right"></image>
				</view>
				<view @click="toPage('/pages/withdrawal_records/withdrawal_records')" class="parea-item">
					<text class="label">提现明细</text>
					<image src="/static/mine_center/arrow_left.png" class="arrow_right"></image>
				</view>
			</view>
		</view>
		<view class="main-base" v-if="status == 1">
			<uni-nav-bar @clickLeft="navBack" left-icon="left" :border="false" title="门店收入" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<input v-model="priceInfo.price" type="digit" class="withdraw" placeholder="输入提现金额" :style="{marginTop:'24rpx'}" placeholder-style="color:#DADCE0"/>
			<view class="remark" :style="{marginTop: '24rpx'}">
				<text class="remark-label">提现备注</text>
				<input v-model="priceInfo.remark" type="text" class="remark-desc" placeholder="请输入备注说明"/>
			</view>
			<view @click="submitWithdraw" class="cash_out" :style="{marginTop: '64rpx'}">
				<text>提交</text>
			</view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import { getCurrentInstance, ref, computed, reactive } from 'vue'
	import { useStore } from 'vuex'
	const app = getCurrentInstance().appContext.app
	const status = ref(0)
	const store = useStore()
	const shopInfo = computed(() => store.state.user.shopInfo)
	const priceInfo = reactive({
		price: null,
		remark: null
	}) 
	
	const navBack = () => {
		uni.navigateBack()
		
	}
	
	const toPage = (path:string) => {
		uni.navigateTo({
			url: path
		})
	}
	
	const submitWithdraw = async () => {
		// 提交
		if (priceInfo.price == null && priceInfo.price.length <= 0) {
			uni.showToast({
				icon:'none',
				title:"请输入提现金额"
			})
			return;
		}
		if (parseFloat(priceInfo.price) == null && parseFloat(priceInfo.price) > 0) {
			uni.showToast({
				icon:'none',
				title:"请输入合适的提现金额"
			})
			return;
		}
		if (shopInfo.value.balance == null || shopInfo.value.balance < priceInfo.price) {
			uni.showToast({
				icon:'none',
				title:"提现金额不可超出余额"
			})
			return;
		}
		const res:any = await api.post('my/shop/cash',priceInfo)
		if (res.code == 1) {
			uni.showToast({
				icon:'none',
				title:res.msg
			})
			status.value = 0
		}
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
		.cash_out{
			width: 686rpx;
			height: 88rpx;
			background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
			border-radius: 44rpx;
			line-height: 88rpx;
			text-align: center;
			font-size: 28rpx;
			color: #fff;
			font-weight: 500;
		}
		.remark{
			width: 686rpx;
			height: 108rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			background-color: #ffffff;
			border-radius: 24rpx;
			&-label{
				margin-left: 32rpx;
				font-size: 28rpx;
				color: #1D2129;
			}
			&-desc{
				flex: 1;
				flex-shrink: 0;
				min-width: 0;
				text-align: right;
				font-size: 28rpx;
				color: #1D2129;
				margin-left: 32rpx;
				margin-right: 32rpx;
			}
		}
		.withdraw{
			width: 686rpx;
			background-color: #ffffff;
			height: 160rpx;
			border-radius: 24rpx;
			font-size: 48rpx;
			font-weight: 600;
			padding: 0 32rpx;
			box-sizing: border-box;
			color: #1D2129;
		}
		.revenue{
			width: 670rpx;
			background-color: #ffffff;
			border-radius: 32rpx;
			display: flex;
			flex-direction: column;
			align-items: center;
			&-txt1{
				font-size: 28rpx;
				color: #868D9C;
				margin-top: 40rpx;
			}
			&-txt2{
				margin-top: 8rpx;
				font-size: 64rpx;
				color: #1D2129;
				font-weight: 600;
			}
			&-btn{
				width: 248rpx;
				height: 80rpx;
				background: linear-gradient(106deg,#4a97e7, #b57aff 100%);
				border-radius: 44rpx;
				line-height: 80rpx;
				text-align: center;
				font-size: 28rpx;
				color: #fff;
				font-weight: 500;
				margin: 40rpx 0;
			}
		}
		.parea {
			width: 670rpx;
			background-color: #ffffff;
			border-radius: 32rpx;
			padding: 0 32rpx;
			box-sizing: border-box;
			display: flex;
			flex-direction: column;
			align-items: stretch;
			&-item{
				display: flex;
				flex-direction: row;
				justify-content: space-between;
				align-items: center;
				height: 108rpx;
				.label{
					font-size: 28rpx;
					color: #1D2129;
					font-weight: 500;
				}
				.arrow_right{
					width: 16rpx;
					height: 24rpx;
				}
			}
			.bd_bottom{
				border-bottom: 1px solid #F0F2F5;
			}
		}
	}
}
</style>
