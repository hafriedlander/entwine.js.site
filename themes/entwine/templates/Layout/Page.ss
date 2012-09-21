<div class="row">
	<% if Parent.Parent %>
		<div class="span2">
			<% include PrimaryNav %>
		</div>
		<div class="span10">
	<% else %>
		<div class="span12">
	<% end_if %>
			<div class="page-header">
				<h1>$Title</h1>
			</div>

			$Form
			$Content
		</div>
	</div>
</div>

