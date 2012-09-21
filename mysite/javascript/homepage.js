jQuery(function($){

	var search = '%23entwinejs'; // search = '%23obama';

	$.ajax({
		dataType: 'jsonp',
		url: 'http://search.twitter.com/search.json?rpp=5&include_entities=1&q='+search,
		success: function(data, textStatus, jqXHR){
			var source   = $('#twitter-posts > script[type="text/handlebar"]').html();
			var template = Handlebars.compile(source);

			var handlers = {
				hashtags: Handlebars.compile('<a href="http://twitter.com/search?q=%23{{text}}">#{{text}}</a>'),
				urls: Handlebars.compile('<a href="{{url}}">{{display_url}}</a>'),
				media: Handlebars.compile('<a href="{{url}}">{{display_url}}</a>'),
				user_mentions: Handlebars.compile('@<a href="http://twitter.com/{{screen_name}}">{{screen_name}}</a>')
			};

			$.each(data.results, function(){
				var html = this.text.split('');

				if (this.entities) for (var k in handlers) {
					if (this.entities[k]) $.each(this.entities[k], function(){
						html[this.indices[0]] = handlers[k](this);
						for (var i = this.indices[0]+1; i < this.indices[1]; i++) html[i] = '';
					});
				}

				this.html = html.join('');
			});

			$('#twitter-posts > ul').html(template(data));
		}
	})

	var repo = 'hafriedlander/jquery.entwine';

	$.ajax({
		dataType: 'jsonp',
		url: 'https://api.github.com/repos/'+repo+'/issues?per_page=5',
		success: function(data, textStatus, jqXHR){
			var source   = $('#github-issues > script[type="text/handlebar"]').html();
			var template = Handlebars.compile(source);

			$('#github-issues > ul').html(template(data));
		}
	})

	var tags = 'entwine;entwinejs'; // tags='apache';

	$.ajax({
		dataType: 'jsonp',
		url: 'https://api.stackexchange.com/2.0/search?site=stackoverflow&pagesize=10&tagged='+tags,
		success: function(data, textStatus, jqXHR){
			var source   = $('#stackoverflow > script[type="text/handlebar"]').html();
			var template = Handlebars.compile(source);

			$('#stackoverflow > ul').html(template(data));
		}
	})



})