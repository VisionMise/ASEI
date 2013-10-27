<!DOCTYPE html>
<html lang="en">
	<head>
		<title>ASEI</title>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="asei.js"></script>
		<script>

			function handler(msg) {
				var json = JSON.parse(msg.data);
				document.getElementById('asei').innerHTML = json.uptime;
			}

			function makeRequest() {
				onServer.requestFrom({
					class: 	"test",
					method:	"test", 
					arg1: 	"value1" 
				}, getResp);
			}

			function getResp(data) {
				if (data.error) {
					document.getElementById('time').innerHTML = data.error;
				} else {
					document.getElementById('time').innerHTML = data.result;
				}
			}

			window.onload = function() {
				onServer.listenFor('aseiPoll', handler);
			}
		</script>
	</head>
	<body>
		<div id="asei"></div>
		<button id="time" onclick="makeRequest();">Click</button>
	</body>
</html>