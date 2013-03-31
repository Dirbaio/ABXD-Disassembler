function applyHighlights()
{
	$(".highlight")
	.unbind('mouseover')
	.unbind('mouseout')
	.mouseover(function() {
		$(".highlighted").removeClass("highlighted");
		var thetext = this.innerHTML;
		$(".highlight").filter(function() {
		    return $(this).text() == thetext;
		}).addClass("highlighted");
	});
/*	.mouseout(function() {
		$(".highlighted").removeClass("highlighted");
	});*/
}

$(document).keypress(function (e) {
	if(e.target.nodeName == "TEXTAREA" || e.target.nodeName == "INPUT")
		return true;
	
	key = String.fromCharCode(e.which).toLowerCase();
	
	if(key == 'u') {
		setAddressType(highlightedAddr, 0);
		return false;
	}

	if(key == 'd') {
		setAddressType(highlightedAddr, 1);
		return false;
	}

	if(key == 'c') {
		setAddressType(highlightedAddr, 2);
		return false;
	}

	if(key == 'p') {
		setAddressType(highlightedAddr, 3);
		return false;
	}

	if(key == 'r') {
		reloadDisassembly();
		return false;
	}
});

function setAddressType(addr, type)
{
	var theaddr = addr;
	beginLoad();
	url="./?page=setaddrtype&addr="+addr+"&type="+type;
	$.get(url, function(data) {
		gotoAddress(theaddr, true);
	});
}
function getAddressRow(addr)
{
	return document.getElementById("addr_"+addr);
}

var highlightedAddr = -1;

function highlightAddress(addr, row)
{
	highlightedAddr = addr;
	
	if(!row)
		row = getAddressRow(addr);
	
	if(row)
	{
		$(".highlightedrow").removeClass("highlightedrow");
		$(row).addClass("highlightedrow");
	}
}

function scrollToAddress(addr)
{
	row = getAddressRow(addr);
	if(row == undefined) return;
	
	document.getElementById("disassembly_container").scrollTop = row.offsetTop;
	var theaddr = addr;
	
	setTimeout(function() {
		highlightAddress(addr);
	}, 10);
	/*
	var theaddr = addr;
	$.scrollTo(row, 200, {offset:-window.innerHeight/2, onAfter:function(){
		highlightAddress(addr);
	}});*/	
}

var lastLoadedAddr;

function gotoAddress(addr, reload)
{
	var theaddr = addr;
	row = getAddressRow(addr);
	if(row && !reload)
	{
		scrollToAddress(addr);
	}
	else
	{
		beginLoad();
		loadDisassembly(addr, function(){
			scrollToAddress(theaddr);
		});
	}
}

function reloadDisassembly()
{
	beginLoad();
	loadDisassembly(lastLoadedAddr);
}

function loadDisassembly(addr, callback)
{
	lastLoadedAddr = addr;
	url="./?page=getdisassembly&addr="+addr;
	$("#disassembly").load(url, function() {
		if(callback)
			callback();
		applyHighlights();
		endLoad();
	});
}

function loadLabelList()
{
	url="./?page=getlabellist";
	$("#labellist").load(url);
}

function beginLoad()
{
	$("#loading_container").fadeIn(100);
}

function endLoad()
{
	$("#loading_container").fadeOut(100);
}

