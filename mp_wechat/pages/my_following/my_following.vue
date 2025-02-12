<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" :border="false" :shadow="false" title="我的关注" background-color="transparent" :status-bar="true"></uni-nav-bar>
			
			<scroll-view type="list" :scroll-y="true" class="scroll" @scrolltolower="loadFollowList">
				<view class="item" v-for="(item,index) in dataList" :key="'follow'+index">
					<view class="item-left">
						<image @click="toUserDetail(item.follow_user_id)" mode="aspectFill" :src="item.followuser.avatar_text" class="head_icon"></image>
						<view :style="{display:'flex',flexDirection:'column',marginLeft:'24rpx',flex:1}">
							<view :style="{display:'flex',flexDirection:'row',alignItems:'center'}">
								<text class="name">{{item.followuser.nickname}}</text>
								<image v-if="item.followuser.gender == 1" src="/static/focus/man.png" :style="{width: '28rpx',height:'28rpx',marginLeft:'8rpx'}"></image>
								<image v-else="item.followuser.gender == 2" src="/static/focus/woman.png" :style="{width: '28rpx',height:'28rpx',marginLeft:'8rpx'}"></image>
							</view>
							<text class="desc">{{item.followuser.birth_text}}年 · {{item.followuser.height}}cm · {{item.followuser.area}}</text>
						</view>
					</view>
					<text @click="cancelFocus(item.follow_user_id)" v-if="item.follow_type == 2" class="item-status">已关注</text>
					<text @click="cancelFocus(item.follow_user_id)" v-if="item.follow_type == 3" class="item-status">互相关注</text>
				</view>
				<text v-if="finish" class="scroll-status">{{dataList.length == 0?'没数据了':'数据加载完毕'}}</text>
				<text v-else class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
				<view :style="{height: '96px',width:'100%'}"></view>
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
		getFollowList(true)
	})
	
	const cancelFocus = (userId) => {
		api.post('index/follow',{
			follow_id: userId
		}).then((res:any) => {
			if (res.code == 1) {
				uni.showToast({
					icon: 'none',
					title: res.msg
				})
				getFollowList(true)
			}
		})
	}
	
	const toUserDetail = (id:string) => {
		uni.navigateTo({
			url: `/pages/personal_details/personal_details?id=${id}`
		})
	}
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const loadFollowList = () => {
		pageNo++
		getFollowList(false)
	}
	
	const getFollowList = async (refresh:boolean) => {
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
			const res:any = await api.post('my/follow_list',{
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
	background-color: #fff;
	display: flex;
	flex-direction: column;
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
		// align-items: center;
		.scroll{
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			width: 100%;
			&-status {
				font-size: 26rpx;
				color: #999;
				text-align: center;
				margin: 20rpx auto;
				display: block;
			}
			.item{
				width: 686rpx;
				height: 144rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				margin: 0 auto;
				&-left{
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					display: flex;
					flex-direction: row;
					align-items: center;
					.head_icon{
						width: 96rpx;
						height: 96rpx;
						border-radius: 48rpx;
					}
					.name{
						font-size: 28rpx;
						color: #1D2129;
						font-weight: 500;
						line-height: 44rpx;
					}
					.desc{
						margin-top: 6rpx;
						font-size: 22rpx;
						line-height: 38rpx;
						margin-top: 6rpx;
						color: #868D9C;
					}
				}
				&-status{
					width: 128rpx;
					height: 56rpx;
					border: 1px solid #DADCE0;
					border-radius: 28rpx;
					line-height: 56rpx;
					text-align: center;
					font-size: 24rpx;
					color: #B4B7BF;
				}
			}
		}
	}
}
</style>
