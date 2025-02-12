<template>
	<view
		class="kw-slider"
		:style="kwSliderStyle"
		@touchmove.prevent.stop
	>
		<view
			v-if="showLabel"
			class="kw-slider-label"
		>{{ min }}{{ unit }}</view>
		<view
			class="kw-slider-bar"
			:style="bgBarStyle"
		>
			<view
				class="kai-slider-bar-progress"
				:style="progressBarStyle"
			>
			</view>
			<template v-if="range">
				<view
					class="kw-slider-block"
					:class="[{'block-box': !customBlock}]"
					:style="minBlockStyle"
					data-index="0"
					@touchstart="_onBlockTouchStart"
					@touchmove.prevent.stop="_onBlockTouchMove"
					@touchend="_onBlockTouchEnd"
				>
					<view
						v-if="showTip"
						:style="tipStyle"
						class="kw-slider-tips"
					>{{ bindValue[0] }}{{ unit }}</view>
					<slot name="minBlock"></slot>
				</view>
				<view
					class="kw-slider-block"
					:class="[{'block-box': !customBlock}]"
					:style="maxBlockStyle"
					data-index="1"
					@touchstart="_onBlockTouchStart"
					@touchmove.prevent.stop="_onBlockTouchMove"
					@touchend="_onBlockTouchEnd"
				>
					<view
						v-if="showTip"
						:style="tipStyle"
						class="kw-slider-tips"
					>{{ bindValue[1] }}{{ unit }}</view>
					<slot name="maxBlock"></slot>
				</view>
			</template>
			<template v-else>
				<view
					class="kw-slider-block"
					:class="[{'block-box': !customBlock}]"
					:style="blockStyle"
					data-index="1"
					@touchstart="_onBlockTouchStart"
					@touchmove.prevent.stop="_onBlockTouchMove"
					@touchend="_onBlockTouchEnd"
				>
					<view
						v-if="showTip"
						:style="tipStyle"
						class="kw-slider-tips"
					>{{ bindValue }}{{ unit }}</view>
					<slot name="block"></slot>
				</view>
			</template>
		</view>
		<view
			v-if="showLabel"
			class="kw-slider-label"
		>{{ max }}{{ unit }}</view>


	</view>
</template>
<script>
	import useValueModel from '../../hooks/useValueModel'
	const min = 0
	const max = 100
	const step = 1
	const {
		mixin
	} = useValueModel([Array, Number], [0, 100])
	export default {
		mixins: [mixin],
		props: {
			//区间进度条高度
			barHeight: {
				type: [Number, String],
				default: 5
			},
			// 单位
			unit: {
				type: String,
				default: ''
			},
			// 是否区间选择
			range: {
				type: Boolean,
				default: true
			},
			// 自定义block
			customBlock: {
				type: Boolean,
				default: false
			},
			//背景条颜色
			backgroundColor: {
				type: String,
				default: '#D3D3D3'
			},
			//已选择的颜色
			activeColor: {
				type: String,
				default: '#3141ff'
			},
			//label颜色
			labelColor: {
				type: String,
				default: '#999'
			},
			//滑块颜色
			blockColor: {
				type: String,
				default: '#fff'
			},
			//值颜色
			tipColor: {
				type: String,
				default: '#333'
			},
			//值背景颜色
			tipBackgroundColor: {
				type: String,
				default: '#fff'
			},
			//值显示位置  top inner bottom
			tipPosition: {
				type: String,
				default: 'top'
			},
			// 是否显示最大值最小值
			showLabel: {
				type: Boolean,
				default: true
			},
			// 是否显示当前值
			showTip: {
				type: Boolean,
				default: true
			},
			//最小值
			min: {
				type: Number,
				default: min
			},
			//最大值
			max: {
				type: Number,
				default: max
			},
			//步长值
			step: {
				type: Number,
				default: step
			}
		},
		data() {
			return {
				initWidth: 0,
				startX: 0,
			};
		},
		created() {},
		computed: {
			rate() {
				return (this.initWidth || 1) / (this.max - this.min)
			},
			minBlockStyle() {
				const styArr = [
					this.initWidth ? `left: ${(this.bindValue[0] - this.min) * this.rate}px;` : 'left: 0;'
				]
				return styArr.join('')
			},
			maxBlockStyle() {
				const styArr = [
					this.initWidth ? `left: ${(this.bindValue[1] - this.min) * this.rate}px;` : 'left: 100%;'
				]
				return styArr.join('')
			},
			blockStyle() {
				const styArr = [
					this.initWidth ? `left: ${(this.bindValue - this.min) * this.rate}px;` : 'left: 100%;'
				]
				return styArr.join('')
			},
			bgBarStyle() {
				const styArr = [
					`height: ${this.barHeight}rpx;`
				]
				return styArr.join('')
			},
			progressBarStyle() {
				const styArr = [
					`left: ${ this.range ? (Math.min(...this.bindValue) - this.min) * this.rate : 0}px;`,
					this.initWidth ?
					`width: ${(this.range ? (Math.max(...this.bindValue) - Math.min(...this.bindValue)) : this.bindValue - this.min) * this.rate }px;` :
					'width: 100%;'
				]
				return styArr.join('')
			},
			tipStyle() {
				let position = 'top: 50%;'
				switch (this.tipPosition) {
					case 'top':
						position = 'top: -44rpx;'
						break
					case 'bottom':
						position = 'bottom: -44rpx;'
						break
				}
				const styArr = [
					position,
					this.tipPosition === 'inner' ? 'transform: translateY(-50%) translateX(-50%);' : ''
				]
				return styArr.join('')
			},
			kwSliderStyle() {
				const styArr = [
					this.tipPosition === 'inner' ? '' : `padding-${this.tipPosition}: 0;`,
					`--kw-slider-tip-arrow-top-show: ${this.tipPosition === 'bottom' ? 'block:' : 'none'};`,
					`--kw-slider-tip-arrow-bottom-show: ${this.tipPosition === 'top' ? 'block:' : 'none'};`,
					`--kw-slider-bar-height: ${this.barHeight}rpx;`,
					`--kw-slider-label-color: ${this.labelColor};`,
					`--kw-slider-block-color: ${this.blockColor};`,
					`--kw-slider-tip-background-color: ${this.tipPosition === 'inner' ? 'tranparent' : this.tipBackgroundColor};`,
					`--kw-slider-tip-color: ${this.tipColor};`,
					`--kw-slider-bar-background-color: ${this.backgroundColor};`,
					`--kw-slider-bar-active-color: ${this.activeColor};`
				]
				return styArr.join('')
			}
		},
		mounted() {
			this.getInitWidth()
		},
		methods: {
			_onBlockTouchStart: function(e) {
				// 避免某些时候渲染后宽度为0而出错
				if (!this.initWidth) {
					this.getInitWidth()
				}
				let changedTouche = e.changedTouches[0]
				this.startX = changedTouche.clientX
			},
			_onBlockTouchMove: function(e) {
				let changedTouche = e.changedTouches[0]
				const index = e.currentTarget.dataset.index
				const deltaX = changedTouche.clientX - this.startX || 0
				let deltaValue = parseInt(deltaX / this.rate)
				if (deltaValue >= this.step || deltaValue <= -this.step) {
					deltaValue = parseInt(deltaValue / this.step) * this.step
					this.updateValues(deltaValue, index)
					this.startX = changedTouche.clientX
				}
			},
			_onBlockTouchEnd: function(e) {
				this.startX = 0
				if (this.range) {
					if ((this.bindValue[1] <= this.bindValue[0] || this.bindValue[0] >= this.bindValue[1])) {
						this.bindValue.sort()
					}
				}

				this.updateValue()
			},
			updateValues(value, index) {
				if (this.range) {
					this.bindValue[index] += value
					if (this.bindValue[index] >= this.max) {
						this.bindValue[index] = this.max
					}
					if (this.bindValue[index] <= this.min) {
						this.bindValue[index] = this.min
					}
				} else {
					this.bindValue += value
					if (this.bindValue >= this.max) {
						this.bindValue = this.max
					}
					if (this.bindValue <= this.min) {
						this.bindValue = this.min
					}
				}
				this.updateValue() // 新加，及时显示
				this.$emit('changing', this.bindValue)
			},
			getInitWidth() {
				uni.createSelectorQuery()
					.in(this)
					.select('.kw-slider-bar')
					.fields({
						size: true
					}, data => {
						if (data.width) {
							this.initWidth = data.width
						}
					})
					.exec()
			}
		}
	};
</script>

<style
	lang="scss"
	scoped
>
	$kw-slider-height: 50rpx !default;
	$kw-slider-bar-height: var(--kw-slider-bar-height, 4rpx) !default;
	$kw-color-dark-grey: var(--kw-slider-bar-background-color, #D3D3D3) !default;
	$kw-color-active: var(--kw-slider-bar-active-color, #3141ff) !default;
	$kw-slider-tip-background-color: var(--kw-slider-tip-background-color, #fff) !default;
	$kw-slider-tip-color: var(--kw-slider-tip-color, #333) !default;
	$kw-slider-block-color: var(--kw-slider-block-color, #fff) !default;
	$kw-label-color: var(--kw-slider-label-color, #999) !default;

	.kw-slider {
		width: 100%;
		height: $kw-slider-height;
		display: flex;
		align-items: center;
		box-sizing: border-box;
	}

	.kw-slider-bar {
		width: 100%;
		height: $kw-slider-bar-height;
		position: relative;
		/* margin: 0 30rpx; */
		background-color: $kw-color-dark-grey;
	}

	.kai-slider-bar-progress {
		position: absolute;
		height: 100%;
		background-color: $kw-color-active;
	}

	.kw-slider-label {
		text-align: center;
		color: $kw-label-color;
		font-size: 24rpx;
	}

	.kw-slider-block {
		position: absolute;
		left: 0;
		top: 50%;
		transform: translateY(-50%) translateX(-50%);

		&.block-box {
			width: 40rpx;
			height: 40rpx;
			border-radius: 50%;
			background-color: $kw-slider-block-color;

		}

		.kw-slider-tips {
			position: absolute;
			/* top: -40rpx; */
			background-color: $kw-slider-tip-background-color;
			color: $kw-slider-tip-color;
			padding: 5rpx;
			left: 50%;
			transform: translateX(-50%);
			font-size: 22rpx;
			line-height: 1;
			border-radius: 10rpx;

			&::before {
				content: '';
				position: absolute;
				width: 0;
				height: 0;
				top: -4px;
				display: var(--kw-slider-tip-arrow-top-show);
				border-bottom: 5px solid $kw-slider-tip-background-color;
				border-top: 0;
				border-right: 6px solid transparent;
				border-left: 6px solid transparent;
				left: 50%;
				transform: translateX(-50%);
			}

			&::after {
				content: '';
				position: absolute;
				width: 0;
				height: 0;
				bottom: -4px;
				display: var(--kw-slider-tip-arrow-bottom-show);
				border-top: 5px solid $kw-slider-tip-background-color;
				border-bottom: 0;
				border-right: 6px solid transparent;
				border-left: 6px solid transparent;
				left: 50%;
				transform: translateX(-50%);
			}
		}
	}
</style>