<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar  left-icon="left" @clickLeft="navBack" color="#1D2129" :border="false" background-color="transparent" :title="pageTitle" :statusBar="true"></uni-nav-bar>
			<view v-if="pageType == 1" class="child">
				<view class="content">
					<view class="content-item content-bd">
						<text class="label">姓名<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="realInfo.name" type="text" class="input" placeholder="请输入你的真实姓名"/>
					</view>
					<view class="content-item">
						<text class="label">身份证号<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="realInfo.idcard" type="text" class="input" placeholder="请输入你的身份证号"/>
					</view>
				</view>
				<view @click="submitRealName" class="child-btn">
					<text>提交审核</text>
				</view>
			</view>
			<view v-if="pageType == 2" class="child">
				<view class="content">
					<view class="content-item content-bd">
						<text class="label">毕业院校<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="eduInfo.school" type="text" class="input" placeholder="请输入院校名称"/>
					</view>
					<view class="content-item">
						<text class="label">学历<text :style="{color:'#FF546F'}">*</text></text>
						<view @click="openSelect(eduPopup)" class="select">
							<text class="txt" :class="eduInfo.degree != null?'active':''">{{eduInfo.degree == null?"请选择":eduInfo.degree}}</text>
							<image class="arrow_right" src="/static/mine_center/arrow_left.png"></image>
						</view>
					</view>
					<text class="upload_title">图片上传(多图上传)<text :style="{color:'#FF546F'}">*</text></text>
					<view class="upload">
						<view v-for="(item,ind) in eduInfo.images" :key="'img'+ind" class="upload-item">
							<image class="img" :src="item"></image>
							<image @click="deleteImg(ind)" src="/static/publish/delete.png" class="delete"></image>
						</view>
						<image @click="pickImgs" v-if="eduInfo.images.length < 9" src="/static/publish/addPhoto.png" class="upload-item"></image>
						<view v-for="n of spaceNum" class="upload-item" :key="'space'+n"></view>
					</view>
				</view>
				<view @click="submitEduInfo" class="child-btn">
					<text>提交审核</text>
				</view>
			</view>
			<view v-if="pageType == 3" class="child">
				<view class="content">
					<view class="content-item content-bd">
						<text class="label">公司名称<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="workInfo.company" type="text" class="input" placeholder="请输入公司名称"/>
					</view>
					<view class="content-item">
						<text class="label">职位<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="workInfo.position" type="text" class="input" placeholder="请输入职位名称"/>
					</view>
					<text class="upload_title">图片上传(3张以内)<text :style="{color:'#FF546F'}">*</text></text>
					<view class="upload">
						<view v-for="(item,ind) in workInfo.images" :key="'img'+ind" class="upload-item">
							<image class="img" :src="item"></image>
							<image @click="deleteWorkImg(ind)" src="/static/publish/delete.png" class="delete"></image>
						</view>
						<image @click="chooseWorkImg" v-if="workInfo.images.length < 3" src="/static/publish/addPhoto.png" class="upload-item"></image>
						<view v-for="n of workSpace" class="upload-item" :key="'work-space'+n"></view>
					</view>
				</view>
				<view @click="submitWorkInfo" class="child-btn">
					<text>提交审核</text>
				</view>
			</view>
		</view>
		<uni-popup ref="eduPopup" type="bottom">
			<view class="popup_stature">
				<view class="top">
					<text @click="closePopup(eduPopup)" class="top-cancel">取消</text>
					<text class="top-title">选择学历</text>
					<text @click="confirmEdu" class="top-confirm">确定</text>
				</view>
				<picker-view immediate-change="true" :value="[eduIndex]" @change="changeEduPicker" class="picker" indicator-style="height:48px">
					<picker-view-column>
						<view class="picker-item" v-for="(item,ind) in eduList" :key="'education'+ind">
							<text>{{item.name}}</text>
						</view>
					</picker-view-column>
				</picker-view>
			</view>
		</uni-popup>
	</view>
</template>

<script lang="ts" setup>
	import * as qiniuUploader from '@/common/upload/qiniuUploader.ts';
	import { api } from '@/common/request/index.ts'
	import { onLoad } from '@dcloudio/uni-app'
	import { computed, ref,getCurrentInstance, reactive } from 'vue'
	import { useStore } from 'vuex'
	const store = useStore()
	const app = getCurrentInstance().appContext.app
	const pageType = ref(1) // 1:实名认证，2：学历认证，3，工作认证
	const realInfo = reactive({
		name: null,
		idcard: null
	})
	const eduList = ref([]) // 学历列表
	const eduPopup = ref()
	const eduIndex = ref(0)
	
	const eduInfo = reactive({
		school: null,
		degree: null,
		images: []
	})
	
	const workInfo = reactive({
		company: null,
		position: null,
		images: []
	})
	
	const workSpace = computed(() => {
		if (workInfo.images.length >= 2) {
			return 1
		} else {
			return 3 - workInfo.images.length
		}
	})
	
	const spaceNum = computed(() => {
		return 4 - ((eduInfo.images.length % 4) + 1)
	})
	
	const pageTitle = computed(() => {
		let str = ""
		switch (pageType.value) {
			case 1:
				str = "实名认证"
			break;
			case 2:
				str = "学历认证"
			break;
			case 3:
				str = "工作认证"
			break;
		}
		return str
	})
	
	onLoad((options) => {
		pageType.value = parseInt(options.type)
		if (pageType.value == 2) {
			// 获得学历列表
			api.post('/common/education_type_list').then((res:any) => {
				if (res.code == 1) {
					eduList.value = res.data
				}
			})
		}
	})
	const changeEduPicker = (e:any) => {
		eduIndex.value = e.detail.value[0]
	}
	const confirmEdu = () => {
		eduInfo.degree = eduList.value[eduIndex.value].name
		closePopup(eduPopup.value)
	}
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const closePopup = (e:any) => {
		e.close()
	}
	
	const openSelect = (e:any) => {
		e.open()
	}
	
	const deleteImg = (ind:number) => {
		eduInfo.images.splice(ind,1)
	}
	
	const deleteWorkImg = (ind:number) => {
		workInfo.images.splice(ind,1)
	}
	const chooseWorkImg = () => {
		if (workInfo.images.length>=3) {
			uni.showToast({
				icon: 'none',
				title: "选择图片已达上限"
			})
			return;
		}
		uni.chooseImage({
			count: (3 - workInfo.images.length),
			success: (res) => {
				workInfo.images = workInfo.images.concat(res.tempFilePaths)
			}
		})
	}
	const pickImgs = () => {
		if (eduInfo.images.length>=9) {
			uni.showToast({
				icon: 'none',
				title: "选择图片已达上限"
			})
			return;
		}
		uni.chooseImage({
			count: (9 - eduInfo.images.length),
			success: (res) => {
				eduInfo.images = eduInfo.images.concat(res.tempFilePaths)
			}
		})
	}
	
	const submitRealName = async () => {
		// 实名认证
		if (realInfo.name == null || realInfo.name.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请输入你的真实姓名"
			})
			return
		}
		if (realInfo.idcard == null || realInfo.idcard.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请输入你的身份证号"
			})
			return
		}
		const regstr = /^[1-9]\d{5}(18|19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/;
		if (!regstr.test(realInfo.idcard)) {
			uni.showToast({
				icon: 'none',
				title: "身份证号格式不正确"
			})
			return
		}
		const res:any = await api.post('/my/cert/realname',realInfo)
		if (res.code == 1) {
			store.commit('setRealNameStatus',2)
			store.commit('setIsCertRealName',1)
			uni.showToast({
				icon:'none',
				title: res.msg
			})
			setTimeout(() => {
				uni.navigateBack()
			},1000)
		}
	}
	
	const uplaodFile = (path:string) => {
		return new Promise((resolve,reject) => {
			qiniuUploader.upload({
				filePath: path,
				success: (res) => {
					resolve(res.imageURL)
				},
				fail: (err) => {
					reject(null)
				}
			})
		})
	}
	
	//工作认证
	const submitWorkInfo = async () => {
		if (workInfo.company == null || workInfo.company.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请输入公司名称"
			})
			return;
		}
		if (workInfo.position == null || workInfo.position.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请输入职位名称"
			})
			return;
		}
		if (workInfo.images.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请上传证明图片"
			})
			return;
		}
		try{
			uni.showLoading({
				icon:'none',
				title:'loading...'
			})
			let tasks = []
			const vres:any = await api.post('common/qiniu')
			qiniuUploader.init({
				domain: vres.data.cdnurl,
				region: 'ECN',
				regionUrl: vres.data.uploadurl,
				uptoken: vres.data.multipart.qiniutoken,
			})
			for(let img of workInfo.images) {
				tasks.push(uplaodFile(img))
			}
			Promise.all(tasks).then(imgList => {
				workInfo.images = imgList
				api.post('my/cert/work',workInfo).then((result:any) => {
					if (result.code == 1) {
						store.commit('setWorkStatus',1)
						uni.hideLoading()
						uni.showToast({
							icon:'none',
							title: result.msg
						})
						setTimeout(() => {
							uni.navigateBack()
						},1000)
					}
				})
			}).catch(e => {
				uni.hideLoading()
			})
			
		}catch(e) {
			uni.hideLoading()
		}
	}
	
	// 学历认证提交
	const submitEduInfo = async () => {
		if (eduInfo.school == null || eduInfo.school.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请输入院校名称"
			})
			return;
		}
		if (eduInfo.degree == null || eduInfo.degree.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请选择学历"
			})
			return;
		}
		if (eduInfo.images.length <= 0) {
			uni.showToast({
				icon: 'none',
				title: "请上传证明图片"
			})
			return;
		}
		try{
			uni.showLoading({
				icon:'none',
				title:'loading...'
			})
			let tasks = []
			const vres:any = await api.post('common/qiniu')
			qiniuUploader.init({
				domain: vres.data.cdnurl,
				region: 'ECN',
				regionUrl: vres.data.uploadurl,
				uptoken: vres.data.multipart.qiniutoken,
			})
			for(let img of eduInfo.images) {
				tasks.push(uplaodFile(img))
			}
			Promise.all(tasks).then(imgList => {
				eduInfo.images = imgList
				api.post('/my/cert/education',eduInfo).then((result:any) => {
					if (result.code == 1) {
						store.commit('setEduStatus',1)
						uni.hideLoading()
						uni.showToast({
							icon:'none',
							title: result.msg
						})
						setTimeout(() => {
							uni.navigateBack()
						},1000)
					}
				})
			}).catch(e => {
				uni.hideLoading()
			})
			
		}catch(e) {
			uni.hideLoading()
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
		.child{
			width: 100%;
			flex: 1;
			flex-shrink: 0;
			min-height: 0;
			display: flex;
			flex-direction: column;
			align-items: center;
			&-btn{
				width: 686rpx;
				height: 88rpx;
				line-height: 88rpx;
				background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
				border-radius: 44rpx;
				text-align: center;
				font-size: 28rpx;
				color: #fff;
				font-weight: 500;
				margin-top: 64rpx;
			}
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
				.select{
					flex: 1;
					flex-shrink: 0;
					min-width: 0;
					padding-left: 16rpx;
					box-sizing: border-box;
					display: flex;
					flex-direction: row;
					align-items: center;
					justify-content: flex-end;
					.txt{
						font-size: 28rpx;
						color: #C2C5CC;
					}
					.active{
						color: #1D2129;
					}
					.arrow_right{
						width: 16rpx;
						height: 24rpx;
						margin-left: 16rpx;
					}
				}
			}
			.upload_title{
				margin-top: 32rpx;
				font-size: 28rpx;
				color: #1D2129;
			}
			.upload{
				display: flex;
				flex-direction: row;
				margin-top: 24rpx;
				margin-bottom: 32rpx;
				flex-wrap: wrap;
				justify-content: space-between;
				&-item{
					position: relative;
					margin-top: 10rpx;
					width: 148rpx;
					height: 148rpx;
					.img{
						width: 148rpx;
						height: 148rpx;
						border-radius: 16rpx;
						z-index: 9;
					}
					.delete{
						width: 36rpx;
						height: 36rpx;
						position: absolute;
						top: 12rpx;
						right: 12rpx;
						z-index: 10;
					}
					
				}
			}
			&-bd {
				border-bottom: 1px solid #E8EAEF;
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
