tinyMCEPopup.requireLangPack();

var ChartDialog = {
	init : function() {
		var f = document.forms[0];
		var ed = tinyMCEPopup.editor, 
			dom = ed.dom, 
			n = ed.selection.getNode();
		var jsonData = decodeURIComponent(dom.getAttrib(n, 'data-chart')) || '';
		fixWidth('#main-table');
		if(jsonData != '')
		{
			var restoredData = JSON.parse(jsonData);
			restoreData(restoredData, '#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
			var width = parseInt(restoredData.size);
			var height = parseInt(width*0.6);
			$('#size').val(width);
			$('#canvas-container, #canvas').width(width);
			$('#canvas').height(height);
			$('#canvas').attr('width', width);
			$('#canvas').attr('height', height);

		}
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	},

	insert : function() {
		var outputOfChartPlugin = '';
		mcTabs.displayTab('chart_tab','chart_panel');
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
		setTimeout(function(){
		var url = canvas.toDataURL();
		var img = document.createElement('img');
		img.setAttribute('src', url);
		img.setAttribute('class', 'chart-image');
		img.setAttribute('data-chart', JSON.stringify(chartData));
		img.style.maxWidth = '100%';
		img.style.height = 'auto';
		outputOfChartPlugin = img.outerHTML;
		tinyMCEPopup.editor.execCommand('mceInsertRawHTML', false, outputOfChartPlugin);
		tinyMCEPopup.close();
		}, 100);
	}
};
window.chartColors = {
	red: 'rgb(255, 99, 132)',
	orange: 'rgb(255, 159, 64)',
	green: 'rgb(75, 192, 192)',
	blue: 'rgb(54, 162, 235)',
	purple: 'rgb(153, 102, 255)',
	grey: 'rgb(201, 203, 207)',
	yellow: 'rgb(255, 205, 86)',
	brown:'rgb(168, 43, 43)',
	pink:'rgb(255, 73, 182)',
	cadetblue:'rgb(96, 160, 162)'
};
window.chartColorsArray = [
	'rgb(255, 99, 132)',
	'rgb(255, 205, 86)',
	'rgb(75, 192, 192)',
	'rgb(54, 162, 235)',
	'rgb(153, 102, 255)',
	'rgb(201, 203, 207)',
	'rgb(255, 159, 64)',
	'rgb(168, 43, 43)',
	'rgb(255, 73, 182)',
	'rgb(96, 160, 162)'
];

var chartData = {};
$(document).ready(function(e) {
    $(document).on('click', '#add-series', function(e){
		addSeries('#main-table');
		renderData('#type', '#type', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	});
    $(document).on('click', '#subtract-series', function(e){
		subtractSeries('#main-table');
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	});
    $(document).on('click', '#add-rows', function(e){
		addRows('#main-table');
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	});
    $(document).on('click', '#subtract-rows', function(e){
		subtractRows('#main-table');
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	});
    $(document).on('click', '#build', function(e){
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	});
	$(document).on('change', '#type, input', function(e){
		chartData = buildData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	});
	$(document).on('change', '#size', function(e){ 
		var width = parseInt($(this).val());
		var height = parseInt(width*0.6);
		$('#canvas-container, #canvas').width(width);
		$('#canvas').height(height);
		$('#canvas').attr('width', width);
		$('#canvas').attr('height', height);
		chartData = buildData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
		renderData('#type', '#size', '#chart-title', '#axes-x', '#axes-y', '#main-table');
	});
	
});
function fixWidth(selector)
{
	var cnt = $(selector).find('thead').find('tr').find('td').length;
	var width = 85/(cnt-1);
	$(selector).find('thead').find('tr').find('td').css({'width':width+'%'});
	$(selector).find('thead').find('tr').find('td:first-child').css({'width':'15%'});
}
function addSeries(selector)
{
	var cnt = $(selector).find('thead').find('tr').find('td').length;
	$(selector).find('thead').find('tr').append('<td><input name="series" type="text" autocomplete="off" value="Series '+cnt+'"></td>');
	$(selector).find('tbody').find('tr').each(function(index, element) {
        $(this).append('<td><input name="value" type="text" autocomplete="off" value="0"></td>');
    });
	fixWidth(selector);
}
function subtractSeries(selector)
{
	var cnt = $(selector).find('thead').find('tr').find('td').length;
	if(cnt > 2)
	{
		$(selector).find('thead').find('tr').find('td:last-child').remove();
		$(selector).find('tbody').find('tr').each(function(index, element) {
			$(this).find('td:last-child').remove();
		});
	}
	fixWidth(selector);
}
function addRows(selector)
{
	var cntRows = $(selector).find('tbody').find('tr').length;
	var cntSeries = $(selector).find('thead').find('tr').find('td').length;
	var tr = $('<tr>');
	var i;
	tr.append('<td><input name="time" type="text" autocomplete="off" value="Time '+(cntRows+1)+'"></td>');
	for(i = 1; i<cntSeries; i++)
	{
		tr.append('<td><input name="value" type="text" autocomplete="off" value="0"></td>');
	}
	$(selector).find('tbody').append(tr);
}
function subtractRows(selector)
{
	var cntRows = $(selector).find('tbody').find('tr').length;
	if(cntRows > 1)
	{
		$(selector).find('tbody').find('tr:last-child').remove();
	}
}
function renderData(typeSelector, sizeSelector, titleSelector, axesXSelector, axesYSelector, selector)
{
	if(enableRender)
	{
		chartData = buildData(typeSelector, sizeSelector, titleSelector, axesXSelector, axesYSelector, selector);
		config.options.title.text = chartData.title;
		var i;
		config.type = chartData.type;
		config.size = chartData.size;
		if(!config.type)
		{
			config.type = 'line';
		}
		if(!config.size)
		{
			config.size = '500';
		}
		config.data.labels = chartData.labels;
		config.options.scales.xAxes[0].scaleLabel.labelString = chartData.axesX;
		config.options.scales.yAxes[0].scaleLabel.labelString = chartData.axesY;
		config.data.datasets = [];
		for(i in chartData.datasets)
		{
			config.data.datasets[i] = {};
			config.data.datasets[i].label = chartData.datasets[i].label;
			config.data.datasets[i].data = chartData.datasets[i].data;
			config.data.datasets[i].fill = false;
			config.data.datasets[i].backgroundColor = window.chartColorsArray[i%(window.chartColorsArray.length)];
			config.data.datasets[i].borderColor = window.chartColorsArray[i%(window.chartColorsArray.length)];
		}
		canvas = document.getElementById('canvas');
		ctx = canvas.getContext('2d');
		chartObject = new Chart(ctx, config);
		chartObject.update();
	}
	enableRender = false;
	clearTimeout(timeout);
	timeout = setTimeout(function(){
		enableRender = true;
	}, 200);
}
function buildData(typeSelector, sizeSelector, titleSelector, axesXSelector, axesYSelector, selector)
{
	var data = {};
	data.type = $(typeSelector).val().trim();
	data.size = $(sizeSelector).val().trim();
	data.title = $(titleSelector).val().trim();
	data.title = $(titleSelector).val().trim();
	data.axesX = $(axesXSelector).val().trim();
	data.axesY = $(axesYSelector).val().trim();
	data.labels = [];
	data.datasets = [];
	if(!data.type)
	{
		data.type = 'line';
	}
	if(!data.size)
	{
		data.type = '500';
	}
	
	$(selector).find('tbody').find('tr').each(function(index, element) {
        data.labels[data.labels.length] = $(this).find('td:first-child').find('input').val().trim();
    });
	var cntSeries = $(selector).find('thead').find('tr').find('td').length;
	var cntRows = $(selector).find('tbody').find('tr').length;
	var i, j, k;
	for(i = 2; i<=cntSeries; i++)
	{
		var values = [];
		for(k = 1; k<=cntRows; k++)
		{
			values[values.length] = parseFloat($(selector).find('tbody').find('tr:nth-child('+k+')').find('td:nth-child('+i+')').find('input').val().trim());
		}
		data.datasets[data.datasets.length] = {
			label:$(selector).find('thead').find('tr').find('td:nth-child('+i+')').find('input').val().trim(),
			data:values
		}
	}
	return data;
}
function restoreData(data, typeSelector, sizeSelector, titleSelector, axesXSelector, axesYSelector, selector)
{
	if(!data.type)
	{
		data.type = 'line';
	}
	if(!data.size)
	{
		data.size = '500';
	}
	
	$(typeSelector).val(data.type);
	$(sizeSelector).val(data.size);
	$(titleSelector).val(data.title);
	$(axesXSelector).val(data.axesX);
	$(axesYSelector).val(data.axesY);
	
	var cntSeries = data.datasets.length;
	var cntRows   = data.labels.length;
	
	subtractRows(selector);
	subtractRows(selector);
	subtractRows(selector);
	subtractSeries(selector);
	subtractSeries(selector);
	subtractSeries(selector);
	
	var i, j, k, l;
	for(i = 1; i<cntSeries; i++)
	{
		addSeries(selector);
	}
	for(i = 1; i<cntRows; i++)
	{
		addRows(selector);
	}
	for(i = 0, j = 2; i<cntSeries; i++, j++)
	{
		$(selector).find('thead').find('tr').find('td:nth-child('+(j)+')').find('input').val(data.datasets[i].label);
	}
	for(i = 0, j = 2; i<cntSeries; i++, j++)
	{
		for(k = 0, l = 1; k < cntRows; k++, l++)
		{
			$(selector).find('tbody').find('tr:nth-child('+(l)+')').find('td:nth-child('+(j)+')').find('input').val(data.datasets[i].data[k]);
		}
	}
	for(k = 0, l = 1; k < cntRows; k++, l++)
	{
		$(selector).find('tbody').find('tr:nth-child('+(l)+')').find('td:first-child').find('input').val(data.labels[k]);
	}
}

var canvas;
var ctx;
var chartObject;
var enableRender = true;
var timeout = setTimeout(function(){}, 1000);
var config = {
	type: 'line',
	data: {
		labels: [],
		datasets: []
	},
	options: {
		responsive: true,
		title:{
			display:true
		},
		tooltips: {
			mode: 'index',
			intersect: false,
		},
		hover: {
			mode: 'nearest',
			intersect: true
		},
		scales: {
			xAxes: [{
				display: true,
				scaleLabel: {
					display: true,
					labelString: 'Time'
				}
			}],
			yAxes: [{
				display: true,
				scaleLabel: {
					display: true,
					labelString: 'Value'
				}
			}]
		},
		ticks: {
            min: 0
        },
		animation: {
			duration: 10,
			easing: 'linear'
      	}
	}
};
tinyMCEPopup.onInit.add(ChartDialog.init, ChartDialog);