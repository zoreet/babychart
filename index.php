<!DOCTYPE html>
<html>
	<head>
		<title><? echo $_GET['name'] ?></title>
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
			<h1><? echo $_GET['name'] ?></h1>
			<div id="add-day">+</div>
		</div>
		<section id="chart" class="panel" data-bind="foreach: days">
			<div class="day">
				<div class="day__top sticky">
					<h2 class="day__title">
						<span data-bind="text: title"></span>
						<span><span data-bind="text: totalFed()"></span><span class="small"> ml</span></span>
					</h2>
					<div class="day__subtitle">
						<span>Day <span data-bind="text: $root.days().length - $index()"></span></span>
						<span><span data-bind="text: feedGoal()"></span> ml</span>
						<!-- <span data-bind="visible: avgMilkPerMeal() > 0">Daily avg <span data-bind="text: avgMilkPerMeal()"></span>ml</span> -->
					</div>
					<div class="day__head day__row" data-bind="visible: records().length > 0">
						<div class="day__cell day__cell--time">🕑</div>
						<div class="day__cell day__cell--pumped">⛽️</div>
						<div class="day__cell day__cell--breastFed">👩</div>
						<div class="day__cell day__cell--fed">🍼</div>
						<div class="day__cell day__cell--formula">🍼F</div>
						<div class="day__cell day__cell--pipi">💧</div>
						<div class="day__cell day__cell--caca">💩</div>
					</div>
				</div>
				<div class="day__body" data-bind="foreach: records">
					<div class="day__row">
						<div class="day__cell day__cell--time"><input step="1" type="time" data-bind="value: time, event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day__cell day__cell--pumped"><input class="number" step="1" type="number" data-bind="value: pumped, valueUpdate: 'afterkeydown', event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day__cell day__cell--breastFed"><input class="number" step="1" type="number" data-bind="value: breastFed, valueUpdate: 'afterkeydown', event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day__cell day__cell--fed"><input class="number" step="1" type="number" data-bind="value: fed, valueUpdate: 'afterkeydown', event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day__cell day__cell--formula"><input class="number" step="1" type="number" data-bind="value: formula, valueUpdate: 'afterkeydown', event: { change: function() { bbc.saveData(); } }" /></div>
						<div class="day__cell day__cell--pipi"><input type="checkbox" class="checkbox" data-bind="checked: pipi, event: { change: function() { bbc.saveData(); } }" /><span>💧<span></div>
						<div class="day__cell day__cell--caca"><input type="checkbox" class="checkbox" data-bind="checked: caca, event: { change: function() { bbc.saveData(); } }" /><span>💩<span></div>
					</div>
				</div>
				<div class="day__foot sticky">
					<button type="button" data-bind="click: addRecord">Add Record</button>
					<div class="day__stats">
						<div class="day__stats__row">
							💪<input type="number" step="1" data-bind="value: weight, valueUpdate: 'afterkeydown', event: { change: function() { bbc.saveData(); } }" /> grams
						</div>
						<div class="day__stats__row">
							🍼<input type="number" step="1" data-bind="value: bottlesPerDay, valueUpdate: 'afterkeydown', event: { change: function() { bbc.saveData(); } }" /> per day
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
			bbc.init("<? echo $_GET['name'] ?>", "<? echo $_GET['bday'] ?>");
		});
	</script>
</html>