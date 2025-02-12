<template>
	<view class="popup">
		<view class="popup-content">
			<text class="info">{{props.msg}}</text>
		</view>
		<view class="popup-btns">
			<view class="item left" @click="cancel">
				<text class="item-cancel">{{props.cancelText}}</text>
			</view>
			<view class="item" @click="confirm">
				<text class="item-confirm">{{props.confirmText}}</text>
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
	width: 255px;
	border-radius: 10px;
	background-color: #fff;
	display: flex;
	flex-direction: column;
	&-content{
		padding: 20px 16px;
		box-sizing: border-box;
		text-align: center;
		.info{
			width: 223px;
			font-size: 16px;
			color: #000;
			font-weight: 500;
			
			line-height: 24px;
		}
	}
	&-btns{
		display: flex;
		flex-direction: row;
		.item{
			flex: 1;
			flex-shrink: 0;
			min-width: 0;
			height: 38px;
			border-top: 1px solid #f5f5f5;
			display: flex;
			flex-direction: row;
			justify-content: center;
			align-items: center;
			&-cancel{
				font-size: 14px;
				font-weight: 500;
				color: #868d9c;
			}
			&-confirm{
				font-size: 14px;
				font-weight: 500;
				color: #2c94ff;
			}
		}
		.left {
			border-right: 1px solid #f0f0f0;
		}
	}
}
</style>