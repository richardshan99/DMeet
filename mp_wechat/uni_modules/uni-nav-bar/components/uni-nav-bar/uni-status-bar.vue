<template>
	<view :style="{ height: statusBarHeight }" class="uni-status-bar">
		<slot />
	</view>
</template>

<script>
/*	export default {
		name: 'UniStatusBar',
		data() {
			return {
				statusBarHeight: uni.getSystemInfoSync().statusBarHeight + 'px'
			}
		}
	}*/
	export default {
		name: 'UniStatusBar',
		data() {
			return {				
				statusBarHeight: ''// 初始化 statusBarHeight 为一个默认值
			};
		},
		created() {
			uni.getSystemInfo({
				success: (res) => {
					this.statusBarHeight = res.statusBarHeight + 'px';
				},
				fail: (err) => {
					console.error('获取系统信息失败:', err);
				}
			});
		}
	};
</script>

<style lang="scss" >
	.uni-status-bar {
		// width: 750rpx;
		height: 20px;
		// height: var(--status-bar-height);
	}
</style>
