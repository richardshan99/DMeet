<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" :title="'感兴趣的用户('+count+')'" :statusBar="true"></uni-nav-bar>
			<scroll-view type="list" :scroll-y="true" class="scroll" @scrolltolower="loadConcernList">
				<view class="intrest" v-for="(item,ind) in dataList" :key="'intrest'+ind">
					<image @click="toUserDetail(item.user.id)" mode="aspectFill" class="main_avatar" :src="item.user.avatar_text"></image>
					<view :style="{display:'flex',flexDirection:'column',marginLeft: '24rpx',flex:1,flexShrink:0,minWidth:0}">
						<view :style="{width:'100%',display:'flex',flexDirection:'row'}">
							<view :style="{display:'flex',flexDirection:'column',flex:1,flexShrink:0,minWidth:0}">
								<view :style="{display:'flex',flexDirection:'row',alignItems:'center'}">
									<text class="name">{{item.user.nickname}}</text>
									<image v-if="item.user.gender == 1" src="/static/sex_man.png" class="sex"></image>
									<image v-if="item.user.gender == 2" src="/static/sex_woman.png" class="sex"></image>
								</view>
								<text class="intro">{{item.user.birth_year}}年 · {{item.user.height}}cm · {{item.user.area}}</text>
							</view>
							<view @click="selectPerson(item.user.id)" class="checked">
								<view class="btn">
									<text>选中TA</text>
								</view>
							</view>
						</view>
						
						
						<view class="items">
							<view class="option">
								<text>{{item.user.school}}</text>
							</view>
							<view class="option">
								<text>{{item.user.education_type_text}}</text>
							</view>
							<view class="option">
								<text>{{item.user.work_type_text}}</text>
							</view>
						</view>
					</view>
					
				</view>
				<text v-if="finish" class="scroll-status">{{dataList.length == 0?'没数据了':'数据加载完毕'}}</text>
				<text v-else class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
			</scroll-view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import { getCurrentInstance, ref } from 'vue'
	import { onLoad } from '@dcloudio/uni-app'
	const app = getCurrentInstance().appContext.app
	let callId = null
	const dataList = ref([]);
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	
	const count = ref(0)
	
	onLoad((options) => {
		callId = options.id
		count.value = options.count
		getConcernList(true)
	})
	const toUserDetail = (id:string) => {
		uni.navigateTo({
			url: `/pages/personal_details/personal_details?id=${id}`
		})
	}
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const loadConcernList = () => {
		pageNo++
		getConcernList(false)
	}
	
	const selectPerson = (uid:string) => {
		uni.showModal({
			title:'提示',
			content: '确认选TA吗？',
			success(hres) {
				if (hres.confirm) {
					api.post('/call/invitation',{
						call_id: callId,
						user_id: uid
					}).then((res:any) => {
						if(res.code == 1) {
							uni.navigateTo({
								url: '/pages/give_success/give_success'
							})
						}
					})
				}
			}
		})
	}
	
	const getConcernList = async (refresh:boolean) => {
		if (refresh) {
			pageNo = 1
			finish.value = false
		}
		if (finish.value) {
			return;
		}
		if (loading.value) {
			return;
		}
		loading.value = true
		try {
			const res:any = await api.post('call/concern_list',{
				page: pageNo,
				call_id: callId
			})
			loading.value = false
			if (res.code == 1) {
				if (refresh) {
					dataList.value = res.data.data
				} else {
					dataList.value = dataList.value.concat(res.data.data)
				}
				if (dataList.value.length == res.data.total) {
					finish.value = true
				}
			}
		}catch(e) {
			loading.value = false
		}
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
		.scroll {
			margin-top: 32rpx;
			width: 100%;
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			&-status {
				font-size: 26rpx;
				color: #999;
				text-align: center;
				margin: 20rpx auto;
				display: block;
			}
			.intrest{
				width: 686rpx;
				margin: 0 auto;
				background-color: #ffffff;
				border-radius: 24rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				padding-top: 32rpx;
				padding-bottom: 24rpx;
				box-sizing: border-box;
				.main_avatar{
					width: 160rpx;
					height: 160rpx;
					border-radius: 16rpx;
					margin-left: 32rpx;
				}
				.name{
					font-size: 28rpx;
					color: #1D2129;
					font-weight: 500;
				}
				.sex{
					width: 32rpx;
					height: 32rpx;
					margin-left: 16rpx;
				}
				.intro{
					margin-top: 2rpx;
					font-size: 22rpx;
					color: #868D9C;
				}
				.items{
					margin-top: 16rpx;
					display: flex;
					flex-direction: row;
					align-items: center;
					flex-wrap: wrap;
					.option{
						padding: 4rpx 16rpx;
						border: 1px solid #e8eaef;
						border-radius: 12rpx;
						box-sizing: border-box;
						font-size: 24rpx;
						color: #4E5769;
						margin-right: 12rpx;
						margin-bottom: 6rpx;
					}
				}
				.checked{
					// height: 160rpx;
					.btn{
						width: 120rpx;
						height: 56rpx;
						background: linear-gradient(112deg,#4a97e7, #b57aff 100%);
						border-radius: 36rpx 0px 0px 36rpx;
						line-height: 56rpx;
						text-align: center;
						font-size: 24rpx;
						font-weight: 500;	
						color: #fff;
					}
				}
			}
		}
	}
}
</style>
