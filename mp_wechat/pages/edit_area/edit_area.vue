<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" :border="false" :shadow="false" :title="pageTitle" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<view class="edit_area">
				<textarea v-model="content" maxlength="-1" class="input"></textarea>
			</view>
			<view class="bottom">
				<view @click="saveInfo" class="save">
					<text>保存</text>
				</view>
			</view>
		</view>
		
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import { getCurrentInstance, ref} from 'vue'
	import { onLoad } from '@dcloudio/uni-app'
	import { useStore } from 'vuex'
	const store = useStore()
	const { proxy } = getCurrentInstance()
	const app = getCurrentInstance().appContext.app
	const content = ref(null)
	const pageTitle = ref("")
	let pageType = 1
	onLoad((options) => {
		const eventChannel = proxy.getOpenerEventChannel()
		eventChannel.on('acceptDataFromOpenerPage',(data:any) => {
			// 获取上个页面的传值
			if (data != null && data.content != null) {
				content.value = data.content
			}
		})
		pageType = parseInt(options.type)
		switch(pageType) {
			case 1:
				pageTitle.value = "关于我"
			break;
			case 2:
				pageTitle.value = "对Ta的要求/期望"
			break;
		}
	})
	
	const saveInfo = () => {
		let data = { },url = ""
		if (content.value == null || content.value.length <= 0) {
			uni.showToast({
				title:'请先输入',
				icon:'none'
			})
			return;
		}
		switch(pageType) {
			case 1:
				url = "user/edit_intro"
				data = {
					intro: content.value
				}
			break;
			case 2:
				url = "user/edit_myExpect"
				data = {
					myExpect: content.value
				}
			break;
		}
		api.post(url,data).then((res:any) => {
			if(res.code == 1) {
				uni.showToast({
					icon: 'none',
					title:res.msg
				})
				switch(pageType) {
					case 1:
						store.commit('setAbout',content.value)
					break;
					case 2:
						store.commit('setmyExpect',content.value)
					break;
				}
				
				setTimeout(() => {
					uni.navigateBack()
				},1000)
			}
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
		.edit_area{
			width: 686rpx;
			background-color: #ffffff;
			border-radius: 24rpx;
			display: flex;
			flex-direction: row;
			.input{
				margin: 32rpx 32rpx 84rpx 32rpx;
				flex: 1;
				flex-shrink: 0;
				min-width: 0;
				font-size: 30rpx;
				line-height: 48rpx;
				color: #1D2129;
				height: 384rpx;
			}
		}
		.bottom{
			width: 750rpx;
			height: 94px;
			position: fixed;
			bottom: 0;
			left: 0;
			z-index: 99;
			display: flex;
			flex-direction: column;
			align-items: center;
			background-color: #fff;
			.save{
				width: 686rpx;
				height: 88rpx;
				background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
				border-radius: 44rpx;
				margin-top: 16rpx;
				line-height: 88rpx;
				text-align: center;
				font-size: 28rpx;
				color: #fff;
				font-weight: 500;
				z-index: 999;
			}
		}
	}
}
</style>
