<!DOCTYPE html>
<html lang="en">
	<head>
		<% base_tag %>

		<title>$Title &raquo; $SiteConfig.Title</title>
		$MetaTags(false)

		<link rel="shortcut icon" href="/favicon.ico" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	</head>

	<body>
		<div class="page">
			<div class="navbar navbar-fixed-top">
				<div class="navbar-inner">
					<div class="container">
						<a class="brand" href="">Entwine.js</a>
						<ul class="nav">
							<% control Menu(1) %>
								<li <% if LinkingMode != link %>class="active"<% end_if %>>
									<a href="$Link">$MenuTitle</a>
								</li>
							<% end_control %>
						</ul>
					</div>
				</div>
			</div>
			<div class="container">
				$Layout
			</div>
		</div>
	</body>
</html>
