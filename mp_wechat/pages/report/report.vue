<template>
	<view class="main">
		<image class="main-top" :src="app.config.globalProperties.$imgBase+'/xlyl_meet/index/top_back.png'"></image>
		<view class="main-base">
			<uni-nav-bar leftIcon="left" @clickLeft="navBack" :border="false" title="举报" background-color="transparent" :status-bar="true"></uni-nav-bar>
			<view class="publish_area">
				<textarea v-model="dynamicContent" placeholder="简单说明你要举报的内容，可上传图片举证（最多三张）" class="input_area" :maxlength="-1" auto-focus></textarea>
				<view class="upload">
					<view v-for="(item,ind) in checkedImgs" :key="'img'+ind" class="upload-item">
						<image mode="aspectFill" class="img" :src="item.path"></image>
						<image @click="deleteImg(ind)" src="/static/publish/delete.png" class="delete"></image>
					</view>
					<image @click="pickImgs" v-if="checkedImgs.length < 3" src="/static/publish/addPhoto.png" class="upload-item"></image>
					<view v-for="n of spaceNum" class="upload-item"></view>
				</view>
			</view>
			<view @click="submitInfo" class="submit">
				<text>提交</text>
			</view>
		</view>
	</view>
</template>

<script lang="ts" setup>
	import * as qiniuUploader from '@/common/upload/qiniuUploader.ts';
	import { computed, getCurrentInstance, ref } from 'vue'
	import { api } from '@/common/request/index.ts'
	import { onLoad } from '@dcloudio/uni-app'
	const app = getCurrentInstance().appContext.app
	const checkedImgs = ref<Array<any>>([])
	const dynamicContent = ref(null)
	
	let blogId = null
	
	onLoad((options) => {
		blogId = options.id
	})
	
	const navBack = () => {
		uni.navigateBack()
	}
	
	const spaceNum = computed(() => {
		if (checkedImgs.value.length >= 2) {
			return 1
		} else {
			return 3 - checkedImgs.value.length
		}
	})
	
	const deleteImg = (ind:number) => {
		checkedImgs.value.splice(ind,1)
	}
	
	const pickImgs = () => {
		if (checkedImgs.value.length>=3) {
			uni.showToast({
				icon: 'none',
				title: "选择图片已达上限"
			})
			return;
		}
		uni.chooseImage({
			count: (3 - checkedImgs.value.length),
			success: (res) => {
				console.error(res)
				checkedImgs.value = checkedImgs.value.concat(res.tempFiles)
			}
		})
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
	const submitInfo = async () => {
		if (dynamicContent.value == null || dynamicContent.value.length <= 0) {
			uni.showToast({
				icon: 'none',
				title:'请输入发布内容'
			})
			return;
		}
		uni.showLoading({
			mask: true,
			title:'发布中'
		})
		try {
			const vres:any = await api.post('common/qiniu')
			qiniuUploader.init({
				domain: vres.data.cdnurl,
				region: 'ECN',
				regionUrl: vres.data.uploadurl,
				uptoken: vres.data.multipart.qiniutoken,
			})
			let tasks = []
			for(let img of checkedImgs.value) {
				tasks.push(uplaodFile(img.path)) 
			}
			Promise.all(tasks).then(imgList => {
				api.post('blog/report',{
					blog_id: blogId,
					content: dynamicContent.value,
					images: imgList
				}).then((result:any) => {
					if (result.code == 1) {
						uni.showToast({
							icon:'none',
							title:'发布成功'
						})
						uni.hideLoading()
						uni.navigateBack()
					}
				}).catch(e => {
					uni.hideLoading()
				}) 
			}).catch(e => {
				uni.hideLoading()
			})
		} catch(e) {
			uni.hideLoading()
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
		align-items: center;
		.publish_area{
			margin-top: 24rpx;
			width: 686rpx;
			background-color: #ffffff;
			border-radius: 24rpx;
			display: flex;
			flex-direction: column;
			align-items: center;
			.input_area{
				margin-top: 32rpx;
				width: 622rpx;
				height: 352rpx;
				font-size: 28rpx;
				color: #1D2129;
			}
			.upload{
				width: 622rpx;
				display: flex;
				flex-direction: row;
				margin-top: 14rpx;
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
		}
		.submit{
			width: 686rpx;
			height: 88rpx;
			background: linear-gradient(96deg,#4a97e7, #b57aff 100%);
			border-radius: 96rpx;
			line-height: 88rpx;
			text-align: center;
			font-size: 32rpx;
			font-weight: 500;
			color: #fff;
			margin-top: 64rpx;
		}
	}
}	
</style>
