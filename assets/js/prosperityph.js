"use strict"
let menu_out;
$(document).ready(function() {
	$(".mega-menu-top a").click(function(e) {
		// e.preventDefault();
		$(this).parent().toggleClass("hoveractive");
	});
}).on("mouseenter", ".mega-menu-top.hoveractive", function() {
	clearTimeout(menu_out);
}).on("mouseleave", ".mega-menu-top.hoveractive", function() {
	clearTimeout(menu_out);
	menu_out = setTimeout (() => $(this).removeClass("hoveractive"), 300);
});