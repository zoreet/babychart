<!DOCTYPE html>
<html>
	<head>
		<title><? echo $_GET['id'] ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" type="text/css" href="/static/css/main.css">
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
		<link rel="manifest" href="/manifest.json">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
		<meta name="theme-color" content="#ffffff">
	</head>
	<body>
		<div id="header">
			<div id="add-day">+</div>
			<h1><? echo $_GET['id'] ?></h1>
		</div>
		<section id="chart" class="panel" data-bind="foreach: days">
			<div class="day">
				<div class="day-top sticky">
					<h2><span data-bind="text: title"></span></h2>
					<div>Day <span data-bind="text: $root.days().length - $index()"></span></div>
					<div class="day-head day-row" data-bind="visible: records().length > 0">
						<div class="day-cell day-cell--time">🕑</div>
						<div class="day-cell day-cell--pumped">⛽️</div>
						<div class="day-cell day-cell--breastFed">👩</div>
						<div class="day-cell day-cell--fed">🍼</div>
						<div class="day-cell day-cell--formula">🍼F</div>
						<div class="day-cell day-cell--pipi">💧</div>
						<div class="day-cell day-cell--caca">💩</div>
					</div>
				</div>
				<div class="day-body" data-bind="foreach: records">
					<div class="day-row">
						<div class="day-cell day-cell--time"><input step="1" type="time" data-bind="value: time, event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day-cell day-cell--pumped"><input class="number" step="1" type="number" data-bind="value: pumped, event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day-cell day-cell--breastFed"><input class="number" step="1" type="number" data-bind="value: breastFed, event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day-cell day-cell--fed"><input class="number" step="1" type="number" data-bind="value: fed, event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day-cell day-cell--formula"><input class="number" step="1" type="number" data-bind="value: formula, event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day-cell day-cell--pipi"><input type="checkbox" class="checkbox" data-bind="checked: pipi, event: { change: function() { bbc.saveData(); } }" /><span>💧<span></div>
						<div class="day-cell day-cell--caca"><input type="checkbox" class="checkbox" data-bind="checked: caca, event: { change: function() { bbc.saveData(); } }" /><span>💩<span></div>
					</div>
				</div>
				<div class="day-foot sticky">
					<button type="button" data-bind="click: addRecord">Add Record</button>
					<div class="day-stats">
						<div class="day-stats--row">
							💪<input type="number" step="1" data-bind="value: weight" /> grams
						</div>
						<div class="day-stats--row">
							🍼<input type="number" step="1" data-bind="value: bottlesPerDay" /> per day
						</div>
					</div>
				</div>
			</div>
		</section>
	</body>
	<script type="text/javascript" src="/static/js/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="/static/js/knockout-3.4.2.js"></script>
	<script type="text/javascript" src="/static/js/moment.min.js"></script>
	<script type="text/javascript" src="/static/js/prefixfree.min.js"></script>
	<script type="text/javascript" src="/static/js/babychart.js"></script>
	<script type="text/javascript">
		$(function() {
			bbc.init("<? echo $_GET['id'] ?>");
		});
	</script>
</html>