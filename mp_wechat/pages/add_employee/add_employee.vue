<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" :title="pageTitle" :statusBar="true"></uni-nav-bar>
			<view class="content">
				<view class="content-item content-bd">
					<text class="label">员工姓名<text :style="{color:'#FF546F'}">*</text></text>
					<input v-model="employee.name" type="text" class="input" placeholder="请输入员工姓名"/>
				</view>
				<view class="content-item">
					<text class="label">手机号<text :style="{color:'#FF546F'}">*</text></text>
					<input v-model="employee.mobile" type="text" class="input" placeholder="请输入员工手机号"/>
				</view>
			</view>
			<view @click="saveEmployee" class="submit_btn">
				<text>确定</text>
			</view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { ref, getCurrentInstance, reactive } from 'vue'
	import { api } from '@/common/request/index.ts'
	import { onLoad } from '@dcloudio/uni-app'
	
	
	const employee = reactive({
		clerk_id: null,
		mobile: null,
		name: null
	})
	const pageTitle = ref("添加店员")
	const app = getCurrentInstance().appContext.app
	
	onLoad((options) => {
		if (options.id != null) {
			pageTitle.value = "编辑店员"
			employee.clerk_id = options.id
			employee.mobile = options.mobile
			employee.name = options.name
		}
	})
	
	const saveEmployee = async () => {
		let url = "my/shop/add_clerk";
		if (employee.clerk_id != null) {
			url = "my/shop/edit_clerk"
		}
		const res:any = await api.post(url,employee)
		if (res.code == 1) {
			uni.showToast({
				icon: 'none',
				title: res.msg
			})
			uni.$emit('refreshEmployee')
			setTimeout(() => {
				uni.navigateBack()
			},1000)
		}
	}
	
	const navBack = () => {
		uni.navigateBack()
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
		.submit_btn{
			margin-top: 64rpx;
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
		.content{
			margin-top: 24rpx;
			width: 686rpx;
			border-radius: 24rpx;
			background-color: #fff;
			display: flex;
			flex-direction: column;
			align-items: stretch;
			padding: 0 32rpx;
			box-sizing: border-box;
			&-item{
				height: 108rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				.label{
					font-size: 28rpx;
					color: #1D2129;
				}
				.input{
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					padding-left: 16rpx;
					box-sizing: border-box;
					text-align: right;
					font-size: 28rpx;
					color: #1D2129;
				}
			}
			&-bd {
				border-bottom: 1px solid #E8EAEF;
			}
		}
	}
}
</style>
