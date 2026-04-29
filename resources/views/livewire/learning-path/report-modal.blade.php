<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important; z-index: 1057;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('learning_path_progress') }}</h5>
                <button wire:click="closeReportModal()" type="button" class="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __t('user') }}</th>
                                <th>{{ __t('progress') }}</th>
                                <th>{{ __t('completed') }}</th>
                                <th>{{ __t('total') }}</th>
                                <th>{{ __t('credit_completed') }}</th>
                                <th>{{ __t('credit_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($learningPathProgress as $item)
                            <tr>
                                <td>{{ $item['user']->firstname ?? '' }} {{ $item['user']->lastname ?? '' }}</td>
                                <td>
                                    <div class="progress" style="height: 1rem;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $item['progress'] ?? 0 }}%;" aria-valuenow="{{ $item['progress'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">{{ ($item['progress'] ?? 0) }}%</div>
                                    </div>
                                </td>
                                <td>{{ $item['completed_count'] ?? 0 }}</td>
                                <td>{{ $item['total_count'] ?? 0 }}</td>
                                <td>
                                    <div class="progress" style="height: 1rem;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $item['credit_progress'] ?? 0 }}%;" aria-valuenow="{{ $item['credit_progress'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">{{ ($item['completed_credit'] ?? 0) }}/{{ ($item['total_credit'] ?? 0) }}</div>
                                    </div>
                                </td>
                                <td>{{ ($item['total_credit'] ?? 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" wire:click="closeReportModal()">{{ __t('close') }}</button>
            </div>
        </div>
    </div>
</div>