import Vue from 'vue'
import './vueBootstrap.js'

import AdminSettings from './views/AdminSettings.vue'

document.addEventListener('DOMContentLoaded', () => {
	OCA.Dashboard.register('doorman-admin-settings', (el, { widget }) => {
		const View = Vue.extend(AdminSettings)
		new View({
			propsData: { title: widget.title },
		}).$mount(el)
	})
})
