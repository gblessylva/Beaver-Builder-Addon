/**
 * Add to Favourite Module Frontend JavaScript
 */

(function($) {
	'use strict';

	/**
	 * Add to Favourite Button functionality
	 */
	class HJAddToFavourite {
		constructor() {
			this.init();
		}

		init() {
			$(document).ready(() => {
				this.bindEvents();
			});
		}

		bindEvents() {
			// Handle favourite button clicks
			$(document).on('click', '.hj-favourite-btn', this.handleFavouriteClick.bind(this));
		}

		handleFavouriteClick(e) {
			const $button = $(e.currentTarget);
			
			// Skip if it's a link with href (let it navigate normally)
			if ($button.is('a') && $button.attr('href') && $button.attr('href') !== 'javascript:void(0);') {
				return true;
			}
			
			// Add visual feedback
			this.addClickFeedback($button);
			
			// Trigger custom event for other scripts to listen to
			$button.trigger('hj:favourite:clicked', {
				button: $button,
				itemId: $button.data('item-id'),
				category: $button.data('category')
			});
		}

		addClickFeedback($button) {
			// Add temporary class for animation
			$button.addClass('hj-btn-clicked');
			
			// Remove class after animation
			setTimeout(() => {
				$button.removeClass('hj-btn-clicked');
			}, 200);
			
			// Optional: Change icon to indicate success
			const $icon = $button.find('.hj-btn-icon');
			if ($icon.length) {
				const originalClass = $icon.attr('class');
				$icon.removeClass().addClass('fas fa-check hj-btn-icon');
				
				// Revert back after 1 second
				setTimeout(() => {
					$icon.attr('class', originalClass);
				}, 1000);
			}
		}
	}

	// Initialize when document is ready
	$(document).ready(() => {
		new HJAddToFavourite();
	});

})(jQuery);