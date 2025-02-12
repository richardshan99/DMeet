Component({
  data: {
    selected: 0,
	showTabbar: true,
	list: [{
		pagePath: "/pages/index/index",
		iconPath: "/static/index/index_unchecked.png",
		selectedIconPath: "/static/index/index_checked.png",
		text: "首页"
	}, {
		pagePath: "/pages/invitation/invitation",
		iconPath: "/static/index/invitation_unchecked.png",
		selectedIconPath: "/static/index/invitation_checked.png",
		text: "邀约",
		invationCount: 0
	},{
		iconPath: '/static/index/meet.png',
		text: '召集',
		pagePath: '/pages/meeting/meeting'
	},{
		pagePath: "/pages/activity/activity",
		iconPath: "/static/index/activity_unchecked.png",
		selectedIconPath: "/static/index/activity_checked.png",
		text: "活动"
	}, {
		pagePath: "/pages/mine/mine",
		iconPath: "/static/index/mine_unchecked.png",
		selectedIconPath: "/static/index/mine_checked.png",
		text: "我的",
		count: 0
	}]
  },
  attached() {
  },
  methods: {
    switchTab(e) {
      const data = e.currentTarget.dataset
	  if (data.index == 2) {
		  wx.navigateTo({url: data.path})
		  return;
	  }
/*	  if (data.index == 3) {
		wx.navigateTo("https://dmeetclub.com")
		return;
	}	*/  
      const url = data.path
	  this.setData({
	    selected: data.index,
	  })
      wx.switchTab({url})      
    }
  }
})