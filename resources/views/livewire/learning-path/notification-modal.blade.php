<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('notification_settings') }} - {{ $selectedPathId }}</h5>
                <button wire:click="closeNotificationModal()" type="button" class="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="notificationData.enrollment_enable" 
                                           wire:change="refreshForm"
                                           id="enrollmentEnable">
                                    <label class="form-check-label" for="enrollmentEnable">{{ __t('enrollment_notification') }}</label>
                                </div>
                            </div>
                            <div class="card-body enrollment-body" style="{{ !empty($notificationData['enrollment_enable']) ? '' : 'display:none;' }}">
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('email_template') }}</label>
                                    <p class="text-muted small">{{ __t('available_variables') }}: {{ '{{' }}user_fullname}}, {{ '{{' }}learning_path_name}}, {{ '{{' }}learning_path_startdate}}, {{ '{{' }}learning_path_enddate}}, {{ '{{' }}learning_path_coursesrequired}}</p>
                                    <textarea id="enrollment_template" class="form-control tiny-mce" wire:model="notificationData.enrollment_mail_templates" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="notificationData.expiration_enable" 
                                           wire:change="refreshForm"
                                           id="expirationEnable">
                                    <label class="form-check-label" for="expirationEnable">{{ __t('expiration_notification') }}</label>
                                </div>
                            </div>
                            <div class="card-body expiration-body" style="{{ !empty($notificationData['expiration_enable']) ? '' : 'display:none;' }}">
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('email_template') }}</label>
                                    <textarea id="expiration_template" class="form-control tiny-mce" wire:model="notificationData.expiration_mail_templates" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="notificationData.enrollment_reminder_enable" 
                                           wire:change="refreshForm"
                                           id="enrollmentReminderEnable">
                                    <label class="form-check-label" for="enrollmentReminderEnable">{{ __t('enrollment_reminder') }}</label>
                                </div>
                            </div>
                            <div class="card-body enrollment-reminder-body" style="{{ !empty($notificationData['enrollment_reminder_enable']) ? '' : 'display:none;' }}">
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('day_after_enrollment') }}</label>
                                    <input type="number" class="form-control" wire:model="notificationData.day_after_enrollment" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('email_template') }}</label>
                                    <textarea id="enrollment_reminder_template" class="form-control tiny-mce" wire:model="notificationData.enrollment_reminder_mail_templates" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="notificationData.expiration_reminder_enable" 
                                           wire:change="refreshForm"
                                           id="expirationReminderEnable">
                                    <label class="form-check-label" for="expirationReminderEnable">{{ __t('expiration_reminder') }}</label>
                                </div>
                            </div>
                            <div class="card-body expiration-reminder-body" style="{{ !empty($notificationData['expiration_reminder_enable']) ? '' : 'display:none;' }}">
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('day_before_expiration') }}</label>
                                    <input type="number" class="form-control" wire:model="notificationData.day_before_expiration" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('email_template') }}</label>
                                    <textarea id="expiration_reminder_template" class="form-control tiny-mce" wire:model="notificationData.expiration_reminder_mail_templates" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="notificationData.day_frequency_enable" 
                                           wire:change="refreshForm"
                                           id="dayFrequencyEnable">
                                    <label class="form-check-label" for="dayFrequencyEnable">{{ __t('enable_reminder') }}</label>
                                </div>
                            </div>
                            <div class="card-body day-frequency-body" style="{{ !empty($notificationData['day_frequency_enable']) ? '' : 'display:none;' }}">
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('reminder_interval_days') }}</label>
                                    <input type="number" class="form-control" wire:model="notificationData.day_frequency" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('email_template') }}</label>
                                    <textarea id="day_frequency_template" class="form-control tiny-mce" wire:model="notificationData.day_frequency_mail_templates" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="notificationData.completion_path_enable" 
                                           wire:change="refreshForm"
                                           id="completionPathEnable">
                                    <label class="form-check-label" for="completionPathEnable">{{ __t('enable_completion_notification') }}</label>
                                </div>
                            </div>
                            <div class="card-body completion-body" style="{{ !empty($notificationData['completion_path_enable']) ? '' : 'display:none;' }}">
                                <div class="mb-3">
                                    <label class="form-label">{{ __t('email_template') }}</label>
                                    <textarea id="completion_template" class="form-control tiny-mce" wire:model="notificationData.completion_path_mail_templates" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="saveNotificationSettings()" class="btn btn-primary" wire:loading.attr="disabled">
                    <i class="bi bi-check-circle" wire:loading.remove></i>
                    <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                    {{ __t('save') }}
                </button>
                <button wire:click="closeNotificationModal()" class="btn btn-secondary">{{ __t('cancel') }}</button>
            </div>
        </div>
    </div>
</div>

@script
<script>
setTimeout(() => {
    document.querySelectorAll('textarea.tiny-mce').forEach(textarea => {
        if (typeof tinymce !== 'undefined' && !tinymce.get(textarea.id)) {
            tinymce.init({
                selector: '#' + textarea.id,
                language: 'vi',
                language_url: 'https://cdn.jsdelivr.net/npm/tinymce@6/langs/vi.js',
                plugins: 'lists link image table codesample fullscreen',
                toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | fullscreen',
                menubar: false,
                height: 300,
                setup: function(editor) {
                    editor.on('change', function() {
                        textarea.value = editor.getContent();
                        textarea.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    editor.on('blur', function() {
                        textarea.value = editor.getContent();
                        textarea.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }
            });
        }
    });
}, 100);
</script>
@endscript