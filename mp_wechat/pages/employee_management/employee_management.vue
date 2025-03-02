<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" title="店员管理" :statusBar="true"></uni-nav-bar>
			<scroll-view type="list" :scroll-y="true" class="scroll">
				<view class="employee" v-for="(item,index) in dataList" :key="'employee'+index">
					<view class="employee-head">
						<text class="name">{{item.name}}</text>
						<text class="status" :class="{'error': (item.status == 'hidden')}">{{item.status == 'normal'?'正常':'冻结'}}</text>
					</view>
					<text class="employee-phone">{{item.mobile}}</text>
					<view class="employee-bottom">
						<view @click="deleteEmployee(item.id)" class="btn">
							<text>删除</text>
						</view>
						<view @click="toggleEmployeeStatus(item.id,item.status)" class="btn" :style="{marginLeft: '20rpx'}">
							<text>{{item.status == 'normal'?'冻结':'恢复'}}</text>
						</view>
						<view @click="editEmployee(item)" class="btn" :style="{marginLeft: '20rpx'}">
							<text>编辑</text>
						</view>
					</view>
				</view>
				<text class="scroll-status">{{loading?'正在加载':'加载完毕'}}</text>
				<view :style="{height: '96px',width:'100%'}"></view>
			</scroll-view>
			<view class="add_employee">
				<view @click="addEmployee" class="btn">
					<text>添加店员</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import { ref, getCurrentInstance } from 'vue'
	import { api } from '@/common/request/index.ts'
	import { onLoad, onUnload } from '@dcloudio/uni-app'
 	
	const app = getCurrentInstance().appContext.app
	const dataList = ref([]);
	const loading = ref(false)
	
	onLoad(() => {
		getEmployeeList(true)
		uni.$on('refreshEmployee',refreshData)
	})
	onUnload(() => {
		uni.$off('refreshEmployee',refreshData)
	})
	
	const refreshData = () => {
		getEmployeeList(true)
	}
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const editEmployee = (item:any) => {
		uni.navigateTo({
			url: `/pages/add_employee/add_employee?id=${item.id}&mobile=${item.mobile}&name=${item.name}`
		})
	}
	
	const toggleEmployeeStatus = (id:string,status:string) => {
		uni.showModal({
			title: `确定要${status == 'normal'?'冻结':'恢复'}该员工权限吗？`,
			success: async (res) => {
				if (res.confirm) {
					const res:any = await api.post('/my/shop/operate_clerk',{
						'clerk_id': id
					})
					if (res.code == 1) {
						uni.showToast({
							icon:'none',
							title: "修改员工权限成功",
						})
						getEmployeeList(true)
					}
				}
			}
		})
	}
	
	const deleteEmployee = (id:string) =>{
		uni.showModal({
			title:'删除提示',
			content: '确认删除员工',
			success:async function(res){
				if (res.confirm) {
					const vres:any = await api.post('my/shop/del_clerk',{
						'clerk_id': id
					})
					if (vres.code == 1) {
						uni.showToast({
							icon: 'none',
							title: "删除员工成功",
						})
						getEmployeeList(true)
					}
				}
			}
		})
	}
	
	const getEmployeeList = async (refresh:boolean) => {
		if (loading.value) {
			return;
		}
		loading.value = true
		try {
			const res:any = await api.post('my/shop/clerk_list')
			loading.value = false
			if (res.code == 1) {
				dataList.value = res.data
			}
		}catch(e) {
			loading.value = false
		}
	}
	const addEmployee = () => {
		uni.navigateTo({
			url: '/pages/add_employee/add_employee'
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
		.add_employee{
			width: 100%;
			height: 80px;
			background-color: #fff;
			display: flex;
			flex-direction: column;
			align-items: center;
			.btn{
				margin-top: 16rpx;
				width: 686rpx;
				height: 88rpx;
				line-height: 88rpx;
				text-align: center;
				background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
				border-radius: 44rpx;
				font-size: 28rpx;
				color: #fff;
				font-weight: 500;
			}
		}
		.scroll {
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
			.employee{
				width: 686rpx;
				padding: 24rpx 32rpx;
				background-color: #ffffff;
				border-radius: 24rpx;
				box-sizing: border-box;
				display: flex;
				flex-direction: column;
				align-items: stretch;
				margin: 0 auto;
				&-head{
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: space-between;
					.name{
						font-size: 28rpx;
						color: #1D2129;
						font-weight: 500;
					}
					.status{
						font-size: 28rpx;
						font-weight: 500;
						color: #0DC84D;
					}
					.error{
						color: #FF546F;
					}
				}
				&-phone{
					margin-top: 8rpx;
					font-size: 28rpx;
					color: #868D9C;
					line-height: 44rpx;
				}
				&-bottom{
					display: flex;
					flex-direction: row;
					justify-content: flex-end;
					align-items: center;
					.btn{
						width: 112rpx;
						height: 64rpx;
						border: 1px solid #e8eaef;
						border-radius: 36rpx;
						line-height: 64rpx;
						text-align: center;
						font-size: 24rpx;
						color: #4E5769;
					}
				}
			}
		}
	}
}
</style>
