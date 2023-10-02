import {
	translate as t,
	// translatePlural as n,
} from '@nextcloud/l10n'
import { loadState } from '@nextcloud/initial-state'

/**
 *
 * @param el
 */
function renderWidget(el) {
	const gifItems = loadState('doorman', 'dashboard-widget-items')

	const paragraph = document.createElement('p')
	paragraph.textContent = t('doorman', 'You can define the frontend part of a widget with plain Javascript.')
	el.append(paragraph)

	const paragraph2 = document.createElement('p')
	paragraph2.textContent = t('doorman', 'Here is the list of files in your gif folder:')
	el.append(paragraph2)

	const list = document.createElement('ul')
	list.classList.add('widget-list')
	gifItems.forEach(item => {
		const li = document.createElement('li')
		li.textContent = item.title
		list.append(li)
	})
	el.append(list)
}

document.addEventListener('DOMContentLoaded', () => {
	OCA.Dashboard.register('doorman-simple-widget', (el, { widget }) => {
		renderWidget(el)
	})
})
