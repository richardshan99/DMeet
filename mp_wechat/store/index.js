import { createStore } from 'vuex'
import user from '@/store/modules/user.js'
export default createStore({
	modules: {
		user
	}
})