<!DOCTYPE html>
<html lang="en">
	<head>
		<title>ASEI</title>
		<script src="asei.js"></script>
		<script>

			function handler(msg) {
				var json = JSON.parse(msg.data);
				document.getElementById('asei').innerHTML = json.uptime;
			}

			window.onload = function() {
				onServer.listenFor('aseiPoll', handler);
			}
		</script>
	</head>
	<body id="asei">
	</body>
</html>