<template>
	<view class="content">
		<image class="content-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="content-base">
			<uni-nav-bar :border="false" title="线下活动" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<view class="topfix">
				<view class="topfix-left" @click="openCity">
					<image src="/static/activity/location.png" class="location"></image>
					<text class="city">{{activeCity.name}}</text>
					<view class="down_icon">
						<image :style="{transform: `rotate(${cityFlag?180:0}deg)`}" class="img" src="/static/activity/arrow_down.png"></image>
					</view>
				</view>
				<view @click="openPopup(activityTypePopup)" class="topfix-type">
					<text class="txt">{{activeType == null?"活动类型":activeType.name}}</text>
					<image src="/static/activity/arrow_down.png" class="down"></image>
				</view>
			</view>
			<scroll-view v-if="dataList.length > 0" :scroll-y="true" type="list" class="scroll" @scrolltolower="loadActivityList">
				<view v-for="(item,ind) in dataList" :key="ind+'activity'" class="item" @click="toDetail(item.id)">
					<view :style="{display:'flex',flexDirection:'row'}">
						<image mode="aspectFill" :src="item.thumb_text" class="item-mainImg"></image>
						<view class="item-intro">
							<text class="txt1">{{item.name}}</text>
							<view :style="{display:'flex',flexDirection:'column'}">
								<view class="row">
									<image src="/static/activity/title_tag.png" class="icon"></image>
									<text class="txt">{{item.type_text}}</text>
								</view>
								<view class="row" :style="{marginTop:'4rpx'}">
									<image src="/static/activity/time.png" class="icon"></image>
									<text class="txt">{{item.begin_time_text}}</text>
								</view>
							</view>
						</view>
					</view>
					<view class="item-bottom">
						<view class="area">
							<image src="/static/activity/area_location.png" class="area-icon"></image>
							<text class="area-txt">{{item.area}}</text>
						</view>
						<view v-if="item.join_status == 1" class="signUp non">
							<text>报名</text>
						</view>
						<view v-else class="finish_btn">
							<text>{{item.join_status_text}}</text>
						</view>
					</view>
				</view>
				<text v-if="finish" class="scroll-status">{{dataList.length == 0?'没数据了':'数据加载完毕'}}</text>
				<text v-else class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
				<view :style="{height: '96px'}"></view>
			</scroll-view>
			<text v-else :style="{marginTop:'64rpx',fontSize:'32rpx',color:'#666'}">暂无活动，换个城市试试</text>
		</view>
		<uni-popup ref="activityTypePopup" type="bottom">
			<view class="popup_stature">
				<view class="top">
					<text @click="closePopup(activityTypePopup)" class="top-cancel">取消</text>
					<text class="top-title">选择活动类型</text>
					<text @click="confirmType" class="top-confirm">确定</text>
				</view>
				<picker-view immediate-change="true" :value="[typeIndex]" @change="changeType" class="picker" indicator-style="height:48px">
					<picker-view-column>
						<view class="picker-item" v-for="(item,ind) in typeList" :key="'activeType'+ind">
							<text>{{item.name}}</text>
						</view>
					</picker-view-column>
				</picker-view>
			</view>
		</uni-popup>
		<uni-popup ref="citySelectPopup" type="bottom" @maskClick="setStatus">
			<city-select :nowOption="[0,0,0]" @confirm="confirmCity" @cancel="setStatus"></city-select>
		</uni-popup>
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import { onShow } from "@dcloudio/uni-app"
	import { computed, getCurrentInstance, reactive, ref } from 'vue'
	import { useStore } from 'vuex'
	const store = useStore()
	const defaultCity = computed(() => store.state.user.defaultCity)
	const token = computed(() => store.state.user.token)
	const invationCount = computed(() => store.state.user.invationCount)
	const userInfo = computed(() => store.state.user.userInfo)
	const activityTypePopup = ref() // 活动类型弹窗
	const citySelectPopup = ref() // 城市弹窗
	
	const typeIndex = ref(0) // 活动类型当前索引
	const typeList = ref([])
	
	const activeType = ref(null) // 当前确认的活动类型
	const activeCity = reactive({
		name: null,
		id: null
	})
	
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	
	const cityFlag = ref(false)
	const dataList = ref([])
	
	const setStatus = () => {
		cityFlag.value = !cityFlag.value
	}
	
	const loadActivityList = () => {
		pageNo++
		getActivityList(false)
	}
	
	const getActivityList = async (refresh:boolean) => {
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
			const res:any = await api.post('activity/list',{
				page: pageNo,
				filter_area_id: activeCity.id,
				filter_category_id: activeType.value?.id
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
	
	const confirmCity = (options,name,cityId,cityName) => {
		activeCity.name = cityName
		activeCity.id = cityId
		setStatus()
		getActivityList(true)
	}
	
	const openCity = () => {
		if (token.value == null) {
			uni.showToast({
				icon: 'none',
				title:'请先登录'
			})
			return
		}
		cityFlag.value = !cityFlag.value
		citySelectPopup.value.open()
	}
	const changeType = (e:any) => {
		typeIndex.value = e.detail.value[0]
	}
	
	const app = getCurrentInstance().appContext.app
	
	onShow(() => {
		const curPages = getCurrentPages()[0];  // 获取当前页面实例
		if (typeof curPages.getTabBar === 'function' && curPages.getTabBar()) {  
			curPages.getTabBar().setData({
				['list[1].invationCount']: invationCount.value,
				['list[4].count']: userInfo.value.new_message_num,
				selected: 3   // 表示当前菜单的索引，该值在不同的页面表示不同
			});
		}
		 if (token.value != null) {
			 if (defaultCity.value != null) {
			 	activeCity.id = defaultCity.value.cityInd
			 	activeCity.name = defaultCity.value.child
			 	getActivityList(true)
			} else {
                // 新增：如果 defaultCity 没有值，等待一段时间后再次检查
                const checkInterval = setInterval(() => {
                    if (defaultCity.value != null) {
                        activeCity.id = defaultCity.value.cityInd
                        activeCity.name = defaultCity.value.child
                        getActivityList(true)
                        clearInterval(checkInterval)
                    }
                }, 200)
			 }
			 api.post('activity/category').then((res:any) => {
			 	if (res.code == 1) {
			 		typeList.value = res.data
			 	}
			 })
		 }
	})
	
	const confirmType = () => {
		activeType.value = typeList.value[typeIndex.value]
		closePopup(activityTypePopup.value)
		getActivityList(true)
	}
	
	const closePopup = (e:any) => {
		e.close()
	}
	
	const openPopup = (e:any) => {
		if (token.value == null) {
			uni.showToast({
				icon: 'none',
				title:'请先登录'
			})
			return
		}
		e.open()
	}
	
	const toDetail = (id:string) => {
		uni.navigateTo({
			url: `/pages/activity_detail/activity_detail?id=${id}`
		})
	}
</script>

<style lang="scss" scoped>
.content{
	width: 100%;
	height: 100%;
	background-color: #f7f8fa;
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
		width: 100%;
		height: 100%;
		z-index: 10;
		display: flex;
		flex-direction: column;
		align-items: center;
		.topfix{
			width: 100%;
			height: 80rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 8rpx;
			&-left{
				margin-left: 32rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				.location{
					width: 40rpx;
					height: 40rpx;
				}
				.city{
					font-size: 32rpx;
					color: #1D2129;
					font-weight: 600;
					margin-left: 8rpx;
				}
				.down_icon{
					width: 32rpx;
					height: 32rpx;
					background: rgba(0,0,0,0.05);
					border-radius: 32rpx;
					margin-left: 16rpx;
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: center;
					.img{
						width: 16rpx;
						height: 8rpx;
						transition-property: transform;
						transition-duration: 200ms;
						transition-timing-function: linear;
					}
				}
			}
			&-type{
				margin-right: 32rpx;
				padding: 0 24rpx;
				box-sizing: border-box;
				height: 48rpx;
				background-color: rgba(0,0,0,0.05);
				border-radius: 32rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				.txt{
					font-size: 24rpx;
					color: #1D2129;
				}
				.down{
					margin-left: 8rpx;
					width: 16rpx;
					height: 8rpx;
					transition-property: transform;
					transition-duration: 200ms;
					transition-timing-function: linear;
				}
			}
		}
		.scroll{
			width: 100%;
			display: flex;
			flex-direction: column;
			align-items: center;
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
			.item{
				width: 686rpx;
				padding: 24rpx;
				box-sizing: border-box;
				background-color: #fff;
				border-radius: 24px;
				display: flex;
				flex-direction: column;;
				margin: 0 auto 24rpx auto;
				&-mainImg{
					width: 288rpx;
					height: 192rpx;
					border-radius: 16rpx;
				}
				&-intro{
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					margin-left: 28rpx;
					display: flex;
					flex-direction: column;
					justify-content: space-between;
					.txt1{
						font-size: 28rpx;
						color: #1D2129;
						font-weight: 500;
						line-height: 44rpx;
						display: -webkit-box;
						-webkit-line-clamp: 2;
						-webkit-box-orient: vertical;
						overflow: hidden;
						text-overflow: ellipsis;
					}
					.row{
						display: flex;
						flex-direction: row;
						align-items: center;
						.icon{
							width: 24rpx;
							height: 26rpx;
						}
						.txt{
							margin-left: 8rpx;
							font-size: 22rpx;
							color: #868D9C;
							line-height: 38rpx;
						}
					}
				}
				&-bottom{
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: space-between;
					margin-top: 24rpx;
					.area{
						display: flex;
						flex-direction: row;
						align-items: center;
						&-icon{
							width: 20rpx;
							height: 24rpx;
						}
						&-txt{
							margin-left: 8rpx;
							font-size: 26rpx;
							color: #868D9C;
						}
					}
					.signUp{
						width: 144rpx;
						height: 56rpx;
						border-radius: 96rpx;
						font-size: 24rpx;
						line-height: 56rpx;
						text-align: center;
						color: #fff;
					}
					.finish_btn{
						width: 144rpx;
						height: 56rpx;
						background-color: #dadce0;
						border-radius: 96rpx;
						line-height: 56rpx;
						text-align: center;
						font-size: 24rpx;
						color: #FFFFFF;
					}
					.already{
						background: #DADCE0;
					}
					.non{
						background: linear-gradient(109deg,#4a97e7, #b57aff 100%);;
					}
				}
			}
		}
	}
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
}
</style>
