<template>
    <!-- 最外层容器，使用 drag-img 类名 -->
    <view class="drag-img">
        <!-- 仅当 viewWidth 存在时渲染内部内容 -->
        <template v-if="viewWidth">
            <!-- 可移动区域，设置高度为计算得到的 areaHeight -->
            <!-- 绑定鼠标进入和离开事件 -->
            <movable-area class="drag-img-area" :style="{ height: areaHeight }" 
                          @mouseenter="mouseenter" @mouseleave="mouseleave">
                <!-- 遍历 imageList 数组，为每个图片项创建一个可移动视图 -->
                <movable-view v-for="(item, index) in imageList" :key="item.id" 
                              class="drag-img-area-view" direction="all" 
                              :y="item.y" :x="item.x" 
                              :damping="40" 
                              :disabled="item.disable" 
                              @change="onChange($event, item)"
                              @touchstart="touchstart(item)" 
                              @mousedown="touchstart(item)" 
                              @touchend="touchend(item)"
                              @mouseup="touchend(item)" 
                              :style="{width: viewWidth + 'px', height: viewWidth + 'px', 
                                       'z-index': item.zIndex, 
                                       opacity: item.opacity }">
                    <!-- 图片显示区域，设置宽度、高度、圆角和缩放比例 -->
                    <view class="drag-img-area-view-image" :style="{
                        width: childWidth, height: childWidth, 
                        borderRadius: borderRadius + 'rpx',
                        transform: 'scale(' + item.scale + ')' }">
                        <!-- 显示图片，使用 aspectFill 模式 -->
                        <image class="drag-img-area-view-image-img" :src="item.src" mode="aspectFill"></image>
                        <!-- 删除按钮区域，绑定点击事件 -->
                        <view class="drag-img-area-view-image-del" @click="delImages(item, index)"
                              @touchstart.stop="delImageMp(item, index)"
                              @touchend.stop="nothing()" @mousedown.stop="nothing()" @mouseup.stop="nothing()">
                            <image class="del-img" src="./icon_del.png"></image>
                        </view>
                    </view>
                </movable-view>
                <!-- 添加图片按钮，仅当图片数量小于限制数量时显示 -->
                <view class="drag-img-area-view-add" v-if="imageList.length < number"
                      :style="{ top: add.y, left: add.x, width: viewWidth + 'px', height: viewWidth + 'px' }"
                      @click="addImages">
                    <view class="add-view"
                          :style="{ width: childWidth, height: childWidth }">
                        <image :style="{ width: childWidth, height: childWidth }"
                               src="./icon_add.png">
                        </image>
                    </view>
                </view>
            </movable-area>
        </template>
    </view>
</template>


<script>
	import { mapState } from 'vuex';
	export default {
		emits: ['input', 'update:modelValue'],
		props: {
			showAudit: {
				type: Boolean,
				default: false
			},
			// 排序图片
			value: {
				type: Array,
				default: function() {
					return []
				}
			},
			// 排序图片
			modelValue: {
				type: Array,
				default: function() {
					return []
				}
			},
			// 从 list 元素对象中读取的键名
			keyName: {
				type: String,
				default: null
			},
			// 选择图片数量限制
			number: {
				type: Number,
				default: 9
			},
			// 图片父容器宽度（实际显示的图片宽度为 imageWidth / 1.1 ），单位 rpx
			// imageWidth > 0 则 cols 无效
			imageWidth: {
				type: Number,
				default: 0
			},
			// 图片列数
			cols: {
				type: Number,
				default: 3
			},
			// 图片圆角，单位 rpx
			borderRadius: {
				type: Number,
				default: 25
			},
			// 图片周围空白填充，单位 rpx
			padding: {
				type: Number,
				default: 10
			},
			// 拖动图片时放大倍数 [0, ∞)
			scale: {
				type: Number,
				default: 1.1
			},
			// 拖动图片时不透明度
			opacity: {
				type: Number,
				default: 0.7
			},
			// 自定义添加
			addImage: {
				type: Function,
				default: null
			},
			// 删除确认
			delImage: {
				type: Function,
				default: null
			}
		},
		data() {
			return {
				imageList: [],
				width: 0,
				add: {
					x: 0,
					y: 0
				},
				colsValue: 0,
				viewWidth: 0,
				tempItem: null,
				timer: null,
				changeStatus: true,
				preStatus: true,
				first: true,
			}
		},
		computed: {
			areaHeight() {
				let height = ''
				// return '355px'
				if (this.imageList.length < this.number) {
					height = (Math.ceil((this.imageList.length + 1) / this.colsValue) * this.viewWidth).toFixed() + 'px'
				} else {
					height = (Math.ceil(this.imageList.length / this.colsValue) * this.viewWidth).toFixed() + 'px'
				}
//				console.log('areaHeight', height)
				return height
			},
			childWidth() {
				return this.viewWidth - this.rpx2px(this.padding) + 'px'
			},
			...mapState({
			  userInfo: state => state.user.userInfo
			})
		},
		watch: {
			value: {
				handler(n) {
					if (!this.first && this.changeStatus) {
						let flag = false
						for (let i = 0; i < n.length; i++) {
							if (flag) {
								this.addProperties(this.getSrc(n[i]))
								continue
							}
							if (this.imageList.length === i || this.imageList[i].src !== this.getSrc(n[i])) {
								flag = true
								this.imageList.splice(i)
								this.addProperties(this.getSrc(n[i]))
							}
						}
						this.$emit('uploadFile', n);
					}
				},
				deep: true
			},
			modelValue: {
				handler(n) {
					if (!this.first && this.changeStatus) {
//						console.log('watch', n)
						let flag = false
						for (let i = 0; i < n.length; i++) {
							if (flag) {
								this.addProperties(this.getSrc(n[i]))
								continue
							}
							if (this.imageList.length === i || this.imageList[i].src !== this.getSrc(n[i])) {
								flag = true
								this.imageList.splice(i)
								this.addProperties(this.getSrc(n[i]))
							}
						}
					}
				},
				deep: true
			},
		},
		created() {
			uni.getSystemInfo({
				success: (res) => { this.width = res.windowWidth; },
				fail: (err) => { console.error('getSystemInfo获取系统信息失败:', err); }
        	});
		},
		
		mounted() {
			 // 创建一个选择器查询对象，用于查询当前组件内的元素信息
			const query = uni.createSelectorQuery().in(this)
			// 选择类名为 'drag-img' 的元素，并获取其边界矩形信息
			query.select('.drag-img').boundingClientRect(data => {
				this.colsValue = this.cols  // 将组件 props 中的 cols 属性值赋给 colsValue，用于后续计算图片列数
				this.viewWidth = data.width / this.cols
				if (this.imageWidth > 0) {
					this.viewWidth = this.rpx2px(this.imageWidth)
					this.colsValue = Math.floor(data.width / this.viewWidth)
				}
				let list = this.value 	// 获取组件的 value 属性值，该值通常是一个包含图片信息的数组
				// #ifdef VUE3
				list = this.modelValue
				// #endif
				for (let item of list) {  // 遍历图片信息数组
				// 调用 getSrc 方法从 item 中获取图片的源地址，并将其作为参数传递给 addProperties 方法
					// addProperties 方法会将图片信息添加到 imageList 数组中，并进行相关的初始化设置
					this.addProperties(this.getSrc(item))
				}
				this.first = false 	// 将 first 标志设置为 false，表示初始化完成
			})
			query.exec() 	// 执行查询操作，触发上述回调函数
		},
		
		methods: {
			getSrc(item) {
				if (this.keyName !== null) {
					return item[this.keyName]
				}
				return item
			},
			onChange(e, item) {
				if (!item) return
				item.oldX = e.detail.x
				item.oldY = e.detail.y
				if (e.detail.source === 'touch') {
					if (item.moveEnd) {
						item.offset = Math.sqrt(Math.pow(item.oldX - item.absX * this.viewWidth, 2) + Math.pow(item.oldY -
							item
							.absY * this.viewWidth, 2))
					}
					let x = Math.floor((e.detail.x + this.viewWidth / 2) / this.viewWidth)
					if (x >= this.colsValue) return
					let y = Math.floor((e.detail.y + this.viewWidth / 2) / this.viewWidth)
					let index = this.colsValue * y + x
					if (item.index != index && index < this.imageList.length) {
						this.changeStatus = false
						for (let obj of this.imageList) {
							if (item.index > index && obj.index >= index && obj.index < item.index) {
								this.change(obj, 1)
							} else if (item.index < index && obj.index <= index && obj.index > item.index) {
								this.change(obj, -1)
							} else if (obj.id != item.id) {
								obj.offset = 0
								obj.x = obj.oldX
								obj.y = obj.oldY
								setTimeout(() => {
									this.$nextTick(() => {
										obj.x = obj.absX * this.viewWidth
										obj.y = obj.absY * this.viewWidth
									})
								}, 0)
							}
						}
						item.index = index
						item.absX = x
						item.absY = y
						if (!item.moveEnd) {
							setTimeout(() => {
								this.$nextTick(() => {
									item.x = item.absX * this.viewWidth
									item.y = item.absY * this.viewWidth
								})
							}, 0)
						}
						// console.log('bbb', JSON.parse(JSON.stringify(item)));
						this.sortList()
					}
				}
			},
			change(obj, i) {
				obj.index += i
				obj.offset = 0
				obj.x = obj.oldX
				obj.y = obj.oldY
				obj.absX = obj.index % this.colsValue
				obj.absY = Math.floor(obj.index / this.colsValue)
				setTimeout(() => {
					this.$nextTick(() => {
						obj.x = obj.absX * this.viewWidth
						obj.y = obj.absY * this.viewWidth
					})
				}, 0)
			},
			touchstart(item) {
				this.imageList.forEach(v => {
					v.zIndex = v.index + 9
				})
				item.zIndex = 99
				item.moveEnd = true
				this.tempItem = item
				this.timer = setTimeout(() => {
					item.scale = this.scale
					item.opacity = this.opacity
					clearTimeout(this.timer)
					this.timer = null
				}, 200)
			},
			touchend(item) {
				this.previewImage(item)
				item.scale = 1
				item.opacity = 1
				item.x = item.oldX
				item.y = item.oldY
				item.offset = 0
				item.moveEnd = false
				setTimeout(() => {
					this.$nextTick(() => {
						item.x = item.absX * this.viewWidth
						item.y = item.absY * this.viewWidth
						this.tempItem = null
						this.changeStatus = true
					})
					// console.log('ccc', JSON.parse(JSON.stringify(item)));
				}, 0)
				// console.log('ddd', JSON.parse(JSON.stringify(item)));
			},
			previewImage(item) {
				if (this.timer && this.preStatus && this.changeStatus && item.offset < 28.28) {
					clearTimeout(this.timer)
					this.timer = null
					const list = this.value || this.modelValue
					let srcList = list.map(v => this.getSrc(v))
					console.log(list, srcList);
					uni.previewImage({
						urls: srcList,
						current: item.src,
						success: () => {
							this.preStatus = false
							setTimeout(() => {
								this.preStatus = true
							}, 600)
						},
						fail: (e) => {
							console.log(e);
						}
					})
				} else if (this.timer) {
					clearTimeout(this.timer)
					this.timer = null
				}
			},
			mouseenter() {
				//#ifdef H5
				this.imageList.forEach(v => {
					v.disable = false
				})
				//#endif

			},
			mouseleave() {
				//#ifdef H5
				if (this.tempItem) {
					this.imageList.forEach(v => {
						v.disable = true
						v.zIndex = v.index + 9
						v.offset = 0
						v.moveEnd = false
						if (v.id == this.tempItem.id) {
							if (this.timer) {
								clearTimeout(this.timer)
								this.timer = null
							}
							v.scale = 1
							v.opacity = 1
							v.x = v.oldX
							v.y = v.oldY
							this.$nextTick(() => {
								v.x = v.absX * this.viewWidth
								v.y = v.absY * this.viewWidth
								this.tempItem = null
							})
						}
					})
					this.changeStatus = true
				}
				//#endif
			},
			addImages() {
				if (typeof this.addImage === 'function') {
					this.addImage.bind(this.$parent)()
				} else {
					let checkNumber = this.number - this.imageList.length
					uni.chooseImage({
						count: checkNumber,
						sourceType: ['album', 'camera'],
						success: res => {
							let count = checkNumber <= res.tempFilePaths.length ? checkNumber : res
								.tempFilePaths.length
							for (let i = 0; i < count; i++) {
								this.addProperties(res.tempFilePaths[i])
							}
							this.sortList()
						}
					})
				}
			},
			delImages(item, index) {
				if (typeof this.delImage === 'function') {
					this.delImage.bind(this.$parent)(() => {
						this.delImageHandle(item, index)
					})
				} else {
					this.delImageHandle(item, index)
				}
			},
			delImageHandle(item, index) {
				this.imageList.splice(index, 1)
				for (let obj of this.imageList) {
					if (obj.index > item.index) {
						obj.index -= 1
						obj.x = obj.oldX
						obj.y = obj.oldY
						obj.absX = obj.index % this.colsValue
						obj.absY = Math.floor(obj.index / this.colsValue)
						this.$nextTick(() => {
							obj.x = obj.absX * this.viewWidth
							obj.y = obj.absY * this.viewWidth
						})
					}
				}
				this.add.x = (this.imageList.length % this.colsValue) * this.viewWidth + 'px'
				this.add.y = Math.floor(this.imageList.length / this.colsValue) * this.viewWidth + 'px'
				this.sortList()
			},
			delImageMp(item, index) {
				//#ifdef MP
				this.delImages(item, index)
				//#endif
			},
			sortList() {
				console.log('sortList');
				const result = []
				let source = this.value
				// #ifdef VUE3
				source = this.modelValue
				// #endif

				let list = this.imageList.slice()
				list.sort((a, b) => {
					return a.index - b.index
				})
				for (let s of list) {
					let item = source.find(d => this.getSrc(d) == s.src)
					if (item) {
						result.push(item)
					} else {
						if (this.keyName !== null) {
							result.push({
								[this.keyName]: s.src,
								local: true
							})
						} else {
							result.push(s.src)
						}
					}
				}
				this.$emit("input", result);
				this.$emit("update:modelValue", result);
			},
			addProperties(item) {
				console.log(item);
				let absX = this.imageList.length % this.colsValue
				let absY = Math.floor(this.imageList.length / this.colsValue)
				let x = absX * this.viewWidth
				let y = absY * this.viewWidth
				this.imageList.push({
					src: item,
					x,
					y,
					oldX: x,
					oldY: y,
					absX,
					absY,
					scale: 1,
					zIndex: 9,
					opacity: 1,
					index: this.imageList.length,
					id: this.guid(16),
					disable: false,
					offset: 0,
					moveEnd: false
				})
				this.add.x = (this.imageList.length % this.colsValue) * this.viewWidth + 'px'
				this.add.y = Math.floor(this.imageList.length / this.colsValue) * this.viewWidth + 'px'
			},
			nothing() {},
			rpx2px(v) {
				return this.width * v / 750
			},
			guid(len = 32) {
				const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('')
				const uuid = []
				const radix = chars.length
				for (let i = 0; i < len; i++) uuid[i] = chars[0 | Math.random() * radix]
				uuid.shift()
				return `u${uuid.join('')}`
			}
		}
	}
</script>

<style lang="scss" scoped>
	.drag-img {
		.drag-img-area {
			width: 100%;

			.drag-img-area-view {
				display: flex;
				justify-content: center;
				align-items: center;

				.drag-img-area-view-image {
					position: relative;
					overflow: hidden;

					.drag-img-area-view-image-img {
						width: 100%;
						height: 100%;
					}
					.audit_mask{
						width: 140rpx;
						height: 112rpx;
						position: absolute;
						top: 18rpx;
						left: 4rpx;
						z-index: 99;
					}
					.drag-img-area-view-image-del {
						position: absolute;
						top: 12rpx;
						right: 12rpx;
						// padding: 0 0 20rpx 20rpx;
						&>.del-img {
							z-index: 9000;
							width: 36rpx;
							height: 36rpx;
						}
					}
				}
			}

			.drag-img-area-view-add {
				position: absolute;
				display: flex;
				justify-content: center;
				align-items: center;

				&>.add-view {
					display: flex;
					justify-content: center;
					align-items: center;
					// background-color: #eeeeee;
				}
			}
		}
	}
</style>