<!--
 虚拟列表
 @Author: mosowe
 @Date:2023-03-07 09:14:40
-->

<template>
  <view class="mosowe-invented-list">
    <scroll-view
      class="container"
      scroll-y
      :style="{ height: boxHeight + 'px' }"
	  @scrolltolower="scrollEnd"
      @scroll="handleScroll">
      <div
        class="mosowe-invented-wrap"
        :style="{ height: itemHeight * list.length + 'px' }">
        <div
          class="mosowe-invented-content"
          :style="{ transform: 'translateY(' + offsetY + 'px)' }">
          <view
            class="mosowe-invented-item"
            v-for="(item, index) in showList"
            :key="index">
            <slot :item="item"></slot>
          </view>
        </div>
      </div>
    </scroll-view>
  </view>
</template>

<script>
export default {
  props: {
    list: {
      type: Array,
      default: () => []
    },
    cacheNum: {
      // 前后缓存数目
      type: Number,
      default: 50
    }
  },
  data() {
    return {
      showList: [], // 展示的数据列表
      boxHeight: 0, // 组件可视区高度
      itemHeight: 0, // 每条数据高度，计算第一条数据的高度，以第一条数据高度为主
      offsetY: 0
    };
  },
  computed: {
    pageNum() {
      if (this.boxHeight && this.itemHeight) {
        return Math.ceil(this.boxHeight / this.itemHeight) + this.cacheNum;
      } else {
        return this.cacheNum;
      }
    }
  },
  watch: {
    pageNum() {
      this.setShowList(0);
    },
    list: {
      handler() {
        this.init();
      },
      deep: true,
      immediate: true
    }
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      this.$nextTick(() => {
        this.setShowList(0);
        let t = setTimeout(() => {
          clearTimeout(t);
          t = null;
          const query = uni.createSelectorQuery().in(this);
          query
            .select('.mosowe-invented-list')
            .boundingClientRect((res) => {
              this.boxHeight = Math.floor(res?.height) || 0;
            })
            .select('.mosowe-invented-item')
            .boundingClientRect((res) => {
              this.itemHeight = Math.floor(res?.height) || 0;
            })
            .exec();
        }, 100);
      });
    },
    handleScroll(e) {
      const scrollTop = Math.floor(e.detail.scrollTop);

      this.offsetY = scrollTop - (scrollTop % this.itemHeight);

      let startIndex = Math.floor(scrollTop / this.itemHeight);

      if (startIndex > this.cacheNum) {
        this.offsetY -= this.cacheNum * this.itemHeight;
        startIndex = startIndex - this.cacheNum;
      }

      this.setShowList(startIndex);

      this.$emit('scroll', scrollTop);
    },
	scrollEnd (e) {
		this.$emit('scrollEnd', e);
	},
    setShowList(startIndex) {
      this.showList = this.list.slice(startIndex, startIndex + this.pageNum);
    }
  }
};
</script>

<style lang="scss" scoped>
.mosowe-invented-list {
  overflow: hidden;
  height: 100%;
  width: 100%;
  .container {
    width: 100%;
    height: 100%;
  }
}
</style>
