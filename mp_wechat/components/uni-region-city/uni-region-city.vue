<template>
	<view :style="{width: '100%',height:'100%'}">
		<view @click="openCity" class="area">
			<text class="choose_val">{{areaShow}}</text>
			<image class="arrow_left" src="/static/mine_center/arrow_left.png"></image>
		</view>
		<uni-popup ref="regionPopup" type="bottom">
			<city-select :title="title" @confirm="confirmInfo"></city-select>
		</uni-popup>
	</view>
</template>

<script lang="ts" setup>
	import { ref,onMounted } from 'vue'
	const regionPopup = ref()
	const props = withDefaults(defineProps<{
		title: string,
		areaName: string
	}>(),{
		title: '区域',
		areaName: ''
	})
	const areaShow = ref('')
	onMounted(() => {
		areaShow.value = props.areaName
	})
	const emit = defineEmits(['confirm'])
	const confirmInfo = (options:Array<number>,address:string,cityId:any) => {
		regionPopup.value.close()
		areaShow.value = address
		emit('confirm',{
			options: options,
			address: address,
			cityId: cityId
		})
	}
	
	const openCity = () => {
		console.log(regionPopup.value)
		regionPopup.value.open()
	}
</script>

<style lang="scss" scoped>
.area{
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: flex-end;
	.choose_val{
		font-size: 28rpx;
		color: #4E5769;
	}
	.arrow_left{
		width: 16rpx;
		height: 24rpx;
		margin-left: 16rpx;
	}
}
</style>