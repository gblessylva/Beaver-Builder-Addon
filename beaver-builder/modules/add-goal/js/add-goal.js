/**
 * Add Goal Module Frontend JavaScript
 */

(function ($) {
	'use strict';

	class HJAddGoalForm {
		constructor() {
			this.form = null;
			this.submitBtn = null;
			this.messageContainer = null;
			this.init();
		}

		init() {
			$(document).ready(() => {
				this.bindEvents();
				this.initializeForm();
			});
		}

		initializeForm() {
			// Set up initial state based on pre-selected options
			this.updateGoalTypeDropdowns();

			// Initialize form references
			this.form = $('#hj-goal-form');
			this.submitBtn = this.form.find('.hj-btn-save');
			this.messageContainer = this.form.find('.hj-form-message');

			// Ensure message container exists
			if (!this.messageContainer.length) {
				console.warn('Message container not found, creating one');
				this.messageContainer = $('<div class="hj-form-message" style="display: none;"><div class="hj-message-content"></div></div>');
				this.form.find('.hj-goal-textarea').after(this.messageContainer);
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

			this.submitGoal(formData)
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

			if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
				this.resetForm();
				// Optionally redirect or close modal
				const redirectUrl = this.getRedirectUrl();
				if (redirectUrl) {
					window.location.href = redirectUrl;
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

			// Create title based on goal types and focus areas
			let title = 'Goal: ';
			const goalTypesArray = Object.values(goalTypes);
			const uniqueGoalTypes = [...new Set(goalTypesArray)];

			if (focusAreas.length > 0) {
				if (uniqueGoalTypes.length === 1) {
					// All areas have the same goal type
					title += uniqueGoalTypes[0].charAt(0).toUpperCase() + uniqueGoalTypes[0].slice(1) + ' ' +
						focusAreas.map(area => area.replace(/_/g, ' ')).join(' and ');
				} else {
					// Mixed goal types
					title += 'Mixed goals for ' + focusAreas.map(area => area.replace(/_/g, ' ')).join(' and ');
				}
			} else {
				title += 'Health and wellness';
			}

			return {
				title: title,
				content: goalDescription,
				purpose: uniqueGoalTypes.length === 1 ? uniqueGoalTypes[0] : 'mixed',
				focus_areas: focusAreas,
				goal_types: goalTypes, // Store individual goal types for each area
				status: 'inprogress'
			};
		}

		submitGoal(data) {
			return new Promise((resolve, reject) => {
				$.ajax({
					url: '/wp-json/healthyjoint/v1/goals',
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
						if (response.success) {
							resolve(response);
						} else {
							reject(new Error(response.message || 'Failed to create goal'));
						}
					})
					.fail((xhr) => {
						let errorMessage = 'An error occurred while creating the goal';

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
			// Show success toast notification
			this.showToast('Goal created successfully!', 'success');

			// Redirect to dashboard after 2 seconds
			setTimeout(() => {
				const redirectUrl = this.getRedirectUrl();
				window.location.href = redirectUrl || '/dashboard';
			}, 2000);
		}

		handleError(error) {
			console.error('Goal creation error:', error);
			this.showMessage(error.message, 'error');
		}

		setLoadingState(isLoading) {
			if (isLoading) {
				this.submitBtn.prop('disabled', true);
				this.submitBtn.find('.hj-btn-text').text('Saving...');
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

		resetForm() {
			if (this.form && this.form.length) {
				this.form[0].reset();
				$('.hj-form-section').removeClass('hj-error');
				this.hideMessage();

				// Reset priority options - uncheck all first
				$('input[name="focus_areas[]"]').prop('checked', false);
				$('.hj-priority-option').removeClass('hj-selected');

				// Remove all checkmarks from labels
				$('.hj-priority-label').each(function () {
					const $label = $(this);
					$label.text($label.text().replace('✓ ', ''));
				});

				// Set only "Pain" as selected by default
				const painCheckbox = $('input[name="focus_areas[]"][value="pain"]');
				if (painCheckbox.length) {
					painCheckbox.prop('checked', true);
					const painLabel = painCheckbox.closest('.hj-priority-option');
					painLabel.addClass('hj-selected');
					const labelText = painLabel.find('.hj-priority-label');
					if (!labelText.text().includes('✓')) {
						labelText.text('✓ ' + labelText.text());
					}
				}

				// Clear the goal description textarea
				$('#hj-goal-description').val('');

				// Reset the goal type dropdowns to initial state
				this.updateGoalTypeDropdowns();
			}
		}

		getApiToken() {
			// Get API token from localized script
			if (typeof hjAddGoal !== 'undefined' && hjAddGoal.apiToken) {
				return hjAddGoal.apiToken;
			}

			// Fallback to meta tag
			const tokenElement = $('meta[name="hj-api-token"]');
			if (tokenElement.length) {
				return tokenElement.attr('content');
			}
		}

		getRedirectUrl() {
			// Get redirect URL from module settings or global variable
			if (typeof hjAddGoal !== 'undefined' && hjAddGoal.redirectUrl) {
				return hjAddGoal.redirectUrl;
			}

			// Fallback to data attribute
			const form = $('.hj-add-goal-form');
			return form.data('redirect-url') || '/dashboard';
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
	}

	// Initialize the form handler
	new HJAddGoalForm();

})(jQuery);