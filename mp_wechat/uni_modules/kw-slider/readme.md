#kw-slider 说明
兼容移动端H5，小程序，app，注意：本插件使用到touch事件，PC端不兼容
## 属性Props
|属性名				|类型			|默认值	|说明							|
|:-:				|:-:			|:-:	|:-:							|
|v-model			|Array/Number	|[1,100]|双向绑定值						|
|range				|Boolean		|true	|是否区间选择					|
|barHeight			|Number/String	|5		|rpx							|
|unit				|String			|-		|数值后显示的单位				|
|backgroundColor	|String			|#D3D3D3|滑动条背景						|
|activeColor		|String			|#3141ff|选择范围颜色					|
|labelColor			|String			|#999	|两端label文字颜色				|
|customBlock		|Boolean		|false	|自定义滑块，需结合插槽block使用	|
|blockColor			|String			|#fff	|滑块颜色						|
|tipBackgroundColor	|String			|#fff	|值背景颜色						|
|tipColor			|String			|#333	|值字体颜色						|
|tipPosition		|String			|top	|值显示位置  top inner bottom	|
|showLabel			|Boolean		|true	|是否显示最大值最小值				|
|min				|Number			|0		|最小值							|
|max				|Number			|100	|最大值							|
|values				|Array			|[0,100]|当前区间值						|
|step				|Number			|1		|步长值							|


## 事件Events

|事件名		|说明			|回调参数	|
|:-:		|:-:			|:-:		|
|@change	|滑块值变化事件	|变化后的值	|
|@changing	|滑块值变化事件	|变化中的值	|


## 插槽Slots

|插槽名		|说明		|
|:-:		|:-:		|
|block		|自定义滑块	|
|maxBlock	|自定义右滑块	|
|minBlock	|自定义左滑块	|