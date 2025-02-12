<template>
	<view class="verifyCode">
		<text class="title">核销码</text>
		<image :src="qrCodeImg" class="qrcode"></image>
		<text class="package_name">{{codeInfo.packName}}</text>
		<text class="price">¥{{codeInfo.price}}</text>
		<view class="line"></view>
		<text class="warn">注意：不要泄露核销码，请在到店后向商家出示</text>
		<view @click="cancel" class="close">
			<text>关闭</text>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import popup from '../uni-popup/popup.js'
	import { getCurrentInstance,onMounted, watch,ref } from 'vue'
	const qrCodeImg = ref(null)
	
	const props = defineProps({
		codeInfo: {
			type: Object,
			default: () => {
				
			}
		}
	})
	
	const { proxy } = getCurrentInstance()
	defineOptions({
		mixins: [popup]
	})
	const emit = defineEmits(['confirm','cancel'])
	
	watch(() => props.codeInfo.id,(n,o) => {
		if (n != null) {
			api.post('invite/qrcode',{ invite_id: n }).then((res:any) => {
				if (res.code == 1) {
					qrCodeImg.value = res.data.qrcode
				}
			})
		}
	})
	
	onMounted(() => {
	})
	
	const confirm = () => {
		proxy.popup.close()
		emit('confirm')
		
	}
	const cancel = () => {
		proxy.popup.close()
		emit('cancel')
	}
	
</script>

<style lang="scss" scoped>
.verifyCode{
	width: 750rpx;
	background-color: #fff;
	border-radius: 24rpx 24rpx 0px 0px;
	display: flex;
	flex-direction: column;
	align-items: center;
	.title{
		margin-top: 32rpx;
		width: 686rpx;
		font-size: 32rpx;
		color: #1D2129;
		font-weight: 500;
	}
	.qrcode{
		margin-top: 32rpx;
		width: 180px;
		height: 180px;
	}
	.package_name{
		margin-top: 32rpx;
		font-size: 32rpx;
		color: #1D2129;
		font-weight: 500;
	}
	.price{
		margin-top: 4rpx;
		font-size: 36rpx;
		color: #FF546F;
		font-weight: 500;
	}
	.line{
		width: 686rpx;
		height: 0px;
		border: 1px dashed #e8eaef;
		margin: 32rpx 0;
	}
	.warn{
		font-size: 28rpx;
		color: #868D9C;
	}
	.close{
		margin-top: 64rpx;
		margin-bottom: 100rpx;
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
}
</style>