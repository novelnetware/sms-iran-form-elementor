jQuery(document).ready(function($) {

    $(document).on('click', '.sms-verification-input', function(e) {
        e.preventDefault();
        const $button = $(this);
        if ($button.prop('disabled')) return;

        const phoneFieldId = $button.data('phone-field');
        const formId = $button.data('form-id');
        const validatePhone = $button.data('validate-phone') === 'yes';
        const lockPhoneField = $button.data('lock-phone-field') === 'yes';
        const countdownText = $button.data('countdown-text');
        const countdownPosition = $button.data('countdown-position');
        const emptyPhoneMessage = $button.data('empty-phone-message');
        const invalidPhoneMessage = $button.data('invalid-phone-message');
        
        const $phoneField = $(`#form-field-${phoneFieldId}`);
        const $fieldGroup = $phoneField.closest('.elementor-field-group');
        const phoneNumber = $phoneField.val() ? $phoneField.val().trim() : '';

        // Clear any previous messages
        clearFieldMessage($fieldGroup);

        // Disable button and show loading state
        $button.prop('disabled', true)
               .data('original-text', $button.val())
               .val(smsVerificationSettings.sending_text);

        // اطمینان از اینکه فیلد شماره موبایل تا زمانی که نتیجه دریافت نشود، قفل نشده باشد
        $phoneField.prop('readonly', false);

        $.ajax({
            url: smsVerificationSettings.ajax_url,
            type: 'POST',
            data: {
                action: 'send_verification_code',
                security: smsVerificationSettings.nonce,
                phone_number: phoneNumber,
                token: $button.data('token'),
                form_id: formId
            },
            success: function(response) {
                if (response.success) {
                    // Success - start countdown
                    const expiryTime = response.data.expiry_time;
                    startCountdown($button, expiryTime, countdownText, countdownPosition);
                    
                    // تنها در صورتی که lockPhoneField فعال باشد، فیلد شماره موبایل قفل شود
                    if (lockPhoneField) {
                        $phoneField.prop('readonly', true);
                    }
                    
                    // Show success message
                    showFieldMessage($fieldGroup, response.data.message, 'success');
                    
                    // Hide success message after 3 seconds
                    setTimeout(() => {
                        clearFieldMessage($fieldGroup);
                    }, 3000);
                } else {
                    // Error handling
                    resetButton($button);
                    $phoneField.prop('readonly', false); // Ensure field is unlocked on error
                    
                    if (response.data.type === 'empty_phone') {
                        showFieldMessage($fieldGroup, emptyPhoneMessage || 'لطفاً شماره موبایل را وارد کنید', 'danger');
                    } else if (response.data.type === 'invalid_phone' && validatePhone) {
                        showFieldMessage($fieldGroup, invalidPhoneMessage || 'شماره موبایل وارد شده معتبر نیست', 'danger');
                    } else {
                        showFieldMessage($fieldGroup, response.data.message || 'خطایی در ارسال کد رخ داده است', 'danger');
                    }
                }
            },
            error: function(xhr) {
                resetButton($button);
                $phoneField.prop('readonly', false); // Ensure field is unlocked on error
                const errorMsg = xhr.responseJSON?.data?.message || 'خطایی در ارسال کد رخ داده است';
                showFieldMessage($fieldGroup, errorMsg, 'danger');
            }
        });
    });

    // Countdown function
    function startCountdown($button, seconds, countdownText, position) {
        let remaining = seconds;
        const originalText = $button.data('original-text');
        const $phoneField = $(`#form-field-${$button.data('phone-field')}`);
        
        $button.prop('disabled', true);
        
        let displayText = position === 'before' ? 
            countdownText + ' ' + remaining : 
            remaining + ' ' + countdownText;
        $button.val(displayText);

        // Clear any existing interval
        if ($button.data('countdownInterval')) {
            clearInterval($button.data('countdownInterval'));
        }

        const interval = setInterval(function() {
            remaining--;
            
            displayText = position === 'before' ? 
                countdownText + ' ' + remaining : 
                remaining + ' ' + countdownText;
            
            $button.val(displayText);
            
            if (remaining <= 0) {
                clearInterval(interval);
                $button.data('countdownInterval', null);
                resetButton($button, originalText);
                $phoneField.prop('readonly', false); // Unlock when countdown ends
            }
        }, 1000);

        $button.data('countdownInterval', interval);
    }

    // Show field message (error or success)
    function showFieldMessage($fieldGroup, message, type = 'danger') {
        clearFieldMessage($fieldGroup);
        
        $fieldGroup.addClass(`elementor-${type === 'danger' ? 'error' : type}`);
        
        const $message = $(`
            <span class="elementor-message elementor-message-${type} elementor-help-inline elementor-form-help-inline" role="alert">
                ${message}
            </span>
        `);
        
        $fieldGroup.find('.elementor-field').after($message);
        $fieldGroup.find('.elementor-field').attr('aria-invalid', type === 'danger' ? 'true' : 'false');
    }

    // Clear field messages
    function clearFieldMessage($fieldGroup) {
        $fieldGroup.removeClass('elementor-error elementor-success');
        $fieldGroup.find('.elementor-message').remove();
        $fieldGroup.find('.elementor-field').attr('aria-invalid', 'false');
    }

    // Reset button to initial state
    function resetButton($button, text = null) {
        $button.prop('disabled', false)
               .val(text || $button.data('original-text'));
    }

    // New code verification handler
    $(document).on('input', '.elementor-field-textual[data-verify-sms]', function(e) {
        const $codeField = $(this);
        const code = $codeField.val().trim();
        const $form = $codeField.closest('.elementor-form');
        const formId = $form.data('elementor-id');
        const phoneFieldId = $form.find('.sms-verification-input').data('phone-field');
        const $phoneField = $(`#form-field-${phoneFieldId}`);
        const phoneNumber = $phoneField.val().trim();
        const $fieldGroup = $codeField.closest('.elementor-field-group');

        if (code.length < 4) return; // Wait until minimum code length

        clearFieldMessage($fieldGroup);

        $.ajax({
            url: smsVerificationSettings.ajax_url,
            type: 'POST',
            data: {
                action: 'verify_sms_code',
                security: smsVerificationSettings.nonce,
                phone_number: phoneNumber,
                code: code,
                form_id: formId
            },
            success: function(response) {
                if (response.success) {
                    // Disable both fields on success
                    $phoneField.prop('readonly', true);
                    $codeField.prop('readonly', true);
                    showFieldMessage($fieldGroup, response.data.message, 'success');
                    
                    // Disable the send button
                    $form.find('.sms-verification-input').prop('disabled', true);
                } else {
                    let message = response.data.message;
                    if (response.data.remaining_time) {
                        message += ` (${response.data.remaining_time} ثانیه باقی مانده)`;
                    }
                    showFieldMessage($fieldGroup, message, 'danger');
                }
            },
            error: function(xhr) {
                showFieldMessage($fieldGroup, 'خطا در بررسی کد!', 'danger');
            }
        });
    });

    
});
