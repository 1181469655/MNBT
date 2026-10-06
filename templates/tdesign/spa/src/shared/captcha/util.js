/**
 * 根据容器尺寸换算验证码图片/滑轨的实际宽高（官方 util.js resetSize，修正 this 误用）
 */
export function resetSize(vm) {
	let img_width, img_height, bar_width, bar_height
	const parentWidth = (vm.$el.parentNode && vm.$el.parentNode.offsetWidth) || window.offsetWidth || 300
	const parentHeight = (vm.$el.parentNode && vm.$el.parentNode.offsetHeight) || window.offsetHeight || 150

	if (vm.imgSize.width.indexOf('%') !== -1) {
		img_width = (parseInt(vm.imgSize.width) / 100) * parentWidth + 'px'
	} else {
		img_width = vm.imgSize.width
	}
	if (vm.imgSize.height.indexOf('%') !== -1) {
		img_height = (parseInt(vm.imgSize.height) / 100) * parentHeight + 'px'
	} else {
		img_height = vm.imgSize.height
	}
	if (vm.barSize.width.indexOf('%') !== -1) {
		bar_width = (parseInt(vm.barSize.width) / 100) * parentWidth + 'px'
	} else {
		bar_width = vm.barSize.width
	}
	if (vm.barSize.height.indexOf('%') !== -1) {
		bar_height = (parseInt(vm.barSize.height) / 100) * parentHeight + 'px'
	} else {
		bar_height = vm.barSize.height
	}
	return { imgWidth: img_width, imgHeight: img_height, barWidth: bar_width, barHeight: bar_height }
}
