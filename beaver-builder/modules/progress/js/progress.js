/**
 * Progress Module Frontend JavaScript
 */

(function ($) {
	'use strict';

	class HJProgressTracker {
		constructor() {
			this.module = null;
			this.goalId = 0;
			this.goalData = null;
			this.checkinsData = [];
			this.activeFilters = [];
			this.init();
		}

		init() {
			$(document).ready(() => {
				this.initializeModule();
				this.bindEvents();
				this.loadData();
			});
		}

		initializeModule() {
			this.module = $('.hj-progress-module');
			if (!this.module.length) {
				console.error('Progress module not found');
				return;
			}

			// For Progress module, we'll fetch the latest in-progress goal dynamically
			// instead of requiring a specific goal ID
		}

		getGoalId() {
			// Try to get goal ID from various sources
			if (typeof hjProgress !== 'undefined' && hjProgress.goalId) {
				return hjProgress.goalId;
			}

			// Fallback to data attribute
			return this.module.data('goal-id') || 0;
		}

		bindEvents() {
			// Filter button clicks
			$(document).on('click', '.hj-filter-btn', (e) => {
				e.preventDefault();
				this.handleFilterClick($(e.currentTarget));
			});

			// Goal action buttons
			$(document).on('click', '.hj-btn-edit-goal', (e) => {
				e.preventDefault();
				this.handleEditGoal();
			});

			$(document).on('click', '.hj-btn-complete-goal', (e) => {
				e.preventDefault();
				this.handleCompleteGoal();
			});

			// Check-in button
			$(document).on('click', '.hj-btn-checkin', (e) => {
				// Allow natural navigation but add goal ID parameter
				const url = new URL(e.currentTarget.href, window.location.origin);
				url.searchParams.set('goal_id', this.goalId);
				e.currentTarget.href = url.toString();
			});
		}

		async loadData() {
			this.showLoading();

			try {
				console.log('Loading Data');
				// First, get the latest in-progress goal
				const latestGoal = await this.getLatestInProgressGoal();

				if (!latestGoal) {
					this.showError('No active goals found. Please create a goal first.');
					return;
				}

				console.log('Latest in-progress goal:', latestGoal);

				this.goalId = latestGoal.id;
				this.goalData = latestGoal;

				// Update the module's data attribute for other functions
				this.module.attr('data-goal-id', this.goalId);

				// Now load check-ins for this goal
				this.checkinsData = await this.loadCheckinsData();


				this.renderGoalSection();
				this.renderFilters();
				this.renderTimeline();
				this.hideLoading();

			} catch (error) {
				console.error('Error loading progress data:', error);
				this.showError('Unable to load progress data. Please try again.');
			}
		}

		async getLatestInProgressGoal() {
			return new Promise((resolve, reject) => {
				$.ajax({
					url: `${hjProgress.restUrl}goals?status=in-progress&orderby=date&order=desc&per_page=1`,
					type: 'GET',
					beforeSend: (xhr) => {
						const token = this.getApiToken();
						if (token) {
							xhr.setRequestHeader('X-HJ-API-KEY', token);
						}
					}
				})
					.done((goals) => {
						resolve(goals.length > 0 ? goals[0] : null);
					})
					.fail(reject);
			});
		}

		loadGoalData() {
			if (!this.goalId) {
				return Promise.reject(new Error('No goal ID available'));
			}

			return new Promise((resolve, reject) => {
				$.ajax({
					url: `${hjProgress.restUrl}goals/${this.goalId}`,
					type: 'GET',
					beforeSend: (xhr) => {
						const token = this.getApiToken();
						if (token) {
							xhr.setRequestHeader('X-HJ-API-KEY', token);
						}
					}
				})
					.done(resolve)
					.fail(reject);
			});
		}

		loadCheckinsData() {
			return new Promise((resolve, reject) => {
				$.ajax({
					url: `${hjProgress.restUrl}goals/${this.goalId}/checkins`,
					type: 'GET',
					beforeSend: (xhr) => {
						const token = this.getApiToken();
						if (token) {
							xhr.setRequestHeader('X-HJ-API-KEY', token);
						}
					}
				})
					.done(resolve)
					.fail(reject);
			});
		}

		renderGoalSection() {
			if (!this.goalData) return;

			// Update goal text in the display card
			const goalText = this.module.find('.hj-goal-text');
			goalText.text(`"${this.goalData.content.rendered}"`);

			// Update button URLs with goal ID
			this.updateButtonUrls();
		}

		updateButtonUrls() {
			const editUrl = hjProgress.editGoalUrl;
			const completeUrl = hjProgress.completeGoalUrl;

			if (editUrl) {
				const editUrlObj = new URL(editUrl, window.location.origin);
				editUrlObj.searchParams.set('goal_id', this.goalId);
				$('.hj-btn-edit-goal').attr('href', editUrlObj.toString());
			}

			if (completeUrl) {
				const completeUrlObj = new URL(completeUrl, window.location.origin);
				completeUrlObj.searchParams.set('goal_id', this.goalId);
				$('.hj-btn-complete-goal').attr('href', completeUrlObj.toString());
			}
		}

		renderFilters() {
			// Extract all unique rating categories from check-ins
			const allRatingTypes = new Set();

			this.checkinsData.forEach(checkin => {
				if (checkin.ratings && typeof checkin.ratings === 'object') {
					Object.keys(checkin.ratings).forEach(ratingKey => {
						// Remove '_rating' suffix and add to set
						const baseKey = ratingKey.replace('_rating', '');
						allRatingTypes.add(baseKey);
					});
				}
			});

			if (allRatingTypes.size === 0) {
				$('.hj-progress-filters').hide();
				return;
			}

			// Initialize all filters as active by default
			this.activeFilters = Array.from(allRatingTypes);

			const filtersHtml = Array.from(allRatingTypes).map(area => {
				const label = this.formatAreaLabel(area);
				return `
					<button class="hj-filter-btn active" data-filter="${area}">
						${this.escapeHtml(label)}
					</button>
				`;
			}).join('');

			$('.hj-filter-buttons').html(filtersHtml);
			$('.hj-progress-filters').show();
		}

		renderTimeline() {
			if (!this.checkinsData || this.checkinsData.length === 0) {
				this.showNoCheckins();
				return;
			}

			const filteredCheckins = this.filterCheckins();

			if (filteredCheckins.length === 0) {
				// Check if no filters are selected vs no matching results
				if (this.activeFilters.length === 0) {
					this.showNoCheckins('Please select at least one filter to view check-ins.');
				} else {
					this.showNoCheckins('No check-ins match the selected filters.');
				}
				return;
			}

			const timelineHtml = filteredCheckins.map(checkin => {
				return this.renderTimelineItem(checkin);
			}).join('');

			$('.hj-checkins-timeline').html(timelineHtml);
		}

		renderTimelineItem(checkin) {
			const date = new Date(checkin.checkin_date).toLocaleDateString('en-US', {
				year: 'numeric',
				month: 'long',
				day: 'numeric'
			});

			// Build ratings display from the ratings object
			let ratingsHtml = '';
			if (checkin.ratings && typeof checkin.ratings === 'object') {
				ratingsHtml = Object.entries(checkin.ratings).map(([ratingKey, value]) => {
					const baseKey = ratingKey.replace('_rating', '');
					const label = this.formatAreaLabel(baseKey);
					const progressPercentage = (value / 5) * 100; // Convert 1-5 rating to percentage

					return `
						<div class="hj-checkin-rating">
							<div class="hj-rating-bar">
								<div class="hj-rating-fill" style="width: ${progressPercentage}%"></div>
								<div class="hj-rating-fill-dark" style="width: ${100 - progressPercentage}%"></div>
							</div>
							<div class="hj-rating-label">${this.escapeHtml(label)}</div>
						</div>
					`;
				}).join('');
			}

			// Get rating keys for filtering
			const ratingKeys = checkin.ratings ? Object.keys(checkin.ratings).map(key => key.replace('_rating', '')) : [];

			return `
				<div class="hj-timeline-item" data-rating-areas='${JSON.stringify(ratingKeys)}'>
					
					<div class="hj-checkin-content">
                    <div class="hj-checkin-date">${date}</div>
						<div class="hj-checkin-ratings">
							${ratingsHtml}
						</div>
						${checkin.content ? `<div class="hj-checkin-notes">${this.escapeHtml(checkin.content)}</div>` : ''}
					</div>
				</div>
			`;
		}

		filterCheckins() {
			// If no filters are active, show no results
			if (this.activeFilters.length === 0) {
				return [];
			}

			return this.checkinsData.filter(checkin => {
				if (!checkin.ratings) return false;

				// Check if any of the active filters match the rating keys
				const ratingKeys = Object.keys(checkin.ratings).map(key => key.replace('_rating', ''));
				return this.activeFilters.some(filter => ratingKeys.includes(filter));
			});
		}

		handleFilterClick($button) {
			const filter = $button.data('filter');
			console.log('Filter clicked:', filter, 'Currently active:', $button.hasClass('active'));

			if ($button.hasClass('active')) {
				// Remove filter
				this.activeFilters = this.activeFilters.filter(f => f !== filter);
				$button.removeClass('active');
				console.log('Filter deactivated:', filter);
			} else {
				// Add filter
				this.activeFilters.push(filter);
				$button.addClass('active');
				console.log('Filter activated:', filter);
			}

			console.log('Active filters:', this.activeFilters);
			this.renderTimeline();
		}

		handleEditGoal() {
			const goalId = this.goalId;
			if (goalId) {
				// Save goal ID and API token to localStorage for iframe compatibility
				localStorage.setItem('hj_current_goal_id', goalId);

				// Save API token if available
				const token = this.getApiToken();
				if (token) {
					localStorage.setItem('hj_api_token', token);
				}

				window.location.href = `/edit-goal?goal_id=${goalId}`;
				if (!this.goalId) {
					this.showError('No goal selected.');
					return;
				}

				if (confirm('Are you sure you want to mark this goal as complete? This action cannot be undone.')) {
					this.completeGoalViaAPI();
				}
			}
		}
		async completeGoalViaAPI() {
			const $completeBtn = $('.hj-btn-complete-goal');
			const originalText = $completeBtn.text();

			try {
				// Show loading state
				$completeBtn.prop('disabled', true).text('Completing...');

				// Make API call to update goal status
				await this.updateGoalStatus('completed');

				// Show success toast notification
				this.showToast('Goal completed successfully!', 'success');

				// Redirect to dashboard after 5 seconds
				setTimeout(() => {
					window.location.href = '/dashboard';
				}, 5000);

			} catch (error) {
				console.error('Error completing goal:', error);
				this.showToast('Failed to complete goal. Please try again.', 'error');
				$completeBtn.prop('disabled', false).text(originalText);
			}
		}

		updateGoalStatus(status) {
			return new Promise((resolve, reject) => {
				$.ajax({
					url: `${hjProgress.restUrl}goals/${this.goalId}`,
					type: 'POST',
					contentType: 'application/json',
					data: JSON.stringify({ status: status }),
					beforeSend: (xhr) => {
						if (hjProgress.apiToken) {
							xhr.setRequestHeader('X-HJ-API-KEY', hjProgress.apiToken);
						}
					}
				})
					.done((response) => {
						console.log('Goal status updated:', response);
						resolve(response);
					})
					.fail((xhr, textStatus, errorThrown) => {
						console.error('Error updating goal status:', {
							status: xhr.status,
							statusText: xhr.statusText,
							textStatus: textStatus,
							errorThrown: errorThrown,
							responseText: xhr.responseText
						});
						reject(new Error('Failed to update goal status'));
					});
			});
		}


		showToast(message, type = 'success') {
			// Remove any existing toasts
			$('.hj-toast').remove();

			const bgColor = type === 'success' ? '#10b981' : '#ef4444';
			const iconColor = type === 'success' ? '#ffffff' : '#ffffff';

			// Create toast element
			const $toast = $(`
			<div class="hj-toast" style="
				position: fixed;
				top: 20px;
				right: 20px;
				background: ${bgColor};
				color: white;
				padding: 16px 24px;
				border-radius: 8px;
				box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);
				z-index: 9999;
				font-size: 16px;
				font-weight: 500;
				min-width: 300px;
				max-width: 500px;
				display: flex;
				align-items: center;
				gap: 12px;
				animation: slideInRight 0.3s ease-out;
			">
				<span style="font-size: 24px;">${type === 'success' ? '✓' : '✕'}</span>
				<span>${this.escapeHtml(message)}</span>
			</div>
		`);

			// Add to body
			$('body').append($toast);

			// Auto-remove after 5 seconds
			setTimeout(() => {
				$toast.css('animation', 'slideOutRight 0.3s ease-in');
				setTimeout(() => $toast.remove(), 300);
			}, 5000);
		}
		showLoading() {
			$('.hj-progress-loading').show();
			$('.hj-progress-error').hide();
			$('.hj-progress-filters').hide();
			$('.hj-checkins-timeline').empty();
		}

		hideLoading() {
			$('.hj-progress-loading').hide();
		}

		showError(message) {
			$('.hj-progress-loading').hide();
			$('.hj-progress-error .hj-error-message').text(message);
			$('.hj-progress-error').show();
		}

		showNoCheckins(message = 'No check-ins found yet.') {
			$('.hj-checkins-timeline').html(`
				<div class="hj-no-checkins">
					<h3>No Check-ins Yet</h3>
					<p>${this.escapeHtml(message)}</p>
				</div>
			`);
		}

		formatAreaLabel(value) {
			// Convert snake_case to readable format
			return value.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
		}

		escapeHtml(text) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, m => map[m]);
		}

		getApiToken() {
			// Get API token from localized script
			if (typeof hjProgress !== 'undefined' && hjProgress.apiToken) {
				return hjProgress.apiToken;
			}

			// Fallback to meta tag
			const tokenElement = $('meta[name="hj-api-token"]');
			if (tokenElement.length) {
				return tokenElement.attr('content');
			}

		}
	}
	// Initialize the 
	// progress tracker
	new HJProgressTracker();

})(jQuery);