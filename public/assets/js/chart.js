$(function(){
     
	// Pie Chart
	
	var ctx = document.getElementById('pieChart').getContext('2d');
	var pieChart = new Chart(ctx, {
		type: 'pie',
		data: {
			labels: ['PHP', 'IOS', 'Design', 'Android', 'Business', 'Testing'],
			datasets: [{
				label: '# of Votes',
				data: [12, 19, 3, 5, 2, 3],
				backgroundColor: [
					'#f6822d',
					'#353847',
					'#7f2df6',
					'#1bd790',
					'#e8da0d',
					'#f62d82'
				],
				borderWidth: 1
			}]
		},
		options: {
			responsive: true,
			legend: {
				display: false
			}
		}
	});
	
	// Line Chart

});