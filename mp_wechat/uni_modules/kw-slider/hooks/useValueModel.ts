const useValueModel = (type : any, defaultValue : any) => {
	const mixin = {
		// #ifdef VUE2
		// 双向绑定模型
		model: {
			prop: "value",
			event: "update:value",
		},
		// #endif
		props: {
			// #ifdef VUE2
			// 控制显示与否 双向绑定属性
			value: {
				type,
				default: () => defaultValue
			},
			// #endif

			// #ifdef VUE3
			// 控制显示与否 双向绑定属性
			modelValue: {
				type,
				default: () => defaultValue
			},
			// #endif

		},
		data() {
			return {
				bindValue: ''
			}
		},
		methods: {
			updateValue() {
				const value = this.bindValue
				// 正常双向绑定
				// #ifdef VUE2
				this.$emit('update:value', value)
				// #endif
				// #ifdef VUE3
				this.$emit('update:modelValue', value)
				// #endif
				// input兼容小程序
				this.$emit("input", value);
				this.$emit('change', value)
			},
			getInitValue(newVal : any) {
				if (newVal && typeof newVal === 'object') {
					this.bindValue = JSON.parse(JSON.stringify(newVal))
				} else {
					this.bindValue = newVal
				}
			}
		},
		watch: {
			// #ifdef VUE2
			'value': {
				handler(newVal : any) {
					this.getInitValue(newVal)
				},
				deep: true,
				immediate: true
			},
			// #endif
			// #ifdef VUE3
			'modelValue': {
				handler(newVal : any) {
					this.getInitValue(newVal)
				},
				deep: true,
				immediate: true
			},
			// #endif
		}
	}
	return {
		mixin
	}
}

export default useValueModel