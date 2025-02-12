<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="点赞" :statusBar="true"></uni-nav-bar>
			<scroll-view :scroll-y="true" class="scroll" @scrolltolower="loadLikesList()">
				<view class="likes" v-for="(item,ind) in dataList" :key="'system'+ind" @click="toDynamic(item.like_blog_id)">
					<image mode="aspectFill" class="likes-avatar" :src="item.likeuser.avatar_text"></image>
					<view class="likes-info">
						<text class="txt1">{{item.likeuser.nickname}} <text class="txt2">赞了我的动态</text></text>
						<text class="txt3">{{item.create_time_text}}</text>
					</view>
					<image :src="item.image_text" class="likes-img"></image>
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
	
	const dataList = ref([]);
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	
	onLoad(() => {
		getLikesList(true)
	})
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const toDynamic = (tid:string) => {
		uni.navigateTo({
			url: `/pages/dynamic_detail/dynamic_detail?id=${tid}&isMine=T`
		})
	}
	
	const loadLikesList = () => {
		pageNo++
		getLikesList(false)
	}
	
	const getLikesList = async (refresh:boolean) => {
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
			const res:any = await api.post('/message/likes_list',{
				page: pageNo
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
			margin-top: 24rpx;
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
			.likes{
				width: 686rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				// margin-bottom: 48rpx;
				margin: 0 auto 48rpx auto;
				&-avatar{
					width: 96rpx;
					height: 96rpx;
					border-radius: 48rpx;
				}
				&-info{
					display: flex;
					flex-direction: column;
					margin-left: 24rpx;
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					.txt1{
						font-size: 28rpx;
						font-weight: 500;
						color: #1D2129;
					}
					.txt2{
						font-size: 24rpx;
						color: #4E5769;
					}
					.txt3{
						margin-top: 8rpx;
						font-size: 24rpx;
						color: #868D9C;
					}
				}
				&-img{
					width: 88rpx;
					height: 88rpx;
					border-radius: 12rpx;
				}
			}
		}
	}
}
</style>
