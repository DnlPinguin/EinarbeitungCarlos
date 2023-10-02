<template>
	<div id="timer">
		{{ timeInSecond }}
		<div>
			<div class="start" @click="start">
				Start
			</div>
			<div class="pause" @click="pause">
				Pause
			</div>
			<div class="Reset" @click="reset">
				Reset
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'StopwatchTimer',

	components: {},

	props: {},

	data() {
		return {
			isPause: false,
			timeInSecond: 0,
		}
	},

	computed: {},

	watch: {},

	beforeDestroy() {},

	beforeMount() {},

	mounted() {},
	methods: {
		start() {
			if (!this.pause) {
				setInterval(() => {
					// console.log('time')
				}, 1000)
			}
		},
		pause() {},
		reset() {},
	},
}
</script>

<style scoped lang="scss">
// nothing
</style>
