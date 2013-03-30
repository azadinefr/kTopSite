<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>kTopSite : Moteur de topsite open source</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="kTopSite est un moteur open source développé en tant que module pour le framework PHP kowFramework.">
		<meta name="author" content="Kevin Ryser (http://www.koweb.ch)">
		
		<?php css('bootstrap.min.css'); ?>
		<?php css('bootstrap-responsive.min.css'); ?>
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="../assets/js/html5shiv.js"></script>
		<![endif]-->

		<?php css('default.css'); ?>

		<!--<link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="../assets/ico/favicon.png">
		-->
	</head>
	<body>

		<div class="container">
			<div class="masthead">
				<h3 class="muted">kTopSite <small>Classement de site internet</small></h3>
				<div class="navbar">
					<div class="navbar-inner">
						<div class="container">
							<ul class="nav">
								<li class="active"><a href="<?php url('topsite'); ?>">Accueil</a></li>
								<li><a href="<?php url('topsite/register'); ?>">S'inscrire</a></li>
								<li><a href="<?php url('topsite/login'); ?>">Se connecter</a></li>
								<li><a href="<?php url('topsite/contact'); ?>">Contact</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
			<?php echo $layout_content; ?>
			
			<hr />
			<div class="footer">
				<p>
					&copy; kTopSite 2013<?php if (date('Y') > '2013') echo '-' . date('Y'); ?> |
					<a href="http://framework.koweb.ch" target="_blank">
						<img src="<?php img('powered.png'); ?>" alt="Propulsé par kowFramework" />
					</a>
				</p>
			</div>
		</div>
	</body>
</html>
