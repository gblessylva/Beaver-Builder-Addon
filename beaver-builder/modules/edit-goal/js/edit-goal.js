/**
 * Edit Goal Module Frontend JavaScript
 */

(function ($) {
	'use strict';

	class HJEditGoalForm {
		constructor() {
			this.form = null;
			this.submitBtn = null;
			this.messageContainer = null;
			this.goalId = null;
			this.init();
		}

		init() {
	

			$(document).ready(() => {

			// Debug: Log all localized variables. 
			// Delete  after debugging is complete.	
			console.log('=== hjEditGoal Localized Data ===');
			console.log('hjEditGoal exists:', typeof hjEditGoal !== 'undefined');
			if (typeof hjEditGoal !== 'undefined') {
				console.log('hjEditGoal object:', hjEditGoal);
				console.log('apiToken:', hjEditGoal.apiToken);
				console.log('goalId:', hjEditGoal.goalId);
				console.log('restUrl:', hjEditGoal.restUrl);
				console.log('redirectUrl:', hjEditGoal.redirectUrl);
			} else {
				console.error('hjEditGoal is not defined!');
			}
			console.log('================================');
			
				this.bindEvents();
				this.initializeForm();
				this.loadGoalData();
			});
		}

		initializeForm() {
			// Initialize form references with better error handling
			this.form = $('#hj-goal-form');
			if (!this.form.length) {
				console.error('Edit Goal form not found');
				return;
			}

			this.submitBtn = this.form.find('.hj-btn-save');
			this.messageContainer = this.form.find('.hj-form-message');
			this.goalId = this.getGoalId();

			// Ensure message container exists
			if (!this.messageContainer.length) {
				console.warn('Message container not found, creating one');
				this.messageContainer = $('<div class="hj-form-message" style="display: none;"><div class="hj-message-content"></div></div>');
				this.form.find('.hj-goal-textarea').after(this.messageContainer);
			}

			// Verify goal description element exists
			const goalDescElement = $('#hj-goal-description');
			if (!goalDescElement.length) {
				console.error('Goal description element (#hj-goal-description) not found');
				this.showMessage('Form initialization error. Please refresh the page.', 'error');
				return;
			}
		}

		getGoalId() {
			// get goalid from loacal storage for iframe compatibility
			const storedGoalId = localStorage.getItem('hj_current_goal_id');
			if (storedGoalId) {
				return parseInt(storedGoalId, 10);
			}

		}

		loadGoalData() {
			console.log("Load goal initialised");
			if (!this.goalId || this.goalId <= 0) {
				this.showMessage('No goal ID provided or invalid goal ID.', 'error');
				return;
			}

			// Show loading state
			this.setLoadingState(true, 'Loading goal...');
			console.log("calling Ajax ");
			$.ajax({
				url: `/wp-json/healthyjoint/v1/goals/${this.goalId}`,
				type: 'GET',
				beforeSend: (xhr) => {
					console.log("Checking for token");
					const token = this.getApiToken();
					console.log("Token is ", token);
					if (token) {
						xhr.setRequestHeader('X-HJ-API-KEY', token);
					}
					else {
						console.warn('API token not found. Request may fail.');
					}
				}
			})
				.done((response) => {
					this.populateForm(response);
					this.setLoadingState(false);
				})
				.fail((xhr) => {
					console.error('Error loading goal:', xhr);
					let errorMessage = 'Unable to load goal. Please refresh the page and try again.';

					if (xhr.status === 404) {
						errorMessage = 'Goal not found or you do not have permission to edit it.';
					} else if (xhr.status === 403) {
						errorMessage = 'You do not have permission to edit this goal.';
					}

					this.showMessage(errorMessage, 'error');
					this.setLoadingState(false);
				});
		}

		populateForm(goalData) {
			try {
				// Populate goal description
				const { rendered: goalDescription } = goalData.content;
				if (goalDescription) {
					const goalDescElement = $('#hj-goal-description');
					if (goalDescElement.length) {
						goalDescElement.val(goalDescription);
					} else {
						console.error('Goal description element not found during population');
						this.showMessage('Error loading goal data - form element missing.', 'error');
						return;
					}
				}

				// Populate focus areas
				if (goalData.focus_areas && Array.isArray(goalData.focus_areas)) {
					// First, uncheck all checkboxes and remove selected styling
					$('input[name="focus_areas[]"]').prop('checked', false);
					$('.hj-priority-option').removeClass('hj-selected');
					$('.hj-priority-label').each(function () {
						const $label = $(this);
						$label.text($label.text().replace('✓ ', ''));
					});

					// Then check the goal's focus areas
					goalData.focus_areas.forEach((area) => {
						const checkbox = $(`input[name="focus_areas[]"][value="${area}"]`);
						if (checkbox.length) {
							checkbox.prop('checked', true);
							const label = checkbox.closest('.hj-priority-option');
							label.addClass('hj-selected');
							const labelText = label.find('.hj-priority-label');
							if (!labelText.text().includes('✓')) {
								labelText.text('✓ ' + labelText.text());
							}
						}
					});
				}

				// Update goal type dropdowns based on selected focus areas
				this.updateGoalTypeDropdowns();

				// Set goal types if available
				if (goalData.goal_type) {
					// If single goal type, apply to all dropdowns
					$(`.hj-goal-select`).val(goalData.goal_type);
				}

			} catch (error) {
				console.error('Error populating form:', error);
				this.showMessage('Error loading goal data. Please refresh the page and try again.', 'error');
			}
		}

		bindEvents() {
			$(document).on('submit', '#hj-goal-form', (e) => {
				this.handleFormSubmit(e);
			});

			$(document).on('click', '.hj-btn-cancel', (e) => {
				this.handleCancel(e);
			});

			// Priority area selection with max 2 limit
			$(document).on('change', 'input[name="focus_areas[]"]', (e) => {
				this.handlePrioritySelection(e);
			});

			// Real-time validation
			$(document).on('input', '#hj-goal-description', () => {
				this.validateForm();
			});
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

			this.updateGoal(formData)
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

			if (confirm('Are you sure you want to cancel? All changes will be lost.')) {
				// Optionally redirect or close modal
				const redirectUrl = this.getRedirectUrl();
				if (redirectUrl) {
					window.location.href = redirectUrl;
				} else {
					window.history.back();
				}
			}
		}

		handlePrioritySelection(e) {
			const checkbox = $(e.target);
			const label = checkbox.closest('.hj-priority-option');
			const checkedBoxes = $('input[name="focus_areas[]"]:checked');

			// Update visual state
			if (checkbox.is(':checked')) {
				label.addClass('hj-selected');
				// Add checkmark to label
				const labelText = label.find('.hj-priority-label');
				if (!labelText.text().includes('✓')) {
					labelText.text('✓ ' + labelText.text());
				}

				// If more than 2 are selected, uncheck the first one
				if (checkedBoxes.length > 2) {
					const firstChecked = checkedBoxes.first();
					firstChecked.prop('checked', false);
					const firstLabel = firstChecked.closest('.hj-priority-option');
					firstLabel.removeClass('hj-selected');
					// Remove checkmark
					const firstLabelText = firstLabel.find('.hj-priority-label');
					firstLabelText.text(firstLabelText.text().replace('✓ ', ''));
				}
			} else {
				label.removeClass('hj-selected');
				// Remove checkmark from label
				const labelText = label.find('.hj-priority-label');
				labelText.text(labelText.text().replace('✓ ', ''));
			}

			// Update the goal type dropdowns
			this.updateGoalTypeDropdowns();
		}

		updateGoalTypeDropdowns() {
			const checkedAreas = $('input[name="focus_areas[]"]:checked');
			const container = $('.hj-goal-type-container');

			// Clear existing dropdowns
			container.empty();

			// Create dropdown for each selected area
			checkedAreas.each((index, checkbox) => {
				const areaValue = $(checkbox).val();
				const areaLabel = this.formatAreaLabel(areaValue);

				const dropdownHtml = `
					<div class="hj-goal-type-wrapper" data-area="${areaValue}">
						<select name="goal_type_${areaValue}" class="hj-goal-select" data-area="${areaValue}">
							<option value="maintain">Maintain</option>
							<option value="improve" selected>Improve</option>
						</select>
						<span class="hj-goal-focus">my ${areaLabel}</span>
					</div>
				`;

				container.append(dropdownHtml);
			});

			// If no areas selected, show default message
			if (checkedAreas.length === 0) {
				container.html('<p class="hj-no-selection">Please select priority areas above to continue.</p>');
			}
		}

		formatAreaLabel(value) {
			// Convert snake_case to readable format
			return value.replace(/_/g, ' ').toLowerCase();
		}

		validateForm() {
			const focusAreas = $('input[name="focus_areas[]"]:checked').length;
			const goalDescription = $('#hj-goal-description').val().trim();

			let isValid = true;
			const errors = [];

			// Clear previous validation states
			$('.hj-form-section').removeClass('hj-error');

			if (focusAreas === 0) {
				$('.hj-priority-options').closest('.hj-form-section').addClass('hj-error');
				errors.push('Please select at least one priority area');
				isValid = false;
			}

			if (focusAreas > 2) {
				$('.hj-priority-options').closest('.hj-form-section').addClass('hj-error');
				errors.push('Please select up to 2 priority areas only');
				isValid = false;
			}

			if (!goalDescription) {
				$('.hj-goal-textarea').closest('.hj-form-section').addClass('hj-error');
				errors.push('Please describe your goal');
				isValid = false;
			}

			if (!isValid) {
				this.showMessage(errors.join(', '), 'error');
			} else {
				this.hideMessage();
			}

			return isValid;
		}

		collectFormData() {
			const focusAreas = [];
			const goalTypes = {};

			// Collect focus areas and their corresponding goal types
			$('input[name="focus_areas[]"]:checked').each(function () {
				const areaValue = $(this).val();
				focusAreas.push(areaValue);

				// Get the goal type for this area
				const goalTypeSelect = $(`.hj-goal-select[data-area="${areaValue}"]`);
				if (goalTypeSelect.length) {
					goalTypes[areaValue] = goalTypeSelect.val();
				}
			});

			const goalDescription = $('#hj-goal-description').val().trim();

			// Determine the single goal type (use the first one if multiple, or the only one)
			const goalTypesArray = Object.values(goalTypes);
			const goalType = goalTypesArray.length > 0 ? goalTypesArray[0] : 'improve';

			return {
				goal_description: goalDescription,
				focus_areas: focusAreas,
				goal_type: goalType
			};
		}

		updateGoal(data) {
			return new Promise((resolve, reject) => {
				$.ajax({
					url: `/wp-json/healthyjoint/v1/goals/${this.goalId}`,
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
						resolve(response);
					})
					.fail((xhr) => {
						let errorMessage = 'An error occurred while updating the goal';

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
			// Show toast notification instead of inline message
			this.showToast('Goal updated successfully!', 'success');

			// Redirect after short delay
			setTimeout(() => {
				const redirectUrl = this.getRedirectUrl();
				if (redirectUrl) {
					window.location.href = redirectUrl;
				}
			}, 2000);
		}

		handleError(error) {
			console.error('Goal update error:', error);
			// Show toast notification for errors
			this.showToast(error.message, 'error');
		}

		setLoadingState(isLoading, customText = null) {
			if (isLoading) {
				this.submitBtn.prop('disabled', true);
				this.submitBtn.find('.hj-btn-text').text(customText || 'Saving...');
				this.submitBtn.find('.hj-btn-spinner').show();
			} else {
				this.submitBtn.prop('disabled', false);
				this.submitBtn.find('.hj-btn-text').text('Save');
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
				console.warn('Message container not found, creating one');
				this.messageContainer = $('<div class="hj-form-message" style="display: none;"><div class="hj-message-content"></div></div>');
				$('#hj-goal-description').after(this.messageContainer);
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

		getApiToken() {
			// Priority 1: Check localStorage first (for iframe compatibility)
			const storedToken = localStorage.getItem('hj_api_token');
			if (storedToken) {
				return storedToken;
			}
			
			// Priority 2: Get API token from localized script
			if (typeof hjEditGoal !== 'undefined' && hjEditGoal.apiToken) {
				console.log(hjEditGoal);
				return hjEditGoal.apiToken;
			}

			// Priority 3: Fallback to meta tag
			const tokenElement = $('meta[name="hj-api-token"]');
			if (tokenElement.length) {
				return tokenElement.attr('content');
			}

			// Return empty string if no token found
			console.warn('API token not found');
			return '';
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

		escapeHtml(text) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return String(text).replace(/[&<>"']/g, m => map[m]);
		}

		getRedirectUrl() {
			// Get redirect URL from module settings or global variable
			if (typeof hjEditGoal !== 'undefined' && hjEditGoal.redirectUrl) {
				return hjEditGoal.redirectUrl;
			}

			// Fallback to data attribute
			const form = $('.hj-edit-goal-form');
			return form.data('redirect-url') || '';
		}
	}
	// Initialize the form handler
	new HJEditGoalForm();

}) (jQuery);