<template>
	<view class="popup_stature">
		<view class="top">
			<text @click="cancel" class="top-cancel">取消</text>
			<text class="top-title">选择营业时间段</text>
			<text @click="confirm" class="top-confirm">确定</text>
		</view>
		<picker-view immediate-change="true" class="picker" :value="choice" @change="changePicker" indicator-style="height:48px">
			<picker-view-column>
				<view class="picker-item" v-for="(item,ind) in startHours" :key="'start'+ind">
					<text>{{item}}</text>
				</view>
			</picker-view-column>
			<picker-view-column>
				<view class="picker-item" v-for="(item,ind) in endHours" :key="'end'+ind">
					<text>{{item}}</text>
				</view>
			</picker-view-column>
		</picker-view>
	</view>
</template>

<script lang="ts" setup>
	import popup from '../uni-popup/popup.js'
	import { ref, watch,getCurrentInstance, onMounted, reactive, nextTick } from 'vue'
	
	const startHours = ref([])
	const endHours = ref([])
	
	const choice = reactive([0,0])
	
	const { proxy } = getCurrentInstance()
	defineOptions({
		mixins: [popup]
	})
	
	const props = defineProps({
		nowOption: {
			type: Array<number>,
			default: () => [0,0]
		}
	})
	const emit = defineEmits(['confirm','cancel'])
	
	onMounted(() => {
		for (let i = 0;i<24;i++) {
			startHours.value.push(i+':00')
			endHours.value.push(i+':00')
		}
		nextTick(() => {
			Object.assign(choice,props.nowOption)
		})
	})
	
	// 滚动选中项变化
	
	// 取消
	const cancel = () => {
		emit('cancel')
		proxy.popup.close()
	}
	
	const confirm = () => {
		emit('confirm',startHours.value[choice[0]]+"-"+endHours.value[choice[1]])
		proxy.popup.close()
	}
	
	const changePicker = (e) => {
		Object.assign(choice,e.detail.value)
	}
</script>

<style lang="scss" scoped>
	.popup_stature{
		width: 750rpx;
		height: 708rpx;
		background-color: #fff;
		border-radius: 32rpx 32rpx 0px 0px;
		padding: 0 32rpx;
		box-sizing: border-box;
		display: flex;
		flex-direction: column;
		.top{
			display: flex;
			flex-direction: row;
			height: 112rpx;
			align-items: center;
			justify-content: space-between;
			&-cancel{
				font-size: 32rpx;
				color: #868D9C;
			}
			&-title{
				font-size: 32rpx;
				color: #1D2129;
				font-weight: 500;
			}
			&-confirm{
				font-size: 32rpx;
				color: #2C94FF;
				font-weight: 500;
			}
		}
		.second {
			display: flex;
			flex-direction: row;
			width: 100%;
			&-tab{
				flex: 1;
				flex-shrink: 0;
				min-width: 0;
				display: flex;
				flex-direction: row;
				justify-content: center;
				align-items: center;
				.item{
					height: 88rpx;
					display: inline-block;
					border-bottom: 2px solid #fff;
					font-size: 32rpx;
					color: #868D9C;
					font-weight: 500;
					text-align: center;
					line-height: 88rpx;
				}
				.checked{
					border-bottom: 2px solid #2C94FF;
					color: #2C94FF;
				}
			}
		}
		.picker{
			width: 100%;
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			&-item{
				text-align: center;
				line-height: 48px;
				font-size: 16px;
				color: #1D2129;
			}
		}
	}
</style>