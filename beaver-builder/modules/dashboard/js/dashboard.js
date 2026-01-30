/**
 * Dashboard Module Frontend JavaScript
 */

(function($) {
	'use strict';

	/**
	 * Dashboard functionality
	 */
	class HJDashboard {
		constructor() {
			this.module = null;
			this.goalData = null;
			this.currentRating = 3;
			this.init();
		}

		init() {
			$(document).ready(() => {
				this.initializeModule();
				this.bindEvents();
				this.loadGoalData();
			});
		}

		initializeModule() {
			this.module = $('.hj-dashboard-module');
			if (!this.module.length) {
				console.error('Dashboard module not found');
				return;
			}
		}

		bindEvents() {
			// Rating slider interaction
			$(document).on('mousedown', '.hj-slider-thumb', this.handleSliderStart.bind(this));
			$(document).on('click', '.hj-slider-track', this.handleTrackClick.bind(this));
			
			// Button clicks
			$(document).on('click', '.hj-btn-edit-goal', this.handleEditGoal.bind(this));
			$(document).on('click', '.hj-btn-complete-goal', this.handleCompleteGoal.bind(this));
			$(document).on('click', '.hj-btn-submit', this.handleSubmit.bind(this));
			$(document).on('click', '.hj-btn-full-checkin', this.handleFullCheckin.bind(this));
		}

		async loadGoalData() {
			this.showLoading();
			console.log("Starting to load goal data");	
			
			try {
				// Get the latest in-progress goal
				const latestGoal = await this.getLatestInProgressGoal();
				console.log("Loading Goal Data");
				
				if (latestGoal) {
					this.goalData = latestGoal;
					this.updateGoalDisplay();
					this.showActiveGoalUI();
				} else {
					// No active goals found
					this.goalData = null;
					this.showNoGoalUI();
				}
				
				this.hideLoading();
				
			} catch (error) {
				console.error('Error loading goal data:', error);
				this.hideLoading();
			}
		}

		async getLatestInProgressGoal() {
			console.log("checking Goals");
			return new Promise((resolve, reject) => {
				$.ajax({
					url: `${hjGoalAjax.restUrl}goals?status=in-progress&orderby=date&order=desc&per_page=1`,
					type: 'GET',
					beforeSend: (xhr) => {
						if (hjGoalAjax.apiToken) {
							xhr.setRequestHeader('X-HJ-API-KEY', hjGoalAjax.apiToken);
						}
					}
				})
				.done((goals) => {
					console.log("Goals");
					resolve(goals.length > 0 ? goals[0] : null);
				})
				.fail(reject);
			});
		}

		updateGoalDisplay() {
			if (!this.goalData) return;
			console.log(this.goalData);
			const goalText = this.module.find('.hj-goal-text');
			if (this.goalData.title && this.goalData.title.rendered) {
				goalText.text(`"${this.goalData.content.rendered}"`);
			} else if (this.goalData.content && this.goalData.content.rendered) {
				goalText.text(`"${this.goalData.content.rendered}"`);
			}
		}

		showNoGoalUI() {
			// Update goal text
			this.module.find('.hj-goal-text').text('"You haven\'t set a goal yet."');
			
			// Replace Edit and Complete buttons with New Goal button
			const $goalActions = this.module.find('.hj-goal-actions');
			$goalActions.html(`
				<button type="button" class="hj-btn hj-btn-dark hj-btn-new-goal">
					New Goal
				</button>
			`);
			
			// Hide rating section since there's no goal to check in
			this.module.find('.hj-rating-section').hide();
			
			// Bind new goal button event
			$(document).off('click', '.hj-btn-new-goal');
			$(document).on('click', '.hj-btn-new-goal', this.handleNewGoal.bind(this));
		}

		showActiveGoalUI() {
			// Show rating section
			this.module.find('.hj-rating-section').show();
			
			// Ensure Edit and Complete buttons are visible
			const $goalActions = this.module.find('.hj-goal-actions');
			if (!$goalActions.find('.hj-btn-edit-goal').length) {
				$goalActions.html(`
					<button type="button" class="hj-btn hj-btn-dark hj-btn-edit-goal">
						Edit Goal
					</button>
					<button type="button" class="hj-btn hj-btn-outline hj-btn-complete-goal">
						Complete Goal
					</button>
				`);
			}
		}

		handleSliderStart(e) {
			e.preventDefault();
			const $thumb = $(e.currentTarget);
			const $track = $thumb.closest('.hj-rating-slider').find('.hj-slider-track');
			
			const handleMouseMove = (moveEvent) => {
				this.updateSliderPosition(moveEvent, $track, $thumb);
			};
			
			const handleMouseUp = () => {
				$(document).off('mousemove', handleMouseMove);
				$(document).off('mouseup', handleMouseUp);
			};
			
			$(document).on('mousemove', handleMouseMove);
			$(document).on('mouseup', handleMouseUp);
		}

		handleTrackClick(e) {
			const $track = $(e.currentTarget);
			const $thumb = $track.siblings('.hj-slider-thumb');
			this.updateSliderPosition(e, $track, $thumb);
		}

		updateSliderPosition(e, $track, $thumb) {
			const trackRect = $track[0].getBoundingClientRect();
			const trackWidth = trackRect.width;
			const clickX = e.clientX - trackRect.left;
			
			// Calculate position as percentage
			let percentage = Math.max(0, Math.min(100, (clickX / trackWidth) * 100));
			
			// Snap to nearest 25% (5 positions: 0%, 25%, 50%, 75%, 100%)
			const step = 25;
			percentage = Math.round(percentage / step) * step;
			
			// Convert to rating value (1-5)
			const rating = Math.max(1, Math.min(5, Math.round((percentage / 100) * 4) + 1));
			
			// Update UI
			this.updateSliderUI(percentage, rating);
			this.currentRating = rating;
		}

		updateSliderUI(percentage, rating) {
			const $fill = this.module.find('.hj-slider-fill');
			const $thumb = this.module.find('.hj-slider-thumb');
			const $input = this.module.find('.hj-rating-input');
			const $thumbValue = $thumb.find('.hj-thumb-value');
			
			$fill.css('width', percentage + '%');
			$thumb.css('left', `calc(${percentage}% - 16px)`);
			$thumb.attr('data-value', rating);
			$thumbValue.text(rating);
			$input.val(rating);
		}

		handleEditGoal() {
			const editUrl = this.module.data('edit-goal-url');
			if (editUrl && this.goalData) {
			// Save goal ID to localStorage for iframe compatibility
			localStorage.setItem('hj_current_goal_id', this.goalData.id);
			//redirect to edit page with goal_id param
			const url = new URL(editUrl, window.location.origin);
			url.searchParams.set('goal_id', this.goalData.id);
			window.location.href = url.toString();	
			
			}
		}

		handleNewGoal() {
			window.location.href = '/new-goal';
		}

	handleCompleteGoal() {
		if (!this.goalData) {
			this.showMessage('No goal found.', 'error');
			return;
		}

		if (confirm('Are you sure you want to mark this goal as complete? This action cannot be undone.')) {
			this.completeGoalViaAPI();
		}
	}

	async completeGoalViaAPI() {
		const $completeBtn = this.module.find('.hj-btn-complete-goal');
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
				url: `${hjGoalAjax.restUrl}goals/${this.goalData.id}`,
				type: 'POST',
				contentType: 'application/json',
				data: JSON.stringify({ status: status }),
				beforeSend: (xhr) => {
					if (hjGoalAjax.apiToken) {
						xhr.setRequestHeader('X-HJ-API-KEY', hjGoalAjax.apiToken);
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
	}		async handleSubmit() {
			if (!this.goalData) {
				this.showToast('No goal found. Please create a goal first.', 'error');
				return;
			}

			const $submitBtn = this.module.find('.hj-btn-submit');
			const originalText = $submitBtn.text();
			
			try {
				$submitBtn.text('Submitting...').prop('disabled', true);
				
				// Create quick check-in
				await this.createQuickCheckin();
				
				this.showMessage('Check-in submitted successfully!', 'success');
				
				// Redirect after delay
				setTimeout(() => {
					const successUrl = this.module.data('success-redirect-url');
					if (successUrl) {
						window.location.href = successUrl;
					}
				}, 1500);
				
			} catch (error) {
				console.error('Error submitting check-in:', error);
				this.showMessage('Failed to submit check-in. Please try again.', 'error');
			} finally {
				$submitBtn.text(originalText).prop('disabled', false);
			}
		}

		async createQuickCheckin() {
			const today = new Date().toISOString().split('T')[0];
			
			return new Promise((resolve, reject) => {
				$.ajax({
					url: `${hjGoalAjax.restUrl}checkins`,
					type: 'POST',
					beforeSend: (xhr) => {
						if (hjGoalAjax.apiToken) {
							xhr.setRequestHeader('X-HJ-API-KEY', hjGoalAjax.apiToken);
						}
					},
					data: {
						goal_id: this.goalData.id,
						checkin_date: today,
						notes: 'Quick dashboard check-in',
						ratings: {
							'bodyweight_rating': this.currentRating
						}
					}
				})
				.done(resolve)
				.fail(reject);
			});
		}

		handleFullCheckin() {
			const fullCheckinUrl = this.module.data('full-checkin-url');
			if (fullCheckinUrl && this.goalData) {
				const url = new URL(fullCheckinUrl, window.location.origin);
				url.searchParams.set('goal_id', this.goalData.id);
				window.location.href = url.toString();
			}
		}

		showLoading() {
			this.module.find('.hj-dashboard-content').hide();
			this.module.find('.hj-dashboard-loading').show();
		}

		hideLoading() {
			this.module.find('.hj-dashboard-loading').hide();
			this.module.find('.hj-dashboard-content').show();
		}

		showMessage(message, type = 'success') {
			const $messageDiv = this.module.find('.hj-dashboard-message');
			const $messageContent = $messageDiv.find('.hj-message-content');
			
			$messageContent.text(message);
			$messageDiv.removeClass('hj-message-success hj-message-error')
					   .addClass(`hj-message-${type}`)
					   .show();
			
			// Auto-hide after 5 seconds
			setTimeout(() => {
				$messageDiv.fadeOut();
			}, 5000);
		}

		showToast(message, type = 'success') {
			// Remove any existing toasts
			$('.hj-toast').remove();
			
			const bgColor = type === 'success' ? '#10b981' : '#ef4444';
			
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
					<span>${message}</span>
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

		/**
		 * Initialize courses section
		 */
		initializeCourses() {
			// Add click tracking for course cards
			$('.hj-course-btn').on('click', function(e) {
				const courseTitle = $(this).closest('.hj-course-card').find('.hj-course-title').text();
				
				// Track course click (you can integrate with analytics here)
				console.log('Course clicked:', courseTitle);
				
				// Add visual feedback
				$(this).addClass('hj-btn-clicked');
				setTimeout(() => {
					$(this).removeClass('hj-btn-clicked');
				}, 200);
			});
			
			// Animate course cards on scroll (if visible)
			if (window.IntersectionObserver) {
				const courseObserver = new IntersectionObserver((entries) => {
					entries.forEach(entry => {
						if (entry.isIntersecting) {
							entry.target.style.opacity = '1';
							entry.target.style.transform = 'translateY(0)';
						}
					});
				}, {
					threshold: 0.1,
					rootMargin: '50px'
				});
				
				$('.hj-course-card').each(function() {
					this.style.opacity = '0';
					this.style.transform = 'translateY(20px)';
					this.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
					courseObserver.observe(this);
				});
			}
		}
	}

	// Initialize the dashboard
	new HJDashboard();

})(jQuery);