<div class="row">
	<div class="span8">
		<div class="page-header">
			<h1>$Title</h1>
		</div>

		$Form
		$Content
	</div>
	<div class="span4">
		<div id="github-issues" class="block">
			<h4>Recent issues</h4>
			<ul class="unstyled">
				<li>Loading... (unless you don't have javascript)</li>
			</ul>
			<small><a href="https://github.com/hafriedlander/jquery.entwine/issues/new">Raise an issue</a></small>

			<script type="text/handlebar">
				{{#if data}}
					{{#each data}}
						<li><a href="{{html_url}}">{{title}}</a><div>{{comments}} comments</div></li>
					{{/each}}
				{{else}}
					<li>No open issues.</li>
				{{/if}}
			</script>
		</div>

		<div id="stackoverflow" class="block">
			<h4>Recent questions on stack overflow</h4>
			<ul class="unstyled">
				<li>Loading... (unless you don't have javascript)</li>
			</ul>
			<small><a href="http://stackoverflow.com/questions/ask?tags=entwinejs">Ask a question on stack overflow (not a bug tracker or feature request list)</a></small>

			<script type="text/handlebar">
				{{#if items}}
					{{#each items}}
						<li><a href="{{link}}">{{title}}</a><div>{{answer_count}} answers</div></li>
					{{/each}}
				{{else}}
					<li>No questions asked yet.</li>
				{{/if}}
			</script>
		</div>

		<div id="twitter-posts" class="block">
			<h4>Recent tweats</h4>
			<ul class="unstyled">
				<li>Loading... (unless you don't have javascript)</li>
			</ul>
			<small>
				<a href="https://twitter.com/intent/tweet?button_hashtag=entwinejs" class="twitter-hashtag-button" data-lang="msa" data-related="hafriedlander">Tweet #entwinejs</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			</small>

			<script type="text/handlebar">
				{{#if results}}
					{{#each results}}
						<li>
							<a href="http://twitter.com/{{from_user}}">{{from_user}}</a>
							<p>{{{html}}}</p>
						</li>
					{{/each}}
				{{else}}
					<li>No recent tweets.</li>
				{{/if}}
			</script>
		</div>
	</div>
</div>

