<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar :fixed="true" left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="平台公告" :statusBar="true"></uni-nav-bar>
			<scroll-view class="notices" :scroll-y="true" type="list" @scrolltolower="loadNotices">
				<view class="item" :style="{width: '750rpx',paddingTop:'24rpx'}" v-for="(item,ind) in dataList" :key="item.id+'notice'" @click="toDetail(item.id)">
					<view class="notices-item">
						<text class="time">{{item.create_time_text}}</text>
						<text class="content">{{item.intro}}</text>
						<view class="bottom">
							<text class="bottom-search">查看详情</text>
							<image class="bottom-right" src="/static/notices/arrow_right.png"></image>
						</view>
					</view>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { ref, getCurrentInstance } from 'vue'
	import { api } from '@/common/request/index.ts'
	import { onLoad } from '@dcloudio/uni-app'
	
	
	const app = getCurrentInstance().appContext.app
	const dataList = ref([]);
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	
	onLoad(() => {
		getNoticeList(true)
	})
	
	const loadNotices = () => {
		pageNo++
		getNoticeList(false)
	}
	
	const toDetail = (id:string) => {
		uni.navigateTo({
			url: `/pages/announcement_detail/announcement_detail?id=${id}`
		})
	}
	
	const getNoticeList = async (refresh:boolean) => {
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
			const res:any = await api.post('/index/notice_list',{
				page: pageNo
			})
			console.error(res)
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
		.notices{
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			// overflow: auto;
			&-item {
				width: 686rpx;
				background-color: #fff;
				border-radius: 24rpx;
				display: flex;
				flex-direction: column;
				margin: 0 auto 0 auto;
				.time{
					margin-left: 32rpx;
					font-size: 24rpx;
					color: #868D9C;
					margin-top: 24rpx;
				}
				.content{
					margin: 16rpx 32rpx 24rpx 32rpx;
					font-size: 28rpx;
					color: #1D2129;
					line-height: 44rpx;
				}
				.bottom{
					width: 622rpx;
					height: 88rpx;
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: space-between;
					margin: 0 auto;
					border-top: 1px solid #eee;
					&-search{
						font-size: 24rpx;
						color: #4E5769;
					}
					&-right{
						width: 16rpx;
						height: 24rpx;
					}
				}
			}
		}
	}
}
</style>
