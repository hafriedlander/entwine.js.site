<% control Parent %>
	<% if Children %>
		<ul class="nav nav-list">
			<li class="nav-header">
				$MenuTitle
			</li>
			<% control Children %>
				<li class="<% if LinkOrCurrent = current %>active<% end_if %> $LinkOrCurrent">
					<a href="$Link">$MenuTitle</a>
				</li>
			<% end_control %>
		</ul>
	<% end_if %>
<% end_control %>