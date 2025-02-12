<template>
	<view class="main">
		<view class="main-base" v-if="shopInfo.shop_info_status == 3 && !againEdit">
			<image class="auditIcon" src="/static/authentication/audit_reject.png"></image>
			<text class="audit_txt1">店铺信息审核未通过</text>
			<text class="audit_txt2">驳回原因：{{shopInfo.reject_reason}}</text>
			<view :style="{display:'flex',flexDirection:'row',alignItems:'center',justifyContent:'center',marginTop:'64rpx'}">
				<view @click="againEdit = true" class="revise_btn">
					<text>重新修改</text>
				</view>
				<view @click="navBack" class="reback_btn">
					<text>返回首页</text>
				</view>
			</view>
		</view>
		<view class="main-base" v-else>
			<view class="main-navBar">
				<view @click="changeCurrent(0)" class="item" :class="{'active': current == 0}">
					<text class="tab_txt">基本信息</text>
				</view>
				<view @click="changeCurrent(1)" class="item" :class="{'active': current == 1}">
					<text class="tab_txt">提现账号</text>
				</view>
				<view @click="changeCurrent(2)" class="item" :class="{'active': current == 2}">
					<text class="tab_txt">套餐设置</text>
				</view>
			</view>
			<view v-if="current == 0" class="main-content">
				<text class="title">门店信息</text>
				<view class="content">
					<view class="content-item content-bd">
						<text class="label">门店名称<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="storeInfo.shop_name" type="text" class="input" placeholder="请输入门店名称"/>
					</view>
					<text class="upload_title">门店缩略图(单张、160*160)<text :style="{color:'#FF546F'}">*</text></text>
					<view class="upload content-bd">
						<image v-if="storeInfo.thumb.path == null" @click="chooseStoreImg" src="/static/publish/addPhoto.png" class="upload-item"></image>
						<view v-else class="upload-item">
							<image class="img" :src="storeInfo.thumb.path"></image>
							<image @click="storeInfo.thumb.path = null" src="/static/publish/delete.png" class="delete"></image>
						</view>
					</view>
					<text class="upload_title">门店轮播图(多张、750*500)<text :style="{color:'#FF546F'}">*</text></text>
					<view class="upload content-bd">
						<view v-for="(item,ind) in storeInfo.images" :key="'other'+ind" class="upload-item">
							<image class="img" :src="item.path"></image>
							<image @click="deleteSwiperImg(ind)" src="/static/publish/delete.png" class="delete"></image>
						</view>
						<image @click="chooseSwiperImg" v-if="storeInfo.images.length < 9" src="/static/publish/addPhoto.png" class="upload-item"></image>
						<view v-for="n of spaceNum" class="upload-item" :key="'apply-space'+n"></view>
					</view>
					<view class="content-item content-bd">
						<text class="label">门店地址<text :style="{color:'#FF546F'}">*</text></text>
						<view @click="chooseLocation" class="select">
							<text class="txt" :class="storeInfo.address != null?'active':''">{{storeInfo.address == null?"请选择":storeInfo.address}}</text>
							<image class="arrow_right" src="/static/mine_center/arrow_left.png"></image>
						</view>
					</view>
					<view class="content-item">
						<text class="label">营业时间段<text :style="{color:'#FF546F'}">*</text></text>
						<view @click="openTimeLine" class="select">
							<text class="txt" :class="storeInfo.business_time != null?'active':''">{{storeInfo.business_time == null?"请选择":storeInfo.business_time}}</text>
							<image class="arrow_right" src="/static/mine_center/arrow_left.png"></image>
						</view>
					</view>
				</view>
				<text class="title">门店详情<text :style="{color:'#FF546F'}">*</text></text>
				<view class="content" :style="{marginBottom:'64rpx'}">
					<textarea v-model="storeInfo.content" placeholder="请输入门店简介" :style="{height:'200rpx',fontSize:'28rpx',color:'#1D2129',width:'auto',marginTop:'32rpx'}"></textarea>
					<view class="upload">
						<view v-for="(item,ind) in storeInfo.content_images" :key="'other'+ind" class="upload-item">
							<image class="img" :src="item.path"></image>
							<image @click="deleteContentImg(ind)" src="/static/publish/delete.png" class="delete"></image>
						</view>
						<image @click="chooseContentImgs" v-if="storeInfo.content_images.length < 9" src="/static/publish/addPhoto.png" class="upload-item"></image>
						<view v-for="n of space_content" class="upload-item" :key="'apply-space'+n"></view>
					</view>
				</view>
			</view>
			<view v-if="current == 1" class="main-content">
				<text class="title">请选择其中一种账号填写</text>
				<view class="content">
					<view class="content-item content-bd">
						<text class="label">账号类型<text :style="{color:'#FF546F'}">*</text></text>
						<view class="select">
							<view @click="changePayMethod(1)" class="pay_way" :class="{'active_way': storeInfo.cash_type == 1}">
								<text class="txt">微信</text>
							</view>
							<view @click="changePayMethod(2)" class="pay_way" :style="{marginLeft:'20rpx'}" :class="{'active_way': storeInfo.cash_type == 2}">
								<text class="txt">支付宝</text>
							</view>	
							<view @click="changePayMethod(3)" class="pay_way" :style="{marginLeft:'20rpx'}" :class="{'active_way': storeInfo.cash_type == 3}">
								<text class="txt">银行卡</text>
							</view>	
						</view>
					</view>
					<view class="content-item content-bd">
						<text class="label">姓名<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="storeInfo.cash_account.name" type="text" class="input" placeholder="请输入姓名"/>
					</view>
					<view class="content-item" :class="{'content-bd': storeInfo.cash_type == 3}">
						<text v-if="storeInfo.cash_type == 1" class="label">微信号<text :style="{color:'#FF546F'}">*</text></text>
						<text v-if="storeInfo.cash_type == 2" class="label">支付宝账号<text :style="{color:'#FF546F'}">*</text></text>
						<text v-if="storeInfo.cash_type == 3" class="label">银行卡号<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="storeInfo.cash_account.account" type="text" class="input" :placeholder="`请输入${storeInfo.cash_type == 1?'微信号':(storeInfo.cash_type == 2?'支付宝账号':'银行卡号')}`"/>
					</view>
					<view v-if="storeInfo.cash_type == 3"  class="content-item">
						<text class="label">开户行<text :style="{color:'#FF546F'}">*</text></text>
						<input v-model="storeInfo.cash_account.deposit" type="text" class="input" placeholder="请输入开户行"/>
					</view>
				</view>
			</view>
			<view v-if="current == 2" class="main-content">
				<view class="tabbar">
					<view @click="changeCurrentPack(1)" class="tabbar-item" :class="{'selected': currentPackage == 1}">
						<text class="txt">套餐一</text>
					</view>
					<view @click="changeCurrentPack(2)"  class="tabbar-item" :class="{'selected': currentPackage == 2}">
						<text class="txt">套餐二</text>
					</view>
				</view>
				<view v-if="currentPackage == 1" class="child">
					<view class="content">
						<view class="content-item content-bd">
							<text class="label">套餐名称<text :style="{color:'#FF546F'}">*</text></text>
							<input v-model="storeInfo.package1.name" type="text" class="input" placeholder="请输入套餐名称"/>
						</view>
						<view class="content-item">
							<text class="label">套餐简介<text :style="{color:'#FF546F'}">*</text></text>
							<input v-model="storeInfo.package1.intro" type="text" class="input" placeholder="请输入简介"/>
						</view>
					</view>
					<text class="title">服务列表</text>
					<view class="content">
						<view v-for="(item,ind) in storeInfo.package1.service" :key="'pack1_service'+ind" class="content-item">
							<image @click="deleteService(ind,1)" src="/static/delete_tag.png" class="reduce_icon"></image>
							<input v-model="item.name" type="text" class="input" :style="{marginLeft:'24rpx',textAlign:'left'}" placeholder="请输入服务名称"/>
							<view class="divide_line"></view>
							<input v-model="item.price" type="digit" class="num_input" placeholder="请输入价格"/>
						</view>
						<view @click="addService(1)" class="content-item" :style="{justifyContent:'center'}">
							<image src="/static/add_service.png" class="add_icon"></image>
							<text class="add_txt">添加服务</text>
						</view>
					</view>
					<text class="title">费用合计</text>
					<view class="content" :style="{marginBottom: '64rpx'}">
						<view class="content-item content-bd">
							<text class="label">合计价值</text>
							<view class="select">
								<text class="txt active">￥{{totalPrice1}}</text>
							</view>
						</view>
						<view class="content-item">
							<text class="label">套餐价格</text>
							<input v-model="storeInfo.package1.price" type="digit" class="input" placeholder="请输入套餐价格"/>
						</view>
					</view>
				</view>
				<view v-else class="child">
					<view class="content">
						<view class="content-item content-bd">
							<text class="label">套餐名称<text :style="{color:'#FF546F'}">*</text></text>
							<input v-model="storeInfo.package2.name" type="text" class="input" placeholder="请输入套餐名称"/>
						</view>
						<view class="content-item">
							<text class="label">套餐简介<text :style="{color:'#FF546F'}">*</text></text>
							<input v-model="storeInfo.package2.intro" type="text" class="input" placeholder="请输入简介"/>
						</view>
					</view>
					<text class="title">服务列表</text>
					<view class="content">
						<view v-for="(item,ind) in storeInfo.package2.service" :key="'pack1_service'+ind" class="content-item">
							<image @click="deleteService(ind,2)" src="/static/delete_tag.png" class="reduce_icon"></image>
							<input v-model="item.name" type="text" class="input" :style="{marginLeft:'24rpx',textAlign:'left'}" placeholder="请输入服务名称"/>
							<view class="divide_line"></view>
							<input v-model="item.price" type="number" class="num_input" placeholder="请输入价格"/>
						</view>
						<view @click="addService(2)" class="content-item" :style="{justifyContent:'center'}">
							<image src="/static/add_service.png" class="add_icon"></image>
							<text class="add_txt">添加服务</text>
						</view>
					</view>
					<text class="title">费用合计</text>
					<view class="content" :style="{marginBottom: '64rpx'}">
						<view class="content-item content-bd">
							<text class="label">合计价值</text>
							<view class="select">
								<text class="txt active">￥{{totalPrice2}}</text>
							</view>
						</view>
						<view class="content-item">
							<text class="label">套餐价格</text>
							<input v-model="storeInfo.package2.price" type="digit" class="input" placeholder="请输入套餐价格"/>
						</view>
					</view>
				</view>
			</view>
			<view @click="saveAll" class="main-submit">
				<view class="btn">
					<text>保存</text>
				</view>
			</view>
		</view>
		<uni-popup ref="timePeriod" type="bottom">
			<time-select @confirm="confirmTime" :nowOption="[8,20]"></time-select>
		</uni-popup>
	</view>
</template>

<script lang="ts" setup>
	import * as qiniuUploader from '@/common/upload/qiniuUploader.ts';
	import { api } from '@/common/request/index.ts'
 	import { reactive, ref, computed } from 'vue';
	import { onLoad } from '@dcloudio/uni-app'
	import { useStore } from 'vuex'
	const store = useStore()
	const timePeriod = ref()
	const shopInfo = computed(() => store.state.user.shopInfo)
	const againEdit = ref(false)
	
	const currentPackage = ref(1)
	
	const storeInfo = reactive({
		shop_name: null,
		thumb: {
			local: true,
			path: null
		},
		images: [],
		address: null,
		business_time: null,
		content: null,
		content_images: [],
		cash_type: 1,  // 1:微信 2:支付宝 3:银行卡
		cash_account: {
			name: null,
			account: null,
			deposit: null
		},
		package1: {
			price: null,
			name: null,
			intro: null,
			service: []
		},
		package2: {
			price: null,
			name: null,
			intro: null,
			service: []
		}
		
	})
	
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
	const navBack = () => {
		uni.navigateBack()
	}
	
	const validateBankCard = (cardNumber) => {
	    // 移除非数字字符
	    cardNumber = cardNumber.replace(/\D/g, '');
	 
		// 基于Luhn算法的校验位计算
		let sum = 0, mul = 0, lencard = cardNumber.length;
		for (let i = 0; i < lencard; i++) {
			mul = (cardNumber.charAt(lencard - 1 - i) & 15) * (i % 2 === 0 ? 1 : 2);
			sum += (mul < 10) ? mul : (mul - 9);
		}
		 
		// 标准的银行卡号长度范围是13到19位
		const isLengthValid = cardNumber.length >= 13 && cardNumber.length <= 19;
		 
		// Luhn算法校验
		const isLuhnValid = sum % 10 === 0;
	 
	    return isLengthValid && isLuhnValid;
	}
	
	const saveAll = async () => {
		if (storeInfo.shop_name == null ||storeInfo.shop_name.length <= 0
		||storeInfo.thumb == null
		|| storeInfo.images.length <= 0
		// || storeInfo.content_images.length <= 0
		|| storeInfo.address == null
		|| storeInfo.business_time == null
		|| storeInfo.content == null || storeInfo.content.length <= 0) {
			uni.showToast({
				icon:'none',
				title: '请完善基本信息'
			})
			return
		}
		if (storeInfo.cash_account.name == null 
		|| storeInfo.cash_account.name.length <= 0
		|| storeInfo.cash_account.account == null
		|| storeInfo.cash_account.account.length <= 0) {
			uni.showToast({
				icon:'none',
				title: '请完善提现账号'
			})
			return
		}
		if(storeInfo.cash_type == 3 && (storeInfo.cash_account.deposit == null || storeInfo.cash_account.deposit.length <= 0)) {
			uni.showToast({
				icon:'none',
				title: '请输入开户行'
			})
			return
		}
		let packageFinish1 = true
		if (storeInfo.package1.name == null
		|| storeInfo.package1.name.length <= 0
		|| storeInfo.package1.intro == null
		|| storeInfo.package1.intro.length <= 0
		|| storeInfo.package1.price == null
		|| storeInfo.package1.price.length <= 0
		|| storeInfo.package1.service.length <= 0) {
			// uni.showToast({
			// 	icon:'none',
			// 	title: '请完善套餐1'
			// })
			// return
			packageFinish1 = false
		}
		if (storeInfo.cash_type == 2) {
			// 支付宝
			// storeInfo.cash_account.account
			// const phReg = /^[1][3,4,5,7,8][0-9]{9}$/; 
			const phReg = /^1[3456789]\d{9}$/;
			const emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
			if (!phReg.test(storeInfo.cash_account.account) && !emailReg.test(storeInfo.cash_account.account)) {
				uni.showToast({
					icon:'none',
					title: '支付宝账号格式不正确'
				})
				return;
			}
		}
		if (storeInfo.cash_type == 3 && !validateBankCard(storeInfo.cash_account.account)) {
			uni.showToast({
				icon:'none',
				title: '银行卡号格式不正确'
			})
			return;
		}
		let con = true
		for (let item of storeInfo.package1.service) {
			if (item.name == null || item.name.length <= 0||item.price == 0|| item.price.length <= 0) {
				con = false
				break
			}
		}
		if (!con) {
			packageFinish1 = false
			// uni.showToast({
			// 	icon:'none',
			// 	title: '请完善套餐1'
			// })
			// return
		}
		if ((storeInfo.package2.name == null
		|| storeInfo.package2.name.length <= 0
		|| storeInfo.package2.intro == null
		|| storeInfo.package2.intro.length <= 0
		|| storeInfo.package2.price == null
		|| storeInfo.package2.price.length <= 0
		|| storeInfo.package2.service.length <= 0) && !packageFinish1) {
			uni.showToast({
				icon:'none',
				title: '请至少完善一份套餐'
			})
			return
		}
		
		let con2 = true
		for (let vitem of storeInfo.package2.service) {
			if (vitem.name == null || vitem.name.length <= 0||vitem.price == 0|| vitem.price.length <= 0) {
				con2 = false
				break
			}
		}
		if (!con2 && !packageFinish1) {
			uni.showToast({
				icon:'none',
				title: '请至少完善一份套餐'
			})
			return
		}
		try{
			uni.showLoading({
				title: '提交中',
				mask:true
			})
			const hres:any = await api.post('common/qiniu')
			qiniuUploader.init({
				domain: hres.data.cdnurl,
				region: 'ECN',
				regionUrl: hres.data.uploadurl,
				uptoken: hres.data.multipart.qiniutoken,
			})
			if (storeInfo.thumb != null && storeInfo.thumb.local == true) {
				 var nPath = await uplaodFile(storeInfo.thumb.path)
				 storeInfo.thumb = {
				 	local: false,
				 	path: nPath
				 }
			}
			let task1 = []
			for (let one of storeInfo.images) {
				if (one.local == true) {
					task1.push(uplaodFile(one.path))
				}
			}
			if (task1.length > 0) {
				let imgs = await Promise.all(task1)
				let ind = 0
				storeInfo.images.forEach(oitem => {
					if (oitem.local) {
						oitem.path = imgs[ind]
						ind++
						oitem.local = false
					}
				})
			}
			let task2 = []
			for (let one of storeInfo.content_images) {
				if (one.local == true) {
					task2.push(uplaodFile(one.path))
				}
			}
			if (task2.length > 0) {
				let imgs2 = await Promise.all(task2)
				let index = 0
				storeInfo.content_images.forEach(oitem => {
					if (oitem.local) {
						oitem.path = imgs2[index]
						oitem.local = false
						index++
					}
				})
			}
			
			const submitData = Object.assign({},storeInfo)
			submitData.thumb = submitData.thumb.path
			submitData.images = submitData.images.map(item => item.path)
			submitData.content_images = submitData.content_images.map(item => item.path)
			const res:any = await api.post('/my/shop/submit_change',submitData)
			uni.hideLoading()
			if (res.code == 1) {
				uni.showToast({
					icon:'none',
					title: res.msg
				})
				store.commit('setShopStatus',1)
				setTimeout(() => {
					uni.navigateBack()
				},1000)
			}
		} catch(e) {
			uni.hideLoading()
		}
	}
	
	const totalPrice1 = computed(() => {
		let total = 0
		for(let x of storeInfo.package1.service) {
			if (x.price != null && x.price.length > 0) {
				let value = 0
				try{
					value = parseFloat(x.price)
				}catch(e) {
					value = 0
				}
				total += value
			}
		}
		return total.toFixed(2)
	})
	
	const totalPrice2 = computed(() => {
		let total = 0
		for(let x of storeInfo.package2.service) {
			if (x.price != null && x.price.length > 0) {
				let value = 0
				try{
					value = parseFloat(x.price)
				}catch(e) {
					value = 0
				}
				total += value
			}
		}
		return total.toFixed(2)
	})
	
	const spaceNum = computed(() => {
		return 4 - ((storeInfo.images.length % 4) + 1)
	})
	
	const space_content = computed(() => {
		return 4 - ((storeInfo.content_images.length % 4) + 1)
	})
	
	const current = ref(0)
	const openTimeLine = () => {
		timePeriod.value.open()
	}
	
	const addService = (type:number) => {
		if (type == 1) {
			storeInfo.package1.service.push({
				name: null,
				price: null
			})
		} else {
			storeInfo.package2.service.push({
				name: null,
				price: null
			})
		}
	}
	
	const deleteService = (ind:number,type:number) => {
		if (type == 1) {
			storeInfo.package1.service.splice(ind,1)
		} else {
			storeInfo.package2.service.splice(ind,1)
		}
	}
	
	const confirmTime = (val) => {
		storeInfo.business_time = val
	}
	const chooseLocation = () => {
		uni.chooseLocation({
			success: (res) => {
				storeInfo.address = res.address+res.name
			},
			fail:function(err){
				console.error(err)
			}
		})
	}
	
	const changePayMethod = (payWay:number) => {
		if (storeInfo.cash_type != payWay) {
			storeInfo.cash_type = payWay
			storeInfo.cash_account.account = null
		}
	}
	
	const chooseSwiperImg = () => {
		if (storeInfo.images.length>=9) {
			uni.showToast({
				icon: 'none',
				title: "选择图片已达上限"
			})
			return;
		}
		uni.chooseImage({
			count: (9 - storeInfo.images.length),
			success: (res) => {
				storeInfo.images = storeInfo.images.concat(res.tempFiles.map((item:any) => {
					return {
						path: item.path,
						local: true
					}
				}))
			}
		})
	}
	
	const changeCurrentPack = (pack:number) => {
		if (currentPackage.value != pack) {
			currentPackage.value = pack
		}
	}
	
	const chooseContentImgs = () => {
		if (storeInfo.content_images.length>=9) {
			uni.showToast({
				icon: 'none',
				title: "选择图片已达上限"
			})
			return;
		}
		uni.chooseImage({
			count: (9 - storeInfo.content_images.length),
			success: (res) => {
				storeInfo.content_images = storeInfo.content_images.concat(res.tempFiles.map((item:any) => {
					return {
						path: item.path,
						local: true
					}
				}))
			}
		})
	}
	
	const deleteSwiperImg = (ind:number) => {
		storeInfo.images.splice(ind,1)
	}
	
	const deleteContentImg = (ind:number) => {
		storeInfo.content_images.splice(ind,1)
	}
	
	const chooseStoreImg = () => {
		uni.chooseImage({
			count: 1,
			success: (res) => {
				storeInfo.thumb = {
					path: res.tempFilePaths[0],
					local: true
				}
			}
		})
	}
	
	onLoad(() => {
		api.post('/my/shop/change').then((res:any) => {
			if (res.code != null && res.data != null && res.data.shop_name!=null) {
				Object.assign(storeInfo,res.data)
				if (res.data.thumb_text != null && res.data.thumb_text.length > 0) {
					storeInfo.thumb = {
						local: false,
						path: res.data.thumb_text
					}
				}
				storeInfo.images = res.data.images_text.map(item => {
					return {
						local: false,
						path: item
					}
				})
				storeInfo.content = res.data.content.content
				storeInfo.content_images = res.data.content.content_images_text.map(item => {
					return {
						local: false,
						path: item
					}
				})
			}
		})
	})
	
	const changeCurrent = (ind:number) => {
		if (current.value != ind) {
			current.value = ind
		}
	}
</script>

<style lang="scss" scoped>
.main{
	width: 100%;
	height: 100%;
	background-color: #F7F8FA;
	display: flex;
	flex-direction: column;
	align-items: center;
	&-base{
		width: 100%;
		height: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		.auditIcon{
			margin-top: 64rpx;
			width: 146rpx;
			height: 144rpx;
		}
		.audit_txt1{
			margin-top: 40rpx;
			font-size: 32rpx;
			color: #1D2129;
			font-weight: 500;
		}
		.audit_txt2{
			width: 622rpx;
			margin-top: 8rpx;
			font-size: 28rpx;
			color: #868D9C;
			line-height: 44rpx;
			text-align: center;
			word-wrap: break-word; /* 使得长单词或数字可以换行 */
			overflow-wrap: break-word; /* 确保兼容性 */
		}
		.revise_btn{
			width: 176rpx;
			height: 64rpx;
			background-color: #ffffff;
			border: 1px solid #dadce0;
			border-radius: 32rpx;
			line-height: 64rpx;
			text-align: center;
			font-size: 28rpx;
			color: #1D2129;
			font-weight: 500;
		}
		.reback_btn{
			width: 176rpx;
			height: 64rpx;
			background-color: #ffffff;
			border: 1px solid #2c94ff;
			border-radius: 32rpx;
			line-height: 64rpx;
			text-align: center;
			margin-left: 24rpx;
			font-size: 28rpx;
			color: #2C94FF;
			font-weight: 500;
		}
	}
	&-navBar {
		width: 100%;
		height: 44px;
		display: flex;
		flex-direction: row;
		background-color: #fff;
		.item{
			flex: 1;
			flex-shrink: 0;
			min-width: 0;
			height: 44px;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			position: relative;
			.tab_txt{
				font-size: 28rpx;
				color: #1D2129;
			}
		}
		.active{
			.tab_txt{
				font-size: 28rpx;
				font-weight: 500;
				background: linear-gradient(109deg,#4a97e7, #b57aff 100%);
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
			}
			&::after{
				display: block;
				content: '';
				width: 40rpx;
				height: 4rpx;
				background: linear-gradient(95deg,#4a97e7, #b57aff 100%);
				border-radius: 4rpx;
				position: absolute;
				bottom: 0;
			}
		}
	}
	&-content{
		width: 100%;
		flex: 1;
		flex-shrink: 0;
		min-height: 0;
		overflow-y: auto;
		display: flex;
		flex-direction: column;
		align-items: center;
		
		.title{
			width: 686rpx;
			margin-top: 24rpx;
			padding-left: 8rpx;
			font-size: 28rpx;
			color: #868D9C;
			box-sizing: border-box;
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
				.pay_way {
					padding: 8rpx 32rpx;
					box-sizing: border-box;
					border-radius: 28rpx;
					border: 1px solid #DADCE0;
					.txt{
						display: block;
						font-size: 24rpx;
						color: #1D2129;
					}
				}
				.active_way{
					border: 1px solid transparent;
					background: linear-gradient(114deg,rgba(74,151,231,0.15), rgba(181,122,255,0.15) 100%);
					.txt{
						background: linear-gradient(126deg,#4a97e7, #b57aff 100%);
						color: transparent;
						-webkit-background-clip: text;
						-webkit-text-fill-color: transparent;
					}
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
						flex: 1;
						text-align: right;
						flex-shrink: 0;
						min-width: 0;
						font-size: 28rpx;
						color: #C2C5CC;
						white-space: nowrap;
						overflow: hidden;
						text-overflow: ellipsis;
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
				.add_icon{
					width: 34rpx;
					height: 32rpx;
				}
				.add_txt{
					margin-left: 16rpx;
					font-size: 28rpx;
					background: linear-gradient(109deg,#4a97e7, #b57aff 100%);
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
				}
				.reduce_icon{
					width: 40rpx;
					height: 40rpx;
				}
				.divide_line{
					width: 1px;
					height: 32rpx;
					background-color: #e8eaef;
					margin: 0 48rpx;
				}
				.num_input{
					width: 140rpx;
					font-size: 28rpx;
					font-size: 28rpx;
					color: #1D2129;
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
				padding-bottom: 32rpx;
				box-sizing: border-box;
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
		.tabbar{
			width: 686rpx;
			display: flex;
			flex-direction: row;
			align-items: center;
			justify-content: space-between;
			margin-top: 24rpx;
			&-item{
				width: 328rpx;
				height: 64rpx;
				background: #ffffff;
				border-radius: 32rpx;
				display: flex;
				flex-direction: row;
				align-items: center;
				justify-content: center;
				.txt{
					font-size: 28rpx;
					color: #4E5769;
				}
			}
			.selected{
				background: linear-gradient(99deg,rgba(74,151,231,0.15), rgba(181,122,255,0.15) 100%);
				.txt{
					background: linear-gradient(115deg,#4a97e7, #b57aff 100%);
					font-weight: 500;
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
				}
			}
		}
		.child{
			width: 100%;
			display: flex;
			flex-direction: column;
			align-items: center;
		}
	}
	&-submit {
		width: 750rpx;
		height: 80px;
		background-color: #fff;
		display: flex;
		flex-direction: column;
		align-items: center;
		.btn{
			margin-top: 16rpx;
			width: 686rpx;
			height: 88rpx;
			border-radius: 44rpx;
			background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
			line-height: 88rpx;
			text-align: center;
			font-size: 28rpx;
			font-weight: 500;
			color: #fff;
		}
	}
}
</style>
