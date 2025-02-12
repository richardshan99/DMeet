<template>
	<view class="view">
		<swiper next-margin="36rpx" previous-margin="36rpx" class="swiper" :style="`height:100%;`" :autoplay="autoplay" :interval="interval" :duration="duration"
			:current="swiperIndex" :circular="loop" @change="swiperChangeFun" @animationfinish="animationfinishFun"
			disable-programmatic-animation>
			<swiper-item :style="`height:100%;`" v-for="(item,index) in listData" :key="index">
				<slot :item="item" :index="index" class="swiper-img"></slot>
			</swiper-item>
		</swiper>
		<view class="disabled" v-if="disableTouch"></view>
	</view>
</template>

<script>
	export default {
		props: {
			// 轮播的整体高度
			height: {
				type: String,
				default: "calc(100vh - var(--window-top))"
			},
			// 轮播的列表数据
			length: {
				type: Number,
				default: 0
			},
		},
		data() {
			return {
				// 轮播的列表数据
				listData: [],
				// 是否自动轮播，用来实现自动切换到下一题的效果
				autoplay: false,
				// 自动切换时间间隔，用来实现自动切换到下一题的效果
				interval: 100,
				// 过渡动画的时间，用来取消切换时的动画效果
				duration: 300,
				// 是否无限循环
				loop: false,
				// 轮播当前的下标
				swiperIndex: "",
				// 禁止用户操作
				disableTouch: false,
				// 轮播的change事件监听开关，手动改变轮播下标时需要关闭监听
				swiperChangeBr: true,
				// 当前数据的下标（下标从0开始)
				activeIndex: 0,
			};
		},
		created() {

		},
		methods: {
			// 下一个
			nextFun() {
				if (this.activeIndex < this.length - 1) {
					this.autoplay = true;
					setTimeout(() => {
						this.autoplay = false;
					}, 150)
				}
			},
			// 要获取的数据的开始下标
			getDataIndexFun(activeI) {
				return new Promise((resolve, reject) => {
					this.$nextTick(() => {
						// 根据要跳转的位置计算数据的位置
						let dataI = activeI - 2;
						// 如果小于3，都从第一条开始获取
						if (activeI < 2) {
							dataI = 0;
						} else if (activeI > this.length - 3) {
							// 如果是最后两条数据，取倒数的五条数据
							dataI = this.length - 5;
						}
						resolve(dataI);
					})
				})
			},
			// 跳转到指定位置
			linkFun(activeI, listData) {
				return new Promise((resolve, reject) => {
					this.$nextTick(() => {
						// 关闭轮播监听
						this.swiperChangeBr = false;
						// 设置过渡动画的时间
						this.duration = 0;
						// 根据要跳转的位置计算数据的位置
						let dataI = activeI - 2;
						// 轮播下标
						let swiperI = 2;
						// 如果小于3，都从第一条开始获取
						if (activeI < 2) {
							dataI = 0;
							// 更改轮播下标
							swiperI = activeI;
						} else if (activeI > this.length - 3) {
							// 如果是最后两条数据，取倒数的五条数据
							dataI = this.length - 5;
							// 更改轮播下标
							swiperI = activeI - this.length + 5;
						}
						// 更新数据
						this.listData = listData;
						// 设置轮播下标
						this.swiperIndex = swiperI;
						// 设置当前题目的位置
						this.activeIndex = activeI;
						// 延迟一下
						setTimeout(() => {
							// 重新设置过渡动画的时间
							this.duration = 300;
							this.swiperChangeBr = true;
						}, 100)

						resolve();
					})
				})
			},
			// 轮播下标改变时触发
			swiperChangeFun(e) {
				// 轮播下标改变时是否处理
				if (this.swiperChangeBr) {
					// 之前的下标
					let lastI = this.swiperIndex;
					// 最新的下标
					let newI = e.detail.current;
					// 更新轮播的下标
					this.swiperIndex = newI;
					// 获取当前数据的下标
					let index = this.activeIndex;
					// 要获取的数据的下标
					let getIndex = "";
					// 要设置的数据的下标
					let setIndex = "";
					// 是否需要获取新的数据
					let get = true;
					if (newI - lastI == -4 || newI - lastI == 1) {
						// 下一个
						index += 1;
						getIndex = index + 2;
						if (getIndex > this.length - 1) {
							getIndex = "";
							get = false;
						}
						setIndex = newI + 2;
					} else {
						// 上一个
						index -= 1;
						getIndex = index - 2;
						if (getIndex < 0) {
							getIndex = "";
							get = false;
						}
						setIndex = newI - 2;
					}
					if (index <= 0 || index >= this.length - 1) {
						// 轮播下标改变时不处理
						this.swiperChangeBr = false;
						// 如果是第一个或者最后一个，禁止用户操作，等到动画执行完成再允许
						this.disableTouch = true;
					} else if (!this.loop) {
						// 如果是中间的数据，并且轮播被禁止循环了，就禁止用户操作，等动画结束开启循环
						this.disableTouch = true;
					}
					// 如果要设置的数据的下标大于5
					if (setIndex >= 5) {
						setIndex = setIndex - 5;
					} else if (setIndex < 0) {
						setIndex = 5 + setIndex;
					}
					// 更新当前数据的下标
					this.activeIndex = index;
					this.$emit("change", {
						get,
						index,
						getIndex: getIndex,
					}, (data) => {
						this.listData[setIndex] = data;
					})
				}
			},
			// 轮播动画结束时触发
			animationfinishFun(e) {
				// 当前数据的下标为0，代表第一条数据
				if (this.activeIndex <= 0) {
					// 如果是第一个数据，禁止循环
					this.loop = false;
					// 如果轮播不是第一个
					if (this.swiperIndex == 0) {
						// 允许用户操作
						this.disableTouch = false;
						// 轮播下标改变时继续处理
						this.swiperChangeBr = true;
					} else {
						// 将过渡动画的时间设置成0
						this.duration = 0;
						// 整理数据
						let listData = this.listData;
						let list = [];
						for (var i = 0; i <= 4; i++) {
							let index = this.swiperIndex + i;
							if (index >= 5) {
								index -= 5;
							}
							list.push(listData[index]);
						}
						// 先将第一条数据更新过来
						this.listData[0] = list[0];
						// 设置轮播的下标，因为第一条数据更新过并且过渡时间设置成了0，逻辑上讲不会有闪动
						this.swiperIndex = 0;
						// 延迟一下
						setTimeout(() => {
							// 重新设置过渡动画的时间
							this.duration = 300;
							// 更新剩余数据
							this.listData = list;
							// 允许用户操作
							this.disableTouch = false;
							// 轮播下标改变时继续处理
							this.swiperChangeBr = true;
						}, 400)
					}
				} else if (this.activeIndex >= this.length - 1) {
					// 如果是最后一个，禁止循环
					this.loop = false;
					// 如果轮播不是最后一个
					if (this.swiperIndex == 4) {
						// 允许用户操作
						this.disableTouch = false;
						// 轮播下标改变时继续处理
						this.swiperChangeBr = true;
					} else {
						// 将过渡动画的时间设置成0
						this.duration = 0;
						// 整理数据
						if (this.length <= 5) {
							this.duration = 300;
							this.disableTouch = false;
							// 轮播下标改变时继续处理
							this.swiperChangeBr = true;
							return
						}
						let listData = this.listData;
						let list = [];
						for (var i = 1; i <= 5; i++) {
							let index = this.swiperIndex + i;
							if (index >= 5) {
								index -= 5;
							}
							list.push(listData[index]);
						}
						this.listData[4] = list[4];
						// 设置轮播的下标，因为最后一条数据更新过并且过渡时间设置成了0，逻辑上讲不会有闪动
						this.swiperIndex = 4;
						// 延迟一下
						setTimeout(() => {
							// 重新设置过渡动画的时间
							this.duration = 300;
							// 更新其它数据
							this.listData = list;
							// 允许用户操作
							this.disableTouch = false;
							// 轮播下标改变时继续处理
							this.swiperChangeBr = true;
						}, 400)
					}
				} else {
					// 开启循环
					this.loop = true;
					// 允许用户操作
					this.disableTouch = false;
				}
			}
		},
	}
</script>

<style lang="scss" scoped>
	.view {
		width: 100%;
		height: 100%;
		position: relative;

		.disabled {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			opacity: 0;
		}
	}
</style>