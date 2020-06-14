new Vue({
	el: '#app',
	data: {
		search: '',
		items: [],
		chooseItem: null
	},
	mounted: function() {
		$.getJSON('/docs/collection.json', (res) => {
			this.items = res.item;

			const postmanId = location.hash.substring(1);
			if (postmanId) {
				const item = this.findItemById(postmanId);
				this.onAsideItemClick(item);

				this.$nextTick(function() {
					$('.aside').scrollTop(
						$('#r' + postmanId).offset().top
					);
				});
			}
		})
	},
	methods: {
		findItemById: function(postmanId) {
			for (const group of this.items) {
				for (const item of group.item) {
					if (item._postman_id === postmanId) {
						return item;
					}
				}
			}
			return null;
		},

		isShouldShowApi: function(item) {
			const search = this.search.trim();
			if (!search) {
				return true;
			}

			return item.name.indexOf(search) !== -1;
		},

		isAsideItemActive: function(item) {
			if (!this.chooseItem) {
				return false;
			}
			return item._postman_id === this.chooseItem._postman_id;
		},

		onAsideItemClick: function(item) {
			if (!item) {
				this.chooseItem = null;
				return;
			}

			this.chooseItem = item;
			this.$nextTick(function() {
				hljs.initHighlighting();
			});

			location.hash = item._postman_id;
		},

		resolveResponse: function() {
			if (!this.chooseItem) {
				return '';
			}

			const item = this.chooseItem.request.response[0];
			return item.content;
		}
	}
});
