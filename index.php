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
		<div id="add-day">+</div>
		<h1><? echo $_GET['id'] ?></h1>
	</div>
	<section id="chart" class="panel">
		<div class="day template" data-id="YYYYMMDD">
			<div class="day-top sticky">
				<h2></h2>
				<div class="day-head day-row">
					<div class="day-cell day-cell--time">⏰</div>
					<div class="day-cell day-cell--pumped">Pumped</div>
					<div class="day-cell day-cell--fed">Fed</div>
					<div class="day-cell day-cell--formula">Formula</div>
					<div class="day-cell day-cell--pipi">💧</div>
					<div class="day-cell day-cell--caca">💩</div>
				</div>
			</div>
			<div class="day-body">
				<div class="day-row">
					<div class="day-cell day-cell--time">1</div>
					<div class="day-cell day-cell--pumped contenteditable" contenteditable></div>
					<div class="day-cell day-cell--fed contenteditable" contenteditable></div>
					<div class="day-cell day-cell--formula contenteditable" contenteditable></div>
					<div class="day-cell day-cell--pipi js-toggle-visible"></div>
					<div class="day-cell day-cell--caca js-toggle-visible"></div>
				</div>
			</div>
			<div class="day-foot sticky">
				<div class="day-stats">
					<div class="day-stats--row">
						Weight: <span class="weight day-stats__editable" contenteditable>3780</span>g
					</div>
					<div class="day-stats--row">
						Bottles per day: <span class="noBottles day-stats__editable" contenteditable>8</span> @ <span class="mlPerBottle">70</span>ml 🍼
					</div>
					<div class="day-stats--row">
				  		Status: <span class="mlAte">0ml out of 0ml</span>
					</div>
				</div>
			</div>
		</div>
	</section>
</body>
	<script type="text/javascript" src="/static/js/prefixfree.min.js"></script>
	<script type="text/javascript" src="/static/js/jquery-3.1.1.min.js"></script>
	<script type="text/javascript">

		$(function(){

			$('body')
				.on('click', '.js-toggle-visible', function(e){
					$(this).toggleClass('is-visible')
					saveData();
				})
				.on('blur', '.day-stats, .contenteditable', function(e){
					updateStats(e);
				});
			$('#add-day').click(addDay);
			loadData('<? echo $_GET['id'] ?>');
		});




		var addDay = function() {
			$day = $('.day.template:first').clone().removeClass('template');
			daysNo = $('.day').length;

			$('h2', $day).html('Day ' + daysNo);
			$body = $('.day-body', $day)
			$row = $('.day-row', $body)
			for(i=2;i<=24;i++) {
				$row
					.clone()
					.find('.day-cell--time')
						.html(i)
						.end()
					.appendTo($body)
			}

			$day.prependTo('#chart');

			currentWeight = $('.day:first').find('.weight').html();
			currentWeight = parseInt(currentWeight) || 0;
			$('.weight', $day).html(currentWeight).blur();

			saveData();
		}

		var updateStats = function(e) {
			$parent = $(e.target).parents('.day');
			$weight=$('.weight', $parent);
			weight = parseInt($weight.html());
			$noBottles=$('.noBottles', $parent);
			noBottles = parseInt( $noBottles.html() );
			$mlPerBottle=$('.mlPerBottle', $parent);
			mlPerBottle = parseInt( $mlPerBottle.html() );
			$mlAte=$('.mlAte', $parent);


			$mlPerBottle.html( Math.round( weight/1000*150/noBottles ) );

			totalToEat = mlPerBottle * noBottles;
			totalAte = 0;
			$('.day-cell--fed, .day-cell--formula', $parent).each(function(i){
				var ammount = parseInt( $(this).html() ) || 0;
				totalAte += ammount;
			});

			$mlAte.html( totalAte + "ml out of " + totalToEat + "ml" );

			saveData();
		}

		var loadData = function(id) {
			$.post(
				"./loadData.php",
				{
					id: id
				},
				function( data ) {
					serverData = $('<div/>').html(data).text(); // decode the html
					serverData = serverData != "" ? serverData : "";

					if(serverData) {
						$('#chart').html( serverData );
						$('.weight').blur();
					}
				}
			);
		}
		var saveData = function() {
			var chartData = $.trim($('#chart').html());

			$.post(
				"./saveData.php",
				{
					id : "<? echo $_GET['id']; ?>",
					chartData: chartData
				}
			);
		}
	</script>
</html>