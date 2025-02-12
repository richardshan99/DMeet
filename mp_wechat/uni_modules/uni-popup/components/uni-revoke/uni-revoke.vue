<template>
	<view class="popup">
		<text class="title">取消邀约</text>
		<text class="desc">提前2天取消保证金全退，提前1天取消退50%，当天取消保证金不退。</text>
		<view :style="{display: 'flex',flexDirection:'row',alignItems:'center',marginTop:'64rpx',marginBottom:'100rpx'}">
			<view @click="confirm" class="cancel_btn">
				<text class="txt">狠心取消</text>
			</view>
			<view @click="cancel" class="think_btn">
				<text class="txt">我再想想</text>
			</view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import popup from '../uni-popup/popup.js'
	import { getCurrentInstance } from 'vue'
	
	const { proxy } = getCurrentInstance()
	defineOptions({
		mixins: [popup]
	})
	const emit = defineEmits(['confirm','cancel'])
	const confirm = () => {
		proxy.popup.close()
		emit('confirm')
		
	}
	const cancel = () => {
		proxy.popup.close()
		emit('cancel')
	}
	const props = defineProps({
		msg: {
			type: String,
			default: ''
		},
		confirmText: {
			type: String,
			default: '确认'
		},
		cancelText: {
			type: String,
			default: '取消'
		}
	})
	
</script>

<style lang="scss" scoped>
.popup{
	width: 750rpx;
	background-color: #ffffff;
	border-radius: 24rpx 24rpx 0 0;
	padding: 0 32rpx;
	box-sizing: border-box;
	display: flex;
	flex-direction: column;
	align-items: center;
	.title{
		width: 686rpx;
		margin-top: 32rpx;
		font-size: 32rpx;
		color: #1D2129;
		font-weight: 500;
	}
	.desc{
		margin-top: 32rpx;
		width: 686rpx;
		font-size: 28rpx;
		color: #1D2129;
		line-height: 44rpx;
	}
	.cancel_btn{
		width: 328rpx;
		height: 88rpx;
		background: linear-gradient(103deg,rgba(74,151,231,0.2), rgba(74,151,231,0.2) 100%);
		border-radius: 44rpx;
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
	.think_btn{
		margin-left: 32rpx;
		width: 328rpx;
		height: 88rpx;
		background: linear-gradient(103deg,#4a97e7, #b57aff 100%);
		border-radius: 44rpx;
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: center;
		.txt{
			font-size: 28rpx;
			font-weight: 500;
			color: #fff;
		}
	}
}
</style>