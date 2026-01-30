/**
 * Check In Module Frontend JavaScript
 */

(function ($) {
	'use strict';

	class HJCheckInForm {
		constructor() {
			this.form = null;
			this.submitBtn = null;
			this.messageContainer = null;
			this.goalSelect = null;
			this.goalPreview = null;
			this.init();
		}

		init() {
			$(document).ready(() => {
				this.bindEvents();
				this.initializeForm();
				this.loadGoals();
			});
		}

		initializeForm() {
			// Initialize form references
			this.form = $('#hj-check-in-form');
			this.submitBtn = this.form.find('.hj-btn-save');
			this.messageContainer = this.form.find('.hj-form-message');
			this.goalSelect = $('#hj-goal-select');
			this.goalPreview = $('.hj-selected-goal-preview');

			// Ensure message container exists
			if (!this.messageContainer.length) {
				console.warn(this.getTranslation('messageContainerNotFound'));
				this.messageContainer = $('<div class="hj-form-message" style="display: none;"><div class="hj-message-content"></div></div>');
				this.form.find('.hj-check-in-textarea').after(this.messageContainer);
			}
		}

		bindEvents() {
			$(document).on('submit', '#hj-check-in-form', (e) => {
				this.handleFormSubmit(e);
			});

			$(document).on('click', '.hj-btn-cancel', (e) => {
				this.handleCancel(e);
			});

			// Goal action buttons
			$(document).on('click', '.hj-btn-edit-goal', (e) => {
				this.handleEditGoal(e);
			});

			$(document).on('click', '.hj-btn-complete-goal', (e) => {
				this.handleCompleteGoal(e);
			});

			$(document).on('click', '.hj-btn-new-goal', (e) => {
				this.handleNewGoal(e);
			});

			// Slider interactions
			$(document).on('mousedown', '.hj-slider-thumb', (e) => {
				this.handleSliderStart(e);
			});

			$(document).on('click', '.hj-slider-track', (e) => {
				this.handleSliderClick(e);
			});

			// Real-time validation
			$(document).on('input', '#hj-check-in-notes', () => {
				this.validateForm();
			});
		}

		loadGoals() {
			// Load user's goals from API
			$.ajax({
				url: '/wp-json/healthyjoint/v1/goals',
				type: 'GET',
				beforeSend: (xhr) => {
					const token = this.getApiToken();
					if (token) {
						xhr.setRequestHeader('X-HJ-API-KEY', token);
					}
				}
			})
				.done((response) => {
					this.populateGoals(response);
				})
				.fail(() => {
					this.goalSelect.html(`<option value="">${this.getTranslation('noGoalsFound')}</option>`);
					this.showMessage(this.getTranslation('unableToLoadGoals'), 'error');
				});
		}

		populateGoals(goals) {
			console.log('Loading goals:', goals);
			const goalSelect = this.goalSelect;
			goalSelect.empty();

			if (goals && goals.length > 0) {
				// Auto-select the first goal
				const firstGoal = goals[0];
				const option = $('<option></option>')
					.val(firstGoal.id)
					.text(firstGoal.title.rendered)
					.data('goal', firstGoal)
					.prop('selected', true);
				goalSelect.append(option);

				// Display the goal in the UI
				this.displaySelectedGoal(firstGoal);

				// Show all sections when goals exist
				$('.hj-goal-content, .hj-bottom-section, .hj-notes-section, .hj-form-actions-bottom').show();

				// Show normal goal action buttons
				$('.hj-goal-actions').html(`
					<button type="button" class="hj-btn hj-btn-edit-goal">${this.getTranslation('editGoal')}</button>
					<button type="button" class="hj-btn hj-btn-complete-goal">${this.getTranslation('completeGoal')}</button>
				`);

				// Update question text for active goals
				$('.hj-question-text').text(this.getTranslation('workingTowardsGoal'));
				$('.hj-question-subtitle').text(this.getTranslation('editOrCompleteGoal'));

			} else {
				goalSelect.append(`<option value="">${this.getTranslation('noActiveGoalsAvailable')}</option>`);

				// Hide the priority section and notes section when no goals
				$('.hj-bottom-section').hide();

				// Update the goal content to show no goals message
				$('.hj-goal-text').text(this.getTranslation('noCurrentActiveGoal'));

				// Update question section for no goals state
				$('.hj-question-text').text(this.getTranslation('wouldLikeNewGoal'));
				$('.hj-question-subtitle').text(this.getTranslation('createFirstGoal'));

				// Show only "New Goal" button
				$('.hj-goal-actions').html(`
					<button type="button" class="hj-btn hj-btn-new-goal">${this.getTranslation('newGoal')}</button>
				`);

				// Keep goal content section visible but hide form actions
				$('.hj-goal-content').show();
				$('.hj-form-actions-bottom').hide();
			}
		}

		displaySelectedGoal(goal) {
			$('.hj-goal-text').text(goal.content.rendered || this.getTranslation('noGoalDescriptionAvailable'));
		}

		handleEditGoal(e) {
			e.preventDefault();
			// Redirect to edit goal page or show edit modal
			const goalId = this.goalSelect.val();
			console.log(goalId);
			if (goalId) {
			// Save goal ID and API token to localStorage for iframe compatibility
			localStorage.setItem('hj_current_goal_id', goalId);
			
			// Save API token if available
			const token = this.getApiToken();
			if (token) {
				localStorage.setItem('hj_api_token', token);
			}
			
			window.location.href = '/edit-goal?goal_id=' + goalId;
		} else {
			alert(this.getTranslation('navigateToGoalEditing'));
		}
				if (goalId) {
					this.updateGoalStatus(goalId, 'completed');
				}
			}
		}

		handleNewGoal(e) {
			e.preventDefault();
			// Redirect to the Add Goal module/page
			// This could be a specific page URL or relative path where the Add Goal module is located
			const newGoalUrl = this.getNewGoalUrl();
			if (newGoalUrl) {
				window.location.href = newGoalUrl;
			} else {
				// Fallback - alert to let user know where to go
				alert(this.getTranslation('navigateToGoalCreation'));
			}
		}

		updateGoalStatus(goalId, status) {
			$.ajax({
				url: `/wp-json/healthyjoint/v1/goals/${goalId}`,
				type: 'PUT',
				contentType: 'application/json',
				data: JSON.stringify({ status: status }),
				beforeSend: (xhr) => {
					const token = this.getApiToken();
					if (token) {
						xhr.setRequestHeader('X-HJ-API-KEY', token);
					}
				}
			})
				.done((response) => {
					console.log('Goal status updated:', response);

					if (status === 'completed') {
						// Show success toast notification
						this.showToast('Goal completed successfully!', 'success');

						// Redirect to dashboard after 5 seconds
						setTimeout(() => {
							window.location.href = '/dashboard';
						}, 5000);
					} else {
						this.showMessage(this.getTranslation('goalStatusUpdated'), 'success');
					}
				})
				.fail((xhr, textStatus, errorThrown) => {
					console.error('Error updating goal status:', {
						status: xhr.status,
						statusText: xhr.statusText,
						textStatus: textStatus,
						errorThrown: errorThrown,
						responseText: xhr.responseText
					});

					let errorMessage = this.getTranslation('failedToUpdateGoalStatus');
					try {
						const errorResponse = JSON.parse(xhr.responseText);
						errorMessage = errorResponse.message || errorMessage;
					} catch (e) {
						// Use default error message
					}

					this.showToast(errorMessage, 'error');
				});
		} 
		handleSliderStart(e) {
			e.preventDefault();
			const thumb = $(e.target);
			const track = thumb.closest('.hj-slider-container').find('.hj-slider-track');

			this.isDragging = true;
			this.currentSlider = { thumb, track };

			$(document).on('mousemove.slider', (moveEvent) => {
				this.handleSliderMove(moveEvent);
			});

			$(document).on('mouseup.slider', () => {
				this.handleSliderEnd();
			});
		}

		handleSliderMove(e) {
			if (!this.isDragging || !this.currentSlider) return;

			const { thumb, track } = this.currentSlider;
			const trackRect = track[0].getBoundingClientRect();
			const mouseX = e.clientX;
			const trackLeft = trackRect.left;
			const trackWidth = trackRect.width;

			// Calculate position as percentage
			let percentage = ((mouseX - trackLeft) / trackWidth) * 100;
			percentage = Math.max(0, Math.min(100, percentage));

			// Convert to 1-5 scale
			const value = Math.round((percentage / 100) * 4) + 1;
			const actualPercentage = ((value - 1) / 4) * 100;

			// Update thumb position
			thumb.css('left', actualPercentage + '%');
			thumb.attr('data-value', value);

			// Update fill
			const fill = track.find('.hj-slider-fill');
			fill.css('width', actualPercentage + '%');

			// Update hidden input
			const ratingItem = thumb.closest('.hj-priority-rating-item');
			const hiddenInput = ratingItem.find('.hj-rating-value');
			hiddenInput.val(value);
		}

		handleSliderEnd() {
			this.isDragging = false;
			this.currentSlider = null;
			$(document).off('mousemove.slider');
			$(document).off('mouseup.slider');
			this.validateForm();
		}

		handleSliderClick(e) {
			if ($(e.target).hasClass('hj-slider-thumb')) return;

			const track = $(e.target);
			const trackRect = track[0].getBoundingClientRect();
			const mouseX = e.clientX;
			const trackLeft = trackRect.left;
			const trackWidth = trackRect.width;

			// Calculate position as percentage
			let percentage = ((mouseX - trackLeft) / trackWidth) * 100;
			percentage = Math.max(0, Math.min(100, percentage));

			// Convert to 1-5 scale
			const value = Math.round((percentage / 100) * 4) + 1;
			const actualPercentage = ((value - 1) / 4) * 100;

			// Update thumb position
			const thumb = track.find('.hj-slider-thumb');
			thumb.css('left', actualPercentage + '%');
			thumb.attr('data-value', value);

			// Update fill
			const fill = track.find('.hj-slider-fill');
			fill.css('width', actualPercentage + '%');

			// Update hidden input
			const ratingItem = track.closest('.hj-priority-rating-item');
			const hiddenInput = ratingItem.find('.hj-rating-value');
			hiddenInput.val(value);

			this.validateForm();
		}

		handleFormSubmit(e) {
			e.preventDefault();

			this.form = $(e.target);
			this.submitBtn = this.form.find('.hj-btn-save');
			this.messageContainer = this.form.find('.hj-form-message');

			if (!this.validateForm()) {
				return false;
			}

			this.setLoadingState(true);
			this.hideMessage();

			const formData = this.collectFormData();

			this.submitCheckIn(formData)
				.then(response => {
					this.handleSuccess(response);
				})
				.catch(error => {
					this.handleError(error);
				})
				.finally(() => {
					this.setLoadingState(false);
				});
		}

		handleCancel(e) {
			e.preventDefault();

			if (confirm(this.getTranslation('confirmCancel'))) {
				this.resetForm();
				// Optionally redirect or close modal
				const redirectUrl = this.getRedirectUrl();
				if (redirectUrl) {
					window.location.href = redirectUrl;
				}
			}
		}

		validateForm() {
			const goalId = $('#hj-goal-select').val();
			const ratings = this.getAllRatings();
			const hasAnyRating = Object.values(ratings).some(rating => rating > 0);

			let isValid = true;
			const errors = [];

			if (!goalId) {
				errors.push(this.getTranslation('noGoalSelected'));
				isValid = false;
			}

			// We don't require ratings, they're optional

			if (!isValid) {
				this.showMessage(errors.join(', '), 'error');
			} else {
				this.hideMessage();
			}

			return isValid;
		}

		getAllRatings() {
			const ratings = {};
			$('.hj-rating-value').each(function () {
				const name = $(this).attr('name');
				const value = parseInt($(this).val()) || 3; // Default to 3 if not set
				if (name) {
					ratings[name] = value;
				}
			});
			return ratings;
		}

		collectFormData() {
			const goalId = $('#hj-goal-select').val();
			const notes = $('#hj-check-in-notes').val().trim();
			const ratings = this.getAllRatings();

			// Get current date for check-in
			const checkInDate = new Date().toISOString().split('T')[0];

			return {
				goal_id: parseInt(goalId),
				checkin_date: checkInDate,
				notes: notes,
				ratings: ratings,
				status: 'published'
			};
		}

		submitCheckIn(data) {
			return new Promise((resolve, reject) => {
				$.ajax({
					url: '/wp-json/healthyjoint/v1/checkins',
					type: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(data),
					beforeSend: (xhr) => {
						const token = this.getApiToken();
						if (token) {
							xhr.setRequestHeader('X-HJ-API-KEY', token);
						}
					}
				})
					.done((response) => {
						if (response.success || response.id) {
							resolve(response);
						} else {
							reject(new Error(response.message || this.getTranslation('failedToCreateCheckIn')));
						}
					})
					.fail((xhr) => {
						let errorMessage = this.getTranslation('errorCreatingCheckIn');

						try {
							const errorResponse = JSON.parse(xhr.responseText);
							errorMessage = errorResponse.message || errorMessage;
						} catch (e) {
							// Use default error message
						}

						reject(new Error(errorMessage));
					});
			});
		}

		handleSuccess(response) {
			this.showMessage(this.getTranslation('checkInSavedSuccessfully'), 'success');

			// Reset form after short delay
			setTimeout(() => {
				const redirectUrl = this.getRedirectUrl();
				if (redirectUrl) {
					window.location.href = redirectUrl;
				} else {
					this.resetForm();
				}
			}, 2000);
		}

		handleError(error) {
			console.error('Check-in creation error:', error);
			this.showMessage(error.message, 'error');
		}

		setLoadingState(isLoading) {
			if (isLoading) {
				this.submitBtn.prop('disabled', true);
				this.submitBtn.find('.hj-btn-text').text(this.getTranslation('saving'));
				this.submitBtn.find('.hj-btn-spinner').show();
			} else {
				this.submitBtn.prop('disabled', false);
				this.submitBtn.find('.hj-btn-text').text(this.getTranslation('saveCheckIn'));
				this.submitBtn.find('.hj-btn-spinner').hide();
			}
		}

		showMessage(message, type) {
			// Ensure message container is available
			if (!this.messageContainer || !this.messageContainer.length) {
				this.messageContainer = $('.hj-form-message');
			}

			// Create message container if it still doesn't exist
			if (!this.messageContainer || !this.messageContainer.length) {
				console.warn(this.getTranslation('messageContainerNotFound'));
				this.messageContainer = $('<div class="hj-form-message" style="display: none;"><div class="hj-message-content"></div></div>');
				$('#hj-check-in-notes').after(this.messageContainer);
			}

			const messageClass = type === 'success' ? 'hj-success' : 'hj-error';

			// Ensure message content container exists
			let messageContent = this.messageContainer.find('.hj-message-content');
			if (!messageContent.length) {
				messageContent = $('<div class="hj-message-content"></div>');
				this.messageContainer.append(messageContent);
			}

			this.messageContainer
				.removeClass('hj-success hj-error')
				.addClass(messageClass);

			messageContent.text(message);

			// Show the message with animation
			this.messageContainer.stop(true, true).slideDown(300);

			// Auto-hide success messages
			if (type === 'success') {
				setTimeout(() => {
					this.hideMessage();
				}, 5000);
			}
		}

		hideMessage() {
			if (this.messageContainer && this.messageContainer.length) {
				this.messageContainer.stop(true, true).slideUp(300, function () {
					$(this).removeClass('hj-success hj-error');
				});
			}
		}

		resetForm() {
			if (this.form && this.form.length) {
				this.form[0].reset();
				this.hideMessage();

				// Reset all sliders to middle position (value 3)
				$('.hj-slider-thumb').each(function () {
					$(this).css('left', '50%').attr('data-value', 3);
				});

				$('.hj-slider-fill').each(function () {
					$(this).css('width', '50%');
				});

				// Reset all rating values to 3
				$('.hj-rating-value').val(3);

				// Clear notes textarea
				$('#hj-check-in-notes').val('');

				// Reload the first goal
				this.loadGoals();
			}
		}

		getApiToken() {
			// Get API token from localized script
			if (typeof hjCheckIn !== 'undefined' && hjCheckIn.apiToken) {
				return hjCheckIn.apiToken;
			}

			// Fallback to meta tag
			const tokenElement = $('meta[name="hj-api-token"]');
			if (tokenElement.length) {
				return tokenElement.attr('content');
		}

		// Last resort - try to get from WordPress global
		if (typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) {
			return wpApiSettings.nonce;
		}

		console.warn(this.getTranslation('wpNonceNotFound'));
		return '';
		}

		getNewGoalUrl() {
			// Get new goal URL from module settings or global variable
			if (typeof hjCheckIn !== 'undefined' && hjCheckIn.newGoalUrl) {
				return hjCheckIn.newGoalUrl;
			}

			// Fallback to data attribute
			const form = $('.hj-check-in-form');
			return form.data('new-goal-url') || '';
		}

		getTranslation(key) {
			// Get translation from localized strings
			if (typeof hjCheckIn !== 'undefined' && hjCheckIn.strings && hjCheckIn.strings[key]) {
				return hjCheckIn.strings[key];
			}

			// Fallback to key if translation not found
			console.warn(`Translation not found for key: ${key}`);
			return key;
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
	}

	// Initialize the form handler
	new HJCheckInForm();

}) (jQuery);