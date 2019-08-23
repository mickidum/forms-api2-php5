<?php
$request_path = rtrim($_SERVER['REQUEST_URI'], '/') . '/';

if (file_exists('.env')) {
	header("Location: " . $request_path . "crm");
	exit;
} elseif (isset($_POST['submit'])) {

	if (!empty($_POST['login']) and !empty($_POST['password']) and strlen($_POST['password']) > 4 and strlen($_POST['login']) > 2) {
		$login = $_POST['login'];
		$password = hash('sha256', $_POST['password']);
		$key = hash('sha256', time());

		$create_env_string = 'JWT_SECRET=' . $key . "\r\n";
		$create_env_string .= 'SECURE=false' . "\r\n";
		$create_env_string .= 'LOGIN=' . $login . "\r\n";
		$create_env_string .= 'PASSWORD=' . $password . "\r\n";

		$request_uri = $request_path . 'crm';

		$htaccess = '<IfModule mod_rewrite.c>' . "\r\n";
		$htaccess .= 'RewriteEngine On' . "\r\n";
		$htaccess .= 'RewriteBase ' . $request_uri . "\r\n";
		$htaccess .= 'RewriteRule ^index\.html$ - [L]' . "\r\n";
		$htaccess .= 'RewriteCond %{REQUEST_FILENAME} !-f' . "\r\n";
		$htaccess .= 'RewriteCond %{REQUEST_FILENAME} !-d' . "\r\n";
		$htaccess .= 'RewriteRule (.*) index.html [L]' . "\r\n";
		$htaccess .= '</IfModule>';

		$htaccess_file = fopen('crm/.htaccess', 'w');
		fwrite($htaccess_file, $htaccess);
		fclose($htaccess_file);

		$index_html_content = file_get_contents('crm/index.html');
		$index_html_file = fopen('crm/index.html', 'w');
		$index_html_content = str_replace('<head>', '<head><base href="' . $request_uri . '/">', $index_html_content);
		fwrite($index_html_file, $index_html_content);
		fclose($index_html_file);

		$env = fopen('.env', 'w');
		fwrite($env, $create_env_string);
		fclose($env);
		header("Location: " . $request_uri);
		exit;
	} else {
		echo '<div class="error">Error, Password must be minimum 5 symbols<br>Login minimum 3 symbols</div>';
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Create New User</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="<?php echo $request_path; ?>css/foundation.css">
	<style>
		.error {
			position: absolute;
			text-align: center;
			width: 100%;
			left: 0;
			top: 0;
			padding: 5px;
			color: red;
			background: black;
			font-weight: bold;
		}

		.all-wrap {
			position: relative;
			display: -webkit-flex;
			display: -moz-flex;
			display: -ms-flex;
			display: -o-flex;
			display: flex;
			-ms-align-items: center;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 100vh;
			padding: 0 15px;
		}

		.all-wrap-inner {
			width: 100%;
			max-width: 600px;
			border: solid 1px silver;
			padding: 30px 24px;
		}

		input {
			height: 50px !important;
			font-size: 24px !important;
			font-weight: normal;
			box-shadow: none !important;
		}

		h1 {
			font-size: 36px;
			font-weight: bold;
			text-align: center;
			margin-bottom: 40px;
		}

		label {
			font-size: 18px;
		}
	</style>
</head>

<body>
	<div class="all-wrap">
		<div class="all-wrap-inner">
			<h1>Create Admin Account</h1>
			<form id="login" method="post" autocomplete="off">
				<p>
					<label>LOGIN <small>(*minimum 3 symbols)</small>
						<input type="text" name="login">
					</label>
				</p>
				<p>
					<label>PASSWORD <small>(*minimum 5 symbols)</small>
						<input type="password" name="password" autocomplete="new-password">
					</label>
				</p>
				<br>
				<p><button class="button large expanded warning" type="submit" name="submit">CREATE ADMIN</button></p>
			</form>
		</div>
	</div>
</body>

</html>