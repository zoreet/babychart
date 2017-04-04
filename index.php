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
<!-- 	<nav>
		<a href="#">Chart</a>
		<a href="#">Stats</a>
	</nav> -->
	<div id="header">
		<div id="add-day" data-bind="click: addDay">+</div>
		<h1><? echo $_GET['id'] ?></h1>
	</div>
	<section id="chart" class="panel" data-bind="foreach: days">
		<div class="day">
			<div class="day-top sticky">
				<h2>Day <span data-bind="text: title"></span></h2>
				<div class="day-head day-row">
					<div class="day-cell day-cell--time">⏰</div>
					<div class="day-cell day-cell--pumped">Pumped</div>
					<div class="day-cell day-cell--fed">Fed</div>
					<div class="day-cell day-cell--formula">Formula</div>
					<div class="day-cell day-cell--pipi">💧</div>
					<div class="day-cell day-cell--caca">💩</div>
				</div>
			</div>
			<div class="day-body" data-bind="foreach: hours">
				<div class="day-row">
					<div class="day-cell day-cell--time" data-bind="text: time"></div>
					<div class="day-cell day-cell--pumped"><input class="number" step="1" type="number" data-bind="value: pumped, event: { change: saveData }" /></div>
					<div class="day-cell day-cell--fed"><input class="number" step="1" type="number" data-bind="value: fed, event: { change: saveData }" /></div>
					<div class="day-cell day-cell--formula"><input class="number" step="1" type="number" data-bind="value: formula, event: { change: saveData }" /></div>
					<div class="day-cell day-cell--pipi"><input type="checkbox" class="checkbox" data-bind="checked: pipi, event: { change: saveData }" /><span>💧<span></div>
					<div class="day-cell day-cell--caca"><input type="checkbox" class="checkbox" data-bind="checked: caca, event: { change: saveData }" /><span>💩<span></div>
				</div>
			</div>
			<div class="day-foot sticky">
				<div class="day-stats">
					<div class="day-stats--row">
						Weight: <input type="number" step="1" data-bind="value: weight, event: { change: saveData }" />
					</div>
					<div class="day-stats--row">
						Bottles per day: <input type="number" step="1" data-bind="value: bottlesPerDay, event: { change: saveData }" /> @ <span data-bind="text: mlPerBottle"></span>ml 🍼
					</div>
					<div class="day-stats--row">
						Status: <span data-bind="text: totalFed"></span>ml out of <span data-bind="text: dailyFeedGoal"></span>ml</span>
					</div>
				</div>
			</div>
		</div>
	</section>
</body>
	<script type="text/javascript" src="/static/js/prefixfree.min.js"></script>
	<script type="text/javascript" src="/static/js/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.4.2/knockout-debug.js"></script>
	<script type="text/javascript">

		var days;
		$(function(){
			loadData("<? echo $_GET['id'] ?>");
		});



		var initTemplate = function( data ) {
			var Day = function(data, root) {
				var self = this;
				self.title = data.title;
				self.hours = ko.observableArray($.map(data.hours, function(entry){
					return new Entry(entry, root);
				}));
				self.weight = ko.observable(data.weight);
				self.bottlesPerDay = ko.observable(data.bottlesPerDay);
				self.mlPerBottle = ko.computed(function(){
					var value = self.weight() / 1000 * 150 / self.bottlesPerDay();
					value = Math.round( value / 5 ) * 5;
					return value
				});
				self.dailyFeedGoal = ko.computed(function(){
					return self.weight() / 1000 * 150
				});
				self.totalFed = ko.computed(function(){
					var total = 0;
					for(var i=0; i<self.hours().length; i++) {
						var fed = parseInt( self.hours()[i].fed() );
						fed = isNaN(fed) ? 0 : fed;

						var formula = parseInt( self.hours()[i].formula() );
						formula = isNaN(formula) ? 0 : formula;

						total += fed + formula
					}
					return total;
				});
			}

			var Entry = function(data,root) {
				var self = this;
				self.time = data.time;
				self.pumped = ko.observable( data.pumped );
				self.fed = ko.observable( data.fed );
				self.formula = ko.observable( data.formula );
				self.pipi = ko.observable( data.pipi );
				self.caca = ko.observable( data.caca );
				self.saveData = function(a,b,c){
					// console.log(data)
					saveData(root.days());
				}
			}

			var viewModel = function(data) {
				var self = this;
				self.days = ko.observableArray($.map(data, function(day){
					return new Day(day, self);
				}));
				self.addDay = function(){
					var hours=[];
					for(var i=0; i<24; i++) {
						hours.push({
							"time":(i+1),
							"pumped":"",
							"fed":"",
							"formula":"",
							"pipi":false,
							"caca":false
						});
					}
					self.days.unshift(
						new Day({
							title: 'asd',
							bottlesPerDay: 8,
							weight: 3780,
							hours: hours
						}),
						self
					);
					saveData(self.days());
				}
			}

			ko.applyBindings( new viewModel(data) );
		}





		var formatNumber = function(number) {
			number = parseInt(number);
			if(isNaN(number)) {
				return ""
			}
			if(!number) {
				return ""
			}
			return number
		}





		var loadData = function(id) {
			$.post(
				"./loadData.php",
				{
					id: id
				},
				function( data ) {
					data = $.parseJSON(data)
					if(data.code == 200) {
						data = $.parseJSON(data.result);
						initTemplate( data.days )
					} else {
						console.error(data.result);
					}
				}
			);
		}





		var saveData = function(days) {
			$.post(
				"./saveData.php",
				{
					id : "<? echo $_GET['id']; ?>",
					data: ko.toJSON({days: days})
				},
				function( data) {
					console.log( "Response when saving", data )
				}
			);
		}





	</script>
</html>