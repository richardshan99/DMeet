<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="订单管理" :statusBar="true"></uni-nav-bar>
			<text class="title">{{shopInfo.shop_name}}</text>
			<view class="search">
				<view @click="openPopup(meetingDayPopup)" class="area1">
					<text class="txt">{{searchInfo.selectDay == null?'到店时间':searchInfo.selectDay}}</text>
					<image class="down" src="/static/order/arrow_down.png"></image>
				</view>
				<view @click="openPopup(statusPopup)" class="area1" :style="{marginLeft: '16rpx'}">
					<text class="txt">{{searchInfo.show_status == null?'订单号状态':searchInfo.show_status}}</text>
					<image class="down" src="/static/order/arrow_down.png"></image>
				</view>
				<view class="area2" :style="{marginLeft: '16rpx'}">
					<image class="icon" src="/static/order/search.png"></image>
					<input @confirm="getOrders(true)" v-model="searchInfo.nickname" type="text" confirm-type="search" class="input" :style="{marginLeft: '8rpx'}" placeholder="搜索用户昵称"/>
				</view>
			</view>
			
			<scroll-view class="scroll" :scroll-y="true" type="list" @scrolltolower="loadOrders">
				<view v-for="(item,ind) in dataList" :key="'order'+ind" class="item" :class="{'other_invitation': (item.status != 2)}" >
					<view class="meet">
						<view class="top">
							<view :style="{display: 'flex',flexDirection:'row',alignItems:'center'}">
								<image class="top-icon" src="/static/invitation/datetime.png"></image>
								<text class="top-datetime">{{item.meet_time_text}}</text>
							</view>
							<text v-if="item.status == 2" class="status1">{{item.status_text}}</text>
							<text v-else class="status2">{{item.status_text}}</text>
						</view>
						<view class="users">
							<view class="users-head">
								<image mode="aspectFill" :src="item.user.avatar_text" class="avatar"></image>
								<view class="pay_btn" :class="{
										'pay_me': item.pay_mode == 1,
										'pay_you': item.pay_mode == 2,
										'pay_aa': item.pay_mode == 3
									}">
									<text v-if="item.pay_mode == 1">我付</text>
									<text v-if="item.pay_mode == 2">你付</text>
									<text v-if="item.pay_mode == 3">AA</text>
								</view>
							</view>
							<view class="users-partInfo" :style="{marginLeft:'24rpx'}">
								<view :style="{display:'flex',flexDirection:'row',alignItems:'center'}">
									<text class="username">{{item.user.nickname}}</text>
									<image v-if="item.user.gender == 1" class="sex" src="/static/sex_man.png"></image>
									<image v-else class="sex" src="/static/sex_woman.png"></image>
								</view>
								<text class="type">邀约人</text>
								<text v-if="item.inviter_is_verify == 1" class="type">已核销</text>
								<text v-else class="non">未核销</text>
							</view>
							<view class="users-head" :style="{marginLeft: '46rpx'}">
								<image mode="aspectFill" :src="item.inviteuser.avatar_text" class="avatar"></image>
								<view v-if="item.pay_mode == 3" class="pay_btn pay_aa">
									<text>AA</text>
								</view>
							</view>
							<view class="users-partInfo" :style="{marginLeft:'24rpx'}">
								<view :style="{display:'flex',flexDirection:'row',alignItems:'center'}">
									<text class="username">{{item.inviteuser.nickname}}</text>
									<image v-if="item.inviteuser.gender == 1" class="sex" src="/static/sex_man.png"></image>
									<image v-else class="sex" src="/static/sex_woman.png"></image>
								</view>
								<text class="type">受邀人</text>
								<text v-if="item.invitee_is_verify == 1" class="type">已核销</text>
								<text v-else class="non">未核销</text>
							</view>
						</view>
						<image src="/static/invitation/invitation_line.png" class="divide_line"></image>
						<view @click="openPack(item)" class="bt_pay">
							<text class="txt1">{{item.package.name}}</text>
							<text class="txt1">¥{{item.price}}</text>
						</view>
						<text class="moneyInfo" :style="{marginBottom:'24rpx'}">履约保证金¥{{item.deposit}}</text>
					</view>
				</view>
				<text v-if="finish" class="scroll-status">{{dataList.length == 0?'没数据了':'数据加载完毕'}}</text>
				<text v-else class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
				<view :style="{height: '32rpx',width:'100%'}"></view>
			</scroll-view>
			<view @click="scanCode" class="scan_code">
				<text>扫码核销</text>
			</view>
		</view>
		
		<uni-popup ref="statusPopup" type="bottom">
			<view class="popup_stature">
				<view class="top">
					<text @click="closePopup(statusPopup)" class="top-cancel">取消</text>
					<text class="top-title">选择状态</text>
					<text @click="confirmStatus" class="top-confirm">确定</text>
				</view>
				<picker-view immediate-change="true" :value="[statusIndex]" @change="changePickerStatus" class="picker" indicator-style="height:48px">
					<picker-view-column>
						<view class="picker-item" v-for="(item,ind) in statusList" :key="'picker_status'+ind">
							<text>{{item.name}}</text>
						</view>
					</picker-view-column>
				</picker-view>
			</view>
		</uni-popup>
		<uni-popup ref="meetingDayPopup" type="bottom">
			<view class="popup_stature">
				<view class="top">
					<text @click="closePopup(meetingDayPopup)" class="top-cancel">取消</text>
					<text class="top-title">选择到店日期</text>
					<text @click="confirmMeetingDay" class="top-confirm">确定</text>
				</view>
				<!-- @change="changePicker('nowBirth',$event)" -->
				<picker-view immediate-change="true" :value="meetingDay" @change="changePickerMeetingDay" class="picker" indicator-style="height:48px">
					<picker-view-column>
						<view class="picker-item" v-for="(item,ind) in years" :key="'year'+ind">
							<text>{{item}}年</text>
						</view>
					</picker-view-column>
					<picker-view-column>
						<view class="picker-item" v-for="(item,ind) in months" :key="'month'+ind">
							<text>{{item}}月</text>
						</view>
					</picker-view-column>
					<picker-view-column>
						<view class="picker-item" v-for="(item,ind) in daysList" :key="'day'+ind">
							<text>{{item}}日</text>
						</view>
					</picker-view-column>
				</picker-view>
			</view>
		</uni-popup>
		<uni-popup ref="packagePopup" type="bottom">
			<view class="package">
				<text class="package-title">{{packInfo.name}}</text>
				<text class="package-intro">{{packInfo.intro}}</text>
				<view class="package-item" :style="{marginTop:'32rpx'}" v-for="(item,ind) in packInfo.service" :key="'packs'+ind">
					<text class="txt">{{item.name}}</text>
					<text class="txt">{{item.price}}</text>
				</view>
				<view class="package-item" :style="{marginTop:'64rpx'}" >
					<text class="txt">合计价值</text>
					<text class="txt">￥{{packTotal}}</text>
				</view>
				<view class="package-item" :style="{marginTop:'64rpx'}" >
					<text class="txt">套餐价格</text>
					<text :style="{fontSize:'36rpx',color:'#FF546F'}">￥{{packInfo.price}}</text>
				</view>
				<view @click="closePopup(packagePopup)" class="package-confirm">
					<text>确定</text>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script lang="ts" setup>
	import { api } from '@/common/request/index.ts'
	import { getCurrentInstance, ref, computed, reactive, nextTick } from 'vue'
	import { onLoad } from '@dcloudio/uni-app'
	import { useStore } from 'vuex'
	const store = useStore()
	const app = getCurrentInstance().appContext.app
	const shopInfo = computed(() => store.state.user.shopInfo)
	const statusPopup = ref()
	const packagePopup = ref()
	const meetingDayPopup = ref()
	const searchInfo = reactive({
		nickname: null,
		selectDay: null,
		range_meet_time: null,
		status: null,
		show_status: null
	})
	const statusList = [{
		value: null,
		name: '全部'
	},{
		value: 2,
		name: '待见面'
	},{
		value: 3,
		name: '已完成'
	},{
		value: 4,
		name: '已取消'
	}]
	
	const packInfo = reactive({
		name: null,
		intro:null,
		service: [],
		price: null
	})
	
	const packTotal = computed(() => {
		let total = 0
		if (packInfo.service.length > 0) {
			packInfo.service.forEach(item => {
				total+= parseFloat(item.price)
			})
		}
		return total
	})
	
	const years = ref([])
	const months = ref([])
	const daysList = ref([])
	
	const meetingDay = reactive([0,0,0])
	const statusIndex = ref(0)
	const navBack = () => {
		uni.navigateBack()
	}
	const dataList = ref([]);
	let pageNo = 1;
	const loading = ref(false)
	const finish = ref(false)
	
	onLoad(() => {
		getOrders(true)
		const nowDay = new Date()
		for(let year = 1700;year<= (new Date()).getFullYear();year++) {
			years.value.push(year)
		}
		for(let month = 1;month <= 12;month ++) {
			months.value.push(month)
		}
		for (let x = 1;x<=(new Date(nowDay.getFullYear(),nowDay.getMonth()+1,0).getDate());x++){
			daysList.value.push(x)
		}
		
		nextTick(() => {
			Object.assign(meetingDay,[years.value.length - 1,new Date().getMonth(),new Date().getDate()-1])
		})
	})
	
	const openPopup = (e:any) => {
		e.open()
	}
	
	const changePickerMeetingDay = (event) => {
		let choice = event.detail.value
		if (meetingDay[0] != choice[0] || meetingDay[1] != choice[1]) {
			let list = [];
			for (let x = 1;x<=(new Date(years.value[choice[0]],months.value[choice[1]],0).getDate());x++){
				list.push(x)
			}
			daysList.value = list
		}
		Object.assign(meetingDay,choice)
	}
	
	const confirmMeetingDay = () => {
		const dayStr = years.value[meetingDay[0]] + '-' +months.value[meetingDay[1]]+'-'+daysList.value[meetingDay[2]]
		searchInfo.range_meet_time = dayStr+ " - "+dayStr
		searchInfo.selectDay = dayStr
		getOrders(true)
		meetingDayPopup.value.close()
		// console.error(years.value[meetingDay[0]])
		// console.error(months.value[meetingDay[1]])
		// console.error(daysList.value[meetingDay[2]])
	}
	
	const confirmStatus = () => {
		searchInfo.show_status = statusList[statusIndex.value].name
		searchInfo.status = statusList[statusIndex.value].value
		getOrders(true)
		statusPopup.value.close()
	}
	
	const changePickerStatus = (e) => {
		statusIndex.value = e.detail.value[0]
	}
	
	const closePopup = (e:any) => {
		e.close()
	}
	
	const loadOrders = () => {
		pageNo++
		getOrders(false)
	}
	
	const getOrders = async (refresh:boolean) => {
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
			const res:any = await api.post('my/shop/order_list',{
				page: pageNo,
				...searchInfo
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
	
	const openPack = (item:any) => {
		Object.assign(packInfo,item.package)
		packInfo.price = item.price
		packagePopup.value.open()
	}
	
	const scanCode = () => {
		uni.scanCode({
			success(res) {
				if (res.result != null) {
					api.post('/my/shop/order_verify',{ text: res.result }).then((vres:any) => {
						if (vres.code == 1) {
							uni.showToast({
								icon:'none',
								title: vres.msg
							})
							getOrders(true)
						}
					})
				}
			},
			fail(err) {
				console.error(err)
			}
		})
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
		.title{
			margin-top: 16rpx;
			width: 686rpx;
			font-size: 32rpx;
			color: #1D2129;
			font-weight: 500;
		}
		.search{
			margin-top: 24rpx;
			width: 686rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			.area1{
				// width: 168rpx;
				padding-left: 24rpx;
				padding-right: 16rpx;
				box-sizing: border-box;
				height: 64rpx;
				background-color: #ffffff;
				border-radius: 32rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				justify-content: center;
				.txt{
					font-size: 24rpx;
					color: #1D2129;
				}
				.down{
					width: 24rpx;
					height: 24rpx;
				}
			}
			.area2{
				flex: 1;
				flex-shrink: 0;
				min-width: 0;
				background: #ffffff;
				border-radius: 32rpx;
				height: 64rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				.icon{
					margin-left: 24rpx;
					width: 24rpx;
					height: 24rpx;
				}
				.input{
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					font-size: 24rpx;
					color: #1D2129;
				}
			}
		}
		.scroll{
			width: 100%;
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			margin-top: 24rpx;
			&-status {
				font-size: 26rpx;
				color: #999;
				text-align: center;
				margin: 20rpx auto;
				display: block;
			}
			.item{
				width: 686rpx;
				position: relative;
				margin: 0 auto;
				margin-bottom: 20rpx;
				&::before{
					position: absolute;
					width: 100%;
					height: 100%;
					background-image: linear-gradient(117deg,#4a97e7, #b57aff 100%);
					border-radius: 24rpx;
					opacity: 0.1;
					left: 0;
					top: 0;
					z-index: -1;
					display: block;
					content: '';
				}
				.meet{
					width: 686rpx;
					z-index: 10;
					display: flex;
					flex-direction: column;
					align-items: stretch;
					box-sizing: border-box;
					// padding: 32rpx 32rpx 24rpx 32rpx;
					box-sizing: border-box;
					.top{
						padding: 0 32rpx;
						box-sizing: border-box;
						margin-top: 32rpx;
						display: flex;
						flex-direction: row;
						align-items: center;
						justify-content: space-between;
						&-icon{
							width: 32rpx;
							height: 32rpx;
						}
						&-datetime{
							margin-left: 16rpx;
							font-size: 28rpx;
							color: #1D2129;
							font-weight: 500;
						}
						.status1{
							font-size: 28rpx;
							font-weight: 500;
							background-image: linear-gradient(115deg,#4a97e7, #b57aff 100%);
							-webkit-background-clip: text;
							-webkit-text-fill-color: transparent;
						}
						.status2{
							font-size: 28rpx;
							font-weight: 500;
							color: #C2C5CC;
						}
					}
					.users{
						margin-top: 32rpx;
						padding: 0 32rpx;
						box-sizing: border-box;
						display: flex;
						flex-direction: row;
						align-items: flex-start;
						&-head{
							width: 88rpx;
							height: 96rpx;
							position: relative;
							.avatar{
								width: 88rpx;
								height: 88rpx;
								border-radius: 44rpx;
								z-index: 9;
								position: absolute;
								left: 0;
								top: 0;
							}
							.pay_btn{
								width: 64rpx;
								height: 32rpx;
								border: 1px solid #ffffff;
								border-radius: 18rpx;
								font-size: 20rpx;
								color: #fff;
								position: absolute;
								z-index: 10;
								bottom: 0;
								left: 12rpx;
								text-align: center;
								line-height: 32rpx;
							}
							.pay_aa{
								background: linear-gradient(116deg,#00bc6d 0%, #2acf8a 100%);
							}
							.pay_me{
								background: linear-gradient(114deg,#4a97e7, #b57aff 100%);
							}
							.pay_you{
								background: linear-gradient(114deg,#ff66c2, #ff7ccb 100%);
							}
						}
						&-partInfo{
							display: flex;
							flex-direction: column;
							.username{
								font-size: 28rpx;
								line-height: 44rpx;
								color: #1D2129;
							}
							.type{
								font-size: 24rpx;
								color: #868D9C;
								line-height: 40rpx;
							}
							.sex{
								margin-left: 8rpx;
								width: 32rpx;
								height: 32rpx;
							}
							.non{
								font-size: 24rpx;
								color: #F0251B;
							}
						}
					}
					.divide_line{
						width: 686rpx;
						height: 24rpx;
						margin: 16rpx 0;
						z-index: 16;
					}
					.bt_pay{
						padding: 0 32rpx;
						box-sizing: border-box;
						display: flex;
						flex-direction: row;
						align-items: center;
						justify-content: space-between;
						.txt1{
							font-size: 28rpx;
							color: #1D2129;
							font-weight: 500;
							line-height: 44rpx;
						}
					}
					.moneyInfo{
						padding-left: 32rpx;
						box-sizing: border-box;
						font-size: 24rpx;
						color: #868D9C;
						line-height: 40rpx;
					}
				}
			}
			
			
			.other_invitation{
				&::before{
					position: absolute;
					width: 100%;
					height: 100%;
					border-radius: 24rpx;
					left: 0;
					top: 0;
					z-index: 8;
					display: block;
					content: '';
					opacity: unset;
					// background: none;
					background-color: #fff;
					background-image: none;
				}
				.meet{
					position: relative;
					z-index: 11;
				}
			}
		}
		.scan_code{
			z-index: 9999;
			width: 622rpx;
			height: 88rpx;
			background: linear-gradient(97deg,#4a97e7, #b57aff 100%);
			border-radius: 44rpx;
			box-shadow: 0px 16rpx 64rpx 0px rgba(129,137,244,0.15);
			line-height: 88rpx;
			text-align: center;
			font-size: 28rpx;
			color: #fff;
			font-weight: 500;
			position: fixed;
			bottom: 100rpx;
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
	.package{
		width: 750rpx;
		background-color: #ffffff;
		border-radius: 24rpx 24rpx 0px 0px;
		padding: 40rpx;
		box-sizing: border-box;
		display: flex;
		flex-direction: column;
		&-title{
			font-size: 36rpx;
			color: #1D2129;
			font-weight: 500;
		}
		&-intro{
			margin-top: 16rpx;
			font-size: 28rpx;
			color: #868D9C;
			line-height: 44rpx;
		}
		&-item{
			display: flex;
			flex-direction: row;
			align-items: center;
			justify-content: space-between;
			.txt{
				font-size: 28rpx;
				color: #1D2129;
			}
		}
		&-confirm{
			width: 686rpx;
			height: 88rpx;
			background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
			border-radius: 44rpx;
			line-height: 88rpx;
			text-align: center;
			font-size: 28rpx;
			font-weight: 500;
			color: #fff;
			margin: 60rpx auto 40rpx auto;
		}
	}
}
</style>
