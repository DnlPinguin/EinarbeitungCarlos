import Vue from 'vue'
import './vueBootstrap.js'
// import GifWidget from './views/GifWidget.vue'

import StopwatchTimer from './views/StopwatchTimer.vue'

document.addEventListener('DOMContentLoaded', () => {
	OCA.Dashboard.register('doorman-vue-widget', (el, { widget }) => {
		const View = Vue.extend(StopwatchTimer)
		new View({
			propsData: { title: widget.title },
		}).$mount(el)
	})
})
